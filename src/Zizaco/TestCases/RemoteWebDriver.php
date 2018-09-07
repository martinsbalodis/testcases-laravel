<?php namespace Zizaco\TestCases;

use Exception;
use NoSuchElementException;
use RemoteWebElement;
use UnknownServerException;
use WebDriverBy;
use Config;

class RemoteWebDriver extends \RemoteWebDriver {

	public function close() {
		try {
			parent::close();
		}
		catch(UnknownServerException $e) {
			// Sometimes webdriver loses connection with with browser.
			// We don't need the browser anymore. yolo
		}
	}

	public function findElementByjQuery($cssSelector) {

		// @TODO escape double quotes

		$elements = $this->executeScript('if(jQuery !== undefined) { return jQuery("'.$cssSelector.'").get(); } else { return Array.from(document.querySelectorAll("'.$cssSelector.'")); }');
		if(empty($elements)) {
			throw new NoSuchElementException("element not found! ".$cssSelector);
		}

		$id = $elements[0]['ELEMENT'];
		$executor = $this->getExecuteMethod();
		$element = new RemoteWebElement($executor, $id);
		return $element;
	}
}