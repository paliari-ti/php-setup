<?php

namespace Paliari\PhpSetup\Db;

use Doctrine\ORM\Tools\Pagination\Paginator;
use DomainException;
use Paliari\Doctrine\RansackQueryBuilder;

class QB extends RansackQueryBuilder
{

    /**
     * @var Paginator
     */
    protected $_paginator;

    /**
     * @var int
     */
    protected $_per_page = 30;

    /**
     * @var int
     */
    protected $_current_page;

    /**
     * @return Paginator
     */
    public function paginator()
    {
        if (!$this->_paginator) {
            $this->_paginator = new Paginator($this);
        }

        return $this->_paginator;
    }

    /**
     * @param int $page
     *
     * @return int
     */
    public function getCurrentPage($page = null)
    {
        if (null !== $page) {
            if ($page < 1) {
                throw new DomainException('Página deve ser maior que zero!');
            }
            $this->_current_page = $page;
            $this->resetPaginate();
        }

        return $this->_current_page;
    }

    /**
     * @param int $page
     *
     * @return $this
     */
    public function page($page)
    {
        $this->getCurrentPage($page);

        return $this;
    }

    public function totalCount()
    {
        return $this->paginator()->count();
    }

    /**
     * @return int
     */
    public function totalPages()
    {
        return (int)ceil($this->totalCount() / $this->getPerPage());
    }

    /**
     * @return int
     */
    public function getPerPage()
    {
        return $this->_per_page;
    }

    /**
     * @param int $per_page
     *
     * @return $this
     */
    public function setPerPage($per_page)
    {
        if ($per_page < 1) {
            throw new DomainException('Registros por página deve ser maior que zero!');
        }
        $this->_per_page = $per_page;
        $this->resetPaginate();

        return $this;
    }

    protected function resetPaginate()
    {
        $this->setFirstResult(($this->getCurrentPage() - 1) * $this->getPerPage())
             ->setMaxResults($this->getPerPage())
        ;
    }

}
