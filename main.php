<?php
/**
 * -- Brainfuck Interpreter --
 * Thank you for downloading my little Brainfuck-Interpreter.
 * If you have any questions about this, just ask me on github!
 *
 * -- How to use --
 * - Read code from file -
 * You read the code from a file by using the readFile()-function
 *
 * - Read code from string -
 * You can directly read the code from a string by using the readString()-function
 *
 * - Run code -
 * You can run the brainfuck code by using the run()-function
 * 
 * - The Config-Variable -
 * Use the config variable to configure how the interpreter acts.
 * Here is a list of the supported config names
 * - cellsize (the maximum size of a memory cell) => either 8 (8 bit), 16 (16 bit) or 32 (32 bit)
 * - infinitememory (if the memory should be infinite) => either true or false
 * - memorysize (if not infinite, how big the memory should be) => any value of type int
 * - memoverflow (what the interpreter should do if the memory cell reaches the maximum cellsize) => either 1 (nothing), 2 (wrap) or 3 (abort)
 * - inputmode (the way the interpreter gets input. The difference is explained at the get_input()-function) => either "string" or "char"
 *
 * - Examples -
 * Examples are located in the examples folder.
 * 
 * @license https://github.com/Skayo/Brainfuck-Interpreter-PHP/blob/master/LICENSE MIT
 * @link https://github.com/Skayo/Brainfuck-Interpreter-PHP
 * @author Skayo
 */
class BFI {
	private $config = [];

	private $bfcode = []; // cleaned code (array of characters)
	private $memory = [];

	private $targets = [];
	private $codePtr = 0; // Pointer in code (current Character)
	private $memPtr = 0; // Pointer in memory
	private $input_queue = [];

	private $max_mem;
	private $cellsize;
	private $infinitemem;

	function __construct($conf) {
		$this->config = $conf;
		$this->checkConfig(); // check if any config-values are missing
		$this->setVars(); // set some vars for shorter code
	}

	private function error($msg) {
		exit($msg);
	}

	public function checkConfig() {
		$defaultconfig = ["cellsize" => 8, "infinitememory" => false, "memorysize" => 30000, "memoverflow" => 2, "inputmode" => "string"]; // default config values and keys
		foreach ($defaultconfig as $confKey => $confVal) {
			if(!isset($this->config[$confKey])) // if the config is not set...
				$this->config[$confKey] = $confVal; // ...set it
		}

		if($this->config["infinitememory"] && "memoverflow" == 2){ // if you use Memory-Overflow Wrap and Infinte Memory together...
			error("Config Error: Can't use Infinite Memory and Wrap Memory-Overflow together.");  // throw an error (not possible)
		}
	}

	private function setVars() {
		// set cellsize
		switch ($this->config["cellsize"]) {
			case 8:
				$this->cellsize = 255;
				break;
			
			case 16:
				$this->cellsize = 65535;
				break;

			case 32:
				$this->cellsize = 16777215;
				break;

			default:
				$this->error("Config error: cellsize not supported. Supported cellsizes are 8 (8 bit), 16 (16 bit) and 32 (32 bit).");
				break;
		}

		// set max mem
		if(is_numeric($this->config["memorysize"])){
			$this->max_mem = $this->config["memorysize"];
		} else {
			$this->error("Config error: memorysize is not of type int.");
		}

		// set infinite mem
		if($this->config["infinitememory"] == true || $this->config["infinitememory"] == false){
			$this->infinitemem = $this->config["infinitememory"];
		} else {
			$this->error("Config error: infinitememory is not of type boolean. Use true or false.");
		}
	}

	public function readFile($file) {
		if(!file_exists($file)) // test if file exists
			$this->error("File not found: ".$file);
		$this->parse(file_get_contents($file)); // read brainfuck code from file
		if(!$this->config["infinitememory"])
			$this->init_memory(); // if infinite memory is not true, fill memory with 0s
		$this->init_targets(); // locate [ and ] in the code
	}

	public function readString($str) {
		$this->parse($str); // read brainfuck code from given string
		if(!$this->config["infinitememory"])
			$this->init_memory(); // if infinite memory is not true, fill memory with 0s
		$this->init_targets(); // locate [ and ] in the code
	}

	private function parse($code) {
		$code = str_split($code); // split every character of code
		foreach ($code as $char) {
			if($this->isBFchar($char)) // if the character is a brainfuck character...
				array_push($this->bfcode, $char); // append it to the cleaned code array
		}
	}

	private function isBFchar($c) {
		$bfchars = [">", "<", "+", "-", ".", ",", "[", "]"];
		if(in_array($c, $bfchars)) // if the given character exists in the array of valid brainfuck characters...
			return true; // ...return true.
		return false; // else return false
	}

	private function init_memory() {
		for ($i=0; $i < $this->max_mem; $i++) { 
			$this->memory[$i] = 0; // set all values to 0
		}
	}

