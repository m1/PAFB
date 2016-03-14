<?php 

// Set path variables
$dir = dirname(__DIR__);
$appDir = $dir . '/app';

// Create autoLoader
$loader = new \Phalcon\Loader();

// Register namespaces
$loader->registerNamespaces([
	"App\\Controllers" => __DIR__ . '/../app/controller'
])->register();

// Create dependency injector
$di = new \Phalcon\DI\FactoryDefault();

// Create micro application
$app = new Phalcon\Mvc\Micro($di);

// Mount the collections
$test = new \Phalcon\Mvc\Micro\Collection();
$test->setHandler("App\\Controllers\\TestController", true);

// Define routes
$test->get('/{val}', 'selectAction');
$test->post('/{val}', 'updateAction');
$test->post('/delete', 'deleteAction');
$test->post('/', 'insertAction');
$test->get('/', 'indexAction');

$app->mount($test);

$di->set('db', function(){
	if (getenv('DB_CONNECTION') === 'pgsql') {
		return new \Phalcon\Db\Adapter\Pdo\Postgresql([
			"host" => getenv('DB_HOST'),
			"username" => getenv('DB_USERNAME'),
			"password" => getenv('DB_PASSWORD'),
			"dbname" => getenv('DB_DATABASE')
		]);
	}

    return new \Phalcon\Db\Adapter\Pdo\Mysql([
        "host" => getenv('DB_HOST'),
        "username" => getenv('DB_USERNAME'),
        "password" => getenv('DB_PASSWORD'),
        "dbname" => getenv('DB_DATABASE')
    ]);
});

// Handle request
$app->handle();