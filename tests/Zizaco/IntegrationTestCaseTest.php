<?php
use Zizaco\TestCases\IntegrationTestCase;

class IntegrationTestCaseTest extends IntegrationTestCase {

	public function testOpenLandingPage() {
		S::get("/");
		$this->assertTrue(true);
	}

	public function testAssertBodyHasText() {
		S::get("/");
		$this->assertBodyHasText("You have arrived");
	}

	public function testAssertBodyHasNotText() {
		S::get("/");
		$this->assertBodyHasNotText("You haven't arrived");
	}

	public function testAssertBodyHasHtml() {
		S::get("/");
		$this->assertBodyHasHtml("<h1>");
	}

	public function testAssertBodyHasNotHtml() {
		S::get("/");
		$this->assertBodyHasNotHtml("<h4>");
	}

	public function testAssertBodyHasElement() {

		S::get("/");
		$this->assertBodyHasElement("h1", 100);
	}

	public function testAssertBodyHasNotElement() {

		S::get("/");
		$this->assertBodyHasNotElement("h5", 100);
	}

	public function testAssertBodyHasVisibleElement() {

		S::get("/");
		$this->assertBodyHasVisibleElement("h1", 100);
	}

	public function testAassertBodyHasNotVisibleElement() {

		S::get("/");
		$this->assertBodyHasNotVisibleElement(".hidden", 100);
	}

	public function testAssertElementHasText() {
		S::get("/");
		$this->assertElementHasText("h1", "You");
	}

	public function testAssertElementHasNotText() {
		S::get("/");
		$this->assertElementHasNotText("h1", "asdasdasdasd");
	}
}