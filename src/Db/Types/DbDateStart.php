<?php

namespace Paliari\PhpSetup\Db\Types;

use Paliari\PhpSetup\Db\Types\Helpers\DateNotNull;

/**
 * Class DbDateStart
 * Sempre salva data no DB para facilitar query.
 *
 * @package Db\Types
 */
class DbDateStart extends DateNotNull
{

    const TYPE = 'db_date_start';

    protected function defaultDate()
    {
        return '0001-01-01';
    }

}
