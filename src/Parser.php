<?php declare(strict_types=1);

namespace Brainfuck;

// If someone wants to define a custom brainfuck derivative that works the same
// as brainfuck, they can overwrite these constants with custom characters.
if (!defined('BRAINFUCK_CHAR_GT')) {
	define('BRAINFUCK_CHAR_GT', '>');
}
if (!defined('BRAINFUCK_CHAR_LT')) {
	define('BRAINFUCK_CHAR_LT', '<');
}
if (!defined('BRAINFUCK_CHAR_PLUS')) {
	define('BRAINFUCK_CHAR_PLUS', '+');
}
if (!defined('BRAINFUCK_CHAR_MINUS')) {
	define('BRAINFUCK_CHAR_MINUS', '-');
}
if (!defined('BRAINFUCK_CHAR_LSQB')) {
	define('BRAINFUCK_CHAR_LSQB', '[');
}
if (!defined('BRAINFUCK_CHAR_RSQB')) {
	define('BRAINFUCK_CHAR_RSQB', ']');
}
if (!defined('BRAINFUCK_CHAR_PERIOD')) {
	define('BRAINFUCK_CHAR_PERIOD', '.');
}
if (!defined('BRAINFUCK_CHAR_COMMA')) {
	define('BRAINFUCK_CHAR_COMMA', ',');
}

/**
 * Parses brainfuck programs and converts them into an optimized and
 * interpretable set of instructions.
 */
class Parser {

