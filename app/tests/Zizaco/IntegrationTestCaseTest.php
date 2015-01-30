<?php
class IntegrationTestCaseTest extends \Zizaco\TestCases\IntegrationTestCase {

	public function testOpenLandingPage() {
		$this->browser->get("/");
	}

	public function testAssertBodyHasText() {
		$this->browser->get("/");
		$this->assertBodyHasText("You have arrived");
	}

	public function testAssertBodyHasNotText() {
		$this->browser->get("/");
		$this->assertBodyHasNotText("You haven't arrived");
	}

	public function testAssertBodyHasHtml() {
		$this->browser->get("/");
		$this->assertBodyHasHtml("<h1>");
	}

	public function testAssertBodyHasNotHtml() {
		$this->browser->get("/");
		$this->assertBodyHasNotHtml("<h4>");
	}

	public function testAssertBodyHasElement() {

		$this->browser->get("/");
		$this->assertBodyHasElement(WebDriverBy::cssSelector("h1"), 100);
	}
}