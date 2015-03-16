<?php namespace Zizaco\TestCases;

/**
 * Class SimpleRemoteWebDriver
 * Simplified webDriver version to shorten test code
 * @package Zizaco\TestCases
 * @property RemoteWebDriver $browser
 */
class SimpleRemoteWebDriver extends \RemoteWebDriver {

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

		$element = $this->browser->findElementByjQuery($cssSelector);
		$this->browser->waitForElementVisible($element);
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

		$element = $this->browser->findElementByjQuery($cssSelector);
		$this->browser->waitForElementVisible($element);
		// clear input before inputting new keys
		$element->clear();
		$element->sendKeys($text);
		$this->browser->executeScript('jQuery("'.$cssSelector.'").trigger("keyup").trigger("change")');
		$this->browser->waitForAjax(3e4);

		return $this;
	}

}