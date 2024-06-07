<?php

define('DRIVER', 'firefox'); // chrome or firefox (depends on docker image)

/**
 * Browser specific arguments
 * 
 * Browser arguments for Chrome can be found here: https://peter.sh/experiments/chromium-command-line-switches/
 * Browser arguments for Firefox can be found here: https://developer.mozilla.org/en-US/docs/Mozilla/Command_Line_Options
 */
define('BROWSER_ARGUMENTS', [
    // '--headless',
]);

/**
 * Browser specific capabilities
 * 
 */
define('BROWSER_CAPABILITIES', [
    'isW3cCompliant' => true,
    // 'firefox.profile.default.general.upload_max_size' => 1000,
]);

define('HOST', 'http://localhost:4444/wd/hub'); // selenium grid host
define('TESTS_DIR', 'tests'); // directory with test files
define('SHOW_FAILED_TRACE', false); // show code trace for failed tests

define('SELENIUM_CONNECTION_TIMEOUT', 30000); // in milliseconds (30 seconds)
define('SELENIUM_REQUEST_TIMEOUT', 30000); // in milliseconds (30 seconds)

define('MEMORY_LIMIT', '512M'); // in megabytes (-1 for unlimited)
define('MAX_EXECUTION_TIME', 300); // in seconds

define('RESULT_TYPE', 'terminal'); // json|xml|terminal

define('PARALLEL_TESTS', false); // run tests in parallel
define('MAX_PARALLEL_PROCESSES', 3); // max parallel processes