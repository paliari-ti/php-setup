<?php

namespace Paliari\PhpSetup\Config\Routes;

use Slim\App;

interface RouterInterface
{
    public static function register(App $app): void;
}
