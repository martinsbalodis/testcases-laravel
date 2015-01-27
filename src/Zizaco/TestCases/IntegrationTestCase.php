<?php namespace Zizaco\TestCases;

use Config, App;
use ReflectionClass;

class IntegrationTestCase extends \TestCase
{
    static protected $seleniumLaunched = false;

    static protected $serverLaunched = false;

    static protected $loadedBrowser = null;

    static protected $seleniumOptions = null;

    static protected $serverOutputPath = null;

    /**
     * @var RemoteWebDriver
     */
    public $browser;

    public static function setSeleniumOptions($options)
    {
        self::$seleniumOptions = $options;
    }

    public static function setUpBeforeClass()
    {
        static::launchSelenium();
        static::launchServer();
    }

    public static function tearDownAfterClass()
    {
        static::killServer();
        if(IntegrationTestCase::$loadedBrowser)
        {
            IntegrationTestCase::$loadedBrowser->close();
            IntegrationTestCase::$loadedBrowser = null;
        }
    }

    public function setUp()
    {
        parent::setUp();

        $this->startbrowser();
    }

    public function assertBodyHasText($needle)
    {
        $text = $this->browser->getBodyText();

        $needle = (array)$needle;

        foreach ($needle as $singleNiddle) {
            $this->assertContains($singleNiddle, $text, "Body text does not contain '$singleNiddle'");
        }
    }

    public function assertBodyHasNotText($needle)
    {
        $text = $this->browser->getBodyText();

        $needle = (array)$needle;

        foreach ($needle as $singleNiddle) {
            $this->assertNotContains($singleNiddle, $text, "Body text does not contain '$singleNiddle'");
        }
    }

    public function assertElementHasText($locator, $needle)
    {
        $text = $this->browser->getText($locator);

        $needle = (array)$needle;

        foreach ($needle as $singleNiddle) {
            $this->assertContains($singleNiddle, $text, "Body text does not contain '$singleNiddle'");
        }
    }

    public function assertElementHasNotText($locator, $needle)
    {
        $text = $this->browser->getText($locator);

        $needle = (array)$needle;

        foreach ($needle as $singleNiddle) {
            $this->assertNotContains($singleNiddle, $text, "Given element do contain '$singleNiddle' but it shoudn't");
        }
    }

    public function assertBodyHasHtml($needle)
    {
        $html = str_replace("\n", '', $this->browser->getHtmlSource());

        $needle = (array)$needle;

        foreach ($needle as $singleNiddle) {
            $this->assertContains($singleNiddle, $html, "Body html does not contain '$singleNiddle'");
        }
    }

    public function assertBodyHasNotHtml($needle)
    {
        $html = str_replace("\n", '', $this->browser->getHtmlSource());

        $needle = (array)$needle;

        foreach ($needle as $singleNiddle) {
            $this->assertNotContains($singleNiddle, $html, "Body html does not contain '$singleNiddle'");
        }
    }

    public function assertLocation($location)
    {
        $current_location = substr($this->browser->getLocation(), strlen($location)*-1);
        $pattern = '/^(http:)?\/\/(localhost)(:)?\d*(.*)/';

        preg_match($pattern, $current_location, $current_matches);
        $current_location = (isset($current_matches[4])) ? $current_matches[4] : $current_location;

        preg_match($pattern, $location, $shouldbe_matches);
        $current_location = (isset($shouldbe_matches[4])) ? $shouldbe_matches[4] : $location;

        $this->assertEquals($current_location, $current_location, "The current location ($current_location) is not '$location'");
    }

    protected function startBrowser()
    {
        // Set the Application URL containing the port of the test server
        Config::set(
            'app.url',
            Config::get('app.url').':4443'
        );
        App::setRequestForConsoleEnvironment(); // This is a must

        if(! IntegrationTestCase::$loadedBrowser)
        {
            $capabilities = array(\WebDriverCapabilityType::BROWSER_NAME => 'firefox');
            $this->browser = RemoteWebDriver::create('http://localhost:4444/wd/hub', $capabilities);

            IntegrationTestCase::$loadedBrowser = $this->browser;
        }
        else
        {
            $this->browser = IntegrationTestCase::$loadedBrowser;

            // reset selenium session
            $this->browser->manage()->deleteAllCookies();

            $this->browser = IntegrationTestCase::$loadedBrowser;
            $this->browser->get('/');
        }
        
    }

