<?php

namespace Paliari\PhpSetup\Db;

use Paliari\PhpSetup\Db\VO\PaginatedVO;
use Paliari\Utils\AbstractSingleton;

abstract class AbstractRepository extends AbstractSingleton implements RepositoryInterface
{
    abstract protected function modelName(): string;

    protected function newModel(array $params)
    {
        $model = $this->modelName();

        return new $model($params);
    }

    /**
     * Instancia um novo model com os params e salva.
     *
     * @param array $params
     * @param bool  $throw
     *
     * @return object
     */
    public function create(array $params, bool $throw = false)
    {
        $model = $this->newModel($params);
        $this->save($model, $throw);

        return $model;
    }

    /**
     * Atualiza os atributos passados no $params para o model id.
     *
     * @param int|array $id
     * @param array     $params
     * @param bool      $throw
     *
     * @return bool
     */
    public function update($id, array $params, bool $throw = true): bool
    {
        $model = $this->find($id, $throw);
        $model->setAttributes($params);

        return $this->save($model, $throw);
    }

    /**
     * Salva o Model.
     *
     * @param object $model
     * @param bool   $throw
     *
     * @return bool
     */
    public function save($model, bool $throw = false): bool
    {
        return $model->save($throw);
    }

    /**
     * Deleta um model pelo id.
     *
     * @param mixed $id
     * @param bool  $throw
     *
     * @return bool
     */
    public function delete($id, bool $throw = true): bool
    {
        $model = $this->find($id, $throw);

        return $model->destroy($throw);
    }

    public function find($id, bool $throw = false)
    {
        $model = $this->modelName();

        return $model::find($id, $throw);
    }

    /**
     * @param array $params
     *
     * @return QB
     */
    public function ransack(array $params)
    {
        $model = $this->modelName();

        return $model::ransack($params);
    }

    /**
     * @param array $params
     *
     * @return QB
     */
    public function ransackForUpdate(array $params)
    {
        $model = $this->modelName();

        return $model::ransackForUpdate($params);
    }

    /**
     * @param array    $params
     * @param int      $page
     * @param array    $as_json_includes
     * @param int|null $per_page
     *
     * @return PaginatedVO
     */
    public function paginate(array $params, int $page = 1, array $as_json_includes = [], int $per_page = null): PaginatedVO
    {
        return $this->ransack($params)->paginate($page, $as_json_includes, $per_page);
    }
}
