<?php


class Test extends \PHPUnit\Framework\TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->filesController = new \App\Controllers\FilesController();
    }

    public function testFilesControllerCreateFileFromCsv()
    {
        $response = $this->filesController->createFile('2016-readings.csv');

        $this->assertEquals($response['error'], 0);
    }

    public function testFilesControllerCreateFileFromXml()
    {
        $response = $this->filesController->createFile('2016-readings.xml');

        $this->assertEquals($response['error'], 0);
    }

    public function testFilesControllerCreateFileWithBadName()
    {
        $response = $this->filesController->createFile('2016-readings.xm');

        $this->assertEquals($response['error'], 1);
        $this->assertEquals($response['errorTxt'], 'Format unknown');
    }

    public function testFilesControllerGetResults()
    {
        $response = $this->filesController->createFile('2016-readings.xml');
        $results = $this->filesController->getResults($response['filename']);

        $this->assertEquals($results['error'], 0);

    }

    public function testNewSuspect()
    {
        $suspect = new \App\Models\Suspect('1123', '01', '231', '23');

        $this->assertEquals($suspect->getClientID(), '1123');
        $this->assertEquals($suspect->getMonth(), '01');
        $this->assertEquals($suspect->getReading(), '231');
        $this->assertEquals($suspect->getMedian(), '23');
    }
}

