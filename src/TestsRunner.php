<?php

namespace Src;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Firefox\FirefoxOptions;

use Src\ResultsContainer;
use Src\CmdMessages;

/**
 * Class TestsRunner
 *
 * Main class to run Selenium tests
 */
class TestsRunner
{
    private RemoteWebDriver $driver;
    private int $startTime;

    public function __construct()
    {
        if (!function_exists('pcntl_async_signals') || !function_exists('pcntl_signal')) {
            throw new \Exception('PCNTL functions are not available. Ensure PHP is compiled with --enable-pcntl');
        }

        // Handle Ctrl+C or process termination
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
    public function run(array $testFiles): void
    {
        $this->startTime = time();
        $this->driver = $this->createWebDriver();

        foreach ($testFiles as $file) {
            $this->executeTestFile($file);
        }
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
     * Execute a single test file
     *
     * @param string $file
     */
    private function executeTestFile(string $file): void
    {
        CmdMessages::printMessage("Running test: $file");

        require_once $file;

        $className = 'Tests\\' . basename($file, '.php');
        if (class_exists($className)) {
            $testClass = new $className($this->driver);
            $this->runTestMethods($testClass);
        }

        CmdMessages::printMessage("Test completed $file completed.");
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

            ResultsContainer::setSuccess(get_class($testClass), $method);
        } catch (\Throwable $e) {
            $this->handleTestException($testClass, $method, $e);
        }
    }

    /**
     * Handle exceptions thrown during test execution
     *
     * @param object $testClass
     * @param string $method
     * @param \Throwable $e
    */
    private function handleTestException(object $testClass, string $method, \Throwable $e): void
    {
        $exceptionType = get_class($e);
        $trace = $e->getTrace();

        ResultsContainer::setFailed(
            $exceptionType,
            $e->getMessage(),
            get_class($testClass),
            $method,
            $trace
        );
    }

    public function __destruct()
    {
        if (isset($this->driver)) {
            $this->driver->quit();
        }
    }
} 
