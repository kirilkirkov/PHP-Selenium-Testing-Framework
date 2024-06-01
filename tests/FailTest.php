<?php
declare(strict_types=1);

namespace Tests;

// PHP-WebDriver
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
// PHPUnit
use PHPUnit\Framework\TestCase;

class FailTest extends TestCase
{
    private $driver;

    public function __construct($driver)
    { 
        $this->driver = $driver;
    }

    public function beforeEachTest()
    {
        //
    }

    public function afterEachTest()
    {
        // 
    }
    
    public function github_repo_test()
    {
        $this->assertTrue(false);
    }
}