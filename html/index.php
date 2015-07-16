<?php

require_once dirname(__DIR__) . '/registry.php';

/* Turn off standard */
$app['log']->snitcher('standard', function() {}, false, true);

$app->run();
