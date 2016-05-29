<?php
namespace Zizaco\TestCases;

class Process {

	public static function killProcessByPort($port) {
		$processInfo = exec("lsof -i :$port");
		preg_match('/^\S+\s*(\d+)/', $processInfo, $matches);

		if(isset($matches[1]))
		{
			$pid = $matches[1];
			exec("kill $pid");
		}
	}

	private static function getOutpuFilename($command) {
		return "/tmp/selenium-test-".md5($command).".out";
	}

	private static function getPidFilename($command) {
		return "/tmp/selenium-test-".md5($command).".pid";
	}

	public static function getCommandPid($command) {

		$pidFilename = self::getPidFilename($command);
		if(!is_file($pidFilename)) {
			return false;
		}
		return (int) file_get_contents($pidFilename);
	}

	public static function killCommand($command) {
		$pid = self::getCommandPid($command);
		if(!$pid) {
			return;
		}

		exec("kill -9 $pid > /dev/null 2>&1; true");
	}

	public static function execAsyncAndWaitFor($command, $content, $timeout = 30) {

		$outputFile = self::execAsync($command);
		self::waitForOutput($outputFile, $content, $timeout);
		return $outputFile;
	}

	public static function execAsync($command) {

		$pidFilename = self::getPidFilename($command);
		$outputFile = self::getOutpuFilename($command);
		exec("touch $outputFile");

		$command = $command." > $outputFile 2>&1 & echo $!";

		$pid = (int) exec($command);
		file_put_contents($pidFilename, $pid);
		return $outputFile;
	}

	private static function waitForOutput($file, $output, $timeout=0) {
		$found = FALSE;
		$max_tries = 30;
		$num_tries = 0;
		while ( !$found ) {
			$contents = file_get_contents($file);
			// var_dump($contents);
			if ( strstr($contents, $output) ) {
				$found = TRUE;
			} else {
				if ( ++$num_tries > $max_tries ) {
					throw new \Exception("Failed to find $output in $file");
				}
				sleep(1);
			}
		}
	}
}