# brainfuck-php

> An optimizing [Brainfuck](https://en.wikipedia.org/wiki/Brainfuck) parser &
> interpreter written in PHP.

Brainfuck is an extremly minimal, esoteric programming language consisting of
only eight simple commands, a data pointer and an instruction pointer.
Read more about Brainfuck here:
[Brainfuck - Wikipedia](https://en.wikipedia.org/wiki/Brainfuck)  
This project is essentially a parser & interpreter for this programming
language. The term "optimizing" means that I use special optimization techniques
to improve the performance of the Brainfuck program execution.

This is a rework of a very old project of mine, which was actually the first 
project I ever uploaded to GitHub!  
If you want to see the old version, check out the 
[`old-version`](https://github.com/jonasgeiler/brainfuck-php/tree/old-version)
tag.  
But be warned of the bad and slow code :D

## Requirements

- PHP 8.2 or newer

## How to try

Download the repository and then run `composer` to set up the autoloader:

```shell
$ composer install
```

Afterwards, run any of the examples using `./bin/bf`:

```shell
$ ./bin/bf ./examples/<example>.bf
```

For example, [`lost-kingdom.bf`](./examples/lost-kingdom.bf) is pretty fun:

```shell
$ ./bin/bf ./examples/lost-kingdom.bf
```

Make sure to check out all the other [example programs](./examples)
I have uploaded!  

## Benchmarks

As far as I can see, this is one of, if not __*THE*__ fastest PHP Brainfuck
parser & interpreter around:

```shell
$ php -v
PHP 8.2.14 (cli) (built: Dec 21 2023 20:19:23) (NTS)
Copyright (c) The PHP Group
Zend Engine v4.2.14, Copyright (c) Zend Technologies
    with Zend OPcache v8.2.14, Copyright (c), by Zend Technologies

$ time ./bin/bf ./examples/hanoi.bf
real    0m15,946s
user    0m15,881s
sys     0m0,056s

$ time php -dopcache.enable_cli=1 -dopcache.jit_buffer_size=100M -dopcache.jit=1255 ./bin/bf ./examples/hanoi.bf
real    0m9,347s
user    0m9,296s
sys     0m0,040s
```

As you can see, the [`hanoi.bf`](./examples/hanoi.bf) Brainfuck program only
takes **9 seconds** to run on my machine, which is pretty damn fast compared
to where this project started at (7 whole minutes) and compared to other PHP
Brainfuck parsers & interpreters I could find on the internet!
