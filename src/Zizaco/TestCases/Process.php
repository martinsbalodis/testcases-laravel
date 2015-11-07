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

	public static function execAsyncAndWaitFor($command, $content, $timeout = 30)
	{
		$output_path = "/tmp/zizaco-".str_shuffle(MD5(microtime()));
		self::execAsync($command, $output_path);
		self::waitForOutput($output_path, $content, $timeout);
		return $output_path;
	}

	private static function execAsync($command, $output_path = '/dev/null')
	{
		$force_async = " > $output_path 2>&1 &";
		$cmd = "/bin/bash -c '$command'".$force_async;
		exec($cmd);
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