<?php

declare(strict_types=1);

use Gemini\Facade\Facades\Route;

Route::get(path: '/', action: static fn() : string => 'Welcome to the homepage!')->name('test-route');


//Route::get(
//    path  : '/login',
//    action: [AuthenticationController::class, 'index'],
//    name  : 'auth.login.form',
//);
//
//Route::post(
//    path  : '/login',
//    action: [AuthenticationController::class, 'login'],
//    name  : 'auth.login',
//);
//
//Route::get(
//    '/test-blade',
//    static function () {
//        $users = [];
//        dd($users);
//
//        return view(template: 'auth.login', data: $users);
//    },
//);
//
//Route::post(
//    path  : '/tesst',
//    action: [HealthCheckController::class, 'testRTGApi'],
//);
