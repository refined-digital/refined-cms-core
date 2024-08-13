<?php

declare(strict_types=1);

$finder = Symfony\Component\Finder\Finder::create()
    ->in(__DIR__.'/app')
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

    return (new PhpCsFixer\Config())->setRules([
        '@PhpCsFixer' => true,
        'declare_strict_types' => true,
        'php_unit_method_casing' => false,
        'multiline_whitespace_before_semicolons' => false,
        'yoda_style' => false,
        'php_unit_test_class_requires_covers' => false,
        'php_unit_internal_class' => false,
    ])
        ->setCacheFile(__DIR__.'/.php-cs-fixer.cache')
        ->setLineEnding("\n")
        ->setFinder($finder);
