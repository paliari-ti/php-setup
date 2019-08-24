<?php

namespace Paliari\PhpSetup\Db\Types\Helpers;

/**
 * Class EnumItem
 *
 * @package Db\TypesUtils
 */
class EnumItem
{

    private static $_instances  = [];
    public static  $to_json_key = 'key';

    public $id;
    public $key;
    public $label;

    public function __construct($key, $label, $id)
    {
        $this->id    = $id;
        $this->key   = $key;
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->label;
    }

    /**
     * @param string     $key
     * @param string     $label
     * @param string|int $id
     *
     * @return EnumItem
     */
    public static function create($key, $label, $id)
    {
        $uid = "$id.$key.$label";
        if (!isset(static::$_instances[$uid])) {
            static::$_instances[$uid] = new static($key, $label, $id);
        }

        return static::$_instances[$uid];
    }

}
