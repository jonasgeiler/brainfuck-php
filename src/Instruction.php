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
	public int|array|null $value = null;

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
		if (is_array($this->value)) {
			$result .= ' ' . json_encode($this->value);
		} else {
			$result .= ' ' . $this->value;
		}
		if ($this->match) {
			$result .=
				' => '
				. strtolower($this->match->opcode->name);

			if (
				$this->match->next !== null
				&& $this->match->next->opcode !== Opcode::LoopStart
				&& $this->match->next->opcode !== Opcode::LoopEnd
			) {
				$result .= ', ' . $this->match->next;

				if (
					$this->match->next->next !== null
					&& $this->match->next->next->opcode !== Opcode::LoopStart
					&& $this->match->next->next->opcode !== Opcode::LoopEnd
				) {
					$result .= ', ' . $this->match->next->next;

					if ($this->match->next->next->next !== null) {
						$result .= ', ...';
					}
				}
			}
		}

		return $result;
	}

}
