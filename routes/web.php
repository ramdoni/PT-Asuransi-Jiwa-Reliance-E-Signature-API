<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return 'ENTIGI System 1.0';
});

$router->post('auth/register', 'AuthController@register');
$router->post('auth/login', 'AuthController@login');

$router->group(['middleware' => 'auth.jwt'], function () use ($router) {
    $router->get('auth/me', 'AuthController@me');

    $router->get('divisi/index', 'DivisiController@index');
    $router->post('divisi/store', 'DivisiController@store');
    
    $router->get('jenis-dokumen/index', 'JenisDokumenController@index');
    $router->post('jenis-dokumen/store', 'JenisDokumenController@store');

    $router->get('tujuan-tanda-tangan/index', 'TujuanTandaTanganController@index');
    $router->post('tujuan-tanda-tangan/store', 'TujuanTandaTanganController@store');

    $router->get('signatory/index', 'SignatoryController@index');
    $router->post('signatory/store', 'SignatoryController@store');

    $router->get('submission/index', 'SubmissionController@index');
    $router->post('submission/store', 'SubmissionController@store');
});