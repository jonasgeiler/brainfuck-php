<?php declare(strict_types=1);

namespace Brainfuck;

/**
 * Represents an optimized brainfuck opcode.
 * This is not your standard set of brainfuck opcodes, since some are the result
 * of finding common patterns in the program or combining opcodes together.
 */
enum Opcode {
	/** Jump backward or forward in the program a specific amount */
	case Jump;

	/** Increases the current cell value by a specific amount (can also decrease) */
	case Add;

	/** Start of a loop */
	case LoopStart;

	/** End of a loop */
	case LoopEnd;

	/** Read user input */
	case Input;

	/** Output something to the user */
	case Output;

	/** Clear value of current cell */
	case Clear;

	/** Copy values of cells to other cells */
	case Copy;

	/** Move left until the first empty cell */
	case ScanLeft;

	/** Move right until the first empty cell */
	case ScanRight;
}
