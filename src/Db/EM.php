<?php

namespace Paliari\PhpSetup\Db;

use Doctrine\Common\EventManager,
    Doctrine\ORM\Configuration,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\ORMException;

/**
 * Class EM
 *
 * @method AbstractModel|null find($entityName, $id, $lockMode = null, $lockVersion = null)
 *
 * @package Paliari\PhpSetup\Db
 */
class EM extends EntityManager
{

    public function clear($entityName = null)
    {
        ModelUtil::clearCache();
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
