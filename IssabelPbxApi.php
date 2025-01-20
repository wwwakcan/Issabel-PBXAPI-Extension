<?php

namespace App\Models;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class IssabelPbxApi
{


    public  $USERNAME = "";
    public  $PASSWORD = "";

    public $SERVER_URL = "";


    /**
     * @param $server
     * @param $username
     * @param $password
     */
    public function __construct($server, $username, $password){

        $this->SERVER_URL = $server;
        $this->USERNAME = $username;
        $this->PASSWORD = $password;

    }

    /**
     * @param $server
     * @param $username
     * @param $password
     * @return self
     */
    public static function connect($server, $username, $password){

        return new self($server, $username, $password);

    }

    /**
     * @return mixed
     * @throws ConnectionException
     */
    public function getToken(){

        $response = Http::attach('username',  $this->USERNAME)
            ->attach('password', $this->PASSWORD)
            ->post(sprintf('%s/pbxapi/authenticate', $this->SERVER_URL));

        if ($response->failed())
            throw new \Exception("PBX AUTH API Error: " . $response->body());

        return $response->json()['access_token'];

    }

    /**
     * @param $method
     * @param $endpoint
     * @param $data
     * @return mixed
     * @throws ConnectionException
     */
    private function request($method, $endpoint, $data = []){

        $getToken = $this->getToken();

        $response = Http::withToken($getToken)
            ->$method(sprintf("%s/pbxapi/%s",  $this->SERVER_URL, $endpoint), $data);

        if ($response->failed())
            throw new \Exception("PBX API Error: " . $response->body());

        return $response;
    }


    /**
     * @return mixed
     * @throws ConnectionException
     */
    public function extensions(){
        return $this->request('get', 'extensions')->json();
    }


    /**
     * @param $extension
     * @return mixed
     * @throws ConnectionException
     */
    public function extension($extension){
        return $this->request('get', "extensions/$extension")->json();
    }

    /**
     * @return mixed
     * @throws ConnectionException
     */
    public function channels(){
        return $this->request('get', "v2apiservice", [
            "action" => "channels"
        ])->json();
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param $filter
     * @return mixed
     * @throws ConnectionException
     */
    public  function cdr($startDate = null, $endDate = null, $filter = "all"){

        if ($startDate === null)
            $startDate = date("Y-m-d", strtotime("-2 days"));

        if ($endDate === null)
            $endDate = date("Y-m-d");

        return $this->request('get',
            "v2apiservice",
            [
                "action" => "cdr",
                "start_date" => $startDate,
                "end_date" => $endDate,
                "extension" => $filter
            ])->json();
    }

    /**
     * @param $cdrFile
     * @return ResponseFactory|Application|Response
     * @throws ConnectionException
     */
    public function cdrPlayer($cdrFile = null){

        if ($cdrFile === null)
            throw new \Exception("File not found");

        $getFile = $this->request('get',
            "v2apiservice",
            [
                "action" => "player",
                "file" => $cdrFile
            ]);

        return response($getFile->body(), 200)
            ->header('Content-Type', 'audio/mpeg')
            ->header('Content-Disposition', 'inline; filename="' . basename($cdrFile) . '"');

    }

    /**
     * @param $channel
     * @param $extension
     * @param $callerID
     * @param $context
     * @param $timeout
     * @return mixed
     * @throws ConnectionException
     */
    public function originate($channel = "SIP/XXXXXX", $extension = "SIP/XXXXXXX", $callerID = "Title", $context = "from-internal", $timeout = "30000"){

        return $this->request('get',
            "manager/originate",
            [
                'channel' => $channel,
                'extension' => $extension,
                'callerid' => $callerID,
                'context' => $context,
                'priority' => 1,
                'timeout' => $timeout,
            ])->json();
    }

    /**
     * @param $channel
     * @param $extension
     * @param $listenMode
     * @param $callerID
     * @return mixed
     * @throws ConnectionException
     */
    public function spyCall($channel = "SIP/XXXXXX", $extension = "SIP/XXXXXXX", $listenMode = "q", $callerID = "Dinleme"){

        return $this->request('get',
            "manager/originate",
            [
                'channel' => $channel,
                'application' => 'ChanSpy',
                'data' => "$extension,$listenMode",
                'callerid' => $callerID,
            ])->json();
    }

}

