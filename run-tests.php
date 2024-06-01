<?php

if (!file_exists('vendor/autoload.php')) {
    echo "Please run 'composer install' first\n";
    exit(1);
}

require 'vendor/autoload.php';
require 'config.php';
require 'helpers.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Firefox\FirefoxOptions;

ini_set("memory_limit", MEMORY_LIMIT ?? '512M');
ini_set('max_execution_time', MAX_EXECUTION_TIME ?? '300'); 

set_exception_handler(function ($e) {
    echo $e->getMessage();
    exit(1);
});

/**
 * Class RunTests
 *
 * Main class to run Selenium tests
 */
class RunTests
{
    private RemoteWebDriver $driver;
    private array $results = [];

    public function __construct()
    {
        pcntl_async_signals(true);
        pcntl_signal(SIGINT, function () {
            echo "Exiting..." . PHP_EOL;
            if(isset($this->driver)) {
                $this->driver->quit();
            }
            exit;
        });

        register_shutdown_function([$this, 'shutdown']);
    }

    /**
     * Shutdown handler to quit the WebDriver
     */
    private function shutdown(): void
    {
        $error = error_get_last();
        if ($error !== null && isset($this->driver)) {
            $this->driver->quit();
        }
    }

    /**
     * Main method to run all tests
     */
    public function run(): void
    {
        $this->driver = $this->createWebDriver();
        
        $testFiles = $this->getTestFiles(TESTS_DIR);
        if (empty($testFiles)) {
            echo "No test files found in " . TESTS_DIR . PHP_EOL;
            exit(1);
        }
        foreach ($testFiles as $file) {
            $this->executeTestFile($file);
        }

        $this->printResults();
    }

    /**
     * Create a WebDriver instance based on configuration
     *
     * @return RemoteWebDriver
     * @throws Exception if an invalid driver is specified
    */
    private function createWebDriver(): RemoteWebDriver
    {
        $browserArguments = BROWSER_ARGUMENTS ?? [];
        $browserCapabilities = BROWSER_CAPABILITIES ?? [];

        switch (DRIVER) {
            case 'chrome':
                $chromeOptions = new ChromeOptions();
                if(count($browserArguments) > 0) {
                    $chromeOptions->addArguments($browserArguments);
                }
                
                $capabilities = DesiredCapabilities::chrome();
                $capabilities->setCapability(ChromeOptions::CAPABILITY_W3C, $chromeOptions);
                if(count($browserCapabilities) > 0) {
                    foreach ($browserCapabilities as $key => $value) {
                        $capabilities->setCapability($key, $value);
                    }
                }

                return RemoteWebDriver::create(HOST, $capabilities, SELENIUM_CONNECTION_TIMEOUT, SELENIUM_REQUEST_TIMEOUT);
            case 'firefox':
                $firefoxOptions = new FirefoxOptions();
                if(count($browserArguments) > 0) {
                    $firefoxOptions->addArguments($browserArguments);
                }
                
                $capabilities = DesiredCapabilities::firefox();
                $capabilities->setCapability(FirefoxOptions::CAPABILITY, $firefoxOptions);
                if(count($browserCapabilities) > 0) {
                    foreach ($browserCapabilities as $key => $value) {
                        $capabilities->setCapability($key, $value);
                    }
                }
                
                return RemoteWebDriver::create(HOST, $capabilities, SELENIUM_CONNECTION_TIMEOUT, SELENIUM_REQUEST_TIMEOUT);
            default:
                throw new Exception('Invalid driver');
        }
    }

    /**
     * Get all test files from the specified directory
     *
     * @param string $directory
     * @return array
     */
    private function getTestFiles(string $directory): array
    {
        if (!is_dir($directory)) {
            throw new Exception("Directory '{$directory}' in TESTS_DIR from config.php not found.");
        }

        if (!is_readable($directory)) {
            throw new Exception("Directory '{$directory}' in TESTS_DIR from config.php is not readable.");
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        $regex = new RegexIterator($iterator, '/^.+Test\.php$/i', RecursiveRegexIterator::GET_MATCH);

        return array_keys(iterator_to_array($regex));
    }

    /**
     * Execute a single test file
     *
     * @param string $file
     */
    private function executeTestFile(string $file): void
    {
        require_once $file;

        $className = 'Tests\\' . basename($file, '.php');
        if (class_exists($className)) {
            $testClass = new $className($this->driver);
            $this->runTestMethods($testClass);
        }
    }

    /**
     * Run all test methods in a test class
     *
     * @param object $testClass
     */
    private function runTestMethods(object $testClass): void
    {
        $methods = get_class_methods($testClass);
        foreach ($methods as $method) {
            if (strpos($method, '_test') !== false) {
                $this->executeTestMethod($testClass, $method);
            }
        }
    }

    /**
     * Execute a single test method
     *
     * @param object $testClass
     * @param string $method
     */
    private function executeTestMethod(object $testClass, string $method): void
    {
        try {
            if (method_exists($testClass, 'beforeEachTest')) {
                $testClass->beforeEachTest();
            }

            $testClass->$method();

            if (method_exists($testClass, 'afterEachTest')) {
                $testClass->afterEachTest();
            }

            $this->results[] = "\033[0;32m Test passed. " . get_class($testClass) . "::" . $method . "\033[0m";
        } catch (Throwable $e) {
            $this->handleTestException($testClass, $method, $e);
        }
    }

    /**
     * Handle exceptions thrown during test execution
     *
     * @param object $testClass
     * @param string $method
     * @param Throwable $e
    */
    private function handleTestException(object $testClass, string $method, Throwable $e): void
    {
        $exceptionType = get_class($e);
        $trace = $e->getTrace();

        $traceString = '';
        if (SHOW_FAILED_TRACE && is_array($trace) && isset($trace[0])) {
            $traceString = 'Trace: ' . PHP_EOL;
            foreach ($trace as $item) {
                $traceString .= 'File: ' . $item['file'] . ' Line: ' . $item['line'] . PHP_EOL;
            }
        }

        $this->results[] = "\033[0;31m Test failed." . PHP_EOL . " Error type: $exceptionType with message: " . $e->getMessage() . PHP_EOL . " Test Class: " . get_class($testClass) . PHP_EOL . " Test Method: " . $method . PHP_EOL . " $traceString \033[0m";
    }

    /**
     * Print the results of the test execution
    */
    private function printResults(): void
    {
        foreach ($this->results as $result) {
            echo '====================' . PHP_EOL;
            echo $result . PHP_EOL;
        }
        echo '====================' . PHP_EOL;
    }

    public function __destruct()
    {
        if (isset($this->driver)) {
            $this->driver->quit();
        }
    }
}

$test = new RunTests();
$test->run();
