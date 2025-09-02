<?php

namespace App\Http\Controllers\Api;

use App\Models\Internship;
use Illuminate\Http\Request;

class InternshipController extends ApiController
{
    public function index(Request $request)
    {
        $query = Internship::orderBy('created_at', 'desc')->orderBy('id', 'desc');
        return $this->paginate($request, $query);
    }
}
