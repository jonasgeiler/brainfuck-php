<?php declare(strict_types=1);

namespace Brainfuck;

// Settings
const DEFAULT_TAPE_SIZE = 32768;
const DEFAULT_CELL_SIZE = 256;

// Operators
const OP_POINTER_INCREASE = '+';
const OP_POINTER_DECREASE = '-';
const OP_POINTER_RIGHT = '>';
const OP_POINTER_LEFT = '<';
const OP_OUTPUT = '.';
const OP_INPUT = ',';
const OP_LOOP_START = '[';
const OP_LOOP_END = ']';

/**
 * Represents a brainfuck interpreter.
 */
class OldInterpreter {

	/**
	 * Interpret given brainfuck code from a file or resource.
	 * @param resource $programStream The file handler returned from `fopen()`
	 *     or something similar.
	 * @param int $tapeSize The size of the tape.
	 * @param int $cellSize The maximum size of a cell in the tape.
	 */
	static public function interpret(
		$program,
		int $tapeSize = DEFAULT_TAPE_SIZE,
		int $cellSize = DEFAULT_CELL_SIZE,
	) {
		// Create an array of optimized instructions from the program stream
		$instructions = [];
		$instructionsCount = 0;
		for ($c = 0; $c < strlen($program); $c++) {
			$char = $program[$c];

			if (
				$char !== OP_POINTER_INCREASE
				&& $char !== OP_POINTER_DECREASE
				&& $char !== OP_POINTER_RIGHT
				&& $char !== OP_POINTER_LEFT
				&& $char !== OP_INPUT
				&& $char !== OP_OUTPUT
				&& $char !== OP_LOOP_START
				&& $char !== OP_LOOP_END
			) {
				continue;
			}

			if (
				$instructionsCount
				&& (
					$char === OP_POINTER_INCREASE
					|| $char === OP_POINTER_DECREASE
					|| $char === OP_POINTER_RIGHT
					|| $char === OP_POINTER_LEFT
				)
				&& $instructions[$instructionsCount - 1][0] === $char
			) {
				$instructions[$instructionsCount - 1][1]++;
			} else {
				$instructions[$instructionsCount++] = [ $char, 1 ];
			}
		}

		// Go through the instructions and find all the loops
		$stack = [];
		$stackPointer = 0;
		$matches = [];
		for ($i = 0; $i < $instructionsCount; $i++) {
			if ($instructions[$i][0] === OP_LOOP_START) {
				$stack[$stackPointer++] = $i;
			}

			if ($instructions[$i][0] === OP_LOOP_END) {
				if ($stackPointer === 0) {
					throw new \Error('Unmatched ]');
				}

				$result = $stack[--$stackPointer];
				$matches[$result] = $i;
				$matches[$i] = $result;
			}
		}
		if ($stackPointer !== 0) {
			throw new \Error('Unmatched [');
		}

		// Execute the instructions
		$instructionsPointer = 0;
		$tape = array_fill(0, $tapeSize, 0);
		$tapePointer = 0;
		while ($instructionsPointer < $instructionsCount) {
			$op = $instructions[$instructionsPointer][0];
			switch ($op) {
				case OP_POINTER_INCREASE:
					$tape[$tapePointer] += $instructions[$instructionsPointer][1];
					if ($tape[$tapePointer] >= $cellSize) {
						$tape[$tapePointer] -= $cellSize;
					}
					break;

				case OP_POINTER_DECREASE:
					$tape[$tapePointer] -= $instructions[$instructionsPointer][1];
					if ($tape[$tapePointer] < 0) {
						$tape[$tapePointer] += $cellSize;
					}
					break;

				case OP_POINTER_RIGHT:
					$tapePointer += $instructions[$instructionsPointer][1];
					if ($tapePointer >= $tapeSize) {
						throw new \Error('Tape pointer overflow');
					}
					break;

				case OP_POINTER_LEFT:
					$tapePointer -= $instructions[$instructionsPointer][1];
					if ($tapePointer < 0) {
						throw new \Error('Tape pointer underflow');
					}
					break;

				case OP_INPUT:
					if (($input = fgetc(STDIN)) === false) {
						throw new \Error('Input failed');
					}

					if ($input === PHP_EOL) {
						$input = 10;
					} else {
						$input = ord($input);
					}
					$tape[$tapePointer] = $input;
					break;

				case OP_OUTPUT:
					$output = $tape[$tapePointer];
					if ($output === 10) {
						$output = PHP_EOL;
					} else {
						$output = chr($output);
					}

					if (fputs(STDOUT, $output) === false) {
						throw new \Error('Output failed');
					}
					break;

				case OP_LOOP_START:
					if ($tape[$tapePointer] === 0) {
						$instructionsPointer = $matches[$instructionsPointer];
					}
					break;

				case OP_LOOP_END:
					if ($tape[$tapePointer] !== 0) {
						$instructionsPointer = $matches[$instructionsPointer];
					}
					break;

				default:
					throw new \Error('Unknown instruction');
			}

			$instructionsPointer++;
		}
	}
}
