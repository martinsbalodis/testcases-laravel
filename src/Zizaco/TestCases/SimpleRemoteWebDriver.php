<?php namespace Zizaco\TestCases;
use App;
use Auth;
use Config;
use Crypt;
use DesiredCapabilities;
use NoSuchElementException;
use RemoteWebElement;
use Session;
use UnknownServerException;
use WebDriverBy;
use WebDriverSelect;

/**
 * Class SimpleRemoteWebDriver
 * Simplified webDriver version to shorten test code
 * @package Zizaco\TestCases
 * @property RemoteWebDriver $webDriver
 */
class SimpleRemoteWebDriver {

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

	protected $lastScriptResponse = null;

	/**
	 * This url will be opened when a authenticated session cookie needs to be added.
	 * You should make this url return an empty page
	 * @var string
	 */
	protected $preLoginUrl = '/';

	/**
	 * @var RemoteWebDriver
	 */
	protected $webDriver;

//	public function __construct(RemoteWebDriver $browser) {
//		$this->browser = $browser;
//	}

//	/**
//	 * Open URL
//	 * @param string $path
//	 * @return $this
//	 */
//	public function get($path) {
//		$this->webDriver->get($path);
//		return $this;
//	}

	public function setPreLoginUrl($url) {
		$this->preLoginUrl = $url;
	}

	/**
	 * Click on an element and wait for ajax
	 *
	 * @param $cssSelector
	 * @throws TimeOutException
	 * @throws \Exception
	 */
	public function click($cssSelector) {

		$this->waitForElementVisible($cssSelector);
		$element = $this->webDriver->findElementByjQuery($cssSelector);
		$error = false;
		do {
			try {
				$element->click();
				$error = false;
			}
			catch(UnknownServerException $e) {
				if(str_contains($e->getMessage(), "Element is not clickable at point")) {
					$error = true;
				}
				usleep(1e5);
			}
		}
		while($error);
		// clicks are too fast
		usleep(3e5);
		$this->waitForAjax(3e4);
	}

	/**
	 * fill input field
	 *
	 * @param $cssSelector
	 * @param $text
	 * @throws TimeOutException
	 * @throws \Exception
	 */
	public function type($cssSelector, $text) {

		$this->waitForElementVisible($cssSelector);
		$element = $this->webDriver->findElementByjQuery($cssSelector);

		// try multiple times inputing the data if something fails the first time
		for($i = 0;$i<=10;$i++) {
			// clear input before inputting new keys. Do not clear file input.
			if($element->getAttribute("type") !== 'file') {
				$element->clear();
			}

			$element->sendKeys($text);
			// chrome doesn't type fast enough :(
			for($j=1;$j!=4;$j++) {
				$inputValue = $element->getAttribute("value");
				if($inputValue === $text) {
					break 2;
				}
				else if($element->getAttribute("type") === 'file' || str_contains($text, $inputValue)) {
					break 2;
				}
				else {
					usleep(1e5);
				}
			}
		}
		$this->executeScript('jQuery("'.$cssSelector.'").trigger("keyup").trigger("change")');
		$this->waitForAjax(3e4);
	}

	/**
	 * Execute script. Response is not returned!
	 *
	 * @param $script
	 * @param array $arguments
	 */
	public function executeScript($script, $arguments = array()) {

		$response = $this->webDriver->executeScript($script, $arguments);
		$this->lastScriptResponse = $response;
		$this->waitForAjax(3e4);
	}

	public function getLastScriptResponse() {
		return $this->lastScriptResponse;
	}

	/**
	 * Select an option from a select by value
	 * @param $cssSelector
	 * @param $value
	 */
	public function select($cssSelector, $value) {

		$this->waitForElementVisible($cssSelector);
		$element = $this->webDriver->findElementByjQuery($cssSelector);
		$selection = new WebDriverSelect($element);
		$selection->selectByValue($value);
	}

	public function waitForAjax($timeout = 5000) {

		$this->webDriver->wait($timeout/1000)->until(function ($driver) {
			return !$driver->executeScript('return window.jQuery === undefined?false:jQuery.active');
		});
	}

	public function waitForElementVisible($cssSelector, $timeout = 5000) {

		$timeoutTime = microtime(1)+$timeout/1000;
		while(microtime(1) < $timeoutTime) {

			try {
				$element = $this->webDriver->findElementByjQuery($cssSelector);
				if($element->isDisplayed()) {
					return;
				}
			}
			catch(NoSuchElementException $e) {

			}

			usleep(100);
		}
		throw new TimeOutException("Element NOT present! ".$cssSelector);
	}

