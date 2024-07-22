<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ComplianceNotification;

class ComplianceNotificationController extends Controller
{
    public function index()
    {
        return ComplianceNotification::all();
    }

    public function store(Request $request)
    {
        return ComplianceNotification::create($request->all());
    }

    public function show($id)
    {
        return ComplianceNotification::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $notification = ComplianceNotification::findOrFail($id);
        $notification->update($request->all());

        return $notification;
    }

     public function destroy($id)
    {
        $notification = ComplianceNotification::findOrFail($id);
        $notification->delete();

        return 204; // HTTP Response Code 204 - No Content
    }
}
