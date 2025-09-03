<?php
$header = <<<HEADER
This file is part of the "Wordpress Wrapper Loader" package.

(c) Skoropadskyi Roman <zipo.ckorop@gmail.com>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
HEADER;

$finder = (new PhpCsFixer\Finder())
    ->files()
    ->in([
        'src',
        'tests',
    ])
    ->exclude([
        'vendor',
    ])
    ->name([
        '*.php',
        'console',
    ])
;

$config = new PhpCsFixer\Config();

return $config
    ->setCacheFile(__DIR__ . '/.php_cs.cache')
    ->setFinder($finder)
    ->setRules([
        '@PSR2' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP84Migration' => true,
        'list_syntax' => ['syntax' => 'short'],
        'array_syntax' => ['syntax' => 'short'],
        'concat_space' => ['spacing' => 'one'],
        'compact_nullable_typehint' => true,
        'logical_operators' => true,
        'no_null_property_initialization' => true,
        'no_php4_constructor' => true,
        'no_superfluous_elseif' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'combine_consecutive_issets' => true,
        'random_api_migration' => true,
        'native_function_invocation' => ['strict' => true],
        'multiline_promoted_properties' => true,
        'blank_line_before_statement' => ['statements' => [
            'break',
            'continue',
            'declare',
            'return',
            'throw',
            'try',
            'for',
            'foreach',
            'while',
            'do',
            'if',
            'switch',
        ]],
    ])
    ->setRiskyAllowed(true)
;