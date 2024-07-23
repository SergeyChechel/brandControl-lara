<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\HelperFunctionsTrait;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class DemandController extends Controller
{
    use HelperFunctionsTrait;

    public function index(Request $request)
    {
        if (!$request->session()->has('redirected_from_scans') ||
            !($request->session()->get('redirected_from_scans') === true) ||
            !$request->has('q')) {
            return 'Редирект не произошел с /scans либо не передан q';
        }
        $request->session()->forget('redirected_from_scans');

        if ($request->session()->has('last_adn_request_time') &&
            microtime(true) - $request->session()->get('last_adn_request_time') < 1) {
            return "Запрос оффера к getADN можно делать не чаще 1 раза в секунду. Подождите немного.";
        }

        if ($request->session()->has('sid') &&
            $request->session()->has('ua') && $request->session()->has('ip')) {
            $validator = Validator::make($request->all(), [
                'q' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors(),
                ], 422);
            }

            $validated_q = $request->input('q');
            $ADN_response = $this->getADN($request->session()->get('sid'), $request->session()->get('ua'),
                $request->session()->get('ip'), $validated_q, $validated_q);
            $request->session()->put('last_adn_request_time', microtime(true));
        } else {
            return 'Не хватает данных для выполнения запроса к ADN.';
        }

        $log_message = "our selected domain: $validated_q, ADN_response on offer request: " . json_encode($ADN_response);
        $logPath = storage_path('logs/ADN_requests.log');
        File::append($logPath, "$log_message\r\n" . PHP_EOL);

        if ($ADN_response['http_code'] == 204) {
            return response('No Content', 204);
        } elseif ($ADN_response['http_code'] >= 200 && $ADN_response['http_code'] < 300 && isset($ADN_response['data'])) {
            $ADN_offer_url = $this->extractADNOfferUrl($ADN_response['data']);
            if (filter_var($ADN_offer_url, FILTER_VALIDATE_URL)) {
                return redirect($ADN_offer_url);
            }
        } elseif ($ADN_response['http_code'] >= 300) {
            return "Ошибка запроса к ADN. Код ошибки " . $ADN_response['http_code'] . $ADN_response['data'];
        }
    }
}
