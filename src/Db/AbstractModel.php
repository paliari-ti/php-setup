<?php

namespace Paliari\PhpSetup\Db;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use Paliari\Doctrine\AbstractValidatorModel;

class AbstractModel extends AbstractValidatorModel implements ModelInterface
{

    /**
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        $this->_init();
        $this->setAttributes($attributes);
    }

    protected function _init()
    {
    }

    /**
     * @param array $attributes
     *
     * @return $this
     */
    public function setAttributes($attributes)
    {
        // TODO: Implement setAttributes() method.
    }

    /**
     * @param mixed    $id
     * @param int|null $lockMode
     * @param int|null $lockVersion
     *
     * @return $this|null
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     */
    protected static function doFind($id, $lockMode, $lockVersion)
    {
        return static::getEm()->find(static::className(), $id, $lockMode, $lockVersion);
    }

    /**
     * @param mixed    $id
     * @param bool     $throw
     * @param int|null $lockMode
     * @param int|null $lockVersion
     *
     * @return $this
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     * @throws NotFoundException
     */
    public static function find($id, $throw = false, $lockMode = null, $lockVersion = null)
    {
        if (null === $id) {
            return null;
        }
        if ($id instanceof static) {
            return $id;
        }
        $model = static::doFind($id, $lockMode, $lockVersion);
        if ($throw && null === $model) {
            static::notFoundException($id);
        }

        return $model;
    }

    /**
     * @param mixed    $id
     * @param int      $lockMode
     * @param int|null $lockVersion
     *
     * @return $this
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     * @throws NotFoundException
     */
    public static function findLock($id, $lockMode = LockMode::PESSIMISTIC_WRITE, $lockVersion = null)
    {
        return static::find($id, true, $lockMode, $lockVersion);
    }

    /**
     * @param array $criteria
     *
     * @return $this
     */
    public static function findOneBy($criteria)
    {
        // TODO: Implement findOneBy() method.
    }

    /**
     * @param array $params
     * @param array $filter_fields
     *
     * @return $this
     */
    public static function findOrInitializeBy($params, $filter_fields = [])
    {
        // TODO: Implement findOrInitializeBy() method.
    }

    /**
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array
     */
    public static function findBy($criteria, $orderBy = null, $limit = null, $offset = null)
    {
        // TODO: Implement findBy() method.
    }

    /**
     * @param int      $lockMode
     * @param int|null $lockVersion
     */
    public function lock($lockMode = LockMode::PESSIMISTIC_WRITE, $lockVersion = null)
    {
        // TODO: Implement lock() method.
    }

    /**
     * @param bool $throw
     *
     * @return bool
     */
    public function destroy($throw = false)
    {
        // TODO: Implement destroy() method.
    }

    /**
     * @param bool $throw
     *
     * @return bool
     */
    public function save($throw = false)
    {
        // TODO: Implement save() method.
    }

    /**
     * @return $this
     */
    public static function first()
    {
        // TODO: Implement first() method.
    }

    /**
     * @return $this
     */
    public static function last()
    {
        // TODO: Implement last() method.
    }

    /**
     * @return array
     */
    public static function all()
    {
        // TODO: Implement all() method.
    }

    /**
     * Verifica se existe registros pelo ransack_params.
     *
     * @param array $ransack_params
     *
     * @return bool
     */
    public static function exists($ransack_params = [])
    {
        // TODO: Implement exists() method.
    }

    /**
     * @param array $params
     *
     * @return QB
     */
    public static function ransack($params = [])
    {
        // TODO: Implement ransack() method.
    }

    /**
     * @param array $params
     *
     * @return QB
     */
    public static function ransackForUpdate($params = [])
    {
        // TODO: Implement ransackForUpdate() method.
    }

    /**
     * @param string $alias
     *
     * @return QB
     */
    public static function query($alias = 't')
    {
        // TODO: Implement query() method.
    }

    /**
     * @param string $name
     * @param        $value
     */
    public function __set($name, $value)
    {
        // TODO: Implement __set() method.
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        // TODO: Implement __get() method.
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        // TODO: Implement __isset() method.
    }

    /**
     * @param array $options
     *
     * @return array
     */
    public function toArray($options = [])
    {
        // TODO: Implement toArray() method.
    }

    /**
     * @param array $options
     *
     * @return array
     */
    public function asJson($options = [])
    {
        // TODO: Implement asJson() method.
    }

    /**
     * @param array $options
     *
     * @return string
     */
    public function toJson($options = [])
    {
        // TODO: Implement toJson() method.
    }

    /**
     * @return array
     */
    public static function attributes()
    {
        // TODO: Implement attributes() method.
    }

    /**
     * @return array
     */
    public static function exportOnlyAttributes()
    {
        // TODO: Implement exportOnlyAttributes() method.
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        // TODO: Implement tableName() method.
    }

    /**
     * @return string
     */
    public static function tableize()
    {
        // TODO: Implement tableize() method.
    }

    /**
     * @return string
     */
    public static function hum()
    {
        // TODO: Implement hum() method.
    }

    /**
     * @return string
     */
    public function __toString()
    {
        // TODO: Implement __toString() method.
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public static function humAttribute($name)
    {
        // TODO: Implement humAttribute() method.
    }

    /**
     * @return EntityManager
     */
    public static function getEm()
    {
        // TODO: Implement getEm() method.
    }

    protected static function notFoundException($id)
    {
        $id        = var_export($id, true);
        $model_hum = static::hum();
        throw new NotFoundException("Não foi possível encontrar um registro '$model_hum' com o id $id");
    }

}
