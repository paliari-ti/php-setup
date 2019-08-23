<?php

namespace Paliari\PhpSetup\Db;

abstract class AbstractRepository implements RepositoryInterface
{

    abstract protected static function modelName(): string;

    protected static function newModel(array $params)
    {
        $model = static::modelName();

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
    public static function create(array $params, bool $throw = false)
    {
        $model = static::newModel($params);
        static::save($model, $throw);

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
    public static function update($id, array $params, bool $throw = true): bool
    {
        $model = static::find($id, true);
        $model->setAttributes($params);

        return static::save($model, $throw);
    }

    /**
     * Salva o Model.
     *
     * @param object $model
     * @param bool   $throw
     *
     * @return bool
     */
    public static function save($model, bool $throw = false): bool
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
    public static function delete($id, bool $throw = true): bool
    {
        $model = static::find($id, true);

        return $model->destroy($throw);
    }

    public static function find($id, bool $throw = false)
    {
        $model = static::modelName();

        return $model::find($id, $throw);
    }

    /**
     * @param array $params
     *
     * @return QB
     */
    public static function ransack(array $params)
    {
        $model = static::modelName();

        return $model::ransack($params);
    }

}
