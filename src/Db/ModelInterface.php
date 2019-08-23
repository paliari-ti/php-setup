<?php

namespace Paliari\PhpSetup\Db;

use Doctrine\DBAL\LockMode;
use Paliari\Doctrine\ModelValidatorInterface;

interface ModelInterface extends ModelValidatorInterface
{

    /**
     * @param array $attributes
     */
    public function __construct($attributes = []);

    /**
     * @param array $attributes
     *
     * @return $this
     */
    public function setAttributes($attributes);

    /**
     * @param mixed    $id
     * @param bool     $throw
     * @param int|null $lockMode
     * @param int|null $lockVersion
     *
     * @return $this
     */
    public static function find($id, $throw = false, $lockMode = null, $lockVersion = null);

    /**
     * @param mixed    $id
     * @param int      $lockMode
     * @param int|null $lockVersion
     *
     * @return $this
     */
    public static function findLock($id, $lockMode = LockMode::PESSIMISTIC_WRITE, $lockVersion = null);

    /**
     * @param array $criteria
     *
     * @return $this
     */
    public static function findOneBy($criteria);

    /**
     * @param array $params
     * @param array $filter_fields
     *
     * @return $this
     */
    public static function findOrInitializeBy($params, $filter_fields = []);

    /**
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array
     */
    public static function findBy($criteria, $orderBy = null, $limit = null, $offset = null);

    /**
     * @param int      $lockMode
     * @param int|null $lockVersion
     */
    public function lock($lockMode = LockMode::PESSIMISTIC_WRITE, $lockVersion = null);

    /**
     * @param bool $throw
     *
     * @return bool
     */
    public function destroy($throw = false);

    /**
     * @param bool $throw
     *
     * @return bool
     */
    public function save($throw = false);

    /**
     * @return $this
     */
    public static function first();

    /**
     * @return $this
     */
    public static function last();

    /**
     * @return array
     */
    public static function all();

    /**
     * Verifica se existe registros pelo ransack_params.
     *
     * @param array $ransack_params
     *
     * @return bool
     */
    public static function exists($ransack_params = []);

    /**
     * @param array $params
     *
     * @return QB
     */
    public static function ransack($params = []);

    /**
     * @param array $params
     *
     * @return QB
     */
    public static function ransackForUpdate($params = []);

    /**
     * @param string $alias
     *
     * @return QB
     */
    public static function query($alias = 't');

    /**
     * @param string $name
     * @param        $value
     */
    public function __set($name, $value);

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name);

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name);

    /**
     * @param array $options
     *
     * @return array
     */
    public function toArray($options = []);

    /**
     * @param array $options
     *
     * @return array
     */
    public function asJson($options = []);

    /**
     * @param array $options
     *
     * @return string
     */
    public function toJson($options = []);

    /**
     * @return array
     */
    public static function attributes();

    /**
     * @return array
     */
    public static function exportOnlyAttributes();

    /**
     * @return string
     */
    public static function tableName();

    /**
     * @return string
     */
    public static function tableize();

    /**
     * @return string
     */
    public static function hum();

    /**
     * @return string
     */
    public function __toString();

}
