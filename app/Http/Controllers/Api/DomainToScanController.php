<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DomainToScan;

class DomainToScanController extends Controller
{
    public function index()
    {
        // Возвращает список всех domains_to_scan
        return DomainToScan::all();
    }

    public function store(Request $request)
    {
        // Создает новую запись domains_to_scan
        return DomainToScan::create($request->all());
    }

    public function show($id)
    {
        // Возвращает конкретную запись domains_to_scan по $id
        return DomainToScan::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        // Обновляет конкретную запись domains_to_scan по $id
        $domain = DomainToScan::findOrFail($id);
        $domain->update($request->all());

        return $domain;
    }

    public function destroy($id)
    {
        // Удаляет конкретную запись domains_to_scan по $id
        $domain = DomainToScan::findOrFail($id);
        $domain->delete();

        return 204; // HTTP Response Code 204 - No Content
    }
}
