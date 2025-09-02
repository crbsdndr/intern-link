<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    protected function paginate(Request $request, $query)
    {
        $limit = (int) $request->query('limit', 10);
        if ($limit !== 10) {
            $limit = 10;
        }
        $page = (int) $request->query('page', 1);
        if ($page < 1) {
            $page = 1;
        }
        $total = $query->count();
        $totalPages = $limit > 0 ? (int) ceil($total / $limit) : 0;
        if ($page > $totalPages) {
            $page = $totalPages;
            $data = [];
            $shown = 0;
        } else {
            $data = $query->skip(($page - 1) * $limit)->take($limit)->get();
            $shown = count($data);
        }

        return response()->json([
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => $totalPages,
                'shown' => $shown,
            ],
        ]);
    }
}
