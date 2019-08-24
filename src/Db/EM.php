<?php

namespace Paliari\PhpSetup\Db;

use Doctrine\Common\EventManager,
    Doctrine\ORM\Configuration,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\ORMException;

class EM extends EntityManager
{

    public function clear($entityName = null)
    {
        ModelUtil::$cache = [];
        parent::clear($entityName);
    }

    /**
     * @inheritdoc
     */
    public static function create($connection, Configuration $config, EventManager $eventManager = null)
    {
        if (!$config->getMetadataDriverImpl()) {
            throw ORMException::missingMappingDriverImpl();
        }
        $connection = static::createConnection($connection, $config, $eventManager);

        return new static($connection, $config, $connection->getEventManager());
    }

}
