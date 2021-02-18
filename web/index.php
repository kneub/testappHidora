<?php

require "../app/bootstrap.php";

// Access-Control-Allow-Origin frontend
$app->add(function ($req, $res, $next) use($container) {
    $response = $next($req, $res);

    if($container['params.mode'] === 'development' )
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Cache-Control, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');

    return $response
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Cache-Control, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

// Routing
$rm = $container->get('routeManager');
$rm->load(PROJ_PATH.'/Routes/routing.yml');

$app->run();
