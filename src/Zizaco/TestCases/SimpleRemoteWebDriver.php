<?php namespace Zizaco\TestCases;
use WebDriverSelect;

/**
 * Class SimpleRemoteWebDriver
 * Simplified webDriver version to shorten test code
 * @package Zizaco\TestCases
 * @property RemoteWebDriver $browser
 */
class SimpleRemoteWebDriver {

	public $lastScriptResponse = null;

	public function __construct(RemoteWebDriver $browser) {
		$this->browser = $browser;
	}

	/**
	 * Open URL
	 * @param string $path
	 * @return $this
	 */
	public function get($path) {
		$this->browser->get($path);
		return $this;
	}

	/**
	 * Click on an element and wait for ajax
	 *
	 * @param $cssSelector
	 * @return $this
	 * @throws TimeOutException
	 * @throws \Exception
	 */
	public function click($cssSelector) {

		$this->browser->waitForElementVisible($cssSelector);
		$element = $this->browser->findElementByjQuery($cssSelector);
		$element->click();
		$this->browser->waitForAjax(3e4);

		return $this;
	}

	/**
	 * fill input field
	 *
	 * @param $cssSelector
	 * @param $text
	 * @return $this
	 * @throws TimeOutException
	 * @throws \Exception
	 */
	public function type($cssSelector, $text) {

		$this->browser->waitForElementVisible($cssSelector);
		$element = $this->browser->findElementByjQuery($cssSelector);
		// clear input before inputting new keys. Do not clear file input.
		if($element->getAttribute("type") !== 'file') {
			$element->clear();
		}
		$element->sendKeys($text);
		// chrome doesn't type fast enough :(
		usleep(3e5);
		$this->browser->executeScript('jQuery("'.$cssSelector.'").trigger("keyup").trigger("change")');
		$this->browser->waitForAjax(3e4);

		return $this;
	}

	/**
	 * Execute script. Response is not returned!
	 *
	 * @param $script
	 * @param array $arguments
	 * @return $this
	 */
	public function executeScript($script, $arguments = array()) {

		$response = $this->browser->executeScript($script, $arguments);
		$this->lastScriptResponse = $response;
		$this->browser->waitForAjax(3e4);

		return $this;
	}

	/**
	 * Select an option from a select by value
	 * @param $cssSelector
	 * @param $value
	 * @return $this
	 */
	public function select($cssSelector, $value) {

		$this->browser->waitForElementVisible($cssSelector);
		$element = $this->browser->findElementByjQuery($cssSelector);
		$selection = new WebDriverSelect($element);
		$selection->selectByValue($value);

		return $this;
	}
}