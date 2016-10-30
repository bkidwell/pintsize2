<?php
/**
 * Main entrypoint for Pintsize's web interface
 */
namespace Pintsize\www;

require __DIR__ . '/../bootstrap.php';

$f3 = \Base::instance();
$v = 1/0;
$f3->route('GET /',
    function() use ($f3) {
        echo "Hello, world!\n";
        echo var_export($f3->hive(), true);
    }
);
$f3->run();
