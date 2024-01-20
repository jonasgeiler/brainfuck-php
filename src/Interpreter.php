<?php declare(strict_types=1);

namespace Brainfuck;

/**
 * Represents a brainfuck interpreter.
 */
class Interpreter {

	/**
	 * Interpret a given brainfuck instruction linked list.
	 * @param \Brainfuck\Instruction $rootInstruction The start of the linked
	 *     list.
	 */
	static public function interpret(
		Instruction $rootInstruction,
		Size $tapeSize = Size::Bit16,
		Size $cellSize = Size::Bit8,
		EofBehavior $eofBehavior = EofBehavior::Ignore,
	) {
		$tapeSize = $tapeSize->value;
		$cellSize = $cellSize->value;

		// Execute the instructions
		$instruction = $rootInstruction;
		$tape = array_fill(0, $tapeSize + 1, 0);
		$tapePointer = 0;
		while ($instruction !== null) {
			switch ($instruction->opcode) {
				case Opcode::Jump:
					if ($instruction->amount < 0) {
						$tapePointer = (
							(
								$tapePointer
								- (abs($instruction->amount) & $tapeSize)
							)
							& $tapeSize
						);
					} else {
						$tapePointer = (
							(
								$tapePointer
								+ ($instruction->amount & $tapeSize)
							)
							& $tapeSize
						);
					}
					break;

				case Opcode::Add:
					if ($instruction->amount < 0) {
						$tape[$tapePointer] = (
							(
								$tape[$tapePointer]
								- (abs($instruction->amount) & $cellSize)
							)
							& $cellSize
						);
					} else {
						$tape[$tapePointer] = (
							(
								$tape[$tapePointer]
								+ ($instruction->amount & $cellSize)
							)
							& $cellSize
						);
					}
					break;

				case Opcode::LoopStart:
					if ($tape[$tapePointer] === 0) {
						$instruction = $instruction->match->next;
						continue 2;
					}
					break;

				case Opcode::LoopEnd:
					if ($tape[$tapePointer] !== 0) {
						$instruction = $instruction->match->next;
						continue 2;
					}
					break;

				case Opcode::Input:
					$input = fgetc(STDIN);
					if ($input === false) { // Input is EOF
						if ($eofBehavior === EofBehavior::Ignore) {
							break;
						}

						$tape[$tapePointer] = $eofBehavior->value;
					} elseif ($input === PHP_EOL) { // Input is newline
						$tape[$tapePointer] = 10; // 10 is the ASCII code for \n
					} else {
						$tape[$tapePointer] = ord($input);
					}
					break;

				case Opcode::Output:
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

				case Opcode::Clear:
					$tape[$tapePointer] = 0;
					break;

				case Opcode::ScanRight:
				case Opcode::ScanLeft:
					$dir = $instruction->opcode === Opcode::ScanRight ? 1 : -1;
					$oldTapePointer = $tapePointer;
					while ($tape[$tapePointer] !== 0) {
						$tapePointer = ($tapePointer + $dir) & $tapeSize;

						if ($tapePointer === $oldTapePointer) {
							throw new Exceptions\InfiniteLoopException();
						}
					}
					break;

				case Opcode::Copy:
				default:
					throw new Exceptions\UnknownOpcodeException();
			}

			$instruction = $instruction->next;
		}
	}
}
