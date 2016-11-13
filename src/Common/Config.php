<?php
namespace Pintsize\Common;

use Symfony\Component\Yaml\Yaml;

class Config extends ConfigKeys
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

    public static function getF3Db()
    {
        return new \DB\SQL(
            'mysql:host=' . self::get('database.host') .
            ';port=' . self::get('database.port') .
            ';dbname=' . self::get('database.dbname') .
            ';charset=utf8mb4',
            self::get('database.username'),
            self::get('database.password'),
            [
                \PDO::ATTR_PERSISTENT => true,
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ]
        );
    }

    public static function reload()
    {
        self::$config = Yaml::parse(file_get_contents(APPDIR . '/conf/pintsize.conf.yaml'));
    }
}
