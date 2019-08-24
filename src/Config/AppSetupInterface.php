<?php

namespace Paliari\PhpSetup\Config;

interface AppSetupInterface
{
    /**
     * Deve retornar a instacia do App.
     *
     * @return mixed
     */
    public static function app();
}
