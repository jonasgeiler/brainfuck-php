<?php declare(strict_types=1);

namespace Brainfuck;

function wrap($n, $m) {
	return $n >= 0 ? $n % $m : ($n % $m + $m) % $m;
}
