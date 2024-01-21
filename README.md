# brainfuck-php
A brainfuck interpreter written in PHP.

## Requirements
- PHP >= 8.2

## Usage
```bash
$ ./bin/bf ./examples/hello.bf
Hello World!
```

## Benchmarks

```shell

$ php -v
PHP 8.2.14 (cli) (built: Dec 21 2023 20:19:23) (NTS)
Copyright (c) The PHP Group
Zend Engine v4.2.14, Copyright (c) Zend Technologies
    with Zend OPcache v8.2.14, Copyright (c), by Zend Technologies


# Before using scan and clear opcodes (only basic optimization)
$ time ./bin/bf ./examples/hanoi.bf
real    6m46,842s
user    6m46,457s
sys     0m0,144s

$ time php -dopcache.enable_cli=1 -dopcache.jit_buffer_size=100M -dopcache.jit=1255 ./bin/bf ./examples/hanoi.bf
real    3m25,252s
user    3m25,067s
sys     0m0,044s


# After adding scan and clear opcodes
$ time ./bin/bf ./examples/hanoi.bf
real    0m28,194s
user    0m28,132s
sys     0m0,036s

$ time php -dopcache.enable_cli=1 -dopcache.jit_buffer_size=100M -dopcache.jit=1255 ./bin/bf ./examples/hanoi.bf
real    0m15,726s
user    0m15,665s
sys     0m0,052s

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
