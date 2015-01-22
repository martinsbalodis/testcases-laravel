<?php
class IntegrationTestCaseTest extends \Zizaco\TestCases\IntegrationTestCase {

	public function testOpenLandingPage() {
		$this->browser->get("/");
		$a = 1;
	}

}