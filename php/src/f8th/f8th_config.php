<?php

include_once('config_policy.php');

class F8thConfig
{
    private $_isEnabled;
    private $_apiKey;
    private $_apiUrl;
    private $_cdnUrl;
    private $_branchId;

    function __construct()
    {
        // include the configuration for the F8th MFA SDK
        include_once('config.php');
        $this->_isEnabled = $f8thMfaIsEnabled;
        $this->_apiKey = $f8thMfaApiKey;
        $this->_apiUrl = $f8thMfaApiUrl;
        $this->_cdnUrl = $f8thMfaCdnUrl;
        $this->_branchId = $f8thMfaBranchId;
    }

    // return the value of the property _isEnabled
    function isEnabled() : bool
    {
        return $this->_isEnabled;
    }

    // return the value of the property _apiKey
    function apiKey() : string
    {
        return $this->_apiKey;
    }

    // return the value of the property _apiUrl
    function apiUrl() : string
    {
        return $this->_apiUrl;
    }

    // return the value of the property _cdnUrl
    function cdnUrl() : string
    {
        return $this->_cdnUrl;
    }

    // return the value of the property _branchId
    function branchId() : string
    {
        return $this->_branchId;
    }
}