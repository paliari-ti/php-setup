<?php

namespace Paliari\PhpSetup\Db;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\ORMException;
use Paliari\PhpSetup\Db\Types\DbBool;
use Paliari\PhpSetup\Db\Types\DbDate;
use Paliari\PhpSetup\Db\Types\DbDateEnd;
use Paliari\PhpSetup\Db\Types\DbDateStart;
use Paliari\PhpSetup\Db\Types\DbDateTime;
use Paliari\PhpSetup\Db\Types\DbDateTimeEnd;
use Paliari\PhpSetup\Db\Types\DbDateTimeStart;
use Paliari\PhpSetup\Db\Types\DbJsonArray;
use Paliari\PhpSetup\Db\Types\DbMonth;

abstract class AbstractSetup implements SetupInterface
{

    protected static $_em;

    /**
     * @param array $db_params
     * @param bool  $useSimpleAnnotationReader
     *
     * @return Configuration
     * @throws ORMException
     * @throws DBALException
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
     * @return EM
     */
    public static function getEM()
    {
        return static::$_em;
    }

    /**
     * @param array         $db_params
     * @param Configuration $config
     *
     * @return EM
     * @throws ORMException
     */
    protected static function createEm(array $db_params, Configuration $config)
    {
        return EM::create($db_params, $config);
    }

    protected static function getPaths(): array
    {
        return [static::getModelsDir()];
    }

    protected static function getProxyNamespace(): string
    {
        return 'Db\Proxies';
    }

    /**
     * @throws DBALException
     */
    protected static function addTypes()
    {
        Type::addType(DbBool::TYPE, DbBool::class);
        Type::addType(DbDate::TYPE, DbDate::class);
        Type::addType(DbDateEnd::TYPE, DbDateEnd::class);
        Type::addType(DbDateStart::TYPE, DbDateStart::class);
        Type::addType(DbDateTime::TYPE, DbDateTime::class);
        Type::addType(DbDateTimeEnd::TYPE, DbDateTimeEnd::class);
        Type::addType(DbDateTimeStart::TYPE, DbDateTimeStart::class);
        Type::addType(DbJsonArray::TYPE, DbJsonArray::class);
        Type::addType(DbMonth::TYPE, DbMonth::class);
        static::addCustomTypes();
    }

    abstract protected static function addCustomTypes(): void;

    abstract protected static function addPathsI18n(): void;

}
