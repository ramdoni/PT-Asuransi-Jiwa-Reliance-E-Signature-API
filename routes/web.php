<?php
use Illuminate\Http\Request;

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

$router->options('{any:.*}', function (Request $request) {
    return response('', 200)
        ->header('Access-Control-Allow-Origin', env('FRONTEND_URL'))
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
        ->header('Access-Control-Allow-Credentials', 'true');
});

$router->get('/', function () use ($router) { return 'ENTIGI System 1.0'; });

$router->post('auth/login', 'AuthController@login');
$router->get('dashboard/send-notification','DashboardController@sendNotification');
$router->get('submission/validate-link/{id}','SubmissionController@validateLink');
$router->post('legal/process', 'LegalController@process');
$router->post('director/process', 'DirectorController@process');
$router->get('pdf/{id}', 'FileController@showPdf');
$router->get('test-stamp/{id}', 'FileController@testStamp');

$router->group(['middleware' => ['auth.jwt']], function () use ($router) {
    $router->get('dashboard', 'DashboardController@index');
    $router->post('auth/register', 'AuthController@register');
    $router->get('auth/me', 'AuthController@me');
    $router->get('auth/index', 'AuthController@index');
    $router->put('auth/update/{id}', 'AuthController@update');

    $router->get('divisi/index', 'DivisiController@index');
    $router->post('divisi/store', 'DivisiController@store');
    $router->put('divisi/update/{id}', 'DivisiController@update');
    
    $router->get('jenis-dokumen/index', 'JenisDokumenController@index');
    $router->post('jenis-dokumen/store', 'JenisDokumenController@store');
    $router->put('jenis-dokumen/update/{id}', 'JenisDokumenController@update');

    $router->get('tujuan-tanda-tangan/index', 'TujuanTandaTanganController@index');
    $router->post('tujuan-tanda-tangan/store', 'TujuanTandaTanganController@store');

    $router->get('signatory/index', 'SignatoryController@index');
    $router->post('signatory/store', 'SignatoryController@store');

    $router->get('submission/index', 'SubmissionController@index');
    $router->get('submission/{id}/show', 'SubmissionController@show');
    $router->post('submission/finish', 'SubmissionController@finish');
    $router->post('submission/store', 'SubmissionController@store');
    $router->post('submission/submit-signer', 'SubmissionController@submitSigner');
    $router->post('submission/submit-place-fields', 'SubmissionController@submitPlaceFields');
    $router->delete('submission/{id}/delete', 'SubmissionController@delete');
    $router->get('submission/{id}/assigner', 'SubmissionController@assigner');
    $router->get('submission/{id}/logs', 'SubmissionController@logs');
    $router->get('submission/{id}/clear-signer', 'SubmissionController@clearSigner');
});