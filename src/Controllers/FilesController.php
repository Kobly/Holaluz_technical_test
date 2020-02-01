<?php

namespace Src\Controllers;


use function Couchbase\defaultDecoder;

class FilesController
{
//  private $pathFiles=__DIR__ . '/Files/2016-readings.csv';
    private $pathFiles = __DIR__ . '/../Files/';
    private $parsedFilesDir = __DIR__ . '/../parsedFiles/';


    public function createFile($fileName)
    {
        $response = ['error' => 0, 'errorTxt' => '', 'filename' => ''];
        if (strpos($fileName, '.csv') !== false) {
            $createFileResponse = $this->createFileByCSV($fileName);
            if (!$createFileResponse['error']) {
                $response['filename'] = $createFileResponse['filename'];
            } else {
                $response['error'] = 1;
                $response['errorTxt'] = 'Error creating file';
            }
        } elseif (strpos($fileName, '.xml') !== false) {
            $createFileResponse = $this->createFileByXml($fileName);
            if (!$createFileResponse['error']) {
                $response['filename'] = $createFileResponse['filename'];
            } else {
                $response['error'] = 1;
                $response['errorTxt'] = 'Error creating file';
            }
        } else {
            $response['error'] = 1;
            $response['errorTxt'] = 'Format unknown';
        }


        return $response;
    }

    public function getResults($name)
    {
        if (file_exists($this->parsedFilesDir . $name)) {
            $fileInfo = file_get_contents($this->parsedFilesDir . $name);
            $data = json_decode($fileInfo, true);
            $suspects = $this->parseData($data);
            return $suspects;
        }
    }

    private function parseData($data)
    {
        $clientsInfo = [];
        $response = ['error' => 0, 'errorTxt' => '', 'suspects' => []];
        foreach ($data as $info) {
            $date = explode('-', $info['date']);
            $year = $date[0];
            $month = $date[1];
            if (!isset($clientsInfo[$info['clientID']][$year]['totalMonths'])) {
                $clientsInfo[$info['clientID']][$year]['totalMonths'] = 0;
            } else {
                $clientsInfo[$info['clientID']][$year]['totalMonths'] = $clientsInfo[$info['clientID']][$year]['totalMonths'] + 1;
            }
            if (!isset($clientsInfo[$info['clientID']][$year]['totalReadings'])) {
                $clientsInfo[$info['clientID']][$year]['totalReadings'] = 0;
            } else {
                $clientsInfo[$info['clientID']][$year]['totalReadings'] = $clientsInfo[$info['clientID']][$year]['totalReadings'] + $info['reading'];
            }
//            $clientsInfo[$info['clientID']][$year][$month] = $info['reading'];
            $clientsInfo[$info['clientID']][$year]['monthReadigs'][$month] = $info['reading'];

        }
        $response['suspects'] = $this->calculateYearMedian($clientsInfo);
//        $this->findSuspects($clientsInfo);
        return $response;
    }

    private function calculateYearMedian($clientsInfo)
    {
        $resultsClientsInfo = $clientsInfo;
        $suspects = [];
        foreach ($clientsInfo as $clientID => $yearInfo) {
            foreach ($yearInfo as $year => $info) {
                $median = 0;
                if ($info['totalReadings'] > 0 && $info['totalMonths'] > 0) {
                    $median = round($info['totalReadings'] / $info['totalMonths']);
                }
                $resultsClientsInfo[$clientID][$year]['median'] = $median;
                if ($median > 0) {
                    foreach ($resultsClientsInfo[$clientID][$year]['monthReadigs'] as $month => $reading) {
                        $medianUP = (int)$median + 50;
                        $medianDown = (int)$median - 50;

                        if ((int)$reading > $medianUP || (int)$reading < $medianDown) {
                            $suspects[] = [
                                'clientID' => $clientID,
                                'month' => $month,
                                'reading' => $reading,
                                'median' => $median
                            ];
                        }
                    }
                }

            }
        }
        return $suspects;
    }

    private function findSuspects($clientsInfo, $monthReadigs, $median)
    {
        $suspects = [];
        foreach ($clientsInfo as $clientID => $yearInfo) {
            foreach ($yearInfo as $year => $info) {
                $median = $info['median'];
                if (count($info['monthReadigs']) > 0) {
                    foreach ($info['monthReadigs'] as $monthInfo) {

                    }
//                    var_dump($info['monthReadigs']);
                }
//                $median = 0;
//                if ($info['totalReadings'] > 0 && $info['totalMonths'] > 0) {
//                    $median = round($info['totalReadings'] / $info['totalMonths']);
//                }
//                $resultsClientsInfo[$clientID][$year]['median'] = $median;
            }
        }
    }

    private function createFileByCSV($name)
    {
        $response = ['error' => 1, 'filename' => ''];
        $now = date("Ymd_Hisu");
        $newFileRoute = $this->parsedFilesDir . $now;
        if (!is_dir($this->parsedFilesDir)) {
            mkdir($this->parsedFilesDir);
        }
        $info = [];
        if (($handle = fopen($this->pathFiles . $name, "r")) !== false) {
            $row = 1;
            while (($data = fgetcsv($handle, null, ",")) !== false) {
                if ($row != 1) {
                    if (count($data) >= 3) {
                        $info[] = ['clientID' => $data[0], 'date' => $data[1], 'reading' => $data[2]];
                    }
                }
                $row++;
            }
            fclose($handle);
        }
        $newFile = file_put_contents($newFileRoute,
            json_encode($info));
        if (!empty($newFile)) {
            $response = ['error' => 0, 'filename' => $now];
        }
        return $response;
    }

    private function createFileByXml($name)
    {
        $response = ['error' => 1, 'filename' => ''];
        $now = date("Ymd_Hisu");
        $newFileRoute = $this->parsedFilesDir . $now;
        if (!is_dir($this->parsedFilesDir)) {
            mkdir($this->parsedFilesDir);
        }
        $xml = simplexml_load_file($this->pathFiles . $name);
        if (!empty($xml)) {
            $info = [];
            foreach ($xml->children() as $data) {
                $reading = (string)$data[0];
                $clientID = (string)$data['clientID'];
                $date = (string)$data['period'][0];
                $info[] = ['clientID' => $clientID, 'date' => $date, 'reading' => $reading];
            }
            $newFile = file_put_contents($newFileRoute,
                json_encode($info));
            if (!empty($newFile)) {
                $response = ['error' => 0, 'filename' => $now];
            }
        }
        return $response;
    }
}
