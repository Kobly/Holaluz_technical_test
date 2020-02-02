<?php

namespace App\Controllers;

use App\Interfaces\iFilesCreator;
use App\Models\Suspect;

class FilesController
{
    private $parsedFilesDir = __DIR__ . '/../parsedFiles/';
    private $creators = ['csv' => 'App\Controllers\CsvCreator', 'xml' => 'App\Controllers\XmlCreator'];


    public function createFile($fileName)
    {
        $response = ['error' => 0, 'errorTxt' => '', 'filename' => ''];
        $fileExtension = explode('.', $fileName);
        if (count($fileExtension) > 1) {
            if (isset($this->creators[$fileExtension[1]])) {
                $fileController = new $this->creators[$fileExtension[1]];
                if ($fileController instanceof iFilesCreator) {
                    $createFileResponse = $fileController->createFile($fileName);
                    if (!$createFileResponse['error']) {
                        $response['filename'] = $createFileResponse['filename'];
                    } else {
                        $response['error'] = 1;
                        $response['errorTxt'] = 'Error creating file';
                    }
                }
            } else {
                $response['error'] = 1;
                $response['errorTxt'] = 'Format unknown';
            }
        }

        return $response;
    }

    public function getResults($name)
    {
        $suspects = [];
        if (file_exists($this->parsedFilesDir . $name)) {
            $fileInfo = file_get_contents($this->parsedFilesDir . $name);
            $data = json_decode($fileInfo, true);
            $suspects = $this->parseData($data);
            unlink($this->parsedFilesDir . $name);
        }
        return $suspects;
    }

    private function parseData($data)
    {
        $clientsInfo = [];
        $response = ['error' => 0, 'errorTxt' => '', 'suspects' => []];
        foreach ($data as $info) {
            $date = explode('-', $info['date']);
            $year = $date[0];
            $month = $date[1];

            if (!isset($clientsInfo[$info['clientID']][$year]['totalYearReadings'])) {
                $clientsInfo[$info['clientID']][$year]['totalYearReadings'] = 0;
            } else {
                $clientsInfo[$info['clientID']][$year]['totalYearReadings'] = $clientsInfo[$info['clientID']][$year]['totalYearReadings'] + $info['reading'];
            }
            $clientsInfo[$info['clientID']][$year]['monthReadigs'][$month] = $info['reading'];

        }
        $response['suspects'] = $this->calculateMedianAndGetSuspects($clientsInfo);
        return $response;
    }

    private function calculateMedianAndGetSuspects($clientsInfo)
    {
        $resultsClientsInfo = $clientsInfo;
        $suspects = [];
        foreach ($clientsInfo as $clientID => $yearInfo) {
            foreach ($yearInfo as $year => $info) {
                $median = 0;
                if ($info['totalYearReadings'] > 0) {
                    $median = round($info['totalYearReadings'] / 12);
                }
                $resultsClientsInfo[$clientID][$year]['median'] = $median;
                if ($median > 0) {
                    $medianUP = (int)$median + ((int)$median / 2);
                    $medianDown = (int)$median - ((int)$median / 2);
                    foreach ($resultsClientsInfo[$clientID][$year]['monthReadigs'] as $month => $reading) {
                        if ((int)$reading > $medianUP || (int)$reading < $medianDown) {
                            $suspects[] = new Suspect($clientID, $month, $reading, $median);
                        }
                    }
                }
            }
        }
        return $suspects;
    }
}
