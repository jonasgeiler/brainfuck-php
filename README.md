# brainfuck-php
An optimizing [brainfuck](https://en.wikipedia.org/wiki/Brainfuck) parser & interpreter written in PHP.

This is a rework of a very old project of mine, which was actually the first 
project I ever uploaded to GitHub!  
If you want to see the old version, check out the 
[`old-version`](https://github.com/skayo/brainfuck-php/tree/old-version)
tag.  
But be warned of the bad code :D

> [!NOTE]  
> Since this is just a little experiment, I won't go into too much detail about
> how it works, how to use it, or what exactly it does. Feel free to 
> [start a new discussion](https://github.com/skayo/brainfuck-php/discussions)
> if you have any questions!

## Requirements
- PHP (cli) >= 8.2

## Usage
```bash
$ composer install

$ ./bin/bf ./examples/hello.bf
Hello World!
```
Feel free to check out all other example programs I have uploaded!  
For example [`lost-kingdom.bf`](./examples/lost-kingdom.bf) is pretty fun :)

## Benchmarks
As far as I can see, this is one of, if not *the* fastest PHP brainfuck
parser & interpreter around:
```shell

$ php -v
PHP 8.2.14 (cli) (built: Dec 21 2023 20:19:23) (NTS)
Copyright (c) The PHP Group
Zend Engine v4.2.14, Copyright (c) Zend Technologies
    with Zend OPcache v8.2.14, Copyright (c), by Zend Technologies


# With only basic optimization like merging consecutive operators together:
$ time ./bin/bf ./examples/hanoi.bf
real    6m46,842s
user    6m46,457s
sys     0m0,144s

$ time php -dopcache.enable_cli=1 -dopcache.jit_buffer_size=100M -dopcache.jit=1255 ./bin/bf ./examples/hanoi.bf
real    3m25,252s
user    3m25,067s
sys     0m0,044s


# After adding the scan and clear opcodes:
$ time ./bin/bf ./examples/hanoi.bf
real    0m28,194s
user    0m28,132s
sys     0m0,036s

$ time php -dopcache.enable_cli=1 -dopcache.jit_buffer_size=100M -dopcache.jit=1255 ./bin/bf ./examples/hanoi.bf
real    0m15,726s
user    0m15,665s
sys     0m0,052s


# After adding the copy opcode:
$ time ./bin/bf ./examples/hanoi.bf
real    0m15,946s
user    0m15,881s
sys     0m0,056s

$ time php -dopcache.enable_cli=1 -dopcache.jit_buffer_size=100M -dopcache.jit=1255 ./bin/bf ./examples/hanoi.bf
real    0m9,347s
user    0m9,296s
sys     0m0,040s

```
If the above is not clear: the [`hanoi.bf`](./examples/hanoi.bf) brainfuck 
program only takes **9 seconds** to run on my machine, which is pretty fast
compared to where this project started at (7 whole minutes) and compared to 
other PHP brainfuck parsers & interpreters I could find on the internet!
