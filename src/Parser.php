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
						&& $instruction->amount + $amountDiff < PHP_INT_MAX
						&& $instruction->amount + $amountDiff > PHP_INT_MIN + 1
					) {
						$instruction->amount += $amountDiff;
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
						&& $instruction->amount + $amountDiff < PHP_INT_MAX
						&& $instruction->amount + $amountDiff > PHP_INT_MIN + 1
					) {
						$instruction->amount += $amountDiff;
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
						if (
							$instruction->previous->opcode === Opcode::Add
							&& (
								$instruction->previous->amount === 1
								|| $instruction->previous->amount === -1
							)
						) {
							// TODO: Overwrite previous add instruction, since
							//  it wouldn't do anything because of the clear.
							$instruction = $instruction->match;
							$instruction->opcode = Opcode::Clear;
							$instruction->amount = null;
							$instruction->next = null;
							$instruction->match = null;
						} else if (
							$instruction->previous->opcode === Opcode::Jump
							&& $instruction->previous->amount === 1
						) {
							$instruction = $instruction->match;
							$instruction->opcode = Opcode::ScanRight;
							$instruction->amount = null;
							$instruction->next = null;
							$instruction->match = null;
						} else if (
							$instruction->previous->opcode === Opcode::Jump
							&& $instruction->previous->amount === -1
						) {
							$instruction = $instruction->match;
							$instruction->opcode = Opcode::ScanLeft;
							$instruction->amount = null;
							$instruction->next = null;
							$instruction->match = null;
						}
					}

					// TODO: Detect copy loops? (eg. [->+<] ???)
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

	protected static function revertInstructionIfNoop(
		Instruction &$instruction,
	) {
		if (
			$instruction->amount === 0
			&& (
				(
					$instruction->opcode === Opcode::Jump
					&& $instruction->previous->opcode === Opcode::Add
				)
				|| (
					$instruction->opcode === Opcode::Add
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
		?int $amount = null,
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
				|| $instruction->amount !== 0
			)
		) {
			// Create the next instruction
			$instruction->next = new Instruction();
			$instruction->next->previous = $instruction;
			$instruction = $instruction->next;
		}

		$instruction->opcode = $opcode;
		$instruction->amount = $amount;
	}
}
