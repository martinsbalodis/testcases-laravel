<?php namespace Zizaco\TestCases;

use Exception;
use NoSuchElementException;
use RemoteWebElement;
use UnknownServerException;
use WebDriverBy;
use Config;

class RemoteWebDriver extends \RemoteWebDriver {

	public function findElementByjQuery($cssSelector) {

		// @TODO escape double quotes

		$elements = $this->executeScript('return jQuery("'.$cssSelector.'").get()');
		if(empty($elements)) {
			throw new NoSuchElementException("element not found! ".$cssSelector);
		}

		$id = $elements[0]['ELEMENT'];
		$executor = $this->getExecuteMethod();
		$element = new RemoteWebElement($executor, $id);
		return $element;
	}

	public function waitForAjax($timeout = 5000) {

		$this->wait($timeout/1000)->until(function ($driver) {
			return !$driver->executeScript('return jQuery.active');
		});
	}

	public function waitForElementVisible($cssSelector, $timeout = 5000) {

		$timeoutTime = microtime(1)+$timeout/1000;
		while(microtime(1) < $timeoutTime) {

			try {
				$element = $this->findElementByjQuery($cssSelector);
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
				$element = $this->findElementByjQuery($cssSelector);
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
				$this->findElementByjQuery($cssSelector);
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
				$this->findElementByjQuery($cssSelector);
			}
			catch(NoSuchElementException $e) {
				return;
			}
			usleep(100);
		}
		throw new TimeOutException("Element NOT present!");
	}

	public function bodyHasText($textSearch) {

		$text = $this->findElement(WebDriverBy::tagName("body"))->getText();
		$hasText = strpos($text, $textSearch) !== false;
		return $hasText;
	}

	public function get($url) {

		$urlBase = Config::get("app.url");
		parent::get($urlBase.$url);
	}

	public function getBodyText() {
		$text = $this->findElement(WebDriverBy::cssSelector('body'))->getText();
		return $text;
	}

	public function getHtmlSource() {
		$html = $this->findElement(WebDriverBy::cssSelector('html'))->getAttribute("innerHTML");
		return $html;
	}

	public function waitForPageReady() {
		$this->wait()->until(function ($driver) {
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
		return $this->findElementByjQuery($selector);
	}

	public function close() {
		try {
			parent::close();
		}
		catch(UnknownServerException $e) {
			// Sometimes webdriver loses connection with with browser.
			// We don't need the browser anymore. yolo
		}
	}
}