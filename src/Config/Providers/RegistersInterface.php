<?php

namespace Paliari\PhpSetup\Config\Providers;

use Psr\Container\ContainerInterface;

interface RegistersInterface
{
    public static function register(ContainerInterface $container): void;
}
