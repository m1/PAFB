<?php

$app = new Silex\Application();

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array (
	    'driver'    => 'pdo_'.getenv('DB_CONNECTION'),
	    'host'      => getenv('DB_HOST'),
	    'dbname'    => getenv('DB_DATABASE'),
	    'user'      => getenv('DB_USERNAME'),
	    'password'  => getenv('DB_PASSWORD'),
    ),
));

$app->post('/delete', 'App\Controller\TestController::deleteAction');
$app->get('/{v}', 'App\Controller\TestController::selectAction');
$app->post('/{v}', 'App\Controller\TestController::updateAction');
$app->post('/', 'App\Controller\TestController::insertAction');
$app->get('/', 'App\Controller\TestController::indexAction');

return $app;
