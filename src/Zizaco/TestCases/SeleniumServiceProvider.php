<?php

namespace Zizaco\TestCases;
use Illuminate\Support\ServiceProvider;

/**
 * Class SeleniumServiceProvider
 * @property \Illuminate\Foundation\Application $app
 * @package Zizaco\TestCases
 */
class SeleniumServiceProvider extends ServiceProvider {

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register() {

		$this->registerSelenium();
	}

	private function registerSelenium() {
		$this->app->singleton('selenium', function() {
			// It seems that this won't work across multiple tests
			$seleniumClient = SimpleRemoteWebDriver::getInstance();
			return $seleniumClient;
		});
	}

	public function provides() {
		return ['selenium'];
	}
}