	public function waitForElementNotVisible($cssSelector, $timeout = 5000) {

		$timeoutTime = microtime(1)+$timeout/1000;
		while(microtime(1) < $timeoutTime) {

			try {
				$element = $this->webDriver->findElementByjQuery($cssSelector);
				if(!$element->isDisplayed()) {
					return;
				}
			}
				// element not found so it's not visible
			catch(NoSuchElementException $e) {
				return;
			}

			usleep(100);
		}
		throw new TimeOutException("Element is still visible present! ");
	}

	public function waitForElementPresent($cssSelector, $timeout = 5000) {

		$timeoutTime = microtime(1)+$timeout/1000;
		while(microtime(1) < $timeoutTime) {

			try {
				$this->webDriver->findElementByjQuery($cssSelector);
				return;
			}
			catch(NoSuchElementException $e) {

			}
			usleep(100);
		}
		throw new TimeOutException("Element NOT present!");
	}

	public function waitForElementNotPresent($cssSelector, $timeout = 5000) {

		$timeoutTime = microtime(1)+$timeout/1000;
		while(microtime(1) < $timeoutTime) {

			try {
				$this->webDriver->findElementByjQuery($cssSelector);
			}
			catch(NoSuchElementException $e) {
				return;
			}
			usleep(100);
		}
		throw new TimeOutException("Element NOT present!");
	}

	public function bodyHasText($textSearch) {

		$text = $this->webDriver->findElement(WebDriverBy::tagName("body"))->getText();
		$hasText = strpos($text, $textSearch) !== false;
		return $hasText;
	}

	public function get($url) {

		$config = Config::get('selenium.webserver');
		$this->webDriver->get("http://{$config['host']}:{$config['port']}".$url);
	}

	public function route($name, $parameters = [], $absolute = false, $route = null) {
		$url = route($name, $parameters, $absolute, $route);
		$this->get($url);
	}

	public function getBodyText() {
		$text = $this->webDriver->findElement(WebDriverBy::cssSelector('body'))->getText();
		return $text;
	}

	public function getHtmlSource() {
		$html = $this->webDriver->findElement(WebDriverBy::cssSelector('html'))->getAttribute("innerHTML");
		return $html;
	}

	public function waitForPageReady() {
		$this->webDriver->wait()->until(function ($driver) {
			/* @var $driver RemoteWebDriver */
			return $driver->executeScript('return document.readyState == "complete";');
		});
	}

	/**
	 * Element selection with jquery
	 *
	 * @param $selector
	 * @return RemoteWebElement
	 * @throws Exception
	 */
	public function css($selector) {
		return $this->webDriver->findElementByjQuery($selector);
	}

	/**
	 * Login Laravel user. (Sets session cookie in browser)
	 * @param $user
	 */
	public function login($user) {

		// selenium only allows setting cookie when the expected domain is open
		$this->get($this->preLoginUrl);
		$sessionCookieName = Config::get('session.cookie');

		// login user
		Session::start();
		Auth::login($user);
		Session::save();

		// update browsers session cookie to match legged in session
		$sessionId = Session::getId();
		$this->webDriver->manage()->addCookie([
			'name' => $sessionCookieName,
			'value' => Crypt::encrypt($sessionId),
			'path' => '/',
			'domain' => 'localhost',
		]);
	}

	public function startbrowser() {

//		App::setRequestForConsoleEnvironment(); // This is a must

		$config = Config::get('selenium.selenium');
		if(!$this->webDriver) {
			$capabilities = DesiredCapabilities::firefox();
			$this->webDriver = RemoteWebDriver::create('http://'.$config['host'].':'.$config['port'].'/wd/hub', $capabilities);
		}
		else {
			// reset selenium session
			$this->webDriver->manage()->deleteAllCookies();
			$this->webDriver->get('about:blank');
		}
	}

	public function closeBrowser() {
		if($this->webDriver) {
			$this->webDriver->close();
			$this->webDriver = null;
		}
	}

	/**
	 * Switch to an iframe
	 *
	 * @param $idOrName
	 */
	public function switchToFrame($idOrName) {

		$this->webDriver->switchTo()->frame($idOrName);
	}

	/**
	 * Switch back to main frame from an iframe
	 */
	public function switchToDefaultContent() {

		$this->webDriver->switchTo()->defaultContent();
	}
}