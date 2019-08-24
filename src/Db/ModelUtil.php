<?php

namespace Paliari\PhpSetup\Db;

use Doctrine\Common\Persistence\Mapping\MappingException,
    Doctrine\Common\Collections\Collection,
    Doctrine\Common\Inflector\Inflector,
    Doctrine\DBAL\Types\Type,
    BadMethodCallException,
    ReflectionException,
    Paliari\Utils\A,
    Exception;

class ModelUtil
{

    public static $cache = [];

    protected static $_current_user;

    public static function setCurrentUser($current_user)
    {
        static::$_current_user = $current_user;
    }

    public static function getCurrentUser()
    {
        return static::$_current_user;
    }

    /**
     * @return int|null
     */
    public static function getCurrentUserId()
    {
        return static::getCurrentUser() ? static::getCurrentUser()->id : null;
    }

    /**
     * @return EM
     */
    public static function getEm()
    {
        return AbstractSetup::getEM();
    }

    /**
     * @param AbstractModel $model
     * @param array         $options
     * @param bool          $json
     *
     * @return array
     */
    public static function export($model, $options = [], $json = false)
    {
        $ret  = [];
        $cols = A::get($options, 'only') ?: $model::exportOnlyAttributes();
        foreach ($cols as $k) {
            $v = $model->$k;
            if ($json) {
                $v = Convert::handleValueFormat($v);
            }
            $ret[$k] = $v;
        }
        if (isset($options['methods'])) {
            $ret = static::exportMethods($model, A::get($options, 'methods', []), $json, $ret);
        }
        if (isset($options['include'])) {
            $ret = static::exportIncludes($model, A::get($options, 'include', []), $json, $ret);
        }

        return $ret;
    }

    /**
     * @param AbstractModel $model
     * @param array         $methods
     * @param bool          $json
     * @param array         $return
     *
     * @return array
     */
    protected static function exportMethods($model, $methods = [], $json = false, &$return = [])
    {
        if ($methods) {
            foreach ($methods as $key) {
                $method = Inflector::camelize($key);
                $value  = $model->$method();
                if ($json) {
                    $value = Convert::handleValueFormat($value);
                }
                $return[$key] = $value;
            }
        }

        return $return;
    }

    /**
     * @param AbstractModel $model
     * @param array         $includes
     * @param bool|false    $json
     * @param array         $return
     *
     * @return array
     */
    protected static function exportIncludes($model, $includes = [], $json = false, &$return = [])
    {
        foreach ($includes as $attr => $include) {
            if (!is_array($include)) {
                $attr    = $include;
                $include = [];
            }
            $method = 'get' . Inflector::classify($attr);
            if (method_exists($model, $method)) {
                if ($parent = call_user_func([$model, $method])) {
                    if ($parent instanceof Collection) {
                        foreach ($parent as $i => $m) {
                            $return[$attr][$i] = $m->export($include, $json);
                        }
                    } else {
                        $return[$attr] = $parent->export($include, $json);
                    }
                }
            }
        }

        return $return;
    }

    /**
     * @param callable $call
     *
     * @return mixed
     *
     * @throws Exception
     */
    public static function transaction($call)
    {
        static::getEm()->beginTransaction();
        try {
            $ret = $call();
            static::getEm()->commit();

            return $ret;
        } catch (Exception $e) {
            static::getEm()->rollback();
            throw $e;
        }
    }

    /**
     * Obtém um array com as chaves primárias da classe passada.
     *
     * @param string $model_name
     *
     * @return array
     * @throws MappingException
     * @throws ReflectionException
     */
    public static function identifier($model_name)
    {
        return static::getEm()->getMetadataFactory()->getMetadataFor($model_name)->getIdentifier();
    }

    /**
     * @param string $model_name
     *
     * @return AbstractModel
     *
     * @throws MappingException
     * @throws ReflectionException
     */
    public static function first($model_name)
    {
        $qb = static::query($model_name)->setMaxResults(1);
        foreach (static::identifier($model_name) as $col) {
            $qb->addOrderBy("t.$col", 'asc');
        }
        $rows = $qb->getResult();

        return current($rows);
    }

