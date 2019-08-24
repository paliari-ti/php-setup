<?php

namespace Paliari\PhpSetup\Db;

use Doctrine\Common\Collections\ArrayCollection,
    Paliari\PhpSetup\Db\Types\Helpers\EnumItem,
    Paliari\Doctrine\ValidatorErrors,
    DateTime;

class Convert
{

    public static $date_time_format = 'Y-m-d\TH:i:sO';

    /**
     * @param mixed $content
     *
     * @return string
     */
    public static function toJson($content)
    {
        return json_encode(static::asJson($content));
    }

    /**
     * Prepara conteudo para converter em json
     *
     * @param mixed $content
     *
     * @return mixed
     */
    public static function asJson($content)
    {
        if (null === $content) {
            return '';
        }
        if (!$content) {
            return $content;
        }
        if ($content instanceof AbstractModel) {
            return $content->asJson();
        } else if ($content instanceof ValidatorErrors) {
            $content = $content->asJson();
        }
        if (is_array($content) || is_object($content)) {
            $ret = [];
            foreach ($content as $k => $v) {
                $v       = static::handleValueFormat($v);
                $ret[$k] = is_array($v) ? static::asJson($v) : $v;
            }

            return $ret;
        }

        return $content;
    }

    public static function handleValueFormat($value)
    {
        if ($value instanceof DateTime) {
            $value = $value->format(static::$date_time_format);
        } else if ($value instanceof AbstractModel) {
            $value = $value->asJson();
        } else if ($value instanceof EnumItem) {
            $value = $value->{EnumItem::$to_json_key};
        } else if ($value instanceof ArrayCollection) {
            $value = $value->toArray();
        }

        return $value;
    }

}
