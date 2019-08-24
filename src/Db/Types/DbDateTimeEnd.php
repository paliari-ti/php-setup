<?php

namespace Paliari\PhpSetup\Db\Types;

use Paliari\PhpSetup\Db\Types\Helpers\DateNotNull;

/**
 * Class DbDateTimeEnd
 *
 * Sempre salva data no DB para facilitar query.
 *
 * @package Db\Types
 */
class DbDateTimeEnd extends DateNotNull
{

    const TYPE = 'db_datetime_end';

    protected function defaultDate()
    {
        return '9999-12-31';
    }

}
