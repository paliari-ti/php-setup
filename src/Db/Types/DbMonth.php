<?php

namespace Paliari\PhpSetup\Db\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform,
    Paliari\Brasil\DateTime\DateMesBr,
    Paliari\Brasil\DateTime\DateBr;

/**
 * Doctrine DB data Type customizado para mes referencia.
 *
 * Class DbMonth
 *
 * @package Paliari\PhpSetup\Db\Types
 */
class DbMonth extends \Doctrine\DBAL\Types\DateType
{

    const TYPE = 'db_month';

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
            $value = new DateMesBr($value);

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
     * @return DateMesBr
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value == null) {
            return null;
        }
        $val = new DateMesBr($value);

        return $val;
    }

}
