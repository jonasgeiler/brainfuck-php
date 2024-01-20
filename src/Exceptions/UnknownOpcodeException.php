<?php

namespace Brainfuck\Exceptions;

use Exception;

/**
 * Exception that is thrown when an unknown opcode was detected while
 * interpreting the program. This shouldn't normally happen and means that the
 * parser is faulty.
 */
class UnknownOpcodeException extends Exception {
	public function __construct() {
		parent::__construct('Unknown opcode');
	}
}
