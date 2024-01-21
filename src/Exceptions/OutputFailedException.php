<?php

namespace Brainfuck\Exceptions;

use Exception;

/**
 * Exception that is thrown when writing the output of a program failed.
 * In most cases this means there is a problem with the STDOUT stream.
 */
class OutputFailedException extends Exception {
	public function __construct() {
		parent::__construct('Writing program output failed');
	}
}
