<?php

namespace Paliari\PhpSetup\Config\Providers;

use Psr\Container\ContainerInterface;

interface ProvidableInterface
{
    public static function register(ContainerInterface $container, string $name): void;
}
