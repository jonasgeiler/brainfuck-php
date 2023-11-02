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
	) {
		$tapeSize = $tapeSize->value;
		$cellSize = $cellSize->value;

		// Execute the instructions
		$instruction = $rootInstruction;
		$tape = array_fill(0, $tapeSize, 0);
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
					throw new \Exception('To be implemented');
					break;
				case Opcode::Copy:
					throw new \Exception('To be implemented');
					break;
				case Opcode::ScanLeft:
					throw new \Exception('To be implemented');
					break;
				case Opcode::ScanRight:
					throw new \Exception('To be implemented');
					break;
				default:
					throw new \Exception('Unknown opcode');
			}

			$instruction = $instruction->next;
		}
	}
}
