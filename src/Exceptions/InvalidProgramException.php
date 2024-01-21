<?php

namespace Brainfuck\Exceptions;

use Exception;

/**
 * Exception that is thrown when the parser encounters an invalid brainfuck
 * program, this is most likely caused by an "empty program".
 */
class InvalidProgramException extends Exception {
	public function __construct() {
		parent::__construct('Invalid brainfuck program');
	}
}
