<?php
class SimpleRemoteWebDriverTest extends \Zizaco\TestCases\IntegrationTestCase {

	public function testOpenPage() {

		$this->simple()->get("/");
	}

	public function testClick() {

		$this->simple()->get("/")->click("button");
		$this->assertBodyHasText("clicked!");
	}

	public function testCheckCheckbox() {

		$this->simple()->get("/")->click(".checkbox");
	}

	public function testType() {

		$this->simple()->get("/")->type("input", "asd");
		$value = $this->browser->executeScript('return jQuery("input").val()');
		$this->assertEquals("asd", $value);
	}

	public function testTypeIntoFileInput() {

		$file = tempnam('/tmp', "asd");

		$this->simple()->get("/")->type(".input-file", $file);
		$value = $this->browser->executeScript('return jQuery(".input-file").val()');
		$this->assertNotEmpty($value);
	}

	public function testExecuteScript() {

		$response = $this->simple()->get("/")->executeScript("return 1+2;")->lastScriptResponse;
		$this->assertEquals(3, $response);
	}

	public function testSelect() {
		$this->simple()->get("/");

		$value = $this->simple()->executeScript("return $('#select-me').val()")->lastScriptResponse;
		$this->assertEquals(1, $value);

		$this->simple()->select('#select-me', 2);

		$value = $this->simple()->executeScript("return $('#select-me').val()")->lastScriptResponse;
		$this->assertEquals(2, $value);
	}
}