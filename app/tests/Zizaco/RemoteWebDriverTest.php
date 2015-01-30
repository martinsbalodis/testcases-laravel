<?php

use Zizaco\TestCases\IntegrationTestCase;

class RemoteWebDriverTest extends IntegrationTestCase {

	public function testWaitForElement() {

		$this->browser->get("/");
		$this->browser->waitForElement(WebDriverBy::cssSelector('h1'));
	}

	/**
	 * @expectedException \Zizaco\TestCases\TimeOutException
	 */
	public function testWaitForElementFail() {

		$this->browser->get("/");
		$this->browser->waitForElement(WebDriverBy::cssSelector('h5'), 100);
	}
}