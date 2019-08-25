<?php

namespace Paliari\PhpSetup\Config\Routes;

interface RoutableInterface
{
    public static function routes($app): void;
}
