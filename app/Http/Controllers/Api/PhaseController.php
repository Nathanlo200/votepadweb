<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Phase;
use Illuminate\Http\Request;

class PhaseController extends Controller
{
    public function index(){
        $phase = Phase::all();
        return response()->json($phase);
    }
}
