<?php declare(strict_types=1);

namespace Brainfuck;

const DEFAULT_TAPE_SIZE = 30_000;
const DEFAULT_CELL_SIZE = 255;

// Operators
const OP_PLUS = '+';
const OP_MINUS = '-';
const OP_RIGHT = '>';
const OP_LEFT = '<';
const OP_OUTPUT = '.';
const OP_INPUT = ',';
const OP_LOOP_START = '[';
const OP_LOOP_END = ']';
