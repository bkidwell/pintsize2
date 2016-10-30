#!/usr/bin/env php
<?php
require __DIR__ . '/../bootstrap.php';

use Symfony\Component\Console\Application;

$f3 = \Base::instance();
$application = new Application();

foreach (new DirectoryIterator(__DIR__ . '/Commands') as $file) {
    if ($file->getExtension() != 'php') {
        continue;
    }
    $c = '\Pintsize\CLI\Commands\\' . $file->getBasename('.php');
    $application->add(new $c());
}

$application->run();
