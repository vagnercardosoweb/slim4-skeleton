<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 01/07/2021 Vagner Cardoso
 */

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$header = sprintf("Vagner Cardoso <https://github.com/vagnercardosoweb>\n
@author Vagner Cardoso <vagnercardosoweb@gmail.com>
@link https://github.com/vagnercardosoweb
@license http://www.opensource.org/licenses/mit-license.html MIT License
@copyright %s Vagner Cardoso", date('d/m/Y'));

$finder = Finder::create()
    ->exclude([__DIR__ . '/vendor'])
    ->in(dirname(__DIR__))
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$config = new Config();
$config->setFinder($finder)
    ->setUsingCache(false)
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR1' => true,
        '@PSR2' => true,
        '@PhpCsFixer' => true,
        '@Symfony' => true,
        'single_line_throw' => false,
        'single_line_comment_style' => true,
        'align_multiline_comment' => true, // psr-5
        'array_indentation' => true,
        'array_syntax' => ['syntax' => 'short'],
        'cast_spaces' => ['space' => 'none'],
        'concat_space' => ['spacing' => 'none'],
        'compact_nullable_typehint' => true,
        'declare_equal_normalize' => ['space' => 'single'],
        // 'general_phpdoc_annotation_remove' => ['annotations' => ['author']],
        'increment_style' => ['style' => 'post'],
        'list_syntax' => ['syntax' => 'long'],
        'echo_tag_syntax' => ['format' => 'long'],
        'phpdoc_trim' => true,
        'phpdoc_summary' => true,
        'phpdoc_separation' => true,
        'phpdoc_to_comment' => true,
        'phpdoc_add_missing_param_annotation' => ['only_untyped' => false],
        'phpdoc_align' => true,
        'phpdoc_no_empty_return' => false,
        'phpdoc_order' => true,
        'phpdoc_no_useless_inheritdoc' => false,
        'phpdoc_var_without_name' => true,
        'protected_to_private' => false,
        'no_superfluous_phpdoc_tags' => false,
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
            'imports_order' => ['class', 'const', 'function'],
        ],
        'header_comment' => [
            'header' => $header,
            'comment_type' => 'comment',
            'location' => 'after_open',
        ],
    ]);

return $config;
