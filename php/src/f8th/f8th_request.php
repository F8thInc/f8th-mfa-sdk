<?php

class F8thRequest
{
    private $_url;
    private $_method;
    private $_apiKey;
    private $_body;
    public $response;

    function __construct(F8thConfig $_config, string $_resource, array $_body, string $_method = "POST")
    {
        $this->_url = $_config->apiUrl() . $_resource;
        $this->_method = $_method;
        $this->_apiKey = $_config->apiKey();
        $this->_body = $_body;
        $this->response = $this->getResponse();
    }

    // get and set the request response
    private function getResponse() : ?object
    {
        // initiate curl
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->_url);

        // use http request method
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->_method);

        // skip the http restriction
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        // set data to post
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($this->_body));

        // set the api key, and the response and request mime type 
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'X-API-Key:' . $this->_apiKey,
            'Content-Type:application/json',
            'Accept:application/json'
        ]);

        // limit the time spent on trying to connect to the URL
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 15);

        // limit the time the curl operation should take
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);

        // set the response as a string
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // set the object response
        $res = json_decode(curl_exec($curl));

        // close the request
        curl_close($curl);

        // return the response
        return $res;
    }
}