<?php

namespace Paliari\PhpSetup\Db\Types\Helpers;

use Doctrine\DBAL\Types\Type,
    Paliari\I18n;

/**
 * Class EnumValues
 *
 * @package Db\Enums
 */
class EnumValues
{

    protected $_enums = [];

    protected $_values = [];

    protected $_keys = [];

    protected $type = '';

    protected $_class_name = '';

    protected static $_instances = [];

    /**
     * @var string
     * poderá exportar key, id (default 'key') se deixar vazio exporta o objeto original.
     */
    public static $export_format = 'key';

    public function __construct($type)
    {
        $this->type        = $type;
        $this->_class_name = Type::getTypesMap()[$type];
        $this->init();
        static::$_instances[$type] = $this;
    }

    protected function init()
    {
        $this->_enums = $this->i18nEnums();
        foreach ($this->_enums as $key => $label) {
            $value               = $this->getConstantValue($key);
            $this->_values[$key] = $value;
            $this->_keys[$value] = $key;
        }
    }

    /**
     * @param string $type
     *
     * @return static
     */
    public static function instance($type)
    {
        if (!isset(static::$_instances[$type])) {
            static::$_instances[$type] = new static($type);
        }

        return static::$_instances[$type];
    }

    /**
     * Obtem os enums.
     *
     * @return array
     */
    public function getEnums()
    {
        return $this->_enums;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function prepareDBValue($value)
    {
        if ($value instanceof EnumItem) {
            return $value->id;
        }
        $key = $this->getKey($value);

        return isset($this->_values[$key]) ? $this->_values[$key] : null;
    }

    /**
     * @param mixed $value
     *
     * @return EnumItem
     */
    public function preparePHPValue($value)
    {
        if ($value instanceof EnumItem) {
            return $value;
        }
        $key   = $this->getKey($value);
        $label = isset($this->_enums[$key]) ? $this->_enums[$key] : null;
        $id    = isset($this->_values[$key]) ? $this->_values[$key] : null;

        return EnumItem::create($key, $label, $id);
    }

    /**
     * @param mixed $value
     *
     * @return string|null
     */
    public function getKey($value)
    {
        if ($value instanceof EnumItem) {
            return $value->key;
        }
        if (array_key_exists($value, $this->getEnums())) {
            return $value;
        }
        if (array_key_exists($value, $this->_keys)) {
            return $this->_keys[$value];
        }

        return null;
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function isValid($value)
    {
        return null !== $this->getKey($value);
    }

    /**
     * @param mixed $key
     *
     * @return string
     */
    public function getLabel($key)
    {
        $key = $this->getKey($key);

        return isset($this->_enums[$key]) ? $this->_enums[$key] : null;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getConstantValue($key)
    {
        return @constant("$this->_class_name::" . strtoupper($key));
    }

    /**
     * @return mixed
     */
    protected function i18nEnums()
    {
        return I18n::instance()->hum('enums.' . str_replace('db_enum.', '', $this->type));
    }

}
