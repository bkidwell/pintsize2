<?php
namespace Pintsize;

use Symfony\Component\Yaml\Yaml;

class Config
{
    private static $config;

    public static function get($key)
    {
        if (!isset(self::$config)) {
            self::reload();
        }
        $result =& self::$config;
        foreach (explode('.', $key) as $path) {
            $result =& $result[$path];
        }
        return $result;
    }

    public static function reload()
    {
        self::$config = Yaml::parse(file_get_contents(APPDIR . '/conf/pintsize.conf.yaml'));
    }
}
