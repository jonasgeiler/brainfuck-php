<?php declare(strict_types=1);

namespace Brainfuck;

/**
 * Used for interpreter settings to configure EOF behaviour.
 */
enum EofBehavior: int {
	/** Set cell to 0 on EOF */
	case Set0 = 0;

	/** Set cell to 1 on EOF (uncommon) */
	case Set1 = 1;

	/** Ignore input on EOF */
	case Ignore = 2;
}
