<?php namespace Zizaco\TestCases;

use Config, App;
use DesiredCapabilities;
use ReflectionClass;
use S;

abstract class IntegrationTestCase extends \TestCase {

    public static function tearDownAfterClass() {

        // stop web server
        WebServer::getInstance()->killServer();

        SeleniumServer::getInstance()->stopForwardWebServerViaSSH();

        S::closeBrowser();
        parent::tearDownAfterClass();
    }

    public function setUp() {
        parent::setUp();

        // launch selenium server
        SeleniumServer::getInstance()->launchServer();
        SeleniumServer::getInstance()->forwardWebServerViaSSH();

        // launch web server
        WebServer::getInstance()->launchServer();

        S::startbrowser();
    }

    public function assertBodyHasText($needle)
    {
        $text = S::getBodyText();

        $needle = (array)$needle;

        foreach ($needle as $singleNiddle) {
            $this->assertContains($singleNiddle, $text, "Body text does not contain '$singleNiddle'");
        }
    }

    public function assertBodyHasNotText($needle)
    {
        $text = S::getBodyText();

        $needle = (array)$needle;

        foreach ($needle as $singleNiddle) {
            $this->assertNotContains($singleNiddle, $text, "Body text does not contain '$singleNiddle'");
        }
    }

    public function assertElementHasText($cssSelector, $needle)
    {
        $text = S::css($cssSelector)->getText();

        $needle = (array)$needle;

        foreach ($needle as $singleNiddle) {
            $this->assertContains($singleNiddle, $text, "Body text does not contain '$singleNiddle'");
        }
    }

    public function assertElementHasNotText($cssSelector, $needle)
    {
        $text = S::css($cssSelector)->getText();

        $needle = (array)$needle;

        foreach ($needle as $singleNiddle) {
            $this->assertNotContains($singleNiddle, $text, "Given element do contain '$singleNiddle' but it shoudn't");
        }
    }

    public function assertBodyHasHtml($needle)
    {
        $html = str_replace("\n", '', S::getHtmlSource());

        $needle = (array)$needle;

        foreach ($needle as $singleNiddle) {
            $this->assertContains($singleNiddle, $html, "Body html does not contain '$singleNiddle'");
        }
    }

    public function assertBodyHasNotHtml($needle)
    {
        $html = str_replace("\n", '', S::getHtmlSource());

        $needle = (array)$needle;

        foreach ($needle as $singleNiddle) {
            $this->assertNotContains($singleNiddle, $html, "Body html does not contain '$singleNiddle'");
        }
    }

    public function assertLocation($location)
    {
        $current_location = substr(S::getLocation(), strlen($location)*-1);
        $pattern = '/^(http:)?\/\/(localhost)(:)?\d*(.*)/';

        preg_match($pattern, $current_location, $current_matches);
        $current_location = (isset($current_matches[4])) ? $current_matches[4] : $current_location;

        preg_match($pattern, $location, $shouldbe_matches);
        $current_location = (isset($shouldbe_matches[4])) ? $shouldbe_matches[4] : $location;

        $this->assertEquals($current_location, $current_location, "The current location ($current_location) is not '$location'");
    }

    public function assertBodyHasElement($cssSelector, $timeout = 5000) {

        try {
            S::waitForElementPresent($cssSelector, $timeout);
            $this->assertTrue(true, "Element found");
        }
        catch(TimeOutException $e) {
            $this->fail("Element not found. ".$cssSelector);
        }
    }

    public function assertBodyHasNotElement($cssSelector, $timeout = 5000) {

        try {
            S::waitForElementNotPresent($cssSelector, $timeout);
            $this->assertTrue(true, "Element not found");
        }
        catch(TimeOutException $e) {
            $this->fail("Element found. ".$cssSelector);
        }
    }

	public function assertBodyHasVisibleElement($cssSelector, $timeout = 5000) {

		try {
			S::waitForElementVisible($cssSelector, $timeout);
			$this->assertTrue(true, "Element found");
		}
		catch(TimeOutException $e) {
			$this->fail("Element not found. ");
		}
	}

	public function assertBodyHasNotVisibleElement($cssSelector, $timeout = 5000) {

		try {
			S::waitForElementNotVisible($cssSelector, $timeout);
			$this->assertTrue(true, "Element found");
		}
		catch(TimeOutException $e) {
			$this->fail("Element is still visible. ");
		}
	}
}
