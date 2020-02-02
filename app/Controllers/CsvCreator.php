<?php

namespace App\Controllers;

use App\Interfaces\iFilesCreator;

class CsvCreator implements iFilesCreator
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
        $info = [];
        if (($handle = fopen($this->pathFiles . $fileName, "r")) !== false) {
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
}
