<?php

namespace Paliari\PhpSetup\Db\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform,
    Doctrine\DBAL\Types\ConversionException,
    Doctrine\DBAL\Types\JsonType;

class DbJsonArray extends JsonType
{

    const TYPE = 'db_json_array';

    public function getName()
    {
        return static::TYPE;
    }

    /**
     * @param mixed            $value
     * @param AbstractPlatform $platform
     *
     * @return false|mixed|string|null
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (is_array($value)) {
            return parent::convertToDatabaseValue($value, $platform);
        }

        return $value ?: '{}';
    }

    /**
     * @param string           $value
     * @param AbstractPlatform $platform
     *
     * @return mixed|null
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (!$value) {
            return null;
        }
        if (!is_array($value)) {
            return parent::convertToPHPValue($value, $platform);
        }

        return $value;
    }

}
