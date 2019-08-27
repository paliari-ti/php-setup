<?php

namespace Paliari\PhpSetup\Config;

use Slim\App;

interface AppSetupInterface
{
    public static function app(): App;
}
