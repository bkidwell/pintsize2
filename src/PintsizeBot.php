#!/usr/bin/env php
<?php
/**
 * Main entrypoint for Pintsize's IRC component
 */
namespace Pintsize;

use Phergie\Irc\Connection;

define('APPDIR', dirname(__DIR__));
require APPDIR . '/vendor/autoload.php';

(function () {
    $nick = Config::get('connection.nickname');
    $channels = [];
    foreach (Config::get('channels') as $channel) {
        $channels[] = $channel['channel'];
    }
    $phergieConfig = array(
        'plugins' => array(
            new Phergie\PintsizePlugin,
            new \PSchwisow\Phergie\Plugin\AltNick\Plugin([
                'nicks' => ["{$nick}_2", "{$nick}_3", "{$nick}_4"],
                'recovery' => true,
            ]),
            new \Phergie\Irc\Plugin\React\AutoJoin\Plugin(array(
                'channels' => $channels,
                'wait-for-nickserv' => false,
            )),
        ),
        'connections' => array(
            new Connection(Config::get('connection'))
        )
    );

    $bot = new \Phergie\Irc\Bot\React\Bot;
    $bot->setConfig($phergieConfig);
    $bot->run();
})();
