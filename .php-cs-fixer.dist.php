<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/framework')
    ->in(__DIR__ . '/components')
    ->in(__DIR__ . '/tooling')
    ->in(__DIR__ . '/tests/Unit/Framework')
    ->in(__DIR__ . '/tests/Feature/Framework')
    ->in(__DIR__ . '/tests/Contract')
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$config = new PhpCsFixer\Config();

return $config->setRules([
                             '@PSR12'                            => true,
                             '@PHP80Migration'                                  => true,
                             '@PHP81Migration'                                  => true,
                             '@PHP82Migration'                                  => true,
                             '@PHP83Migration'                                  => true,
                             'array_syntax'                      => ['syntax' => 'short'],
                             'ordered_imports'                   => ['sort_algorithm' => 'alpha'],
                             'no_unused_imports'                 => true,
                             'not_operator_with_successor_space' => true,
                             'trailing_comma_in_multiline'       => ['elements' => ['arrays', 'arguments', 'parameters']],
                             'phpdoc_scalar'                     => true,
                             'unary_operator_spaces'             => true,
                             'binary_operator_spaces'            => [
                                 'default' => 'align_single_space_minimal',
                             ],
                             'blank_line_before_statement'       => [
                                 'statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try'],
                             ],
                             'phpdoc_single_line_var_spacing'    => true,
                             'phpdoc_var_without_name'           => true,
                             'method_argument_space'             => [
                                 'on_multiline'                     => 'ensure_fully_multiline',
                                 'keep_multiple_spaces_after_comma' => true,
                             ],
                             'type_declaration_spaces'           => true,
                             'visibility_required'               => [
                                 'elements' => ['property', 'method', 'const'],
                             ],
                             'concat_space'                      => [
                                 'spacing' => 'one',
                             ],
                             'declare_strict_types'              => true,
                             'fully_qualified_strict_types'      => true,
                             'global_namespace_import'           => [
                                 'import_classes'   => true,
                                 'import_constants' => true,
                                 'import_functions' => true,
                             ],
                             'no_trailing_whitespace'            => true,
                             'no_whitespace_in_blank_line'       => true,
                             'single_quote'                      => true,
                             'no_extra_blank_lines'              => [
                                 'tokens' => [
                                     'extra',
                                     'throw',
                                     'use',
                                 ],
                             ],
                             'nullable_type_declaration_for_default_null_value' => ['use_nullable_type_declaration' => false],
                             'static_lambda'                                    => true,
                             'phpdoc_to_comment'                                => false,
                             'phpdoc_align'                                     => ['align' => 'left'],
                             'modernize_strpos'                                 => true,
                             'no_alias_functions'                               => true,
                             'use_arrow_functions'                              => true,
                             'void_return'                                      => true,
                         ])
    ->setFinder($finder)
    ->setRiskyAllowed(true);
