#!/usr/bin/env php
<?php declare(strict_types=1);

if (!ini_get('date.timezone')) {
	ini_set('date.timezone', 'UTC');
}

if (isset($GLOBALS['_composer_autoload_path'])) {
	define('COMPOSER_AUTOLOAD_PATH', $GLOBALS['_composer_autoload_path']);

	unset($GLOBALS['_composer_autoload_path']);
} else {
	foreach (
		[
			__DIR__ . '/../../autoload.php',
			__DIR__ . '/../vendor/autoload.php',
			__DIR__ . '/vendor/autoload.php',
		] as $file
	) {
		if (file_exists($file)) {
			define('COMPOSER_AUTOLOAD_PATH', $file);

			break;
		}
	}

	unset($file);
}

if (!defined('COMPOSER_AUTOLOAD_PATH')) {
	fwrite(
		STDERR,
		'You need to set up the project dependencies using Composer:'
		. PHP_EOL
		. PHP_EOL
		. '    composer install'
		. PHP_EOL
		. PHP_EOL
		. 'You can learn all about Composer on https://getcomposer.org/.'
		. PHP_EOL,
	);

	die(1);
}

require COMPOSER_AUTOLOAD_PATH;

//$fp = fopen($argv[1], 'r');
//\Brainfuck\BrainfuckInterpreter::interpret($fp);

//$program = file_get_contents(__DIR__ . '/../examples/gameoflife.bf');
$program = '>><<';
$root = \Brainfuck\Parser::parse($program);
var_dump($root);
$i = $root;
while ($i !== null) {
    echo $i . PHP_EOL;
    $i = $i->next;
}
