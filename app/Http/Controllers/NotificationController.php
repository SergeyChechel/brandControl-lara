<?php

namespace App\Http\Controllers;

use App\Models\ComplianceNotification;
use App\Models\PostbackLog;
use Illuminate\Http\Request;
use App\Http\Traits\HelperFunctionsTrait;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    use HelperFunctionsTrait;

    public function index(Request $request)
    {
        $json_data = $request->getContent();
        $data = json_decode($json_data);

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            return response('Ошибка при декодировании JSON', 400);
        }

        $dataIsStdClass = false;
        $url = $data->report ?? '';
        if ($url) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_ENCODING, "gzip");
            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                return response('Ошибка при выполнении запроса: ' . curl_error($ch), 500);
            }
            curl_close($ch);
            $data = json_decode($response, true);
        } else {
            $dataIsStdClass = !is_array($data);
            if (!$dataIsStdClass) {
                return response("Уведомление AdSecure не содержит урла для получения данных скана: $data", 400);
            }
        }

        $dataArray = $this->convertToArray($data);

        // Валидация структуры данных
        $validator = Validator::make( $dataArray, [
            'reports' => 'required|array',
            'reports.*.frames' => 'required|array',
            'reports.*.detections' => 'required|array',
            'violation' => 'required|boolean',
            'id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid data structure', 'messages' => $validator->messages()], 400);
        }

        date_default_timezone_set(config('brandControl.timezone'));
        $time = '[' . date('d.m.Y H:i') . '] ';
        $log_message = $time . ($dataIsStdClass ? json_encode($data, JSON_PRETTY_PRINT) : $response) . "\r\n";
        $logPath = storage_path('logs/adsecure_notification.log');
        File::append($logPath, "$log_message\r\n" . PHP_EOL);

        $frames = !$dataIsStdClass ? $data['reports'][0]['frames'] : $data->reports[0]->frames;
        $detections = !$dataIsStdClass ? $data['reports'][0]['detections'] : $data->reports[0]->detections;

        if (!isset($frames) || !is_array($frames) || !isset($detections) || !is_array($detections)) {
            return response('Отсутствуют или неверный формат данных "frames" или "detections"', 400);
        }

        $alert = '';
        $landing_url = $violations = $q_domain = '';

        $history = !$dataIsStdClass ? $frames[0]['history'] : $frames[0]->history;
        if (isset($history)) {
            $lastLanding = null;
            foreach ($history as $historyItem) {
                if (!$q_domain) {
                    preg_match('/q=([^&]*)/', !$dataIsStdClass ? $historyItem['data'] : $historyItem->data, $matches);
                    if (isset($matches[1])) $q_domain = $matches[1];
                }
                if ((!$dataIsStdClass ? $historyItem['type'] : $historyItem->type) === 'LANDING') {
                    $lastLanding = $historyItem;
                }
            }
            $landing_url = !$dataIsStdClass ? $lastLanding['data'] : $lastLanding->data;
        }

        $violation = !$dataIsStdClass ? $data['violation'] : $data->violation;
        if (isset($violation) && $violation === true) {
            $violations = "Security Violations Found:\n";
            // Перебираем и выводим все найденные нарушения безопасности
            foreach ($detections as $detection) {
                if ((!$dataIsStdClass ? $detection['violation'] : $detection->violation) === true) {
                    $violations .= " - " . (!$dataIsStdClass ? $detection['name'] : $detection->name) . "\n";
                }
            }
            $alert .= '1';
        }


        $landing_domain = $this->extractDomainFromUrl($landing_url);
        if ($q_domain !== $landing_domain) $alert .= '2';

        $alert .= '12';

        $emailTo = Config::get('brandControl.email_to');
        $adSecureLink = Config::get('brandControl.ad_secure_link');

        $notification_id = !$dataIsStdClass ? $data['id'] : $data->id;
        $ad_secure_link = $adSecureLink . $notification_id;

        if ($alert) {
            if (strpos($alert, '2') !== false) {
                $complianceNotification = new ComplianceNotification();
                $complianceNotification->scan_id = !$dataIsStdClass ? $data['id'] : $data->id;
                $complianceNotification->intended_domain = $q_domain;
                $complianceNotification->detected_domain = $landing_domain;
                $complianceNotification->full_path = $landing_url;
                $complianceNotification->save();
            }

            $this->sendAlertEmail($q_domain, $landing_domain, $landing_url, $ad_secure_link, $alert, $emailTo);

            $postbackLog = new PostbackLog();
            $postbackLog->notification_id = $complianceNotification->id;
            $postbackLog->json_data = $json_data;
            $postbackLog->save();
        }
    }
}
