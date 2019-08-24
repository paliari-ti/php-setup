<?php

namespace Paliari\PhpSetup\Db\Types\Helpers;

use Doctrine\DBAL\Platforms\AbstractPlatform,
    Doctrine\DBAL\Types\StringType,
    InvalidArgumentException,
    BadMethodCallException,
    Paliari\Utils\A;

/**
 * Class DbEnum
 *
 * @package Isse\Db\Types
 */
abstract class DbEnum extends StringType
{

    const TYPE = '';

    /**
     * @return EnumValues
     */
    public function getEnumValues()
    {
        return EnumValues::instance($this->getName());
    }

    public function getName()
    {
        return static::TYPE;
    }

    /**
     * Retorana array com enum key/label
     *
     * @return array
     */
    public static function getEnums()
    {
        return EnumValues::instance(static::TYPE)->getEnums();
    }

    /**
     * @param mixed            $value
     * @param AbstractPlatform $platform
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!$this->getEnumValues()->isValid($value)) {
            $value = var_export($value, true);
            throw new InvalidArgumentException("Invalid '" . $this->getName() . "' value $value.");
        }

        return $this->getEnumValues()->prepareDBValue($value);
    }

    /**
     * @param mixed            $value
     * @param AbstractPlatform $platform
     *
     * @return EnumItem
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $this->getEnumValues()->preparePHPValue($value);
    }

    /**
     * @param string $method
     * @param array  $args
     *
     * @return bool
     * @throws BadMethodCallException
     */
    public static function __callStatic($method, $args)
    {
        $enum  = EnumValues::instance(static::TYPE);
        $value = $enum->getConstantValue($method);
        if (null === $value) {
            throw new BadMethodCallException("Method '$method' not defined!");
        }

        return $value === $enum->prepareDBValue(A::get($args, 0));
    }

}
