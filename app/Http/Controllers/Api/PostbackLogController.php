<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PostbackLog;

class PostbackLogController extends Controller
{

    public function index()
    {
        return PostbackLog::all();
    }

    public function store(Request $request)
    {
        return PostbackLog::create($request->all());
    }

    public function show($id)
    {
        return PostbackLog::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $log = PostbackLog::findOrFail($id);
        $log->update($request->all());

        return $log;
    }

    public function destroy($id)
    {
        $log = PostbackLog::findOrFail($id);
        $log->delete();

        return 204; // HTTP Response Code 204 - No Content
    }
}
