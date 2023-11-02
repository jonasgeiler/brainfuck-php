<?php declare(strict_types=1);

namespace Brainfuck;

/**
 * Used for interpreter settings to configure tape/cell size.
 * It's basically a list of numbers that only contain 1's in binary.
 */
enum Size: int {
	/** 4-Bit - Maximum: 15 */
	case Bit4 = 0xf;

	/** 8-Bit - Maximum: 255 */
	case Bit8 = 0xff;

	/** 12-Bit - Maximum: 4095 */
	case Bit12 = 0xfff;

	/** 16-Bit - Maximum: 65535 */
	case Bit16 = 0xffff;

	/** 20-Bit - Maximum: 1048575 */
	case Bit20 = 0xffff_f;

	/** 24-Bit - Maximum: 16777215 */
	case Bit24 = 0xffff_ff;

	/** 28-Bit - Maximum: 268435455 */
	case Bit28 = 0xffff_fff;

	/** 32-Bit - Maximum: 4294967295 (only available on 64-bit platforms) */
	case Bit32 = 0xffff_ffff;

	/** 36-Bit - Maximum: 68719476735 (only available on 64-bit platforms) */
	case Bit36 = 0xffff_ffff_f;

	/** 40-Bit - Maximum: 1099511627775 (only available on 64-bit platforms) */
	case Bit40 = 0xffff_ffff_ff;

	/** 44-Bit - Maximum: 17592186044415 (only available on 64-bit platforms) */
	case Bit44 = 0xffff_ffff_fff;

	/** 48-Bit - Maximum: 281474976710655 (only available on 64-bit platforms) */
	case Bit48 = 0xffff_ffff_ffff;

	/** 52-Bit - Maximum: 4503599627370495 (only available on 64-bit platforms) */
	case Bit52 = 0xffff_ffff_ffff_f;

	/** 56-Bit - Maximum: 72057594037927935 (only available on 64-bit platforms) */
	case Bit56 = 0xffff_ffff_ffff_ff;

	/** 60-Bit - Maximum: 1152921504606846975 (only available on 64-bit platforms) */
	case Bit60 = 0xffff_ffff_ffff_fff;
}
