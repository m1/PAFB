<?php

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

$app->post('/delete', ['uses' => 'TestController@deleteAction']);
$app->get('/{val}', ['uses' => 'TestController@selectAction']);
$app->post('/{val}', ['uses' => 'TestController@updateAction']);
$app->post('/', ['uses' => 'TestController@insertAction']);
$app->get('/', ['uses' => 'TestController@indexAction']);