<?php

namespace Paliari\PhpSetup\Db;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\Mapping\ClassMetadata;
use Paliari\Doctrine\ModelException;
use Paliari\PhpSetup\Db\VO\PaginatedVO;
use Paliari\Utils\AbstractSingleton;

/**
 * Class AbstractRepository
 *
 * @method AbstractModel|null find($id, bool $throw = false)
 * @method AbstractModel|null findLock($id, int $lockMode = LockMode::PESSIMISTIC_WRITE, ?int $lockVersion = null, $throw = false)
 * @method AbstractModel|null findOneBy(array $criteria)
 * @method AbstractModel      findOrInitializeBy(array $params, array $filter_fields = [])
 * @method array              findBy($criteria, $orderBy = null, $limit = null, $offset = null)
 * @method bool               exists(array $ransack_params)
 * @method QB                 ransack(array $params)
 * @method QB                 ransackForUpdate(array $params)
 * @method AbstractModel|null first()
 * @method AbstractModel|null last()
 * @method array              all()
 * @method array              attributes()
 * @method array              exportOnlyAttributes()
 * @method array              hideAttributes()
 * @method string             tableName()
 * @method string             tableize()
 * @method string             hum()
 * @method string             humAttribute(string $attribute)
 * @method bool               hasField(string $field)
 * @method bool               typeOfField(string $field)
 * @method bool               hasAssociation(string $property)
 * @method array|null         getAssociationMapping(string $property)
 * @method string|null        targetEntity(string $property)
 * @method array              getAssociationMappings()
 * @method int|null           getAssociationType(string $property)
 * @method ClassMetadata      getClassMetadata()
 * @method EM                 getEm()
 *
 * @package Paliari\PhpSetup\Db
 */
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
     * @throws MappingException
     * @throws ModelException
     */
    public function delete($id, bool $throw = true): bool
    {
        $model = $this->find($id, $throw);

        return $model->destroy($throw);
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

    public function __call($method, $args)
    {
        $model = $this->modelName();

        return call_user_func_array("$model::$method", $args);
    }
}
