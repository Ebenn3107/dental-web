<?php

use App\Http\Controllers\BookingSuccessController;
use App\Http\Controllers\HomeController;
use App\Livewire\BookingForm;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/booking', BookingForm::class)->name('booking');
Route::get('/booking/success/{code}', BookingSuccessController::class)->name('booking.success');
Route::get('/cek-status', fn () => view('booking.check-status'))->name('check-status');
