<?php

namespace App\Models;

use Illuminate\Support\Facades\Http;

class AsteriskAri
{
    protected $baseUrl;

    protected $username;
    protected $password;

    public function __construct($baseUrl, $username, $password){
        $this->baseUrl = $baseUrl;
        $this->username = $username;
        $this->password = $password;
    }

    private function request($method, $endpoint, $data = []){

        $response = Http::withBasicAuth($this->username, $this->password)
            ->$method("{$this->baseUrl}/$endpoint", $data);

        if ($response->failed())
            throw new \Exception("ARI API Error: " . $response->body());

        return $response->json();
    }

    public function listApplications(){
        return $this->request('get', 'applications');
    }

    public function deleteApplicationSubscription($applicationName){
        return $this->request('delete', "applications/{$applicationName}/subscription");
    }

    public function listBridges(){
        return $this->request('get', 'bridges');
    }

    public function createBridge($data){
        return $this->request('post', 'bridges', $data);
    }

    public function addChannelToBridge($bridgeId, $channel){
        return $this->request('post', "bridges/{$bridgeId}/addChannel", ['channel' => $channel]);
    }

    public function removeChannelFromBridge($bridgeId, $channel){
        return $this->request('post', "bridges/{$bridgeId}/removeChannel", ['channel' => $channel]);
    }

    public function deleteBridge($bridgeId){
        return $this->request('delete', "bridges/{$bridgeId}");
    }

    public function listChannels(){
        return $this->request('get', 'channels');
    }

    public function setChannel($channelId, $targetNumber, $callerId, $priority, $content){
        return $this->request('post', "channels", [
            'endpoint' => $channelId,
            'extension' => $targetNumber,
            'context' => $content,
            'priority' => $priority,
            'callerId' => $callerId,
        ]);
    }

    public function getChannel($channelId){
        return $this->request('get', "channels/{$channelId}");
    }

    public function hangupChannel($channelId){
        return $this->request('post', "channels/{$channelId}/hangup");
    }

    public function answerChannel($channelId){
        return $this->request('post', "channels/{$channelId}/answer");
    }

    public function playMediaOnChannel($channelId, $mediaUri){
        return $this->request('post', "channels/{$channelId}/play", ['media' => $mediaUri]);
    }

    public function holdChannel($channelId){
        return $this->request('post', "channels/{$channelId}/hold");
    }

    public function unholdChannel($channelId){
        return $this->request('post', "channels/{$channelId}/unhold");
    }

    public function listStoredRecordings(){
        return $this->request('get', 'recordings/stored');
    }

    public function getStoredRecording($recordingName){
        return $this->request('get', "recordings/stored/{$recordingName}");
    }

    public function deleteStoredRecording($recordingName){
        return $this->request('delete', "recordings/stored/{$recordingName}");
    }

    public function startRecording($recordingName, $data){
        return $this->request('post', "recordings/live/{$recordingName}", $data);
    }

    public function stopRecording($recordingName){
        return $this->request('delete', "recordings/live/{$recordingName}");
    }

    public function listEndpoints(){
        return $this->request('get', 'endpoints');
    }

    public function getEndpoint($tech, $resource){
        return $this->request('get', "endpoints/{$tech}/{$resource}");
    }

    public function listDeviceStates(){
        return $this->request('get', 'deviceStates');
    }

    public function getDeviceState($deviceName){
        return $this->request('get', "deviceStates/{$deviceName}");
    }

    public function updateDeviceState($deviceName, $state){
        return $this->request('put', "deviceStates/{$deviceName}", ['state' => $state]);
    }

    public function listSounds(){
        return $this->request('get', 'sounds');
    }

    public function getSound($soundId){
        return $this->request('get', "sounds/{$soundId}");
    }

    public function getPlayback($playbackId){
        return $this->request('get', "playbacks/{$playbackId}");
    }

    public function stopPlayback($playbackId){
        return $this->request('delete', "playbacks/{$playbackId}");
    }

    public function sendTextMessage($data){
        return $this->request('post', 'messages', $data);
    }


    public function listModules(){
        return $this->request('get', 'modules');
    }

    public function loadModule($moduleName){
        return $this->request('post', "modules/load", ['module' => $moduleName]);
    }

    public function unloadModule($moduleName){
        return $this->request('delete', "modules/unload", ['module' => $moduleName]);
    }
}
