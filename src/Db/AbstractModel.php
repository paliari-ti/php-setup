<?php

namespace Paliari\PhpSetup\Db;

use Doctrine\Common\Persistence\Mapping\MappingException,
    Doctrine\ORM\Mapping\ClassMetadataInfo as Info,
    Paliari\Doctrine\Validators\BaseValidator,
    Doctrine\ORM\TransactionRequiredException,
    Doctrine\ORM\PessimisticLockException,
    Doctrine\ORM\OptimisticLockException,
    Paliari\Doctrine\TraitValidatorModel,
    Doctrine\Common\Inflector\Inflector,
    Doctrine\ORM\Mapping\ClassMetadata,
    Paliari\Doctrine\ModelException,
    Doctrine\ORM\ORMException,
    Doctrine\DBAL\Types\Type,
    Paliari\Doctrine\Ransack,
    BadMethodCallException,
    Doctrine\DBAL\LockMode,
    ReflectionException,
    Paliari\Utils\A,
    Paliari\I18n,
    Exception;

/**
 * Class AbstractModel
 *
 * @package Paliari\PhpSetup\Db
 */
abstract class AbstractModel implements ModelInterface
{

    use TraitValidatorModel;

    /**
     * @var array
     */
    protected $_nested_attributes = [];

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
        foreach ((array)$attributes as $name => $value) {
            if (static::hasField($name)) {
                if (preg_match("/(.+)(_id$)/", $name, $matches) && static::hasAssociation($matches[1])) {
                    if (!BaseValidator::instance()->isBlank($value)) {
                        $klass = static::targetEntity($matches[1]);
                        $this->setAssociation($matches[1], $klass::find($value));
                    }
                } else {
                    $this->$name = $this->prepareSetValue($value, static::typeOfField($name));
                }
            } else if (static::hasAssociation($name)) {
                $this->setAssociations($name, $value);
            } else if (static::hasCustomAssociation($name)) {
                $this->setCustomAssociations($name, $value);
            }
        }

