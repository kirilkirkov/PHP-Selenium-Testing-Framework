# PHP Selenium Testing Framework

Welcome to the PHP Selenium Testing Framework project! This framework leverages Selenium and PHPUnit to automate browser testing for PHP applications. Below you'll find detailed information on how to set up, configure, and run the tests, as well as some tips to make the most of this powerful testing tool.

## Table of Contents

- [Introduction](#introduction)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Writing Tests](#writing-tests)
- [Running Tests](#running-tests)
- [Contributing](#contributing)
- [License](#license)

## Introduction

This framework provides a robust environment for automated browser testing using Selenium WebDriver. It supports Selenium Standalone Firefox Docker image and ensures seamless session management, including clean shutdowns on test completion or interruption.

## Features

- **Automated Browser Testing**: Run your browser-based tests automatically.
- **Selenium WebDriver Integration**: Utilize the power of Selenium WebDriver for browser automation.
- **PHPUnit Support**: Write and manage your tests using PHPUnit.
- **Docker Compatibility**: Easily start Selenium using Docker.
- **Automatic Test Discovery**: Automatically discover and run tests located in the `tests` directory.
- **Graceful Shutdown**: Clean up Selenium sessions on script termination or interruption.

## Requirements

- PHP 7.4 or higher
- Composer
- Docker

## Installation

1. **Clone the repository:**
```bash
git clone https://github.com/kirilkirkov/PHP-Selenium-Testing-Framework.git
```
2. **Install dependencies:**
```bash
cd php-selenium-testing-framework
composer install
```
3. **Start Selenium with Docker:**
```bash
docker run -d -p 4444:4444 -p 5900:5900 --shm-size=2g --restart=always selenium/standalone-firefox:latest
```

## Configuration
Ensure you have the necessary configuration files:

- **config.php**: Add your specific configurations.
- **helpers.php**: Helper functions for your tests.

## Usage

1. **Run the test script:**
```bash
php run-tests.php
```

2. **Interrupt the tests:**
Press Ctrl+C to stop the tests gracefully. This will ensure that the Selenium session is properly terminated.

## Writing Tests
Create your test classes in the tests directory. Test classes should follow the naming convention *Test.php and should be placed under the Tests namespace.

Example test class:
```php
<?php
declare(strict_types=1);

namespace Tests;

// PHP-WebDriver
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
// PHPUnit
use PHPUnit\Framework\TestCase;

class SampleTest extends TestCase
{
    private $driver;

    public function __construct(RemoteWebDriver $driver)
    {
        $this->driver = $driver;
    }

    public function beforeEachTest()
    {
        // Setup code before each test
    }

    public function afterEachTest()
    {
        // Cleanup code after each test
    }

    public function example_test()
    {
        // Your test code
    }
}
```

## Running Tests
Execute the tests using the command provided in the [Usage](#usage) section. The framework will automatically discover and run all tests matching the pattern *Test.php in the tests directory.

## Real-time Monitoring
You can watch the tests in real-time using a VNC viewer. The Selenium Docker image allows VNC connections with the following details:

- **Host:** localhost
- **Port:** 5900
- **Password:** secret

To connect using a VNC viewer:

- Download and install a VNC viewer (e.g., <a href="https://www.realvnc.com/en/">RealVNC</a>, <a href="https://tigervnc.org/">TigerVNC</a>).
- Open the VNC viewer and connect to localhost:5900.
- When prompted, enter the password secret.

This allows you to observe the browser interactions as the tests are being executed.

## Contributing
We welcome contributions! Please follow these steps to contribute:

- Fork the repository.
- Create a new branch for your feature or bugfix.
- Commit your changes.
- Push your branch and create a Pull Request.

## License
This project is licensed under the MIT License. See the LICENSE file for details.

