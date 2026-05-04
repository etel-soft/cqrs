<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->files()
    ->name(patterns: '*.php')
    ->in(dirs: __DIR__ . '/src')
    ->in(dirs: __DIR__ . '/tests');

return new Config()
    ->setUsingCache(usingCache: true)
    ->setRiskyAllowed(isRiskyAllowed: true)
    ->setRules(rules: [
        '@PSR12' => true,
        '@PHP8x0Migration' => true,
        '@PHP8x1Migration' => true,
        '@PHP8x2Migration' => true,
        '@PHP8x3Migration' => true,
        '@PHP8x4Migration' => true,
        '@PhpCsFixer' => true,
        'array_syntax' => ['syntax' => 'short'],
        'blank_line_before_statement' => [
            'statements' => [
                'break', 'case', 'continue', 'declare', 'default', 'do', 'for',
                'foreach', 'if', 'return', 'switch', 'throw', 'try', 'while', 'yield',
            ],
        ],
        'class_attributes_separation' => [
            'elements' => ['method' => 'one', 'property' => 'one', 'trait_import' => 'none'],
        ],
        'class_definition' => ['single_line' => false],
        'concat_space' => ['spacing' => 'one'],
        'declare_strict_types' => true,
        'global_namespace_import' => ['import_classes' => true, 'import_constants' => true, 'import_functions' => true],
        'is_null' => true,
        'mb_str_functions' => true,
        'method_argument_space' => ['on_multiline' => 'ignore'],
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        'native_function_invocation' => ['include' => ['@all']],
        'no_extra_blank_lines' => [
            'tokens' => [
                'case', 'continue', 'curly_brace_block', 'default', 'extra',
                'parenthesis_brace_block', 'return', 'square_brace_block',
                'switch', 'throw', 'use',
            ],
        ],
        'non_printable_character' => ['use_escape_sequences_in_strings' => true],
        'no_superfluous_phpdoc_tags' => false,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_class_elements' => true,
        'ordered_imports' => ['imports_order' => ['class', 'const', 'function'], 'sort_algorithm' => 'alpha'],
        'phpdoc_order' => true,
        'phpdoc_separation' => ['skip_unlisted_annotations' => true],
        'phpdoc_to_comment' => ['ignored_tags' => ['todo', 'var'], 'allow_before_return_statement' => true],
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'php_unit_test_class_requires_covers' => false,
        'psr_autoloading' => true,
        'single_line_comment_style' => ['comment_types' => ['hash']],
        'single_line_throw' => false,
        'strict_comparison' => true,
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
    ])
    ->setFinder(finder: $finder);
