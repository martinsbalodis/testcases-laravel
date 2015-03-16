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
		$this->assertBodyHasElement("h1", 100);
	}

	public function testAssertBodyHasNotElement() {

		$this->browser->get("/");
		$this->assertBodyHasNotElement("h5", 100);
	}

	public function testAssertBodyHasVisibleElement() {

		$this->browser->get("/");
		$this->assertBodyHasVisibleElement("h1", 100);
	}

	public function testAassertBodyHasNotVisibleElement() {

		$this->browser->get("/");
		$this->assertBodyHasNotVisibleElement(".hidden", 100);
	}
}