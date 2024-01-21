<?php

namespace Brainfuck\Exceptions;

use Exception;

/**
 * Exception that is thrown when the parser was passed an invalid output stream.
 */
class InvalidOutputStreamException extends Exception {
	public function __construct() {
		parent::__construct('Invalid output stream');
	}
}
