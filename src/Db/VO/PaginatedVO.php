<?php

namespace Paliari\PhpSetup\Db\VO;

use Paliari\Utils\VO\AbstractVO;

class PaginatedVO extends AbstractVO
{

    /**
     * @var int
     */
    public $count = 0;

    /**
     * @var int
     */
    public $page = 1;

    /**
     * @var int
     */
    public $pages = 0;

    /**
     * @var array
     */
    public $rows = [];

}
