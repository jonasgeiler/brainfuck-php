<?php declare(strict_types=1);

namespace Brainfuck;

/**
 * Represents a brainfuck interpreter.
 */
class Interpreter {

	/**
	 * Interpret a given brainfuck instruction linked list.
	 *
	 * @param \Brainfuck\Instruction $rootInstruction The start of the linked
	 *     list.
	 * @param \Brainfuck\Size $tapeSize The size of the tape/memory.
	 * @param \Brainfuck\Size $cellSize The size of a cell in the tape/memory.
	 * @param \Brainfuck\EofBehavior $eofBehavior How to handle EOF outputs.
	 * @param false|resource $inputStream An input stream returned by fopen
	 * @param false|resource $outputStream An output stream returned by fopen
	 *
	 * @throws \Brainfuck\Exceptions\InfiniteLoopException
	 * @throws \Brainfuck\Exceptions\UnknownOpcodeException
	 * @throws \Brainfuck\Exceptions\OutputFailedException
	 */
	static public function interpret(
		Instruction $rootInstruction,
		Size $tapeSize = Size::Bit16,
		Size $cellSize = Size::Bit8,
		EofBehavior $eofBehavior = EofBehavior::Ignore,
		$inputStream = STDIN,
		$outputStream = STDOUT,
	): void {
		$tapeSize = $tapeSize->value;
		$cellSize = $cellSize->value;

		// Execute the instructions
		$instruction = $rootInstruction;
		$tape = array_fill(0, $tapeSize + 1, 0);
		$tapePointer = 0;
		while ($instruction !== null) {
			switch ($instruction->opcode) {
				case Opcode::Jump:
					if ($instruction->value < 0) {
						$tapePointer = (
							(
								$tapePointer
								- (abs($instruction->value) & $tapeSize)
							)
							& $tapeSize
						);
					} else {
						$tapePointer = (
							(
								$tapePointer
								+ ($instruction->value & $tapeSize)
							)
							& $tapeSize
						);
					}
					break;

				case Opcode::Add:
					if ($instruction->value < 0) {
						$tape[$tapePointer] = (
							(
								$tape[$tapePointer]
								- (abs($instruction->value) & $cellSize)
							)
							& $cellSize
						);
					} else {
						$tape[$tapePointer] = (
							(
								$tape[$tapePointer]
								+ ($instruction->value & $cellSize)
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
					$input = fgetc($inputStream);
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
					if (fputs($outputStream, $output) === false) {
						throw new Exceptions\OutputFailedException();
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
					if ($tape[$tapePointer] !== 0) {
						foreach ($instruction->value as $offset => $multiplier) {
							if ($offset < 0) {
								$tempTapePointer = (
									(
										$tapePointer
										- (abs($offset) & $tapeSize)
									)
									& $tapeSize
								);
							} else {
								$tempTapePointer = (
									(
										$tapePointer
										+ ($offset & $tapeSize)
									)
									& $tapeSize
								);
							}

							if ($multiplier < 0) {
								$tape[$tempTapePointer] = (
									(
										$tape[$tempTapePointer]
										- (
											($tape[$tapePointer] * (abs($multiplier) & $cellSize))
											& $cellSize
										)
									)
									& $cellSize
								);
							} else {
								$tape[$tempTapePointer] = (
									(
										$tape[$tempTapePointer]
										+ (
											($tape[$tapePointer] * ($multiplier & $cellSize))
											& $cellSize
										)
									)
									& $cellSize
								);
							}
						}

						$tape[$tapePointer] = 0;
					}
					break;

				default:
					throw new Exceptions\UnknownOpcodeException();
			}

			$instruction = $instruction->next;
		}
	}
}
