<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
	->in(__DIR__ . '/src')
	->in(__DIR__ . '/tests')
	->exclude('temp')
;

return (new Config())
	->setUsingCache(FALSE)
	->setIndent("\t")
	->setRules([
		'@PSR2' => TRUE,
		'statement_indentation' => FALSE, # keeps the aligned multi-constant declarations
		'array_syntax' => ['syntax' => 'short'],
		'trailing_comma_in_multiline' => ['elements' => ['arrays']],
		'constant_case' => [
			'case' => 'upper',
		],
		'declare_strict_types' => TRUE,
		'phpdoc_align' => TRUE,
		'blank_line_after_opening_tag' => TRUE,
		'blank_line_before_statement' => [
			'statements' => ['break', 'continue', 'declare', 'return'],
		],
		'blank_line_after_namespace' => TRUE,
		'blank_lines_before_namespace' => TRUE,
		'return_type_declaration' => [
			'space_before' => 'none',
		],
		'ordered_imports' => [
			'sort_algorithm' => 'length',
		],
		'no_unused_imports' => TRUE,
		'single_line_after_imports' => TRUE,
		'no_leading_import_slash' => TRUE,
	])
	->setRiskyAllowed(TRUE)
	->setFinder($finder)
;
