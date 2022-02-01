<?php

// include all the necessary classes
include_once('f8th_config.php');
include_once('f8th_session.php');
include_once('f8th_request.php');

// check if we should start the session
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// create the class F8th
class F8th
{
    private $_config;
    private $_session;

    function __construct(string $_userId = null)
    {
        // create and an object F8thConfig
        $this->_config = new F8thConfig();
        // cast the method getSession
        $this->_session = $this->getSession($_userId);
    }

    // return the object F8thSession created
    private function getSession(?string $_userId = null) : ?F8thSession
    {
        // if the _config isEnabled is false we return null
        if(!$this->_config->isEnabled()) return null;
        // create and return an object F8thSession
        return new F8thSession($this->_config, $_userId);
    }

    // get the CDN url
    function cdnUrl() : string
    {
        return $this->_config->cdnUrl();
    }

    // get the sessionId
    function sessionId() : int
    {
        // if the _config isEnabled is false we return 0
        if(!$this->_config->isEnabled()) return 0;
        return $this->_session->id();
    }

    // set the userId in _session objects
    function setUserId(?string $_userId = null) : void
    {
        // if the _config isEnabled is true we set the userId in _session objects
        if($this->_config->isEnabled()) $this->_session->setUserId($_userId);
    }

    // check the authenticity of the user
    function authCheck(array $_policy, ?string $_userId = null) : object
    {
        // if the _config isEnabled is false we return is_auth with value 1 automatically
        if(!$this->_config->isEnabled()) return (object) ["is_auth" => 1];
        // coalesce with the input $_userId and the default one
        $userId = $_userId ?? $this->_session->userId();
        // we create a F8thRequest for an authCheck
        $f8thRequest = new F8thRequest(
            $this->_config,
            'auth/check/',
            [
                'session_id' => $this->_session->id(),
                'user_id' => $userId,
                'policy' => $_policy
            ]
        );
        // check if the response is valid, if not, we se is_auth to 0
        if(!$f8thRequest->response) $f8thRequest->response = (object) ["is_auth" => 0];
        // set the response is_auth if isn't set
        elseif(!isset($f8thRequest->response->is_auth)) $f8thRequest->response->is_auth = 0;
        // check if the userId has to change
        elseif($f8thRequest->response->is_auth) $this->setUserId($_userId);
        // return the response
        return $f8thRequest->response;
    }

    private function _authChange(string $_resource, ?int $_sessionId = null, ?string $_userId = null)
    {
        // if the _config isEnabled is false we return affected_sessions with value 0
        if(!$this->_config->isEnabled()) return (object) ["affected_sessions" => 0];
        // coalesce with the input $_sessionId and the default one
        $sessionId = $_sessionId ?? $this->_session->id();
        // coalesce with the input $_userId and the default one
        $userId = $_userId ?? $this->_session->userId();
        // create a F8thRequest object 
        $f8thRequest = new F8thRequest(
            $this->_config,
            'auth/'. $_resource .'/',
            [
                'session_id' => $sessionId,
                'user_id' => $userId
            ],
            "UPDATE"
        );
        // check if the response is valid
        if(!$f8thRequest->response) $f8thRequest->response = (object) ["affected_sessions" => 0];
        // set the response affected_sessions if isn't set
        elseif(!isset($f8thRequest->response->affected_sessions)) $f8thRequest->response->affected_sessions = 0;
        // return the response
        return $f8thRequest->response;
    }

    // confirm the authenticity of the user
    function authConfirm(?int $_sessionId = null, ?string $_userId = null) : object
    {
        return $this->_authChange('confirm', $_sessionId, $_userId);
    }

    // cancel the authenticity of the user
    function authCancel(?int $_sessionId = null, ?string $_userId = null) : object
    {
        return $this->_authChange('cancel', $_sessionId, $_userId);
    }

    // reset the behavioral biometric of an user
    function authReset(?string $_userId = null) : object
    {
        // if the _config isEnabled is false we return affected_sessions with value 0
        if(!$this->_config->isEnabled()) return (object) ["affected_sessions" => 0];
        // coalesce with the input $_userId and the default one
        $userId = $_userId ?? $this->_session->userId();
        // create a F8thRequest object 
        $f8thRequest = new F8thRequest(
            $this->_config,
            'auth/reset/',
            [
                'user_id' => $userId,
                'branch_id' => $this->_config->branchId()
            ],
            "DELETE"
        );
        // check if the response is valid
        if(!$f8thRequest->response) $f8thRequest->response = (object) ["affected_sessions" => 0];
        // set the response affected_sessions if isn't set
        elseif(!isset($f8thRequest->response->affected_sessions)) $f8thRequest->response->affected_sessions = 0;
        // return the response
        return $f8thRequest->response;
    }
}