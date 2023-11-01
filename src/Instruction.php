<?php declare(strict_types=1);

namespace Brainfuck;

/**
 * Represents an optimized brainfuck instruction.
 */
class Instruction {

	/**
	 * This holds the opcode of the instruction.
	 */
	public ?Opcode $opcode = null;

	/**
	 * For the Jump and Add opcodes, this holds the amount to jump/increase.
	 */
	public ?int $amount = null;

	/**
	 * This holds the next instruction in the program.
	 */
	public ?Instruction $next = null;

	/**
	 * This holds the previous instruction in the program.
	 */
	public ?Instruction $previous = null;

	/**
	 * For the LoopStart and LoopEnd opcodes, this holds the corresponding
	 * starting/ending instruction to jump to.
	 */
	public ?Instruction $match = null;

	/**
	 * Convert the instruction into a string representation for debug purposes.
	 * @returns string The instruction as a string.
	 */
	public function __toString(): string {
		$result = strtolower($this->opcode->name);
		if ($this->amount) {
			$result .= ' ' . $this->amount;
		}

		return $result;
	}

}
