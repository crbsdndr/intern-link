<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MetaController extends Controller
{
    public function monitorTypes()
    {
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            $types = collect(DB::select("SELECT unnest(enum_range(NULL::monitor_type_enum)) AS type"))->pluck('type');
        } else {
            $types = collect(['weekly','issue','final','other']);
        }
        return response()->json($types);
    }

    public function supervisors(Request $request)
    {
        $query = DB::table('supervisor_details_view')->select('id','name')->orderBy('name');
        if ($request->filled('internship_id')) {
            $query->join('internship_supervisors','supervisor_details_view.id','=','internship_supervisors.supervisor_id')
                  ->where('internship_supervisors.internship_id', $request->internship_id);
        }
        $supervisors = $query->get();
        if ($supervisors->isEmpty()) {
            $supervisors = DB::table('supervisor_details_view')->select('id','name')->orderBy('name')->get();
        }
        return response()->json($supervisors);
    }
}
