# brainfuck-php
A brainfuck interpreter written in PHP.

## Requirements
- PHP >= 8.2

## Usage
```bash
$ ./bin/bfi.php ./examples/hello.bf
Hello World!
```

## What is a copy loop?

Use cases:
- `[->+<]`: add two cells
- `[->-<]`: subtract two cells
- `[->+<]`: move value to different cell
- `[->+>+<<]`: copy value to multiple cells

These copy loops always consist of the same parts:
1. Always starts with '-'
2. Move n cells to left or right
3. Add or subtract 1 from the cell
4. Move back n cells to original position
(Step 2 and 3 is repeatable for multiple copies)
