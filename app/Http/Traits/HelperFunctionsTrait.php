<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

trait HelperFunctionsTrait {

    function getUserIP() {
        // Default to the connecting IP
        $ip = $_SERVER['REMOTE_ADDR'];

        // Check if X-Forwarded-For header exists and is not empty
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
            // X-Forwarded-For can contain a comma-separated list of IPs; the first one is the client's
            $xff = $_SERVER['HTTP_X_FORWARDED_FOR'];
            $ip_list = explode(',', $xff);

            // Trim each IP in case there are spaces around it
            $ip = trim($ip_list[0]);

            // Validate the IP to ensure it's IPv4
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                // If the IP from X-Forwarded-For is not valid, fallback to REMOTE_ADDR
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        }

        return $ip;
    }


    function getADN($sid, $ua, $ip, $brand, $brandsrc) {
//    $real_url = "http://gnfkixpxnhboms.rp.lowtide.fun/api/v1/srtb?sid={$sid}&ua={$ua}&ip={$ip}&brand={$brand}&brandct=5&brandsrc={$brandsrc}&to=120&li=1&cc=1"; // реальный урл
        $test_url = "https://iphosxpym7pu50.rp.lowtide.fun/api/v1/srtb?ip=2.152.113.201&ua=Mozilla%2F5.0%20%28Windows%20NT%2010.0%3B%20Win64%3B%20x64%29%20AppleWebKit%2F537.36%20%28KHTML%2C%20like%20Gecko%29%20Chrome%2F126.0.0.0%20Safari%2F537.36&sid=FAB&brand=booking.com&brandct=5&l=es&cc=1"; // реальный урл
//    $url = "http://iphosxypm7pu50.rp.lowtide.fun/api/v1/srtb?sid={$sid}&ua={$ua}&ip={$ip}&brand={$brand}&brandct=5&brandsrc={$brandsrc}&to=120&li=1";

        $url = $test_url;

        date_default_timezone_set(config('app.timezone'));
        $time = '[' . date('d.m.Y H:i') . '] ';
        $logPath = storage_path('logs/ADN_requests.log');
        File::append($logPath, "$time Requested_url: $url" . PHP_EOL);

        session(['ADN_url' => $url]); // Установка значения в сессию в Laravel


        $response = Http::get($url);
        $http_code = $response->status(); // Получаем HTTP код ответа

        if ($response->failed()) {
            $response_data = [
                'http_code' => $http_code,
                'data' => ''
            ];
        } else {
            switch ($http_code) {
                case 400:
                    $error = 'Bad Request: The server could not understand the request due to invalid syntax or parameters.';
                    break;
                case 401:
                    $error = 'Unauthorized: Authentication is required and has failed or has not yet been provided.';
                    break;
                case 404:
                    $error = 'Not Found: The requested resource could not be found on the server.';
                    break;
                // Добавьте другие коды ошибок, если необходимо
            }
            $response_data = [
                'http_code' => $http_code,
                'error' => $error ?? '',
                'data' => $response->json()
            ];
        }

        return $response_data;
    }


    function extractADNOfferUrl($responseArray) {

        if (isset($responseArray['seatbid']) && !empty($responseArray['seatbid'])) {
            $seatbid = $responseArray['seatbid'][0];

            if (isset($seatbid['bid']) && !empty($seatbid['bid'])) {
                $bid = $seatbid['bid'][0];

                if (isset($bid['adm'])) {
                    // Возвращаем значение 'adm'
                    return $bid['adm'];
                }
            }
        }

        return null;
    }


    function extractDomainFromUrl($url) {
        $url_without_protocol = preg_replace('#^https?://#', '', $url);
        preg_match('/^([a-z0-9-]+\.)*([a-z0-9-]+\.[a-z]{2,})(:\d+)?(.*)?$/', $url_without_protocol, $matches);
        if (isset($matches[2])) {
            return $matches[2];
        } else {
            return null;
        }
    }


    public function sendAlertEmail($domain_name, $detected_domain, $landing_url, $ad_secure_link, $alert, $to = 'compliance@yeesshh.com')
    {
        $subject = $body = '';

        if ($alert == '2') {
            $subject = 'Mismatch - Wrong domain detected';
            $body = "A mismatch has been detected, the intended domain was $domain_name, the detected domain was $detected_domain\n";
            $body .= "Here is the full path: " . $landing_url . "\n";
            $body .= "Link to AdSecure: " . $ad_secure_link . "\n";
        } elseif ($alert == '1') {
            $subject = 'ALERT - Violation Detected!';
            $body = "A violation has been detected on our Brand Demand: " . (session('violations') ?? '');
            $body .= "Here is the full path: " . $landing_url . "\n";
            $body .= "Link to AdSecure: " . $ad_secure_link . "\n";
        } elseif ($alert == '12') {
            $this->sendAlertEmail($domain_name, $detected_domain, $landing_url, $ad_secure_link, '1', $to);
            $this->sendAlertEmail($domain_name, $detected_domain, $landing_url, $ad_secure_link, '2', $to);
            return;
        }

        $emailFrom = Config::get('brandControl.email_from');
        $emailReplyTo = Config::get('brandControl.email_reply_to');
        $emailContentType = Config::get('brandControl.email_content_type');

        // Send email using Laravel's Mail facade
        Mail::raw($body, function ($message) use ($to, $subject, $emailFrom, $emailReplyTo) {
            $message->to($to)
                ->subject($subject)
                ->from($emailFrom)
                ->replyTo($emailReplyTo);
        });

        return "Email sent successfully to $to";
    }

}
