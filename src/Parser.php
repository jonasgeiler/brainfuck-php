<?php declare(strict_types=1);

namespace Brainfuck;

/**
 * Parses brainfuck programs and converts them into an optimized and
 * interpretable set of instructions.
 */
class Parser {

	/**
	 * Parse a given brainfuck program.
	 *
	 * @param string $program The brainfuck program to parse.
	 * @param string $opJumpRight The jump-right operator
	 * @param string $opJumpLeft The jump-left operator
	 * @param string $opAddIncrease The add-increase operator
	 * @param string $opAddDecrease The add-decrease operator
	 * @param string $opLoopStart The loop-start operator
	 * @param string $opLoopEnd The loop-end operator
	 * @param string $opOutput The output operator
	 * @param string $opInput The input operator
	 *
	 * @return \Brainfuck\Instruction A root instruction if successful and a
	 *
	 * @throws \Brainfuck\Exceptions\UnmatchedLoopEndException
	 * @throws \Brainfuck\Exceptions\UnmatchedLoopStartException
	 * @throws \Brainfuck\Exceptions\InvalidProgramException
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
	): Instruction {
		$rootInstruction = new Instruction();

		$programLength = strlen($program);
		$instruction = $rootInstruction;
		$loopStartStack = [];
		for ($pi = 0; $pi < $programLength; $pi++) {
			$op = $program[$pi];

			switch ($op) {
				case $opJumpRight:
				case $opJumpLeft:
					self::revertInstructionIfNoop($instruction);

					$amountDiff = $op === $opJumpRight ? 1 : -1;
					if (
						$instruction->opcode === Opcode::Jump
						&& $instruction->value + $amountDiff < PHP_INT_MAX
						&& $instruction->value + $amountDiff > PHP_INT_MIN + 1
					) {
						$instruction->value += $amountDiff;
					} else {
						self::initInstruction(
							$instruction,
							Opcode::Jump,
							$amountDiff,
						);
					}
					break;

				case $opAddIncrease:
				case $opAddDecrease:
					self::revertInstructionIfNoop($instruction);

					$amountDiff = $op === $opAddIncrease ? 1 : -1;
					if (
						$instruction->opcode === Opcode::Add
						&& $instruction->value + $amountDiff < PHP_INT_MAX
						&& $instruction->value + $amountDiff > PHP_INT_MIN + 1
					) {
						$instruction->value += $amountDiff;
					} else {
						self::initInstruction(
							$instruction,
							Opcode::Add,
							$amountDiff,
						);
					}
					break;

				case $opLoopStart:
					self::initInstruction($instruction, Opcode::LoopStart);
					$loopStartStack[] = $instruction;
					break;

				case $opLoopEnd:
					if (empty($loopStartStack)) {
						throw new Exceptions\UnmatchedLoopEndException();
					}

					self::initInstruction($instruction, Opcode::LoopEnd);
					$instruction->match = array_pop($loopStartStack);
					$instruction->match->match = $instruction;

					if ($instruction->match === $instruction->previous->previous) {
						// -> Try to detect clear or scan loops

						if (
							$instruction->previous->opcode === Opcode::Add
							&& (
								$instruction->previous->value === 1
								|| $instruction->previous->value === -1
							)
						) {
							// -> Clear loop detected
							// Example clear loops:
							// - `[-]`: set current cell to 0
							// - `[+]`: set current cell to 0 but other way

							if (
								$instruction->match->previous !== null
								&& $instruction->match->previous->opcode === Opcode::Add
							) {
								self::replaceInstruction(
									$instruction,
									$instruction->match->previous,
									Opcode::Clear,
								);
							} else {
								self::replaceInstruction(
									$instruction,
									$instruction->match,
									Opcode::Clear,
								);
							}
						} else if (
							$instruction->previous->opcode === Opcode::Jump
							&& $instruction->previous->value === 1
						) {
							// -> Scan right loop detected
							// Example clear loops:
							// - `[>]`: moves the pointer to the right until it
							//          finds a cell with value 0.

							self::replaceInstruction(
								$instruction,
								$instruction->match,
								Opcode::ScanRight,
							);
						} else if (
							$instruction->previous->opcode === Opcode::Jump
							&& $instruction->previous->value === -1
						) {
							// -> Scan left loop detected
							// Example clear loops:
							// - `[<]`: moves the pointer to the left until it
							//          finds a cell with value 0.

							self::replaceInstruction(
								$instruction,
								$instruction->match,
								Opcode::ScanLeft,
							);
						}
					} else if (
						$instruction->match->next->opcode === Opcode::Add
						&& $instruction->match->next->value === -1
					) {
						// -> Start if copy loop detected
						// Example copy loops:
						// - `[->+<]`: add current cell to other cell
						// - `[->-<]`: subtract current cell from other cell
						// - `[->+<]`: move value to different cell
						// - `[->+>+<<]`: copy value to multiple cells
						// - `[->++<]`: multiply value by 2
						// These copy loops always consist of the same parts:
						// 1. ADD: Always starts with a single '-'
						// 2. One or more of the following:
						//   a. JUMP: Move n cells to left or right
						//   b. ADD: Add or subtract from the cell
						// 4. JUMP: Move back n cells to original position

						// Try to detect the full copy loop:
						$i = $instruction->match->next->next;
						$offset = 0;
						$copies = [];
						while ($i !== null && $i !== $instruction) {
							if (
								$i->opcode === Opcode::Jump
								&& $i->next->opcode === Opcode::Add
							) {
								// This is one of the copy loop "moves"
								$offset += $i->value;
								// TODO: $offset int overflow check
								$copies[$offset] = $i->next->value;
								$i = $i->next->next; // Continue after the "move"
							} else if (
								$i->opcode === Opcode::Jump
								&& $i->value === -$offset
								&& $i->next === $instruction
							) {
								// This is the end of the copy loop
								self::replaceInstruction(
									$instruction,
									$instruction->match,
									Opcode::Copy,
									$copies,
								);
								break;
							} else {
								// If none of the above conditions are met,
								// then this is not a copy loop.
								break;
							}
						}
					}
					break;

				case $opOutput:
					self::initInstruction($instruction, Opcode::Output);
					break;

				case $opInput:
					self::initInstruction($instruction, Opcode::Input);
					break;
			}
		}

		// Check if the loop stack is not empty after parsing, which means
		// there are too many '[' in the program:
		if (!empty($loopStartStack)) {
			// Throw a syntax error.
			throw new Exceptions\UnmatchedLoopStartException();
		}

		// Check if the root instruction is empty:
		if ($rootInstruction->opcode === null) {
			// This means there are no instructions at all, so error.
			throw new Exceptions\InvalidProgramException();
		}

		// Check if the last instruction is an 'add 0' or 'jump 0' instruction:
		if (
			(
				$instruction->opcode === Opcode::Jump
				|| $instruction->opcode === Opcode::Add
			)
			&& $instruction->value === 0
		) {
			// Check if this is the first instruction in the linked list:
			if ($instruction->previous === null) {
				// Throw error since it's the first and last instruction,
				// meaning that there is no real program here.
				throw new Exceptions\InvalidProgramException();
			}

			// Unset the next instruction of the previous instruction, which
			// removes the last instruction from the linked list.
			$instruction->previous->next = null;
		}

		return $rootInstruction;
	}

	protected static function revertInstructionIfNoop(
		Instruction &$instruction,
	) {
		if (
			$instruction->value === 0
			&& (
				(
					$instruction->opcode === Opcode::Jump
					&& $instruction->previous !== null
					&& $instruction->previous->opcode === Opcode::Add
				)
				|| (
					$instruction->opcode === Opcode::Add
					&& $instruction->previous !== null
					&& $instruction->previous->opcode === Opcode::Jump
				)
			)
		) {
			$instruction->previous->next = null;
			$instruction = $instruction->previous;
		}
	}

	protected static function initInstruction(
		Instruction &$instruction,
		Opcode $opcode,
		int|array|null $value = null,
	): void {
		if (
			// Only create the next instruction if the current
			// one isn't still empty:
			$instruction->opcode !== null
			&& (
				// And also prevent creating the next instruction
				// if the current one is 'add 0', which doesn't
				// do anything therefore we can overwrite it.
				(
					$instruction->opcode !== Opcode::Jump
					&& $instruction->opcode !== Opcode::Add
				)
				|| $instruction->value !== 0
			)
		) {
			// Create the next instruction
			$instruction->next = new Instruction();
			$instruction->next->previous = $instruction;
			$instruction = $instruction->next;
		}

		$instruction->opcode = $opcode;
		$instruction->value = $value;
	}

	protected static function replaceInstruction(
		Instruction &$instruction,
		Instruction $newInstruction,
		Opcode $opcode,
		int|array|null $value = null,
	): void {
		$instruction = $newInstruction;
		$instruction->opcode = $opcode;
		$instruction->value = $value;
		$instruction->next = null;
		$instruction->match = null;
	}
}
