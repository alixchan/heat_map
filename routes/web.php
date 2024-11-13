<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UpdateController;
use App\Http\Controllers\AnalysisController;


Route::view('/', 'welcome');


# АНАЛИТИКА
Route::name('upload.')->group(function () {
    Route::post('/esis', [AnalysisController::class, 'uploadActualFile'])->name('act_esis.file');
    Route::post('/ke_esis', [AnalysisController::class, 'uploadActualKe'])->name('act_ke.file');

    Route::post('/br', [AnalysisController::class, 'uploadBrFile'])->name('br.file');
    Route::post('/bs', [AnalysisController::class, 'uploadBsFile'])->name('bs.file');

    Route::post('/import', [AnalysisController::class, 'uploadImportFile'])->name('import.file');
    Route::post('/ke_import', [AnalysisController::class, 'uploadImportKe'])->name('import_ke.file');
});

Route::name('download.')->controller(AnalysisController::class)->group(function () {
    Route::get('/download_esis/{uid}', 'downloadAnalysisEsis')->name('esis');
    Route::get('/download_bs/{uid}', 'downloadAnalysisBs')->name('bs');
    Route::get('/download_br/{uid}', 'downloadAnalysisBr')->name('br');
    Route::get('/download_import/{uid}', 'downloadAnalysisImport')->name('import');
});

Route::view('/esis', 'analysis_esis')->name('esis.page');
Route::view('/bs', 'analysis_bs')->name('bs.page');
Route::view('/br', 'analysis_br')->name('br.page');
Route::view('/import', 'analysis_import')->name('import.page');


# ЗАГРУЗКА
Route::prefix('upload')->name('upload.')->controller(UpdateController::class)->group(function () {
    Route::view('/esis', 'upload', [
        'name' => 'ЕСИС', 
        'function' => '/upload/esis',
        'type' => 'esisFile'
    ])->name('esis.view');

    Route::view('/sw', 'upload', [
        'name' => 'SolarWinds', 
        'function' => '/upload/sw',
        'type' => 'swFile'
    ])->name('sw.view');

    Route::view('/kt670', 'upload', [
        'name' => 'KT-670', 
        'function' => '/upload/kt670',
        'type' => 'ktFile'
    ])->name('kt.view');

    Route::view('/cmdb', 'upload', [
        'name' => 'CMDB', 
        'function' => '/upload/cmdb',
        'type' => 'cmdbFile'
    ])->name('cmdb.view');

    Route::view('/dns', 'upload', [
        'name' => 'DNS', 
        'function' => '/upload/dns',
        'type' => 'dnsFile'
    ])->name('dns.view');

    Route::view('/esis_full', 'upload', [
        'name' => 'esis_full', 
        'function' => '/upload/esis_full',
        'type' => 'esisFullFile'
    ])->name('esis_full.view');

    Route::post('/esis', 'uploadEsisFile')->name('esis.file');
    Route::post('/esis_full', 'uploadEsisFullFile')->name('esis_full.file');

    Route::post('/sw', 'uploadSwFile')->name('sw.file');
    Route::post('/kt670', 'uploadKtFile')->name('kt.file');
    Route::post('/cmdb', 'uploadCmdbFile')->name('cmdb.file');
    Route::post('/dns', 'uploadDnsFile')->name('dns.file');
});