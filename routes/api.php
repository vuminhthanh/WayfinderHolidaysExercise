<?php

use App\Http\Controllers\EnquiryController;
use Illuminate\Support\Facades\Route;

Route::get('/enquiries', [EnquiryController::class, 'index']);
Route::post('/enquiries', [EnquiryController::class, 'store']);
Route::patch('/enquiries/{enquiry}/status', [EnquiryController::class, 'updateStatus']);
