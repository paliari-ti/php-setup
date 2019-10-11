<?php

namespace Paliari\PhpSetup\Db;

use Paliari\PhpSetup\Db\VO\PaginatedVO;

interface RepositoryInterface
{

    public function create(array $params, bool $throw = false);

    public function update($id, array $params, bool $throw = true): bool;

    public function save($model, bool $throw = false): bool;

    public function delete($id, bool $throw = true): bool;

    public function find($id, bool $throw = false);

    /**
     * @param array $params
     *
     * @return QB
     */
    public function ransack(array $params);

    public function paginate(array $params, int $page = 1, array $as_json_includes = [], int $per_page = null): PaginatedVO;

}
