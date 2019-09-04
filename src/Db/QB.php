<?php

namespace Paliari\PhpSetup\Db;

use Doctrine\ORM\Tools\Pagination\Paginator;
use DomainException;
use Paliari\Doctrine\RansackQueryBuilder;
use Paliari\PhpSetup\Db\VO\PaginatedVO;

/**
 * Class QB
 *
 * @method static QB create($em, string $model_name, string $alias = 't')
 * @method QB select(mixed $select = null)
 * @method QB addSelect(mixed $select = null)
 * @method QB where(mixed $predicates)
 * @method QB from(string $from, string $alias, string $indexBy = null)
 * @method QB join(string $join, string $alias, string $conditionType = null, string $condition = null, string $indexBy = null)
 * @method QB innerJoin(string $join, string $alias, string $conditionType = null, string $condition = null, string $indexBy = null)
 * @method QB leftJoin(string $join, string $alias, string $conditionType = null, string $condition = null, string $indexBy = null)
 * @method QB andWhere(mixed $where)
 * @method QB orWhere(mixed $where)
 * @method QB groupBy(mixed $groupBy)
 * @method QB addGroupBy(mixed $groupBy)
 * @method QB having(mixed $having)
 * @method QB andHaving(mixed $having)
 * @method QB orHaving(mixed $having)
 * @method QB orderBy(string $sort, string $order = null)
 * @method QB addOrderBy(string $sort, string $order = null)
 * @method QB setParameter(string $key, mixed $value, string $type = null)
 * @method QB setParameters(mixed $parameters)
 * @method QB setFirstResult(integer $firstResult)
 * @method QB setMaxResults(integer $maxResults)
 * @method QB distinct(bool $flag)
 * @method QB delete(string $delete = null, string $alias = null)
 * @method QB update(string $update = null, string $alias = null)
 * @method QB set(string $key, string $value)
 * @method QB add(string $dqlPartName, string $dqlPart, bool $append = false)
 *
 * @package Paliari\PhpSetup\Db
 */
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
    public function currentPage($page = null): int
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

    public function count(): int
    {
        return $this->paginator()->count();
    }

    /**
     * @return int
     */
    public function pages(): int
    {
        return (int)ceil($this->count() / $this->getPerPage());
    }

    /**
     * @return int
     */
    public function getPerPage(): int
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

    protected function resetPaginate(): void
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
     * @return PaginatedVO
     */
    public function paginate(int $page = 1, array $as_json_includes = [], int $per_page = null): PaginatedVO
    {
        $paginated_vo       = $this->newPaginatedVO($page, $per_page);
        $paginated_vo->rows = $this->asJson($as_json_includes);

        return $paginated_vo;
    }

    /**
     * @param int $page
     * @param int $per_page
     *
     * @return PaginatedVO
     */
    public function paginateModels(int $page = 1, int $per_page = null): PaginatedVO
    {
        $paginated_vo       = $this->newPaginatedVO($page, $per_page);
        $paginated_vo->rows = $this->getResult();

        return $paginated_vo;
    }

    protected function newPaginatedVO(int $page = 1, int $per_page = null): PaginatedVO
    {
        $this->page($page);
        if ($per_page) {
            $this->setPerPage($per_page);
        }
        $paginated_vo        = new PaginatedVO();
        $paginated_vo->count = $this->count();
        $paginated_vo->page  = $this->currentPage();
        $paginated_vo->pages = $this->pages();

        return $paginated_vo;
    }

}
