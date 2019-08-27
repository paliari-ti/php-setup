<?php

namespace Paliari\PhpSetup\Config\Routes;

use Slim\Routing\RouteCollectorProxy;

interface GroupRoutableInterface
{
    public static function routes(RouteCollectorProxy $routes): void;
}
