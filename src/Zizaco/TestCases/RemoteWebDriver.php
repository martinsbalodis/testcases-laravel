<?php namespace Zizaco\TestCases;

use Exception;
use NoSuchElementException;
use RemoteWebElement;
use WebDriverBy;
use Config;

class RemoteWebDriver extends \RemoteWebDriver {

	public function findElementByjQuery($cssSelector) {

		// @TODO escape double quotes

		$elements = $this->executeScript('return jQuery("'.$cssSelector.'").get()');
		if(empty($elements)) {
			throw new Exception("elements not found!");
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

	public function waitForElementVisible($element) {

		if($element instanceof WebDriverBy) {
			$element = $this->findElement($element);
		}

		$timeout = 5000;
		$timeoutTime = microtime(1)+$timeout/1000;
		while(microtime(1) < $timeoutTime) {
			if($element->isDisplayed()) {
				return;
			}
			usleep(100);
		}
		throw new TimeOutException("Element NOT present! ".$element);
	}

	public function waitForElementNotVisible($element) {

		if($element instanceof WebDriverBy) {
			$element = $this->findElement($element);
		}

		$timeout = 5000;
		$timeoutTime = microtime(1)+$timeout/1000;
		while(microtime(1) < $timeoutTime) {
			if(!$element->isDisplayed()) {
				return;
			}
			usleep(100);
		}
		throw new TimeOutException("Element is still visible present! ");
	}

	public function waitForElementPresent($webDriverBy, $timeout = 5000) {

		$timeoutTime = microtime(1)+$timeout/1000;
		while(microtime(1) < $timeoutTime) {

			try {
				$this->findElement($webDriverBy);
				return;
			}
			catch(NoSuchElementException $e) {

			}
			usleep(100);
		}
		throw new TimeOutException("Element NOT present!");
	}

	public function waitForElementNotPresent($webDriverBy, $timeout = 5000) {

		$timeoutTime = microtime(1)+$timeout/1000;
		while(microtime(1) < $timeoutTime) {

			try {
				$this->findElement($webDriverBy);
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
}