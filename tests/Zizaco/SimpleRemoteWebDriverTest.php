<?php
use App\SeleniumTestUser;
use Zizaco\TestCases\IntegrationTestCase;

class SimpleRemoteWebDriverTest extends IntegrationTestCase {

	public function testOpenPage() {

		S::get("/");
		$this->assertTrue(true);
	}

	public function testOpenPageViaRoute() {

		S::route('index');
		$this->assertTrue(true);
	}

	public function testClick() {

		S::get("/");
		S::click("button");
		$this->assertBodyHasText("clicked!");
	}

	public function testCheckCheckbox() {

		S::get("/");
		S::click(".checkbox");
		$this->assertTrue(true);
	}

	public function testType() {

		S::get("/");
		S::type("input", "asd");
		S::executeScript('return jQuery("input").val()');
		$value = S::getLastScriptResponse();
		$this->assertEquals("asd", $value);
	}

	public function testTypeQuotes() {

		S::get("/");
		$input = '"asdasd"';
		S::type("input", $input);
		S::executeScript('return jQuery("input").val()');
		$value = S::getLastScriptResponse();
		$this->assertEquals($input, $value);
	}

	public function testLogin() {

		$user = factory(SeleniumTestUser::class)->create();
		S::login($user);
		S::get('/');
		$this->assertBodyHasText('authenticated');
	}

	public function testTypeIntoFileInput() {

		$file = tempnam('/tmp', "asd");

		S::get("/");
		S::type(".input-file", $file);
		S::executeScript('return jQuery(".input-file").val()');
		$value = S::getLastScriptResponse();
		$this->assertNotEmpty($value);
	}

	public function testExecuteScript() {

		S::get("/");
		S::executeScript("return 1+2;");
		$response = S::getLastScriptResponse();
		$this->assertEquals(3, $response);
	}

	public function testSelect() {
		S::get("/");

		S::executeScript("return $('#select-me').val()");
		$value = S::getLastScriptResponse();
		$this->assertEquals(1, $value);

		S::select('#select-me', 2);

		S::executeScript("return $('#select-me').val()");
		$value = S::getLastScriptResponse();
		$this->assertEquals(2, $value);
	}

	public function testFormSubmit() {

		S::get("/");
		S::click(".form-submit");
		$this->assertBodyHasText("You have arrived");
	}

	public function testGetElementWithoutJQuery() {

		S::executeScript('window.jQuery = undefined;');
		S::waitForElementVisible("input");

		$this->addToAssertionCount(1);
	}

	public function testSwitchToFrame() {

		S::get("/");
		S::switchToFrame("test-frame");

		$this->addToAssertionCount(1);
	}
}