<?php

namespace Paliari\PhpSetup\Db\Types;

use Doctrine\DBAL\Types\BigIntType;

class DbMicroTime extends BigIntType
{

    const TYPE = 'db_micro_time';

    public function getName()
    {
        return static::TYPE;
    }

    public static function now()
    {
        return microSeconds();
    }

}
