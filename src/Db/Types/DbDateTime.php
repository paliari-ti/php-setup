<?php

namespace Paliari\PhpSetup\Db\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform,
    Paliari\Brasil\DateTime\DateTimeBr,
    Doctrine\DBAL\Types\DateTimeType;

/**
 * Doctrine DB data Type customizado.
 *
 * @package Db\Types
 */
class DbDateTime extends DateTimeType
{

    const TYPE = 'db_datetime';

    public function getName()
    {
        return static::TYPE;
    }

    /**
     * Obtem a data no formato do banco de dados.
     *
     * @param mixed            $value
     * @param AbstractPlatform $platform
     *
     * @return null|string
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (DateTimeBr::isDate($value)) {
            $value = new DateTimeBr($value);

            return $value->format($platform->getDateTimeFormatString());
        }

        return null;
    }

    /**
     * Obtem a data no formato PHP.
     *
     * @param mixed            $value
     * @param AbstractPlatform $platform
     *
     * @return DateTimeBr|null
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value == null) {
            return null;
        }

        return new DateTimeBr($value);
    }

}
