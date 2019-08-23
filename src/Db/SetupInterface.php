<?php

namespace Paliari\PhpSetup\Db;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;

interface SetupInterface
{

    public static function configure(array $db_params, bool $useSimpleAnnotationReader = false): Configuration;

    public static function getProxyDir(): string;

    public static function getModelsDir(): string;

    /**
     * @return EntityManager
     */
    public static function getEM();

}
