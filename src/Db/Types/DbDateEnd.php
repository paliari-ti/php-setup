<?php

namespace Paliari\PhpSetup\Db\Types;

use Paliari\PhpSetup\Db\Types\Helpers\DateNotNull;

/**
 * Class DbDateEnd
 * Sempre salva data no DB para facilitar query.
 *
 * @package Db\Types
 */
class DbDateEnd extends DateNotNull
{

    const TYPE = 'db_date_end';

    protected function defaultDate()
    {
        return '9999-12-31';
    }

}
