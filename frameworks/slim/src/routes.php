<?php
// Routes

// $app->get('/[{name}]', function ($request, $response, $args) {
//     // Sample log message
//     $this->logger->info("Slim-Skeleton '/' route");

//     // Render index view
//     return $this->renderer->render($response, 'index.phtml', $args);
// });

// $app->get('/{val}', function ($request, $response, $args) {
//     // // Sample log message
//     // $this->logger->info("Slim-Skeleton '/' route");

//     // // Render index view
//     // return $this->renderer->render($response, 'index.phtml', $args);


//     echo $args;
// });

$app->get('/{val}', 'App\Controller\TestController:selectAction');
$app->post('/delete', 'App\Controller\TestController:deleteAction');
$app->post('/{val}', 'App\Controller\TestController:updateAction');
$app->post('/', 'App\Controller\TestController:insertAction');
$app->get('/', 'App\Controller\TestController:indexAction');

// $test->get('/{val}', 'selectAction');
// $test->post('/{val}', 'updateAction');
// $test->post('/delete', 'deleteAction');
// $test->post('/', 'insertAction');
// $test->get('/', 'indexAction');