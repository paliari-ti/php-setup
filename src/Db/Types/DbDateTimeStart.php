<?php

namespace Paliari\PhpSetup\Db\Types;

use Paliari\PhpSetup\Db\Types\Helpers\DateTimeNotNull;

/**
 * Class DbDateTimeStart
 * Sempre salva data no DB para facilitar query.
 *
 * @package Db\Types
 */
class DbDateTimeStart extends DateTimeNotNull
{

    const TYPE = 'db_datetime_start';

    protected function defaultDate()
    {
        return '0001-01-01';
    }

}
