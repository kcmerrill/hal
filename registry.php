<?php

require 'vendor/autoload.php';

use kcmerrill\utility\config as config;
use kcmerrill\utility\snitchin as snitchin;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Http\HttpServer;

$app = new Silex\Application();

$argv = isset($argv) ? $argv : 1000;

$app['debug'] = true;

$app['config'] = function ($c) {
    $config = new config(__DIR__ . '/config/', true);
    $config->set('hal.config.dir', __DIR__ . '/config');
    $config->set('hal.log', __DIR__ . '/logs/hal_' . date('mdY') . '.log');
    /* If you need to save()
     * $config->save('hal', $config->c('hal.config.dir') . '/hal.log');
    */
    return $config;
};

$app['log'] = function ($c) use($argv) {
    $l = new snitchin($argv, 'standard|file');
    $l['default']->snitcher('file', $c['config']->c('hal.log'), 30); // Set the log and pump everything to it.
    $l['default']->snitcher('standard', array('msg_length'=>80));
    return $l;
};

$app['db'] = function ($c) {
    $connection = new \MongoClient('mongodb://172.17.42.1');
    //$connection = new \MongoClient();
    return $connection->HAL;
};

$app['HAL'] = function($c) {
    return new \kcmerrill\HAL($c['log'], $c['db'], $c['config'], getenv('USERNAME') ?: getenv('USER'));
};

include 'routes.php';
