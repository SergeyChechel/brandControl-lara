<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use App\Http\Traits\HelperFunctionsTrait;
use Illuminate\Support\Facades\Session;
use App\Models\DomainToScan;
use Illuminate\Support\Facades\File;

class ScanController extends Controller
{
    use HelperFunctionsTrait;

    public function index(Request $request)
    {
//        date_default_timezone_set(config('brandControl.timezone'));
        $data = '[' . date('d.m.Y H:i') . '] ' . 'триггер URL с GET параметрами: ' . $request->fullUrl();

        $logPath = storage_path('logs/scan_triggers.log');
        File::append($logPath, "$data\r\n" . PHP_EOL);

        $domains = Config::get('brandControl.domains');
        $selectedDomain = $domains[array_rand($domains)];

        $userAgent = urlencode($request->userAgent());
        $ip = $this->getUserIP();

        Session::put('ua', $userAgent);
        Session::put('ip', $ip);
        Session::put('domain', $selectedDomain);

        if ($selectedDomain) {
            $domainToScan = new DomainToScan();
            $domainToScan->domain = $selectedDomain;
            $domainToScan->geo = 'geo';
            $domainToScan->status = true;
            $domainToScan->save();
            Session::put('sid', $domainToScan->id);

            $redirect_url = "/demand?q=" . urlencode(strtolower($selectedDomain));
            $request->session()->put('redirected_from_scans', true);
            return redirect($redirect_url);
        } else {
            return response()->json(['error' => 'Can not get the domain'], 404);
        }
    }
}
