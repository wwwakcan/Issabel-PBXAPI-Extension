<?php

namespace App\Models;

class AsteriskAmi {

    private $socket;
    private $host;
    private $port;
    private $username;
    private $password;

    public function __construct($host, $port, $username, $password){
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }


    public function connect(){

        $this->socket = fsockopen($this->host, $this->port, $errno, $errstr, 10);

        if (!$this->socket)
            throw new \Exception("Bağlantı hatası: $errstr ($errno)");

        $this->sendCommand("Action: Login\r\n");
        $this->sendCommand("Username: " . $this->username . "\r\n");
        $this->sendCommand("Secret: " . $this->password . "\r\n\r\n");
    }

    public function sendCommand($command) {
        fwrite($this->socket, $command);
    }


    /**
     * @param  $extension
     * @param $targetPhone
     * @param $callerID
     * @param $context
     * @param $priority
     * @param $timeout
     */
    public function originateCall($extension, $targetPhone, $callerID, $context, $priority = 1, $timeout = 10000){

        $this->sendCommand("Action: Originate\r\n");
        $this->sendCommand("Channel: $extension\r\n");
        $this->sendCommand("Exten: $targetPhone\r\n");
        $this->sendCommand("CallerID: $callerID\r\n");
        $this->sendCommand("Context: $context\r\n");
        $this->sendCommand("Priority: $priority\r\n");
        $this->sendCommand("Timeout: $timeout\r\n\r\n");


        $response = "";
        while (!feof($this->socket)) {

            $response .= fgets($this->socket, 1024);

            if (strpos($response, 'Message: Originate successfully queued')) {
                $this->logoff();
                return [
                    "status" => "success",
                    "message" => "Originate successfully queued",
                    "response" => $response
                ];
            }

            if (strpos($response, 'Message: Originate failed') || strpos($response, 'Channel: OutgoingSpoolFailed')) {
                $this->logoff();
                return [
                    "status" => "error",
                    "message" => "Originate failed",
                    "response" => $response
                ];
            }

        }

        return [
            "status" => "error",
            "message" => "Process failed",
            "response" => null
        ];

    }


    /**
     * @param $monitoringChannel
     * @param $targetChannel
     * @param $SpyMode
     * @param $timeout
     * @return array
     */
    public function spyCall($monitoringChannel = "SIP/extension", $targetChannel = "SIP/extension"){

        $this->sendCommand("Action: Originate\r\n");
        $this->sendCommand("Channel: $monitoringChannel\r\n");
        $this->sendCommand("Application: ChanSpy\r\n");
        $this->sendCommand("Data: $targetChannel,q\r\n");
        $this->sendCommand("Async: yes\r\n");

        $response = "";
        while (!feof($this->socket)) {

            $line = fgets($this->socket, 1024);
            $response .= $line;

            if (stripos($line, 'Response: Success') !== false) {
                $this->logoff();
                return [
                    "status" => "success",
                    "message" => "CanSpy success",
                    "response" => $response
                ];
            }
        }

        return [
            "status" => "error",
            "message" => "Process failed",
            "response" => null
        ];
    }



    public function logoff(){
        $this->sendCommand("Action: Logoff\r\n\r\n");
        fclose($this->socket);
    }

}
