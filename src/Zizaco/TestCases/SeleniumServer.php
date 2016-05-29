<?php

namespace Zizaco\TestCases;


use Config;
use Symfony\Component\Process\Process as SymfonyProcess;

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

	protected $portForwardedViaSSH = false;

	protected $seleniumOptions = null;

	public function setSeleniumOptions($options) {
		$this->seleniumOptions = $options;
	}

	public function launchServer() {

		if($this->seleniumLaunched) {
			return;
		}

		$runLocally = Config::get('selenium.selenium.run_locally');
		if(!$runLocally) {
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

	private function getForwardWebServerViaSSHCommand() {

		$config = Config::get('selenium.port_forward');

		$password = $config['password'];
		$sshPort = $config['port'];
		$sshUsername = $config['username'];
		$sshHost = $config['host'];
		$webserverPort = $config['webserver_port'];
		$webserverHost = $config['webserver_host'];

		$command = "sshpass -p $password ssh -o ExitOnForwardFailure=yes -N -R 127.0.0.1:$webserverPort:$webserverHost:$webserverPort -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no -p $sshPort $sshUsername@$sshHost";
		return $command;
	}

	public function forwardWebServerViaSSH() {

		if($this->portForwardedViaSSH) {
			return;
		}

		// kill previous ssh forward
		$this->stopForwardWebServerViaSSH();

		$config = Config::get('selenium.port_forward');

		// port forward is disabled
		if(!$config['enable']) {
			return;
		}

		$command = $this->getForwardWebServerViaSSHCommand();
		Process::execAsync($command, 'Welcome');
		$this->portForwardedViaSSH = true;
	}

	public function stopForwardWebServerViaSSH() {

		// will be stoping by finding the pid that should run this command
		$command = $this->getForwardWebServerViaSSHCommand();
		Process::killCommand($command);
		$this->portForwardedViaSSH = false;
	}

	public function killSelenium() {
		Process::killProcessByPort('4444');
		$this->seleniumLaunched = false;
	}
}