<?php

// define root directory
define('APP_PATH', dirname(__DIR__));

// define project directory
define('PROJ_PATH', APP_PATH . "/app");

//Autoloader from composer
require APP_PATH . '/vendor/autoload.php';

// load config
$settings_common = \Symfony\Component\Yaml\Yaml::parse(file_get_contents(PROJ_PATH.'/config/common.yml'));
$settings_default = \Symfony\Component\Yaml\Yaml::parse(file_get_contents(PROJ_PATH.'/config/config.yml'));
$settings_credentials = \Symfony\Component\Yaml\Yaml::parse(file_get_contents(PROJ_PATH.'/config/credentials.yml'));
$settings = array_merge($settings_common, $settings_default, $settings_credentials);
//Slim init
$app = new \Slim\App($settings);

// Get container
$container = $app->getContainer();

//Dependence injection services
require PROJ_PATH. "/injectionDependance.php";

// Eloquent
$capsule = new \Illuminate\Database\Capsule\Manager();
$capsule->addConnection($container['params.db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();
