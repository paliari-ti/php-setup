<?php

namespace Paliari\PhpSetup\Db;

use Doctrine\ORM\EntityManager;

interface RepositoryInterface
{

    public static function create(array $params, bool $throw = false);

    public static function update($id, array $params, bool $throw = true): bool;

    public static function save($model, bool $throw = false): bool;

    public static function delete($id, bool $throw = true): bool;

    public static function find($id, bool $throw = false);

    /**
     * @param array $params
     *
     * @return QB
     */
    public static function ransack(array $params);

    /**
     * @return EntityManager
     */
    public static function getEM();

}
