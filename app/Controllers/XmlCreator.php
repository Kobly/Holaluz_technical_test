<?php

namespace App\Controllers;


use App\Interfaces\iFilesCreator;

class XmlCreator implements iFilesCreator
{
    private $pathFiles = __DIR__ . '/../Files/';
    private $parsedFilesDir = __DIR__ . '/../parsedFiles/';

    public function createFile($fileName)
    {
        $response = ['error' => 1, 'filename' => ''];
        $now = date("Ymd_Hisu");
        $newFileRoute = $this->parsedFilesDir . $now;
        if (!is_dir($this->parsedFilesDir)) {
            mkdir($this->parsedFilesDir);
        }
        $xml = simplexml_load_file($this->pathFiles . $fileName);
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
