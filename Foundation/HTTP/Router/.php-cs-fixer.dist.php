<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude([
        'vendor',
        'tests',
        'storage',
        'public',
        'docker',
        'tmp',
        'tools',
        '.idea',
        '.git',
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$config = new PhpCsFixer\Config();
return $config
    ->setRules([
        // PSR-12 coding standard
        '@PSR12' => true,

        // Additional enterprise rules
        'strict_param' => true,
        'strict_return' => true,
        'declare_strict_types' => true,

        // Array formatting
        'array_syntax' => ['syntax' => 'short'],
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_whitespace_before_comma_in_array' => true,
        'whitespace_after_comma_in_array' => true,

        // String formatting
        'single_quote' => true,
        'no_useless_concat_operator' => true,

        // Function calls
        'function_declaration' => true,
        'function_typehint_space' => true,
        'no_spaces_after_function_name' => true,
        'no_spaces_inside_parenthesis' => true,

        // Control structures
        'elseif' => true,
        'include' => true,
        'no_else_return' => true,
        'no_useless_else' => true,

        // Classes and objects
        'class_attributes_separation' => ['elements' => ['const' => 'one', 'method' => 'one', 'property' => 'one']],
        'class_definition' => true,
        'no_blank_lines_after_class_opening' => true,
        'no_null_property_initialization' => true,
        'ordered_class_elements' => ['order' => ['use_trait', 'constant_public', 'constant_protected', 'constant_private', 'property_public', 'property_protected', 'property_private', 'construct', 'destruct', 'magic', 'method_public', 'method_protected', 'method_private']],
        'protected_to_private' => true,
        'self_accessor' => true,

        // Namespaces
        'no_leading_import_slash' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'single_import_per_statement' => true,

        // PHPDoc
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_align' => true,
        'phpdoc_annotation_without_dot' => true,
        'phpdoc_indent' => true,
        'phpdoc_inline_tag_normalizer' => true,
        'phpdoc_no_access' => true,
        'phpdoc_no_alias_tag' => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_no_package' => true,
        'phpdoc_no_useless_inheritdoc' => true,
        'phpdoc_order' => true,
        'phpdoc_return_self_reference' => true,
        'phpdoc_scalar' => true,
        'phpdoc_separation' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_summary' => true,
        'phpdoc_tag_type' => true,
        'phpdoc_to_comment' => true,
        'phpdoc_trim' => true,
        'phpdoc_types' => true,
        'phpdoc_var_without_name' => true,

        // Comments
        'no_empty_comment' => true,
        'no_empty_phpdoc' => true,
        'single_line_comment_style' => true,

        // Whitespace and formatting
        'binary_operator_spaces' => true,
        'blank_line_after_namespace' => true,
        'blank_line_before_statement' => ['statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try']],
        'cast_spaces' => true,
        'concat_space' => ['spacing' => 'one'],
        'increment_style' => ['style' => 'post'],
        'indentation_type' => true,
        'line_ending' => true,
        'lowercase_cast' => true,
        'lowercase_keywords' => true,
        'lowercase_static_reference' => true,
        'magic_constant_casing' => true,
        'magic_method_casing' => true,
        'method_chaining_indentation' => true,
        'multiline_whitespace_before_semicolons' => true,
        'native_function_casing' => true,
        'native_function_type_declaration_casing' => true,
        'new_with_braces' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_closing_tag' => true,
        'no_empty_statement' => true,
        'no_extra_blank_lines' => ['tokens' => ['extra', 'parenthesis_brace_block', 'square_brace_block', 'throw', 'use']],
        'no_leading_namespace_whitespace' => true,
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_short_bool_cast' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'no_spaces_after_function_name' => true,
        'no_spaces_around_offset' => true,
        'no_spaces_inside_parenthesis' => true,
        'no_trailing_comma_in_singleline_array' => true,
        'no_trailing_whitespace' => true,
        'no_trailing_whitespace_in_comment' => true,
        'no_unneeded_control_parentheses' => true,
        'no_unneeded_curly_braces' => true,
        'no_unneeded_final_method' => true,
        'no_unused_imports' => true,
        'no_whitespace_before_comma_in_array' => true,
        'no_whitespace_in_blank_line' => true,
        'normalize_index_brace' => true,
        'object_operator_without_whitespace' => true,
        'operators_spaces' => true,
        'php_unit_fqcn_annotation' => true,
        'php_unit_method_casing' => true,
        'return_type_declaration' => true,
        'semicolon_after_instruction' => true,
        'short_scalar_cast' => true,
        'single_blank_line_at_eof' => true,
        'single_class_element_per_statement' => true,
        'single_import_per_statement' => true,
        'single_line_after_imports' => true,
        'single_quote' => true,
        'space_after_semicolon' => true,
        'standardize_increment' => true,
        'standardize_not_equals' => true,
        'switch_case_semicolon_to_colon' => true,
        'switch_case_space' => true,
        'ternary_operator_spaces' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
        'trim_array_spaces' => true,
        'unary_operator_spaces' => true,
        'visibility_required' => true,
        'whitespace_after_comma_in_array' => true,
        'yoda_style' => true,

        // Custom router-specific rules
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true, 'allow_unused_params' => true],
        'phpdoc_to_property_type' => false, // Keep PHPDoc for readonly properties
        'phpdoc_to_return_type' => false,   // Keep PHPDoc for complex return types
        'phpdoc_to_param_type' => false,    // Keep PHPDoc for complex param types
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setUsingCache(true);