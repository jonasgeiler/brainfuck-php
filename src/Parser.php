<?php declare(strict_types=1);

namespace Brainfuck;

/**
 * Parses brainfuck programs and converts them into an optimized and
 * interpretable set of instructions.
 */
class Parser {

	/**
	 * Parse a given brainfuck program.
	 * @param string $program The brainfuck program to parse.
	 * @throws \Exception
	 */
	static public function parse(
		string &$program,
		string $opJumpRight = '>',
		string $opJumpLeft = '<',
		string $opAddIncrease = '+',
		string $opAddDecrease = '-',
		string $opLoopStart = '[',
		string $opLoopEnd = ']',
		string $opOutput = '.',
		string $opInput = ',',
	): ?Instruction {
		$rootInstruction = new Instruction();

		$programLength = strlen($program);
		$instruction = $rootInstruction;
		$loopStack = [];
		for ($pi = 0; $pi < $programLength; $pi++) {
			switch ($program[$pi]) {
				case $opJumpRight:
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

				case $opJumpLeft:
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

				case $opAddIncrease:
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

				case $opAddDecrease:
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

				case $opLoopStart:
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

					$instruction->opcode = Opcode::LoopStart;
					$instruction->amount = null;

					// Just add the current instruction to the loop stack.
					$loopStack[] = $instruction;
					break;

				case $opLoopEnd:
					// Check if the loop stack is currently empty, which means
					// there are too many ']' in the program:
					if (count($loopStack) === 0) {
						// Throw a syntax error.
						throw new \Exception('Unmatched ]'); // TODO: Use custom error
					}

					// TODO: Skip if empty loop

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

					$instruction->opcode = Opcode::LoopEnd;
					$instruction->amount = null;

					// Get the last item of the loop stack, which should be the
					// matching LoopStart instruction, and then create a link.
					$instruction->match = array_pop($loopStack);
					$instruction->match->match = $instruction;
					break;

				case $opOutput:
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

				case $opInput:
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

		// Check if the loop stack is not empty after parsing, which means
		// there are too many '[' in the program:
		if (count($loopStack) !== 0) {
			// Throw a syntax error.
			throw new \Exception('Unmatched [');
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
