<?php

namespace Paliari\PhpSetup\Db;

use Exception;

class NotFoundException extends Exception
{

    public static function modelNotFound($model_name, $id)
    {
        $name = $model_name::hum();
        $id   = var_export($id, true);
        throw new static("Não foi possível encontrar um registro '$name' com o id $id");
    }

}