	/**
	 * Parse a given brainfuck program.
	 * @param string $program The brainfuck program to parse.
	 */
	static public function parse(string &$program): ?Instruction {
		$rootInstruction = new Instruction();

		$programLength = strlen($program);
		$instruction = $rootInstruction;
		for ($pi = 0; $pi < $programLength; $pi++) {
			switch ($program[$pi]) {
				case BRAINFUCK_CHAR_GT:
					// This makes sure to go back to the previous instruction if the
					// current one is a 'add 0' instruction, which doesn't do anything.
					if (
						// Check if the current instruction is 'add 0':
						$instruction->opcode === Opcode::Add
						&& $instruction->amount === 0
						// Check if the previous instruction is a jump instruction:
						&& $instruction->previous->opcode === Opcode::Jump
					) {
						// Reset back to the previous instruction and continue there.
						$instruction->previous->next = null;
						$instruction = $instruction->previous;
					}

					if (
						// First we check if the current instruction is already
						// a Jump instruction:
						$instruction->opcode === Opcode::Jump
						// And then we confirm that increasing the instruction
						// amount doesn't exceed the max integer value:
						&& $instruction->amount + 1 < PHP_INT_MAX
					) {
						// It's safe to just increase the amount and continue.
						$instruction->amount += 1;
					} else {
						// Either the current instruction isn't a Jump instruction,
						// or the amount exceeds the max integer value.
						// In this case we overwrite/create a new instruction:

						if (
							// Only create the next instruction if the current
							// one isn't still empty:
							$instruction->opcode !== null
							&& (
								// And also prevent creating the next instruction
								// if the current one is 'add 0', which doesn't
								// do anything therefore we can overwrite it.
								$instruction->opcode !== Opcode::Add
								|| $instruction->amount !== 0
							)
						) {
							// Create the next instruction
							$instruction->next = new Instruction();
							$instruction->next->previous = $instruction;
							$instruction = $instruction->next;
						}

						// Initialize the instruction with the opcode and amount
						$instruction->opcode = Opcode::Jump;
						$instruction->amount = 1;
					}
					break;

				case BRAINFUCK_CHAR_LT:
					if (
						$instruction->opcode === Opcode::Add
						&& $instruction->amount === 0
						&& $instruction->previous->opcode === Opcode::Jump
					) {
						$instruction->previous->next = null;
						$instruction = $instruction->previous;
					}

					if (
						$instruction->opcode === Opcode::Jump
						&& $instruction->amount - 1 > PHP_INT_MIN
					) {
						$instruction->amount -= 1;
					} else {
						if (
							$instruction->opcode !== null
							&& (
								$instruction->opcode !== Opcode::Add
								|| $instruction->amount !== 0
							)
						) {
							$instruction->next = new Instruction();
							$instruction->next->previous = $instruction;
							$instruction = $instruction->next;
						}

						$instruction->opcode = Opcode::Jump;
						$instruction->amount = -1;
					}
					break;

				case BRAINFUCK_CHAR_PLUS:
					if (
						$instruction->opcode === Opcode::Jump
						&& $instruction->amount === 0
						&& $instruction->previous->opcode === Opcode::Add
					) {
						$instruction->previous->next = null;
						$instruction = $instruction->previous;
					}

					if (
						$instruction->opcode === Opcode::Add
						&& $instruction->amount + 1 < PHP_INT_MAX
					) {
						$instruction->amount += 1;
					} else {
						if (
							$instruction->opcode !== null
							&& (
								$instruction->opcode !== Opcode::Jump
								|| $instruction->amount !== 0
							)
						) {
							$instruction->next = new Instruction();
							$instruction->next->previous = $instruction;
							$instruction = $instruction->next;
						}

						$instruction->opcode = Opcode::Add;
						$instruction->amount = 1;
					}
					break;

				case BRAINFUCK_CHAR_MINUS:
					if (
						$instruction->opcode === Opcode::Jump
						&& $instruction->amount === 0
						&& $instruction->previous->opcode === Opcode::Add
					) {
						$instruction->previous->next = null;
						$instruction = $instruction->previous;
					}

					if (
						$instruction->opcode === Opcode::Add
						&& $instruction->amount - 1 > PHP_INT_MIN
					) {
						$instruction->amount -= 1;
					} else {
						if (
							$instruction->opcode !== null
							&& (
								$instruction->opcode !== Opcode::Jump
								|| $instruction->amount !== 0
							)
						) {
							$instruction->next = new Instruction();
							$instruction->next->previous = $instruction;
							$instruction = $instruction->next;
						}

						$instruction->opcode = Opcode::Add;
						$instruction->amount = -1;
					}
					break;

				case BRAINFUCK_CHAR_LSQB:
				case BRAINFUCK_CHAR_RSQB:
					break;

				case BRAINFUCK_CHAR_PERIOD:
					if (
						$instruction->opcode !== null
						&& (
							(
								$instruction->opcode !== Opcode::Jump
								&& $instruction->opcode !== Opcode::Add
							)
							|| $instruction->amount !== 0
						)
					) {
						$instruction->next = new Instruction();
						$instruction->next->previous = $instruction;
						$instruction = $instruction->next;
					}

					$instruction->opcode = Opcode::Output;
					$instruction->amount = null;
					break;

				case BRAINFUCK_CHAR_COMMA:
					if (
						$instruction->opcode !== null
						&& (
							(
								$instruction->opcode !== Opcode::Jump
								&& $instruction->opcode !== Opcode::Add
							)
							|| $instruction->amount !== 0
						)
					) {
						$instruction->next = new Instruction();
						$instruction->next->previous = $instruction;
						$instruction = $instruction->next;
					}

					$instruction->opcode = Opcode::Input;
					$instruction->amount = null;
					break;

				default:
					continue 2;
			}
		}

		// Check if the root instruction is empty:
		if ($rootInstruction->opcode === null) {
			// This means there are no instructions at all, so just return null.
			return null;
		}

		// Check if the last instruction is an 'add 0' or 'jump 0' instruction:
		if (
			(
				$instruction->opcode === Opcode::Jump
				|| $instruction->opcode === Opcode::Add
			)
			&& $instruction->amount === 0
		) {
			// Check if this is the first instruction in the linked list:
			if ($instruction->previous === null) {
				// Just return null since it's the first and last instruction.
				return null;
			}

			// Unset the next instruction of the previous instruction, which
			// removes the last instruction from the linked list.
			$instruction->previous->next = null;
		}

		return $rootInstruction;
	}
}
