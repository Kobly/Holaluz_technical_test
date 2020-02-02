<?php

require __DIR__ . '/../vendor/autoload.php';


$fileName = $_SERVER['argv'][1];
$filesController = new \App\Controllers\FilesController();
$response = $filesController->createFile($fileName);
if (!$response['error'] && $response['filename'] != '') {
    $results = $filesController->getResults($response['filename']);
    if (!$results['error']) {
        if (count($results['suspects']) > 0) {
            $mask = "|%20.20s |%15.15s |%20.20s |%20.20s \n";
            printf($mask, 'Client', 'Month', 'Suspicious', 'Median');
            foreach ($results['suspects'] as $suspect) {
                if ($suspect instanceof \App\Models\Suspect) {
                    printf($mask, $suspect->getClientID(), $suspect->getMonth(), $suspect->getReading(),
                        $suspect->getMedian());
                }
            }
        } else {
            printf('No suspects this year' . PHP_EOL);
        }
    } else {
        printf($results['errorTxt'] . PHP_EOL);
    }
} else {
    printf($response['errorTxt'] . PHP_EOL);
}





