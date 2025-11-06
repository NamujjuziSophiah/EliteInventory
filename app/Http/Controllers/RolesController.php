<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolesController extends Controller
{
    // Return array of taken roles
    public function taken()
    {
        $roles = DB::table('users')->select('role')->whereNotNull('role')->whereNull('deleted_at')->pluck('role')->unique()->values();
        return response()->json(['taken' => $roles]);
    }
}
