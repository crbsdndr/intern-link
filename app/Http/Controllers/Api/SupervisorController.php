<?php

namespace App\Http\Controllers\Api;

use App\Models\Supervisor;
use Illuminate\Http\Request;

class SupervisorController extends ApiController
{
    public function index(Request $request)
    {
        $query = Supervisor::with('user')->orderBy('created_at', 'desc')->orderBy('id', 'desc');
        return $this->paginate($request, $query);
    }
}