    protected static function launchSelenium()
    {

        if(IntegrationTestCase::$seleniumLaunched)
            return;
        $socket = @fsockopen('localhost', 4444);
        if($socket == false)
        {
            $seleniumFound = false;
            $seleniumDir = $_SERVER['HOME'].'/.selenium';
            $files = scandir($seleniumDir);

            foreach ($files as $file) {
                if(substr($file,-4) == '.jar')
                {
                    $command = "java -jar $seleniumDir/$file";
                    if ( self::$seleniumOptions ) {
                        $command .= " " . self::$seleniumOptions;
                    }
                    static::execAsyncAndWaitFor($command, 'org.openqa.jetty.jetty.Server');
                    $seleniumFound = true;
                    break;
                }
            }

            if(! $seleniumFound)
                trigger_error(
                    "Selenium not found. Please run the selenium server (in port 4444) or place the selenium ".
                    ".jar file in the '.selenium' directory within your home directory. For example: ".
                    "'~/.selenium/anySeleniumName-ver0.jar'"
                );
        }

        IntegrationTestCase::$seleniumLaunched = true;
    }

    protected static function launchServer()
    {
        if(IntegrationTestCase::$serverLaunched)
            return;

        // making sure that the artisan can be found when tests are run with
        // phpunit, IDE or within development environment
        $reflector = new ReflectionClass("\\Illuminate\\Foundation\\Testing\\TestCase");
        $fn = $reflector->getFileName();
        $testCaseDir = dirname($fn);
        $artisanDir = $testCaseDir = $testCaseDir."/../../../../../../../";

        $artisan = $artisanDir."artisan";
        // before starting kill previous process if exists
        $command = "fuser -k 4443/tcp && php $artisan serve --port 4443";
        $outputPath = static::execAsyncAndWaitFor($command, 'development server started');

        IntegrationTestCase::$serverOutputPath = $outputPath;
        IntegrationTestCase::$serverLaunched = true;
    }

    protected static function killSelenium()
    {
        static::killProcessByPort('4444');
        IntegrationTestCase::$seleniumLaunched = false;
    }

    protected static function killServer()
    {
        // print everything that was returned by server
        $output = file_get_contents(IntegrationTestCase::$serverOutputPath);
        echo $output;
        IntegrationTestCase::$serverOutputPath = null;

        static::killProcessByPort('4443');
        IntegrationTestCase::$serverLaunched = false;
    }

    private static function execAsync($command, $output_path = '/dev/null')
    {
        $force_async = " > $output_path 2>&1 &";
        exec($command.$force_async);
    }

    private static function execAsyncAndWaitFor($command, $content, $timeout = 30)
    {
        $output_path = "/tmp/zizaco-".str_shuffle(MD5(microtime()));
        self::execAsync($command, $output_path);
        self::waitForOutput($output_path, $content, $timeout);
        return $output_path;
    }
    private static function waitForOutput($file, $output) {
        $found = FALSE;
        $max_tries = 30;
        $num_tries = 0;
        while ( !$found ) {
            $contents = file_get_contents($file);
            // var_dump($contents);
            if ( strstr($contents, $output) ) {
                $found = TRUE;
            } else {
                if ( ++$num_tries > $max_tries ) {
                    throw new \Exception("Failed to find $output in $file");
                }
                sleep(1);
            }
        }
    }

    private static function killProcessByPort($port)
    {
        $processInfo = exec("lsof -i :$port");
        preg_match('/^\S+\s*(\d+)/', $processInfo, $matches);

        if(isset($matches[1]))
        {
            $pid = $matches[1];
            exec("kill $pid");
        }
    }
}
