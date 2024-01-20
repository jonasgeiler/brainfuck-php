<?php

namespace Brainfuck\Exceptions;

use Exception;

/**
 * Exception that is thrown when a loop-end operator (`]`) has no matching
 * loop-start operator (`[`).
 */
class UnmatchedLoopEndException extends Exception {
	public function __construct() {
		parent::__construct('Unmatched ]');
	}
}
