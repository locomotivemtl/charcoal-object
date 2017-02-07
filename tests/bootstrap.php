<?php

use Pimple\Container;

// Composer autoloader for Charcoal's psr4-compliant Unit Tests
$autoloader = require __DIR__.'/../vendor/autoload.php';
$autoloader->add('Charcoal\\', __DIR__.'/src/');
$autoloader->add('Charcoal\\Tests\\', __DIR__);


$GLOBALS['container'] = new Container([
    'config' => [
        'base_path' => (dirname(__DIR__).'/'),
        'metadata' => [
            'paths' => [
                dirname(__DIR__).'/metadata/'
            ]
        ]
    ],
    'cache'  => new \Stash\Pool(),
    'logger' => new \Psr\Log\NullLogger()
]);
