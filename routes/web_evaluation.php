<?php

use App\Http\Controllers\AssertionController;
use App\Http\Controllers\PhaseController;
use App\Http\Controllers\QuestionController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth')->group(function () {

    Route::get('phase/create', [PhaseController::class,'create'])->name('phase.create');
    Route::get('phase/{id}', [PhaseController::class,'evenementPhase'])->name('phase.show');
    // Route::get('phase/{evenement}', [PhaseController::class,'editPhase'])->name('phases.edite');

    Route::get('phase/encours', [PhaseController::class,'encours'])->name('phase.encours');
    Route::get('phase/active', [PhaseController::class,'active'])->name('phase.active');
    Route::get('phase/desactive', [PhaseController::class,'desactive'])->name('phase.desactive');
    
    Route::get('phase/attente', [PhaseController::class,'enAttente'])->name('phase.attente');
    Route::get('phase/pause', [PhaseController::class,'pause'])->name('phase.pause');
    Route::get('phase/terminer', [PhaseController::class,'terminer'])->name('phase.terminer');

     Route::resource('phases', PhaseController::class);
     Route::resource('questions', QuestionController::class);
     Route::resource('assertions', AssertionController::class);
   
});

require __DIR__.'/auth.php';
