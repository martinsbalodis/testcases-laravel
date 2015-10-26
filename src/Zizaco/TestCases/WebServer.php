<?php
namespace Zizaco\TestCases;
use ReflectionClass;

class WebServer {

	/**
	 * @var WebServer
	 */
	private static $instance;

	/**
	 * @return WebServer
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

	protected $serverLaunched = false;
	protected $serverOutputPath = null;

	public function launchServer() {
		if($this->serverLaunched) return;

		// making sure that the artisan can be found when tests are run with
		// phpunit, IDE or within development environment
		$reflector = new ReflectionClass("\\Illuminate\\Foundation\\Testing\\TestCase");
		$fn = $reflector->getFileName();
		$testCaseDir = dirname($fn);
		$artisanDir = $testCaseDir = $testCaseDir."/../../../../../../../";

		$artisan = $artisanDir."artisan";
		// before starting kill previous process if exists
		Process::killProcessByPort('4443');
		$command = "php $artisan serve --port 4443";
		$command = "(export TESTCASES_LARAVEL=1; $command)";
		$outputPath = Process::execAsyncAndWaitFor($command, 'development server started');

		$this->serverOutputPath = $outputPath;
		$this->serverLaunched = true;
	}

	public function killServer() {
		// print everything that was returned by server
		$output = file_get_contents($this->serverOutputPath);
		echo $output;
		$this->serverOutputPath = null;

		Process::killProcessByPort('4443');
		$this->serverLaunched = false;
	}
}