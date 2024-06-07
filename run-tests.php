<?php

if (!file_exists('vendor/autoload.php')) {
    echo "Please run 'composer install' first\n";
    exit(1);
}

require 'vendor/autoload.php';
require 'config.php';
require 'helpers.php';

use Src\TestsRunner;
use Src\TestsParser;
use Src\ParallelTesting;
use Src\Response;

ini_set("memory_limit", MEMORY_LIMIT ?? '512M');
ini_set('max_execution_time', MAX_EXECUTION_TIME ?? '300'); 

set_exception_handler(function ($e) {
    echo $e->getMessage();
    exit(1);
});

(new Response())->checkResultsDirectory();
$testFiles = (new TestsParser())->getTestFiles();

/**
 * Run tests in parallel
 * /usr/bin/php run-tests.php --parallel tests/ExampleTest.php
 */
if(PARALLEL_TESTS) {
    if(count($argv) == 3 && $argv[1] === '--parallel') { // Run single test in parallel
        $testRunner = new TestsRunner();
        $testRunner->run([$argv[2]]);
    } else {
        $parallelTesting = new ParallelTesting(); // Execute tests in parallel
        $parallelTesting->run($testFiles);
    }
    return;
}

/**
 * Run tests sequentially
 */
$testRunner = new TestsRunner();
$testRunner->run($testFiles);