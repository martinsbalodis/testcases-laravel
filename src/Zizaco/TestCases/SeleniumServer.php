<?php

namespace Zizaco\TestCases;


class SeleniumServer {

	/**
	 * @var SeleniumServer
	 */
	private static $instance;

	/**
	 * @return SeleniumServer
	 */
	public static function getInstance() {
		if (null === static::$instance) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	protected function __construct() {
	}

	private function __clone() {
	}

	private function __wakeup() {
	}

	protected $seleniumLaunched = false;

	protected $seleniumOptions = null;

	public function setSeleniumOptions($options) {
		$this->seleniumOptions = $options;
	}

	public function launchServer() {

		if($this->seleniumLaunched) {
			return;
		}

		$socket = @fsockopen('localhost', 4444);
		if($socket == false)
		{
			$seleniumFound = false;
			$seleniumDir = $_SERVER['HOME'].'/.selenium';
			$files = scandir($seleniumDir);

			foreach ($files as $file) {
				if(substr($file,-4) == '.jar')
				{
					$command = "java -jar $seleniumDir/$file";
					if ( $this->seleniumOptions ) {
						$command .= " " . $this->seleniumOptions;
					}
					Process::execAsyncAndWaitFor($command, 'Selenium Server is up and running');
					$seleniumFound = true;
					break;
				}
			}

			if(! $seleniumFound)
				trigger_error(
					"Selenium not found. Please run the selenium server (in port 4444) or place the selenium ".
					".jar file in the '.selenium' directory within your home directory. For example: ".
					"'~/.selenium/anySeleniumName-ver0.jar'"
				);
		}

		$this->seleniumLaunched = true;
	}

	public function killSelenium() {
		Process::killProcessByPort('4444');
		$this->seleniumLaunched = false;
	}
}