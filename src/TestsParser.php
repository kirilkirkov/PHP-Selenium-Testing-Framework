<?php

namespace Src;

/**
 * Class TestsParser
 */
class TestsParser
{
    public function __construct()
    {
        if (!defined('TESTS_DIR')) {
            throw new \Exception("TESTS_DIR constant is not defined in config.php");
        }
    }

    /**
     * Return all tests files
     */
    public function getTestFiles(): array
    {
        $testFiles = $this->checkTestFiles();

        if (empty($testFiles)) {
            echo "No test files found in " . TESTS_DIR . PHP_EOL;
            exit(1);
        }

        return $testFiles;
    }

    /**
     * Get all test files from the specified directory
     *
     * @return array
     */
    private function checkTestFiles(): array
    {
        $directory = TESTS_DIR;

        if (!is_dir($directory)) {
            throw new \Exception("Directory '{$directory}' in TESTS_DIR from config.php not found.");
        }

        if (!is_readable($directory)) {
            throw new \Exception("Directory '{$directory}' in TESTS_DIR from config.php is not readable.");
        }

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
        $regex = new \RegexIterator($iterator, '/^.+Test\.php$/i', \RecursiveRegexIterator::GET_MATCH);

        return array_keys(iterator_to_array($regex));
    }
}