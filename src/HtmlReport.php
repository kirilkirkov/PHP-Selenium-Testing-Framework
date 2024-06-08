<?php

namespace Src;

use Src\ResultsContainer;

/**
 * Class HtmlReport
 */
class HtmlReport
{
    private const REPORT_FILE = __DIR__ . '/../results/report.html';

    /**
     * Generate HTML report
     */
    public function generate(): void
    {
        if(HTML_REPORT === false) {
            return;
        }

        $results = ResultsContainer::getResults();
        ob_start();
    ?>
        <!doctype html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <script src="https://cdn.tailwindcss.com"></script>
            <script src="https://code.jquery.com/jquery-3.7.1.slim.min.js" integrity="sha256-kmHvs0B+OpCW5GVHUNjv9rOmY0IvSIRcf7zGUDTDQM8=" crossorigin="anonymous"></script>
            <script>
                 $(document).ready(function(){
                    $('input[type="checkbox"]').click(function(){
                        var inputValue = $(this).attr("value");
                        $("." + inputValue).toggle();
                    });
                });
            </script>
        </head>
            <body class="bg-slate-200">
                <?php
                    if(!in_array(RESULT_TYPE, ['json', 'xml'])) {
                ?>
                <p class="px-4 py-2 bg-red-600 text-center text-sm text-white">
                    !! For better results use json or xml as RESULT_TYPE !!
                </p>
                <?php } ?>
                
                <?php 
                    if(in_array(RESULT_TYPE, ['json', 'xml'])) {
                ?>
                <div class="flex bg-slate-600 w-full text-white py-2 px-4 gap-4 mb-4">
                    <!-- TOGGLE PASS TESTS -->
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" value="passed" checked class="sr-only peer">
                        <div class="relative w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                        <span class="ms-2 text-sm font-medium text-white">Pass</span>
                    </label>

                    <!-- TOGGLE FAIL TESTS -->
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" value="failed" checked class="sr-only peer">
                        <div class="relative w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all
                         peer-checked:bg-red-600"></div>
                        <span class="ms-2 text-sm font-medium text-white">Fail</span>
                    </label>
                </div>
                <?php } ?>

                <!-- PROGRESS BAR -->
                <?php 
                    if(in_array(RESULT_TYPE, ['json', 'xml'])) {
                    $passed = count(array_filter($results, function($result) {
                        return strtolower($result['status']) === 'passed';
                    }));
                    $total = count($results);
                    $percentagePass = round($total > 0 ? ($passed / $total) * 100 : 0);

                    $color = $percentagePass < 50 ? 'bg-red-500' : ($percentagePass < 100 ? 'bg-yellow-500' : 'bg-green-500');
                ?>
                <div class="container mx-auto">
                    <div class="w-full max-w-lg bg-gray-300 rounded-full mb-4">
                        <div class="<?php echo $color; ?> text-xs font-medium text-blue-100 text-center p-0.5 leading-none rounded-full" style="width: <?php echo $percentagePass; ?>%"> <?php echo $percentagePass; ?>% Pass</div>
                    </div>
                </div>
                <?php } ?>

                <div class="container mx-auto">
                    <div class="relative overflow-x-auto">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500">
                            <?php if(in_array(RESULT_TYPE, ['json', 'xml'])) { ?>
                                <thead>
                                    <tr class="bg-slate-600 text-white">
                                        <th class="px-6 py-3 text-xs font-medium uppercase">Status</th>
                                        <th class="px-6 py-3 text-xs font-medium uppercase">Error Type</th>
                                        <th class="px-6 py-3 text-xs font-medium uppercase">Error Message</th>
                                        <th class="px-6 py-3 text-xs font-medium uppercase">Test Class</th>
                                        <th class="px-6 py-3 text-xs font-medium uppercase">Test Method</th>
                                        <th class="px-6 py-3 text-xs font-medium uppercase">Trace</th>
                                    </tr>
                                </thead>
                            <?php } ?>
                            <tbody>
                                <?php 
                                    if(in_array(RESULT_TYPE, ['json', 'xml'])) {
                                        foreach ($results as $result) {
                                ?>
                                        <tr class="bg-white border-b <?php echo $result['status']; ?>">
                                            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap uppercase">
                                                <?php echo $result['status']; ?>
                                            </th>
                                            <td class="px-6 py-4">
                                                <?php echo $result['error_type'] ?? '-'; ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?php echo $result['error_message'] ?? '-'; ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?php echo $result['test_class']; ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?php echo $result['test_method']; ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?php echo isset($result['trace']) && trim($result['trace']) != '' ? $result['trace'] :  '-'; ?>
                                            </td>
                                        </tr>
                                <?php } 
                                } else {
                                    foreach ($results as $result) {
                                ?>
                                        <tr class="bg-white border-b">
                                            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                                <?php echo $result; ?>
                                            </th>
                                        </tr>
                                <?php  } 
                                   }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </body>
        </html>
    <?php
        $htmlContent = ob_get_clean();
        file_put_contents(self::REPORT_FILE, $htmlContent);
    }

    /**
     * Get HTML report as string
     */
    public function getReport(): string
    {
        return file_get_contents(self::REPORT_FILE);
    }
}