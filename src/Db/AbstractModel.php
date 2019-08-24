<?php

namespace Paliari\PhpSetup\Db;

use BadMethodCallException;
use Doctrine\Common\Inflector\Inflector;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\DBAL\LockMode;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\PessimisticLockException;
use Doctrine\ORM\TransactionRequiredException;
use Exception;
use Paliari\Doctrine\AbstractValidatorModel;
use Paliari\Doctrine\ModelException;
use Paliari\Doctrine\Ransack;
use Paliari\Utils\A;
use ReflectionException;

class AbstractModel extends AbstractValidatorModel implements ModelInterface
{

    use TraitModelNestedAttributes;

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
        if (empty($criteria)) {
            return null;
        }
        $cache_id = static::cacheId($criteria);
        if ($cache = ModelUtil::getCache($cache_id)) {
            return $cache;
        }
        $rows = static::findBy($criteria, null, 1);
        if ($model = isset($rows[0]) ? $rows[0] : null) {
            ModelUtil::setCache($cache_id, $model);
        }

        return $model;
    }

    /**
     * @param array $params
     * @param array $filter_fields
     *
     * @return $this
     *
     * @throws NotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     */
    public static function findOrInitializeBy($params, $filter_fields = [])
    {
        $model = isset($params['id']) ? static::find($params['id']) : null;
        if ($model) {
            $model->setAttributes($params);
        } else {
            $filter = A::sanitize($params, static::filtersByInitialize($filter_fields));
            if ($model = static::findOneBy($filter)) {
                $model->setAttributes($params);
            } else {
                $model = new static($params);
                if ($filter) {
                    ModelUtil::setCache(static::cacheId($filter), $model);
                }
            }
        }

        return $model;
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
        foreach ($criteria as $k => $v) {
            if ($type = static::typeOfField($k)) {
                if (Type::TEXT == $type) {
                    unset($criteria[$k]); // No oracle nao funciona find eq em campo text|clob
                } else {
                    $criteria[$k] = ModelUtil::convertToPHPValue($v, static::typeOfField($k));
                }
            } else {
                unset($criteria[$k]);
            }
        }

        return static::getEm()->getRepository(static::className())->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * @param int  $lockMode
     * @param null $lockVersion
     *
     * @throws OptimisticLockException
     * @throws PessimisticLockException
     */
    public function lock($lockMode = LockMode::PESSIMISTIC_WRITE, $lockVersion = null)
    {
        static::getEm()->lock($this, $lockMode, $lockVersion);
    }

    /**
     * @param bool $throw
     *
     * @return bool
     *
     * @throws MappingException
     * @throws ModelException
     */
    public function destroy($throw = false)
    {
        $valid = $this->tryAction(function () {
            static::getEm()->remove($this);
            $this->flush();
        }, $throw);
        if (!$valid) {
            @static::getEm()->clear($this);
        }

        return $valid;
    }

    /**
     * @param bool $throw
     *
     * @return bool
     * @throws MappingException
     * @throws ModelException
     */
    public function save($throw = false)
    {
        $valid = $this->tryAction(function () {
            if ($this->_nested_attributes) {
                ModelUtil::transaction(function () {
                    $this->_saveWithNestedAttributes();
                });
            } else {
                $this->persist();
                $this->flush();
                $this->afterSave();
            }
        }, $throw);
        if (!$valid) {
            @static::getEm()->clear($this);
        }

        return $valid;
    }

    /**
     * @return static
     *
     * @throws MappingException
     * @throws ReflectionException
     */
    public static function first()
    {
        return ModelUtil::first(static::className());
    }

    /**
     * @return static
     * @throws MappingException
     * @throws ReflectionException
     */
    public static function last()
    {
        return ModelUtil::last(static::className());
    }

    /**
     * @return array
     */
    public static function all()
    {
        return ModelUtil::all(static::className());
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
        $rows = static::ransack($ransack_params)->select('1')->setMaxResults(1)->getArrayResult();

        return count($rows) > 0;
    }

    /**
     * @param array $params
     *
     * @return QB
     */
    public static function ransack($params = [])
    {
        return Ransack::instance()->query(static::query(), static::className(), $params);
    }

    /**
     * @param array $params
     *
     * @return QB
     */
    public static function ransackForUpdate($params = [])
    {
        return Ransack::instance()->query(static::qbUpdate(), static::className(), $params);
    }

    /**
     * @param string $alias
     *
     * @return QB
     */
    public static function query($alias = 't')
    {
        return ModelUtil::query(static::className(), $alias);
    }

    /**
     * @param string $name
     * @param        $value
     */
    public function __set($name, $value)
    {
        if (static::hasField($name)) {
            $this->$name = $this->prepareSetValue($value, static::typeOfField($name));
        } else {
            $model = static::className();
            throw new BadMethodCallException("Field '$model.$name' not found!");
        }
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function __get($name)
    {
        if ($this->__isset($name)) {
            return $this->$name;
        }
        $method = 'get' . Inflector::classify($name);
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return null;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->$name) || static::hasField($name);
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
     * @return EM
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

    public static function hasField($field)
    {
        return static::getClassMetadata()->hasField($field);
    }

    public static function typeOfField($field)
    {
        return static::getClassMetadata()->getTypeOfField($field);
    }

    public static function getClassMetadata()
    {
        return static::getEm()->getClassMetadata(static::className());
    }

    protected static function cacheId($params)
    {
        return static::className() . Convert::toJson($params);
    }

    protected static function filtersByInitialize($filters = [])
    {
        return $filters;
    }

    private function tryAction($call, $throw = false)
    {
        try {
            $call();

            return true;
        } catch (ModelException $e) {
            if ($throw) {
                throw $e;
            }
        } catch (Exception $e) {
            throw $e;
        }

        return false;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function flush()
    {
        static::getEm()->flush($this);
    }

    /**
     * @throws ORMException
     */
    public function persist()
    {
        static::getEm()->persist($this);
    }

    protected function afterSave()
    {
    }

    /**
     * @param string $alias
     *
     * @return QB
     */
    public static function qbUpdate($alias = 't')
    {
        return ModelUtil::qbUpdate(static::className(), $alias);
    }

    protected function prepareSetValue($value, $type)
    {
        if (!in_array($type, [Type::OBJECT, Type::SIMPLE_ARRAY, Type::JSON_ARRAY, Type::TARRAY]) || is_string($value)) {
            $value = ModelUtil::convertToPHPValue($value, $type);
        }

        return $value;
    }

}
