<?php

namespace App\Models;


class Suspect
{
    private $clientID = null;
    private $month = null;
    private $reading = null;
    private $median = null;

    public function __construct($clientID, $month, $reading, $median)
    {
        $this->clientID = $clientID;
        $this->month = $month;
        $this->reading = $reading;
        $this->median = $median;
    }

    public function getClientID()
    {
        return $this->clientID;
    }

    public function getMonth()
    {
        return $this->month;
    }

    public function getReading()
    {
        return $this->reading;
    }

    public function getMedian()
    {
        return $this->median;
    }

}
