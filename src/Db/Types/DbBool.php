<?php

namespace Paliari\PhpSetup\Db\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform,
    Doctrine\DBAL\Types\BooleanType;

/**
 * Type boolean para forcar (0,1) no oracle
 *
 * @author  Marcos Paliari paliari.com.br
 *
 * @package Db\Types
 */
class DbBool extends BooleanType
{

    const TYPE = 'db_bool';

    public function getName()
    {
        return static::TYPE;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $this->toIntBool($value);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return (bool)$this->toIntBool($value);
    }

    /**
     * @param mixed $v
     *
     * @return int
     */
    private function toIntBool($v)
    {
        if (is_string($v)) {
            $v = trim(strtolower($v));
            switch ($v) {
                case 'true' :
                    return 1;
                case 'false' :
                case 'null' :
                case '0.0' :
                    return 0;
            }
            $v = (int)$v;
        }

        return (int)(bool)$v;
    }

}
