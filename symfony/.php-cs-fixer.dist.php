<?php

$finder = (new PhpCsFixer\Finder())
    ->in([
        dirname(__DIR__) . '/src',
        __DIR__ . '/src',
    ])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
    ])
    ->setCacheFile(__DIR__ . '/var/cache/.php-cs-fixer.cache')
    ->setFinder($finder)
;
