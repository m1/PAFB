<?php
// DIC configuration

use Doctrine\DBAL\DriverManager;

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::DEBUG));
    return $logger;
};

$container['db'] = function ($c) {
    return DriverManager::getConnection([
        'driver'    => 'pdo_'.getenv('DB_CONNECTION'),
        'host'      => getenv('DB_HOST'),
        'dbname'    => getenv('DB_DATABASE'),
        'user'      => getenv('DB_USERNAME'),
        'password'  => getenv('DB_PASSWORD'),
        ], new \Doctrine\DBAL\Configuration());
};

$controllers = [
    'App\Controller\TestController' => 'App\Controller\TestController',
];

foreach ($controllers as $controller) {
    $container[$controller] = function ($c) use ($controller) {
        return new $controller($c);
    };
}