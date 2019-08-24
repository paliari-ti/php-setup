<?php

namespace Paliari\PhpSetup\Db\Types\Helpers;

use Doctrine\DBAL\Platforms\AbstractPlatform,
    Doctrine\DBAL\Types\DateType,
    Exception,
    DateTime;

/**
 * Class DateTimeNotNull
 *
 * Usado para campo de data que sempre salva uma data, se passar null vai salvar defaultDate,
 * isso facilita as queries.
 *
 * @package Db\Types\Helpers
 */
abstract class DateTimeNotNull extends DateType
{

    const TYPE = 'db_date_time_not_null';

    protected function defaultDate()
    {
        return '0001-01-01';
    }

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
     * @throws Exception
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        $value = new DateTime($value ?: $this->defaultDate());

        return $value->format($platform->getDateFormatString());
    }

    /**
     * Obtem a data no formato PHP
     *
     * @param string           $value
     * @param AbstractPlatform $platform
     *
     * @return DateTime|null
     * @throws Exception
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (!$value || $this->isEqDefault($value)) {
            return null;
        }

        return new DateTime($value);
    }

    protected function isEqDefault($value)
    {
        return strpos($value, $this->defaultDate()) === 0;
    }

}
