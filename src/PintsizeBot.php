#!/usr/bin/env php
<?php
/**
 * Main entrypoint for Pintsize's IRC component
 */
namespace Pintsize;

use Phergie\Irc\Connection;
use Symfony\Component\Yaml\Yaml;

define('APPDIR', dirname(__DIR__));
require APPDIR . '/vendor/autoload.php';

(function () {
    $config = Yaml::parse(file_get_contents(APPDIR . '/conf/pintsize.conf.yaml'));

    $phergieConfig = array(
        // Plugins to include for all connections
        'plugins' => array(
            new Phergie\PintsizePlugin
        ),
        'connections' => array()
    );

    foreach ($config['connections'] as $conn) {
        $phergieConfig['connections'][] = new Connection($conn);
    }

    $bot = new \Phergie\Irc\Bot\React\Bot;
    $bot->setConfig($phergieConfig);
    $bot->run();
})();
