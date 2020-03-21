<?php

// Parser yaml
$container['parseryaml'] = function () {
    return new \Kneub\Services\Parser\ParserYaml();
};
// Simplify access config
// params db
$container['params.db'] = function ($container) {
    $tab = array_merge($container["common"]["db"], $container["settings"]["db"], $container["credentials"]["db"]);
    if (! empty(getenv('postgresHost'))){
        $tab['host'] = getenv('postgresHost');
    }
    return $tab;
};
// params logger
$container['params.logger'] = function ($container) {
    return $container["settings"]["logger"];
};

// params paginations
$container['params.paginations'] = function ($container) {
    return $container["settings"]["paginations"];
};

// params controller
$container['params.controller'] = function ($container) {
    return $container["common"]["controller"];
};

// params debug mode
$container['params.debug'] = function ($container) {
    return $container["settings"]["debug"];
};

// params debug mode
$container['params.mode'] = function ($container) {
    return $container["settings"]["mode"];
};

// params Jwt
$container['params.jwt'] = function ($container) {
    return array_merge($container["settings"]["jwt"], $container["credentials"]["jwt"]);
};

// params project path
$container['params.projPath'] = function ($container) {
    return PROJ_PATH;
};

// params app path
$container['params.appPath'] = function ($container) {
    return APP_PATH;
};

// Route manager
$container['routeManager'] = function ($container) use ($app) {
    $params = array_merge(compact($container['params.projPath']), $container['params.controller']);
    return new \Kneub\Services\Routing\RouteManager($app, $container['parseryaml'], $params);
};

// Logger
$container['logger'] = function ($container) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler($container['params.projPath'] . "/logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

// GUMP, validator data
$container['gump'] = function () {
    return new GUMP('fr');
};

$container['uid'] = function ($container) {
    return new \Kneub\Services\Uid\UidGenerator(new \GuzzleHttp\Client(), $container['credentials']['uid.generator']['url']);
};

$container['gedClient'] = function ($container) {
    return new \Kneub\Services\Ged\Nuxeo\Client(new \GuzzleHttp\Client(), $container['credentials']['ged.picker']['url']);
};

$container['gedDocument'] = function ($container) {
    return new \Kneub\Services\Ged\Nuxeo\Api\Document($container['gedClient']);
};

$container['gedDocuments'] = function ($container) {
    return new \Kneub\Services\Ged\Nuxeo\Api\Documents($container['gedClient']);
};

// validator for model
$container['modelValidator'] = function () {
    return new \Kneub\Services\Validator\ModelValidator();
};

// Validator
$container['validator'] = function ($container) {
    return new \Kneub\Services\Validator\Gump($container['gump']);
};

$container['entityManager'] = function ($container) {
    return new Kneub\Services\Database\EntityManager($container);
};

$container['jwtManager'] = $container->factory(function ($container) {
    return new \Kneub\Services\Security\Jwt\JwtManager($container['params.jwt']);
});

// Middlewares Authentication
$container['AuthMiddleware'] = function ($container) {
    return new \Kneub\Middlewares\AuthMiddleware($container);
};

// Form handler
// Form handler Connexion
$container['formHandlerConnexion'] = function ($container) {
    return new \Kneub\Services\Form\Handler\Connexion();
};

// Form handler Recolte
$container['formHandlerRecolte'] = function () {
    return new \Kneub\Services\Form\Handler\Recolte();
};

// Form handler Localite
$container['formHandlerLocalite'] = function () {
    return new \Kneub\Services\Form\Handler\Localite();
};

// Form handler Collecteur
$container['formHandlerCollecteur'] = function () {
    return new \Kneub\Services\Form\Handler\Collecteur();
};

// Form handler Description
$container['formHandlerDescription'] = function () {
    return new \Kneub\Services\Form\Handler\Description();
};

// Form handler TitreMissions
$container['formHandlerTitreMissions'] = function () {
    return new \Kneub\Services\Form\Handler\TitreMissions();
};

// Form handler User Create
$container['formHandlerUser'] = function ($container) {
    return new \Kneub\Services\Form\Handler\User($container['modelValidator'], 'create');
};

// Form handler User Update
$container['formHandlerUserUpdate'] = function ($container) {
    return new \Kneub\Services\Form\Handler\User($container['modelValidator'], 'update');
};

// Form handler ChangePassword
$container['formHandlerChangePassword'] = function ($container) {
    return new \Kneub\Services\Form\Handler\ChangePassword($container['validator']);
};
