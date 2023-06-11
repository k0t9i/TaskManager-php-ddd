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
    ->setFinder($finder)
;
