<?php

/**
 * EBSCOException class
 * Used when EBSCO API calls return error messages
 *
 * Copyright [2014] [EBSCO Information Services]
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
class EBSCOException extends Exception
{
    const CRITICAL_ERROR = 1;

    // Make message argument mandatory
    public function __construct($message, $code = self::CRITICAL_ERROR, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}




class EBSCOConnector
{
/**
     * Error codes defined by EDS API
     */
    const EDS_UNKNOWN_PARAMETER              = 100;
    const EDS_INCORRECT_PARAMETER_FORMAT     = 101;
    const EDS_INVALID_PARAMETER_INDEX        = 102;
    const EDS_MISSING_PARAMETER              = 103;
    const EDS_AUTH_TOKEN_INVALID             = 104;
    const EDS_INCORRECT_ARGUMENTS_NUMBER     = 105;
    const EDS_UNKNOWN_ERROR                  = 106;
    const EDS_AUTH_TOKEN_MISSING             = 107;
    const EDS_SESSION_TOKEN_MISSING          = 108;
    const EDS_SESSION_TOKEN_INVALID          = 109;
    const EDS_INVALID_RECORD_FORMAT          = 110;
    const EDS_UNKNOWN_ACTION                 = 111;
    const EDS_INVALID_ARGUMENT_VALUE         = 112;
    const EDS_CREATE_SESSION_ERROR           = 113;
    const EDS_REQUIRED_DATA_MISSING          = 114;
    const EDS_TRANSACTION_LOGGING_ERROR      = 115;
    const EDS_DUPLICATE_PARAMETER            = 116;
    const EDS_UNABLE_TO_AUTHENTICATE         = 117;
    const EDS_SEARCH_ERROR                   = 118;
    const EDS_INVALID_PAGE_SIZE              = 119;
    const EDS_SESSION_SAVE_ERROR             = 120;
    const EDS_SESSION_ENDING_ERROR           = 121;
    const EDS_CACHING_RESULTSET_ERROR        = 122;
    const EDS_INVALID_EXPANDER_ERROR         = 123;
    const EDS_INVALID_SEARCH_MODE_ERROR      = 124;
    const EDS_INVALID_LIMITER_ERROR          = 125;
    const EDS_INVALID_LIMITER_VALUE_ERROR    = 126;
    const EDS_UNSUPPORTED_PROFILE_ERROR      = 127;
    const EDS_PROFILE_NOT_SUPPORTED_ERROR    = 128;
    const EDS_INVALID_CONTENT_PROVIDER_ERROR = 129;
    const EDS_INVALID_SOURCE_TYPE_ERROR      = 130;
    const EDS_XSLT_ERROR                     = 131;
    const EDS_RECORD_NOT_FOUND_ERROR         = 132;
    const EDS_SIMULTANEOUS_USER_LIMIT_ERROR  = 133;
    const EDS_NO_GUEST_ACCESS_ERROR          = 134;
    const EDS_DBID_NOT_IN_PROFILE_ERROR      = 135;
    const EDS_INVALID_SEARCH_VIEW_ERROR      = 136;
    const EDS_RETRIEVING_FULL_TEXT_ERROR     = 137;


    /**
     * HTTP status codes constants
     * http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     *
     * @global integer HTTP_OK        The request has succeeded
     * @global integer HTTP_NOT_FOUND The server has not found anything matching the Request-URI
     */
    const HTTP_OK                    = 200;
    const HTTP_BAD_REQUEST           = 400;
    const HTTP_NOT_FOUND             = 404;
    const HTTP_INTERNAL_SERVER_ERROR = 500;


    /**
     * The HTTP_Request object used for API transactions
     * @global object HTTP_Request
     */
    private $client;


    /**
     * The URL of the EBSCO API server
     * @global string
     */
    private static $end_point = 'http://eds-api.ebscohost.com/EDSAPI/rest';

    /**
    * The URL of the EBSCO Publication API server
    * @global string
    */
    private static $publication_end_point = 'http://eds-api.ebscohost.com/EDSAPI/publication';


    /**
     * The URL of the EBSCO API server
     * @global string
     */
    private static $authentication_end_point = 'https://eds-api.ebscohost.com/AuthService/rest';


    /**
     * The password used for API transactions
     * @global string
     */
    private $password;


    /**
     * The user id used for API transactions
     * @global string
     */
    private $userId;


    /**
     * The profile ID used for API transactions
     * @global string
     */
    private $profileId;


    /**
     * The interface ID used for API transactions
     * @global string
     */
    private $interfaceId;


    /**
     * The customer ID used for API transactions
     * @global string
     */
    private $orgId;


    /**
     * The isGuest used for API transactions
     * @global string 'y' or 'n'
     */
    private $isGuest;


    /*
     * You can log HTTP_Request requests using this option
     * @global bool logAPIRequests
     */
    private $logAPIRequests;


    /**
     * The logger object
     * @global object Logger
     */
    private $logger;


    /**
     * Constructor
     *
     * Sets up the EBSCO API settings
     *
     * @param none
     *
     * @access public
     */
    public function __construct($config)
    {
        $this->password = $config['password'];
        $this->userId = $config['user'];
        $this->interfaceId = $config['interface'];
        $this->profileId = $config['profile'];
        $this->orgId = $config['organization'];
        $this->isGuest = user_is_logged_in() ? 'n' : 'y';
        $this->logAPIRequests = ($config['log'] == 1);
        if ($this->logAPIRequests) {
            $writer = new Zend_Log_Writer_Stream('php://output');
            $this->logger = new Zend_Log($writer);
        }
    }


    /**
     * Public getter for private isGuest
     *
     * @param none
     *
     * @return string isGuest
     * @access public
     */
    public function isGuest()
    {
        return $this->isGuest;
    }


    /**
     * Request the authentication token
     *
     * @param none
     *
     * @return object SimpleXml or PEAR_Error
     * @access public
     */
    public function requestAuthenticationToken()
    {
        $url = self::$authentication_end_point . '/UIDAuth';

        // Add the body of the request
        $params =<<<BODY
<UIDAuthRequestMessage xmlns="http://www.ebscohost.com/services/public/AuthService/Response/2012/06/01">
    <UserId>{$this->userId}</UserId>
    <Password>{$this->password}</Password>
    <InterfaceId>{$this->interfaceId}</InterfaceId>
</UIDAuthRequestMessage>
BODY;

        $response = $this->request($url, $params, array(), 'POST');
        return $response;
    }


    /**
     * Request the session token
     *
     * @param array $headers  Authentication token
     *
     * @return object SimpleXml or PEAR_Error
     * @access public
     */
    public function requestSessionToken($headers)
    {
        $url = self::$end_point . '/CreateSession';

        // Add the HTTP query params
        $params = array(
            'profile' => $this->profileId,
            'org'     => $this->orgId,
            'guest'   => $this->isGuest
        );

        $response = $this->request($url, $params, $headers);
        return $response;
    }


    /**
     * Request the search records
     *
     * @param array $params Search specific parameters
     * @param array $headers Authentication and session tokens
     *
     * @return object SimpleXml or PEAR_Error
     * @access public
     */
    public function requestSearch($params, $headers)
    {
        $url = self::$end_point . '/Search';

        $response = $this->request($url, $params, $headers);
        return $response;
    }

    /**
    * Request the search publications api
    *
    * @param array $params Search specific parameters
    * @param array $headers Authentication and session tokens
    *
    * @return object SimpleXml or PEAR_Error
    * @access public
    */
    public function requestPublication($params, $headers)
    {
      $url = self::$publication_end_point . '/Search';

      $response = $this->request($url, $params, $headers);
      return $response;
    }


    /**
     * Request a specific record
     *
     * @param array $params Retrieve specific parameters
     * @param array $headers Authentication and session tokens
     *
     * @return object SimpleXml or PEAR_Error
     * @access public
     */
    public function requestRetrieve($params, $headers)
    {
        $url = self::$end_point . '/Retrieve';

        $response = $this->request($url, $params, $headers);
        return $response;
    }


    /**
     * Request the info data
     *
     * @param null $params Not used
     * @param array $headers Authentication and session tokens
     *
     * @return object SimpleXml or PEAR_Error
     * @access public
     */
    public function requestInfo($params, $headers)
    {
        $url = self::$end_point . '/Info';

        $response = $this->request($url, $params, $headers);
        return $response;
    }


    /**
     * Send an HTTP request and inspect the response
     *
     * @param string $url         The url of the HTTP request
     * @param array  $params      The parameters of the HTTP request
     * @param array  $headers     The headers of the HTTP request
     * @param array  $body        The body of the HTTP request
     * @param string $method      The HTTP method, default is 'GET'
     *
     * @return object             SimpleXml or PEAR_Error
     * @access protected
     */
    protected function request($url, $params, $headers = array(), $method = 'GET')
    {
        $xml = false;
        $return = false;
        $data = null;

        if (!empty($params)) {
            // Arrays of parameters are used only for GET requests
            if (is_array($params)) {
                $query = http_build_query($params, '', '&');
                $query = preg_replace('/\%5B\d+\%5D/', '', $query);
                $url = $url . '?' . $query;
            // String parameters are used only for POST requests
            } else {
                $data = $params;
                $headers = array_merge(
                    array('content-type' => 'text/xml'),
                    $headers
                );
            }
        }

        $options = array(
            'headers' => $headers,
            'method'  => $method,
            'data'    => $data
        );

        // Send the request
        try {
            $response = drupal_http_request($url, $options);
//print_r($response);
            $code = $response->code;
            switch ($code) {
                case self::HTTP_OK:
                    $xml_str = $response->data;
                    try {
                        $xml = simplexml_load_string($xml_str);
                        $return = $xml;
                    } catch(Exception $e) {
                        $return = new EBSCOException($xml);
                    }
                    break;

                case self::HTTP_BAD_REQUEST:
                    $xml_str = $response->data;
                    try {
                        $xml = simplexml_load_string($xml_str);

                        // If the response is an API error
                        $isError = isset($xml->ErrorNumber) || isset($xml->ErrorCode);
                        if ($isError) {
                            $error = ''; $code = 0;
                            if (isset($xml->DetailedErrorDescription) && !empty($xml->DetailedErrorDescription)) {
                                $error = (string) $xml->DetailedErrorDescription;
                            } else if (isset($xml->ErrorDescription)) {
                                $error = (string) $xml->ErrorDescription;
                            } else if (isset($xml->Reason)) {
                                $error = (string) $xml->Reason;
                            }
                            if (isset($xml->ErrorNumber)) {
                                $code = (integer) $xml->ErrorNumber;
                            } else if (isset($xml->ErrorCode)) {
                                $code = (integer) $xml->ErrorCode;
                            }
                            $return = new EBSCOException($error, $code);
                        } else {
                            $return = new EBSCOException("HTTP {$code} : The request could not be understood
                                by the server due to malformed syntax. Modify your search before retrying.");
                        }
                    } catch (Exception $e) {
                        $return = new EBSCOException($xml);
                    }
                    break;

                case self::HTTP_NOT_FOUND:
                    $return = new EBSCOException("HTTP {$code} : The resource you are looking for might
                        have been removed, had its name changed, or is temporarily unavailable.");
                    break;

                case self::HTTP_INTERNAL_SERVER_ERROR:
                    $return = new EBSCOException("HTTP {$code} : The server encountered an unexpected condition
                        which prevented it from fulfilling the request.");
                    break;

                default:
                    $return = new EBSCOException("HTTP {$code} : Unexpected HTTP error.");
                    break;
            }
        } catch (Exception $e) {
             $return = new EBSCOException($response);
        }

        // Log any error
        /*if ($this->logAPIRequests) {
            // $client = both the HTTP request and response
            // $response = only the HTTP response
            $message = $this->toString($client); // or $this->toString($response)
            $this->logger->log($message, Zend_Log::ERR);
        }*/

        return $return;
    }


    /*
     * Capture the output of print_r into a string
     *
     * @param object Any object
     * @access private
     */
    private function toString($object)
    {
        ob_start();
        print_r($object);
        return ob_get_clean();
    }


}


?>
