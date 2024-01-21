<?php

namespace Brainfuck\Exceptions;

use Exception;

/**
 * Exception that is thrown when the parser was passed an invalid input stream.
 */
class InvalidInputStreamException extends Exception {
	public function __construct() {
		parent::__construct('Invalid input stream');
	}
}
