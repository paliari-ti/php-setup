<?php

namespace Paliari\PhpSetup\Db\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform,
    Paliari\Brasil\DateTime\DateBr,
    Doctrine\DBAL\Types\DateType;

/**
 * Doctrine DB data Type customizado.
 *
 * @package Db\Types
 */
class DbDate extends DateType
{

    const TYPE = 'db_date';

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
     * @return string
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (DateBr::isDate($value)) {
            $value = new DateBr($value);

            return $value->format($platform->getDateFormatString());
        }

        return null;
    }

    /**
     * Obtem a data no formato PHP
     *
     * @param string           $value
     * @param AbstractPlatform $platform
     *
     * @return DateBr
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (!$value) {
            return null;
        }

        return new DateBr($value);
    }

}
