<?php

use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PdfAnalysisController;
Route::post('/generate-pdf', [PdfController::class, 'generate']);

Route::prefix('pdf')->group(function () {
    Route::post('/analyze', [PdfAnalysisController::class, 'analyze']);
    Route::post('/compare', [PdfAnalysisController::class, 'compare']);
    Route::get('/mappings/{ngo}', [PdfAnalysisController::class, 'getMapping']);
    Route::get('/ngos', [PdfAnalysisController::class, 'getSupportedNgos']);
    Route::get('/test', [PdfAnalysisController::class, 'test']);
});