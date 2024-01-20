<?php

namespace Brainfuck\Exceptions;

use Exception;

/**
 * Exception that is thrown when a loop-start operator (`[`) has no matching
 * loop-end operator (`]`).
 */
class UnmatchedLoopStartException extends Exception {
	public function __construct() {
		parent::__construct('Unmatched [');
	}
}
