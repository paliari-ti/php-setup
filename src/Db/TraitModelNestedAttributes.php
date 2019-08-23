<?php

namespace Paliari\PhpSetup\Db;

use Doctrine\ORM\Mapping\ClassMetadataInfo as Info;
use Paliari\Doctrine\ModelException;

trait TraitModelNestedAttributes
{

    protected $_nested_attributes = [];

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

    protected function _validateNestedAttributesAll()
    {
        foreach ($this->_nested_attributes as $nesteds) {
            foreach ($nesteds as $attribute => $models) {
                $this->_validateNestedAttributes($attribute, $models);
            }
        }
    }

    protected function _validateNestedAttributes($attribute, $models)
    {
        foreach ($models as $model) {
            if (!$model->isValid()) {
                if (!$this->errors) {
                    $this->_validate();
                }
                $this->errors->add($attribute, $model->errors->toArray());
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

    protected function _saveWithNestedAttributes()
    {
        $this->persist();
        $this->_validateNestedAttributesAll();
        $this->_saveNestedAttributes(Info::MANY_TO_ONE);
        static::getEm()->flush($this);
        $this->_saveNestedAttributes(Info::ONE_TO_MANY);
        $this->_saveNestedAttributes(Info::MANY_TO_MANY);
        $this->_saveNestedAttributes(Info::ONE_TO_ONE);
        $this->afterSave();
    }

}
