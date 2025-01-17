<?php

use App\Http\Controllers\Api\CritereController;
use App\Http\Middleware\IntervenantTokenIsValid;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EvenementController;
use App\Http\Controllers\Api\IntervenantController;
use App\Http\Controllers\Api\IntervenantPhaseController;
use App\Http\Controllers\Api\PhaseCritereController;
use App\Http\Controllers\SwaggerController;
use App\Http\Middleware\JuryTokenIsValid;

Route::post('/login', [SwaggerController::class ,'login']);
Route::post('/register', [SwaggerController::class ,'register']);
Route::apiResource('evenements', EvenementController::class, ['as'=>'api']);
Route::apiResource('criteres', CritereController::class, ['as'=>'api']);
Route::apiResource('intervenants', IntervenantController::class, ['as'=>'api']);
Route::apiResource('phase-criteres', PhaseCritereController::class, ['as'=>'api']);
Route::apiResource('intervenant-phases', IntervenantPhaseController::class, ['as'=>'api'])->middleware(JuryTokenIsValid::class);

Route::post('/intervenants-authenticate', [IntervenantController::class, 'authenticate']);