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
    public function currentPage($page = null)
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
        $this->currentPage($page);

        return $this;
    }

    public function count()
    {
        return $this->paginator()->count();
    }

    /**
     * @return int
     */
    public function pages()
    {
        return (int)ceil($this->count() / $this->getPerPage());
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
        $this->setFirstResult(($this->currentPage() - 1) * $this->getPerPage())
             ->setMaxResults($this->getPerPage())
        ;
    }

    /**
     * @param int   $page
     * @param array $as_json_includes
     * @param int   $per_page
     *
     * @return array
     */
    public function paginate(int $page = 1, array $as_json_includes = [], int $per_page = null)
    {
        $this->page($page);
        if ($per_page) {
            $this->setPerPage($per_page);
        }

        return [
            'count' => $this->count(),
            'page'  => $this->currentPage(),
            'pages' => $this->pages(),
            'rows'  => is_array($as_json_includes) ? $this->asJson($as_json_includes) : $this->getResult(),
        ];
    }

}
