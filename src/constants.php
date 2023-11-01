<?php declare(strict_types=1);

namespace Brainfuck;

// Defaults
const DEFAULT_TAPE_SIZE = 30_000;
const DEFAULT_CELL_SIZE = 255;

// Operators
const OP_POINTER_INCREASE = '+';
const OP_POINTER_DECREASE = '-';
const OP_POINTER_RIGHT = '>';
const OP_POINTER_LEFT = '<';
const OP_OUTPUT = '.';
const OP_INPUT = ',';
const OP_LOOP_START = '[';
const OP_LOOP_END = ']';
