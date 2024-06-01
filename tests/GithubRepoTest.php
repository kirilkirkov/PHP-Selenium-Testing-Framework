<?php
declare(strict_types=1);

namespace Tests;

// PHP-WebDriver
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
// PHPUnit
use PHPUnit\Framework\TestCase;

class GithubRepoTest extends TestCase
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
        $this->driver->get('https://github.com/kirilkirkov');
        $this->driver->wait()->until(
            WebDriverExpectedCondition::titleContains('kirilkirkov')
        );

        $this->driver->wait()->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::cssSelector('a[href="/kirilkirkov?tab=repositories"]'),
                'Repositories'
            )
        );
        
        $this->driver->findElement(WebDriverBy::cssSelector('a[href="/kirilkirkov?tab=repositories"]'))->click();

        $this->driver->wait()->until(
            WebDriverExpectedCondition::titleContains('Repositories')
        );

        $this->driver->findElement(WebDriverBy::linkText('PHP-Selenium-Testing-Framework'))->click();
        $this->driver->wait()->until(
            WebDriverExpectedCondition::titleContains('PHP-Selenium-Testing-Framework')
        );
    }

    public function phpunit_test()
    {
        $this->assertTrue(true);
    }
}