<?php
namespace Pintsize\Common;

class Util
{
    /**
     * recursively create a long directory path
     */
    static function createPath($path) {
        if (is_dir($path)) return true;
        $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1 );
        $return = self::createPath($prev_path);
        return ($return && is_writable($prev_path)) ? mkdir($path) : false;
    }
}
