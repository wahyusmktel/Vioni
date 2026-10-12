<?php

declare(strict_types=1);

use Vioni\Foundation\Application;

$app = new Application(dirname(__DIR__));

return $app->boot();
