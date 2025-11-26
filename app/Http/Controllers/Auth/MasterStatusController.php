<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterStatus;

class MasterStatusController extends Controller
{
    public function index()
    {
        $master_status = MasterStatus::all(['code', 'name']);

        return response()->json([
            'success' => true,
            'data' => $master_status
        ]);
    }
}
