<?php
class SimpleRemoteWebDriverTest extends \Zizaco\TestCases\IntegrationTestCase {

	public function testOpenPage() {

		$this->simple()->get("/");
	}

	public function testClick() {

		$this->simple()->get("/")->click("button");
		$this->assertBodyHasText("clicked!");
	}

	public function testType() {

		$this->simple()->get("/")->type("input", "asd");
		$value = $this->browser->executeScript('return jQuery("input").val()');
		$this->assertEquals("asd", $value);
	}
}