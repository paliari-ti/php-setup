<?php

namespace Paliari\PhpSetup\Db;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;

abstract class AbstractSetup implements SetupInterface
{

    protected static $_em;

    /**
     * @param array $db_params
     * @param bool  $useSimpleAnnotationReader
     *
     * @return Configuration
     * @throws ORMException
     */
    public static function configure(array $db_params, bool $useSimpleAnnotationReader = false): Configuration
    {
        $config     = new Configuration();
        $driverImpl = $config->newDefaultAnnotationDriver(static::getPaths(), $useSimpleAnnotationReader);
        $config->setMetadataDriverImpl($driverImpl);
        $config->setProxyDir(static::getProxyDir());
        $config->setProxyNamespace(static::getProxyNamespace());
        $config->setAutoGenerateProxyClasses(false);
        static::$_em = static::createEm($db_params, $config);
        static::addTypes();
        static::addPathsI18n();

        return $config;
    }

    /**
     * @return EntityManager
     */
    public static function getEM()
    {
        return static::$_em;
    }

    /**
     * @param array         $db_params
     * @param Configuration $config
     *
     * @return EntityManager
     * @throws ORMException
     */
    protected static function createEm(array $db_params, Configuration $config)
    {
        return EntityManager::create($db_params, $config);
    }

    protected static function getPaths(): array
    {
        return [static::getModelsDir()];
    }

    protected static function getProxyNamespace(): string
    {
        return 'Db\Proxies';
    }

    abstract protected static function addTypes(): void;

    abstract protected static function addPathsI18n(): void;

}
