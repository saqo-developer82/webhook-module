<?php

use App\WebhookModule\Http\Controllers\WebhookGlobalTriggersController;
use App\WebhookModule\Http\Controllers\WebhooksController;
use App\WebhookModule\Http\Controllers\WebhookTriggerValuesController;
use Illuminate\Support\Facades\Route;

$v = 'v2.5';
Route::prefix('/' . $v . '/webhook-global-trigger')->group(function () {
    Route::get('', [WebhookGlobalTriggersController::class, 'Enum']);
    Route::get('{id}', [WebhookGlobalTriggersController::class, 'Find']);
});

Route::prefix('/' . $v . '/webhook-trigger-value')->group(function () {
     Route::get('', [WebhookTriggerValuesController::class, 'Enum']);
     Route::post('', [WebhookTriggerValuesController::class, 'Create']);
     Route::get('{id}', [WebhookTriggerValuesController::class, 'Find']);
     Route::put('{id}', [WebhookTriggerValuesController::class, 'Update']);
     Route::delete('{id}', [WebhookTriggerValuesController::class, 'Delete']);
});

Route::prefix('/' . $v . '/webhook')->group(function () {
    Route::get('', [WebhooksController::class, 'Enum']);
    Route::post('', [WebhooksController::class, 'Create']);
    Route::get('{id}', [WebhooksController::class, 'Find']);
    Route::put('{id}', [WebhooksController::class, 'Update']);
    Route::delete('{id}', [WebhooksController::class, 'Delete']);
});