        return $this;
    }

    /**
     * @param string $property
     *
     * @return bool
     */
    public static function hasCustomAssociation($property)
    {
        return false;
    }

    protected function setCustomAssociations($name, $value)
    {
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
        $model = static::getEm()->find(static::className(), $id, $lockMode, $lockVersion);
        if ($throw && null === $model) {
            NotFoundException::modelNotFound(static::className(), $id);
        }

        return $model;
    }

    /**
     * @param mixed    $id
     * @param int      $lockMode
     * @param int|null $lockVersion
     * @param bool     $throw
     *
     * @return $this
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     * @throws NotFoundException
     */
    public static function findLock($id, $lockMode = LockMode::PESSIMISTIC_WRITE, $lockVersion = null, $throw = false)
    {
        return static::find($id, $throw, $lockMode, $lockVersion);
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
            static::getEm()->clear($this::className());
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
            static::getEm()->clear($this::className());
        }

        return $valid;
    }

    protected function afterSave()
    {
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
     * Alias static::getEm()->persist($this);
     *
     * @throws ORMException
     */
    public function persist()
    {
        static::getEm()->persist($this);
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
     * @param QB    $qb
     * @param array $params
     *
     * @return QB
     */
    protected static function qbRansack($qb, $params = [])
    {
        return Ransack::instance()->query($qb, static::className(), $params);
    }

    /**
     * @param array $params
     *
     * @return QB
     */
    public static function ransack($params = [])
    {
        return static::qbRansack(static::query(), $params);
    }

    /**
     * @param array $params
     *
     * @return QB
     */
    public static function ransackForUpdate($params = [])
    {
        return static::qbRansack(static::qbUpdate(), $params);
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
     * @param string $alias
     *
     * @return QB
     */
    public static function qbUpdate($alias = 't')
    {
        return ModelUtil::qbUpdate(static::className(), $alias);
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
    public function asJson($options = [])
    {
        return ModelUtil::export($this, $options, true);
    }

    /**
     * @param array $options
     *
     * @return array
     */
    public function toArray($options = [])
    {
        return ModelUtil::export($this, $options, false);
    }

    /**
     * @param array $options
     *
     * @return string
     */
    public function toJson($options = [])
    {
        return json_encode($this->asJson($options));
    }

    /**
     * @return array
     */
    public static function attributes()
    {
        return static::getClassMetadata()->getFieldNames();
    }

    /**
     * @return array
     */
    public static function exportOnlyAttributes()
    {
        return array_diff(static::attributes(), static::hideAttributes());
    }

    /**
     * @return array
     */
    public static function hideAttributes()
    {
        return [];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return static::getClassMetadata()->getTableName();
    }

    /**
     * @return string
     */
    public static function tableize()
    {
        return Inflector::tableize(static::className());
    }

    /**
     * @return string
     */
    public static function hum()
    {
        $table = static::tableize();

        return I18n::instance()->hum("db.models.$table") ?: $table;
    }

    /**
     * @param string $attribute
     *
     * @return string
     */
    public static function humAttribute($attribute)
    {
        $table = static::tableize();

        return I18n::instance()->hum("db.attributes.$table.$attribute") ?: $attribute;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * @return EM
     */
    public static function getEm()
    {
        return ModelUtil::getEm();
    }

    public static function hasField($field)
    {
        return static::getClassMetadata()->hasField($field);
    }

    public static function typeOfField($field)
    {
        return static::getClassMetadata()->getTypeOfField($field);
    }

    /**
     * @param string $property
     *
     * @return bool
     */
    public static function hasAssociation($property)
    {
        return static::getClassMetadata()->hasAssociation($property);
    }

    public static function targetEntity($property)
    {
        return static::hasAssociation($property) ? static::getAssociationMapping($property)['targetEntity'] : null;
    }

    /**
     * @param string $property
     *
     * @return array|null
     */
    public static function getAssociationMapping($property)
    {
        return static::hasAssociation($property) ? static::getAssociationMappings()[$property] : null;
    }

    /**
     * Obtem todos os mapeamentos de associacao de classe.
     *
     * @return array
     */
    public static function getAssociationMappings()
    {
        return static::getClassMetadata()->getAssociationMappings();
    }

    /**
     * @param string $property
     *
     * @return int|null
     */
    public static function getAssociationType($property)
    {
        return A::get(static::getAssociationMapping($property), 'type');
    }

    /**
     * @return ClassMetadata
     */
    public static function getClassMetadata()
    {
        return static::getEm()->getClassMetadata(static::className());
    }

    protected function setAssociations($name, $value)
    {
        $klass = static::targetEntity($name);
        if (in_array(static::getAssociationType($name), [Info::ONE_TO_MANY, Info::MANY_TO_MANY])) {
            foreach ($value as $item) {
                $action = 'add';
                if ($this->isTargetDestroy($name, $item)) {
                    $action = 'remove';
                    $model  = isset($item['id']) ? $klass::find($item['id']) : null;
                    if ($model && Info::ONE_TO_MANY == static::getAssociationType($name)) {
                        static::getEm()->remove($model);
                    }
                } else {
                    $model = $this->findAssociation($klass, $item);
                    $this->addNestedAttributes($name, $model);
                }
                $this->setAssociation(Inflector::singularize($name), $model, $action);
            }
        } else {
            $model = $this->findAssociation($klass, $value);
            $this->setAssociation($name, $model);
            $this->addNestedAttributes($name, $model);
        }
    }

    protected function isTargetDestroy($name, $item)
    {
        return static::isCascadeRemove($name) && isset($item['_destroy']);
    }

    protected static function isCascadeRemove($property)
    {
        return static::hasAssociation($property) ? static::getAssociationMapping($property)['isCascadeRemove'] : false;
    }

    protected static function isCascade($property)
    {
        return static::hasAssociation($property) ? !empty(static::getAssociationMapping($property)['cascade']) : false;
    }

    protected function setAssociation($association, $model, $action = 'set')
    {
        $method = $action . Inflector::classify($association);
        if ($model && method_exists($this, $method)) {
            $this->$method($model);
        }
    }

    protected function findAssociation($klass, $value)
    {
        return $value ? $klass::findOrInitializeBy($value, ['id']) : null;
    }

    protected static function cacheId($params)
    {
        return static::className() . Convert::toJson($params);
    }

    protected static function filtersByInitialize($filters = [])
    {
        return $filters;
    }

    protected function prepareSetValue($value, $type)
    {
        if (!in_array($type, [Type::OBJECT, Type::SIMPLE_ARRAY, Type::JSON_ARRAY, Type::TARRAY]) || is_string($value)) {
            $value = ModelUtil::convertToPHPValue($value, $type);
        }

        return $value;
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

    protected function addNestedAttributes($attribute, $model)
    {
        if ($model) {
            $type = static::getAssociationType($attribute);
            if (isset($this->_nested_attributes[$type][$attribute])) {
                $this->_nested_attributes[$type][$attribute][$this->oid($model)] = $model;
            }
        }
    }

    protected function isNestedAttributes($attribute)
    {
        $type = static::getAssociationType($attribute);

        return isset($this->_nested_attributes[$type][$attribute]);
    }

    private function oid($model)
    {
        return $model->id ?: md5(spl_object_hash($model) . $model);
    }

    /**
     * @throws ModelException
     */
    protected function _validateNestedAttributesAll()
    {
        foreach ($this->_nested_attributes as $nesteds) {
            foreach ($nesteds as $attribute => $models) {
                $this->_validateNestedAttributes($attribute, $models);
            }
        }
    }

    /**
     * @param string   $attribute
     * @param static[] $models
     *
     * @throws ModelException
     */
    protected function _validateNestedAttributes($attribute, $models)
    {
        foreach ($models as $model) {
            if (!$model->isValid()) {
                if (!$this->errors) {
                    $this->validate();
                }
                $this->errors->add($attribute, $model->errors);
                throw new ModelException($model->errors);
            }
        }
    }

    protected function _saveNestedAttributes($type)
    {
        if (isset($this->_nested_attributes[$type])) {
            foreach ($this->_nested_attributes[$type] as $attr => $models) {
                foreach ($models as $model) {
                    $model->save(true);
                }
            }
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws ModelException
     */
    protected function _saveWithNestedAttributes()
    {
        $this->persist();
        $this->_validateNestedAttributesAll();
        $this->_saveNestedAttributes(Info::MANY_TO_ONE);
        $this->flush();
        $this->_saveNestedAttributes(Info::ONE_TO_MANY);
        $this->_saveNestedAttributes(Info::MANY_TO_MANY);
        $this->_saveNestedAttributes(Info::ONE_TO_ONE);
        $this->afterSave();
    }

}
