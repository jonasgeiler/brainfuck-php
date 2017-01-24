# Brainfuck-Interpreter-PHP
A Brainfuck Interpreter written in PHP.  
Look into the main.php file for help.

## Requirements
- PHP >= 7.0

## Example usage
```php
<?php
include("path/to/main.php");

$file = "path/to/HelloWorldExample.bf";
$config = [
  "cellsize" => 8/* bit */, 
  "infinitememory" => false, 
  "memorysize" => 30000,
  "memoverflow" => 2, // 1 = nothing, 2 = wrap, 3 = abort
  "inputmode" => "string" // "string" or "char"
];

$bfi = new BFI($config);
$bfi->readFile($file);
$bfi->run();
?>
```