	private function init_targets() {
		// no comments for this code. sorry...
		$temp_stack = [];
		foreach ($this->bfcode as $charnum => $char) {
			if($char == '[')
				array_push($temp_stack, $charnum);

			if($char == ']'){
				if(count($temp_stack) == 0)
					$this->error("Parseing error: ] with no matching [.");
				$target = array_pop($temp_stack);
				$this->targets[$charnum] = $target;
				$this->targets[$target] = $charnum;
			}
		}

		if(count($temp_stack) > 0)
			$this->error("Parseing error: [ with no matching ].");
	}

	public function run() {
		while($this->codePtr != count($this->bfcode)){ // while the codePointer is not at the end of the program...
			$op = $this->bfcode[$this->codePtr];
			$this->execute_opcode($op);
			$this->codePtr++;
		}
	}

	private function execute_opcode($op) {
		// no comments for this code. sorry...
		switch ($op) {
			case '>':
				$this->increasePtr();
				break;

			case '<':
				$this->decreasePtr();
				break;

			case '+':
				$this->increaseMem();
				break;

			case '-':
				$this->decreaseMem();
				break;

			case '.':
				echo chr($this->memory[$this->memPtr]);
				break;

			case ',':
				$this->memory[$this->memPtr] = ord($this->get_input());
				break;

			case '[':
				if($this->memory[$this->memPtr] == 0)
					$this->codePtr = $this->targets[$this->codePtr];
				break;

			case ']':
				$this->codePtr = $this->targets[$this->codePtr] - 1;
				break;

		}
	}

	private function increasePtr() {
		$this->memPtr++; // increase memPtr
		if(!$this->infinitemem){ // if infinite memory is disabled...
			if($this->memPtr >= $this->max_mem){ // if memPtr is bigger than or equal to max_mem...
				$this->memPtr = 0; // ...set memPtr to 0
			}
		} else { // if infinite memory is enabled...
			// nothing because it's infinite :)
		}
	}

	private function decreasePtr() {
		$this->memPtr--; // decrease memPtr
		if(!$this->infinitemem){ // if infinite memory is disabled...
			if($this->memPtr < 0){ // if memPtr is less then 0...
				$this->memPtr = $this->max_mem; // ...set memPtr to "end" of memory
			}
		} else { // if infinite memory is enabled...
			if($this->memPtr < 0){ // if memPtr is less then 0...
				$this->memPtr = 0; // ...reset memPtr to zero
			}
		}
	}

	private function increaseMem() {
		$this->memory[$this->memPtr]++; // increase memPtr
		if($this->memory[$this->memPtr] > $this->cellsize){ // if memory cell at pointer is bigger than the maximum allowed cellsize...
			$memoverflow = $this->config["memoverflow"];
			if($memoverflow == 1){ // ...and memoverflow action is 1 (nothing)...
				$this->memory[$this->memPtr] = $this->cellsize; // ...reset memory at pointer cell to the maximum allowed cellsize.

			} elseif($memoverflow == 2) { // ...and memoverflow action is 2 (wrap)...
				$this->memory[$this->memPtr] = 0; // ...set memory cell at pointer to 0.

			} elseif($memoverflow == 3) { // ...and memoverflow action is 3 (abort)...
				$this->error("Memory Overflow. Execution aborted."); // ...abort. 
			}
		} 
	}

	private function decreaseMem() {
		$this->memory[$this->memPtr]--; // decrease memPtr
		if($this->memory[$this->memPtr] < 0){ // if memory cell at pointer is smaller than 0...
			$memoverflow = $this->config["memoverflow"];
			if($memoverflow == 1){ // ...and memoverflow action is 1 (nothing)...
				$this->memory[$this->memPtr] = 0; // ...reset memory cell at pointer to 0.

			} elseif($memoverflow == 2) { // ...and memoverflow action is 2 (wrap)...
				$this->memory[$this->memPtr] = $this->cellsize; // ...set memory cell at pointer to the maximum allowed cellsize

			} elseif($memoverflow == 3) { // ...and memoverflow action is 3 (abort)...
				$this->error("Memory Overflow. Execution aborted."); // ...abort. 
			}
		}
	}

	private function get_input() {
		/*
			inputmode "string" means that the input string is splitted into chars and put in a queue, 
			and if the program calls for input, the first is returned.

			inputmode "char" means that the input is a single char wich is returned to the program
		*/
		
		if($this->config["inputmode"] == "string"){ // if inputmode "string" is configured
			if(count($this->input_queue) == 0){ // if nothing is in the input queue...
				$input = readline_info(readline()); // ...get input from user...
				$input = str_split($input); // ...split it into chars...
				foreach ($input as $char) {
					array_push($this->input_queue, $char); // ...and add every char to the queue.
				}
			}

			return array_shift($this->input_queue);
		} elseif($this->config["inputmode"] == "char") { // if inputmode "string" is configured
			$input = readline_info(readline()); // get input from user
			while(strlen($input) > 1){	// while input is not a single char...
				$input = readline_info(readline()); // ...get input from user again
			}

			return $input;
		} else {
			$this->error("Config error: inputmode not supported. Supported inputmodes are 'char' and 'string'.");
		}
	}
}
?>