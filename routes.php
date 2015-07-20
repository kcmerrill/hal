<?php

use Symfony\Component\HttpFoundation\Request;
use kcmerrill\HAL\client;

$app->get('/', function() {
    include __DIR__ . '/html/views/index.html';
    return '';
});

$app->post('/', function(Request $request) use ($app) {
    $msg = $request->getContent();
    $WebSocketClient = new client('127.0.0.1', 8080);
    $success = $WebSocketClient->sendData($msg);
    if($success) {
        return $app->json(array('success'=>true), 200);
    } else {
        return $app->json(array('success'=>false), 500);
    }
});

$app->post('/channels', function(Request $request) use ($app) {
      $channel = $request->getContent();
      $channel = json_decode($channel, TRUE);
      if(isset($channel['_id']) && $app['config']->get('hal.channels.update', false)) {
        unset($channel['_id']);
      }
      $c = new \kcmerrill\HAL\channel($app['db'], $app['log']);
      if(isset($channel['_id'])) {
        $c->import($channel['_id']);
      }
      $c->import($channel);
      $c->save();
      return $app->json($c->toArray());
});

$app->post('/subscribers', function(Request $request) use ($app) {
      $subscribers = $request->getContent();
      $subscribers = json_decode($subscribers, TRUE);
      if(isset($subscribers['_id']) && $app['config']->get('hal.subscribers.update', false)) {
        unset($subscribers['_id']);
      }
      $s = new \kcmerrill\HAL\subscriber($app['db'], $app['log']);
      if(isset($subscribers['_id'])){
          $s->import($subscribers['_id']);
      }
      $s->import($subscribers);
      $s->save();
      return $app->json($s->toArray());
});
