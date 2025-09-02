<?php

namespace App\Http\Controllers\Api;

use App\Models\MonitoringLog;
use Illuminate\Http\Request;

class MonitoringLogController extends ApiController
{
    public function index(Request $request)
    {
        $query = MonitoringLog::orderBy('created_at', 'desc')->orderBy('id', 'desc');
        return $this->paginate($request, $query);
    }
}
