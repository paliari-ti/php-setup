<?php

namespace Paliari\PhpSetup\Config\Routes;

use Slim\App;

interface RoutableInterface
{
    public static function routes(App $app): void;
}
