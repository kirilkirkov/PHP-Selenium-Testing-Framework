<?php

namespace Src;

class ResultsContainer
{
    private static array $results = [];

    public static function setFailed(
        string $exceptionType,
        string $exceptionMessage,
        string $className,
        string $method,
        array $trace
    ) {
        $traceString = '';
        if (SHOW_FAILED_TRACE && is_array($trace) && isset($trace[0])) {
            $traceString = PHP_EOL . ' Trace: ' . PHP_EOL;
            foreach ($trace as $item) {
                $traceString .= 'File: ' . $item['file'] . ' Line: ' . $item['line'] . PHP_EOL;
            }
        }

        switch(RESULT_TYPE) {
            case 'json':
                self::$results[] = [
                    'status' => 'failed',
                    'error_type' => $exceptionType,
                    'error_message' => $exceptionMessage,
                    'test_class' => $className,
                    'test_method' => $method,
                    'trace' => $traceString
                ];
                break;
            case 'xml':
                self::$results[] = [
                    'status' => 'failed',
                    'error_type' => $exceptionType,
                    'error_message' => $exceptionMessage,
                    'test_class' => $className,
                    'test_method' => $method,
                    'trace' => $traceString
                ];
                break;
            default:
                self::$results[] = "\033[0;31m Test failed." . PHP_EOL . " Error type: $exceptionType " . PHP_EOL . " Error message: " . $exceptionMessage . PHP_EOL . " Test Class: " . $className . PHP_EOL . " Test Method: " . $method . " $traceString \033[0m";
        }
    }

    public static function setSuccess(string $className, string $method)
    {
        switch(RESULT_TYPE) {
            case 'json':
                self::$results[] = [
                    'status' => 'passed',
                    'test_class' => $className,
                    'test_method' => $method
                ];
                break;
            case 'xml':
                self::$results[] = [
                    'status' => 'passed',
                    'test_class' => $className,
                    'test_method' => $method
                ];
                break;
            default:
                self::$results[] = "\033[0;32m Test passed. " . $className . "::" . $method . "\033[0m";
        }
    }

    public static function getResults(): array
    {
        return self::$results;
    }
}