    /**
     * @param string $model_name
     *
     * @return AbstractModel
     *
     * @throws MappingException
     * @throws ReflectionException
     */
    public static function last($model_name)
    {
        $qb = static::query($model_name)->setMaxResults(1);
        foreach (static::identifier($model_name) as $col) {
            $qb->addOrderBy("t.$col", 'desc');
        }
        $rows = $qb->getResult();

        return current($rows);
    }

    /**
     * @param string $model_name
     *
     * @return array
     */
    public static function all($model_name)
    {
        return static::getEm()->getRepository($model_name)->findAll();
    }

    /**
     * @param string $model_name
     * @param string $alias
     *
     * @return QB
     */
    public static function query($model_name, $alias = 't')
    {
        return QB::create(static::getEm(), $model_name, $alias)->select($alias);
    }

    /**
     * @param string $model_name
     * @param string $alias
     *
     * @return QB
     */
    public static function qbUpdate($model_name, $alias = 't')
    {
        $qb = new QB(static::getEm());
        $qb->update($model_name, $alias);

        return $qb;
    }

    /**
     * @param mixed  $value
     * @param string $type
     *
     * @return mixed
     */
    public static function convertToDatabaseValue($value, $type)
    {
        return static::getEm()->getConnection()->convertToDatabaseValue($value, $type);
    }

    /**
     * @param mixed  $value
     * @param string $type
     *
     * @return mixed
     */
    public static function convertToPHPValue($value, $type)
    {
        return static::getEm()->getConnection()->convertToPHPValue($value, $type);
    }

    /**
     * @param string $className
     * @param string $field
     *
     * @return Type|null|string
     */
    public static function typeOfField($className, $field)
    {
        return static::getEm()->getClassMetadata($className)->getTypeOfField($field);
    }

    /**
     * @param string $className
     * @param string $field
     * @param mixed  $value
     *
     * @return mixed
     */
    public static function toDbValue($className, $field, $value)
    {
        return static::convertToDatabaseValue($value, static::typeOfField($className, $field));
    }

    /**
     * Insere registros no db.
     *
     * @param string $tableName
     * @param array  $data
     *
     * @return int|null
     * @throws Exception
     */
    public static function insertDb($tableName, $data)
    {
        if (!$tableName) {
            throw new BadMethodCallException('Indefined table!');
        }
        if (empty($data)) {
            throw new BadMethodCallException('Indefined params!');
        }

        return static::getEm()->getConnection()->insert($tableName, $data);
    }

    /**
     * @param string $tableName
     * @param array  $data
     * @param array  $where
     *
     * @return int|null
     * @throws Exception
     */
    public static function updateDb($tableName, $data, $where)
    {
        if (empty($data)) {
            return null;
        }
        if (!$tableName) {
            throw new Exception('Informe a tabela a ser alterada.');
        }
        if (empty($where)) {
            throw new Exception('Informe o filtro para alterar registros na tabela.');
        }

        return static::getEm()->getConnection()->update($tableName, $data, $where);
    }

    /**
     * @param AbstractModel $model
     *
     * @return array
     */
    public static function getEntityChangeSet($model)
    {
        return static::getEm()->getUnitOfWork()->getEntityChangeSet($model);
    }

    /**
     * @param AbstractModel $model
     *
     * @return mixed
     */
    public static function getEntityIdValue($model)
    {
        if (property_exists($model, 'id')) {
            return $model->id;
        }
        if ($id = static::getEm()->getUnitOfWork()->getEntityIdentifier($model)) {
            foreach ($id as $k => $v) {
                $id[$k] = static::toDbValue($model->className(), $k, $v);
            }
        }

        return $id;
    }

    public static function setCache($cache_id, $model)
    {
        return static::$cache[$cache_id] = $model;
    }

    public static function getCache($cache_id)
    {
        return isset(static::$cache[$cache_id]) ? static::$cache[$cache_id] : null;
    }

}
