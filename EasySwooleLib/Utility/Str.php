<?php

namespace EasySwooleLib\Utility;

use function ctype_lower;

class Str
{
    /**
     * 驼峰转下划线
     *
     * @param string $value
     * @param string $delimiter
     *
     * @return string
     */
    public static function snake(string $value, string $delimiter = '_'): string
    {
        if (!ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', $value);
            $value = self::lower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value));
        }

        return $value;
    }

    /**
     * 字符串转小写
     *
     * @param string $value
     *
     * @return string
     */
    public static function lower(string $value): string
    {
        return mb_strtolower($value, 'UTF-8');
    }
}
