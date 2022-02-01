<?php

class F8thSession
{
    private $_id;
    private $_timestamp;
    private $_ip;
    private $_url;
    private $_groupId;
    private $_userId;
    private $_branchId;

    function __construct(F8thConfig $_config, ?string $_userId)
    {
        // set data to post
        $this->_timestamp = $this->getTimestamp();
        $this->_ip = $this->getIp();
        $this->_url = $this->getUrl();
        $this->_groupId = $this->getGroupId();
        $this->_userId = $this->getUserId($_userId);
        $this->_branchId = $_config->branchId();
        $this->_id = $this->getId($_config);
    }

    // return the timestamp in millisecond
    private function getTimestamp() : int
    {
        return round(microtime(true) * 1000);
    }

    // return the client ip
    private function getIp() : string
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    // return the complete url
    private function getUrl() : string
    {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }

    // return the group id
    private function getGroupId() : string
    {
        // check if we should start the session
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        // return the session id
        return session_id();
    }

    // sanitize and return the user id
    private function getUserId(?string $_userId) : string
    {
        // sanitize and return the _userId 
        return $_userId ?? '';
        
        // example of automated userId
        // return $_userId ?? $_SESSION['user']->user ?? '';
    }

    // request a session id to the api and return the response
    private function getId($_config) : int
    {
        // create a F8thRequest object
        $f8thRequest = new F8thRequest(
            $_config,
            'sessions/',
            [
                'timestamp' => $this->_timestamp,
                'ip' => $this->_ip,
                'url' => $this->_url,
                'group_id' => $this->_groupId,
                'user_id' => $this->_userId,
                'branch_id' => $this->_branchId,
            ]
        );
        return isset($f8thRequest->response->id) ? $f8thRequest->response->id : 0;
    }

    // sanitize and set the property _userId 
    function setUserId(?string $_userId) : void
    {
        // coalesce with the input $_userId and an empty string
        $this->_userId = $_userId ?? "";
    }

    // return the value of the property _id
    function id() : int
    {
        return $this->_id;
    }

    // return the value of the property _userId
    function userId() : string
    {
        return $this->_userId;
    }
}