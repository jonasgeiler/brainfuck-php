<?php

namespace Brainfuck\Exceptions;

use Exception;

/**
 * Exception that is thrown when an infinite loop was detected, either during
 * parsing or during interpreting of the program.
 */
class InfiniteLoopException extends Exception {
	public function __construct() {
		parent::__construct('Infinite loop detected');
	}
}
