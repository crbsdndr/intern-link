<?php

namespace App\Http\Controllers\Api;

use App\Models\Application;
use Illuminate\Http\Request;

class ApplicationController extends ApiController
{
    public function index(Request $request)
    {
        $query = Application::orderBy('created_at', 'desc')->orderBy('id', 'desc');
        return $this->paginate($request, $query);
    }
}
