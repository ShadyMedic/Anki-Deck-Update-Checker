<?php

namespace AnkiDeckUpdateChecker\Models;

/**
 * Class responsible for handling critical errors that end up in an error page being displayed
 * @author Jan Štěch
 */
class ErrorProcessor
{
    /**
     * @var int $httpHeaderCode HTTP response code to send back in a header (for example 404 for "Not found" error)
     */
    public int $httpHeaderCode = 0;

    /**
     * @var string $httpHeaderMessage HTTP response message to send back in a header (for example "Not Found" for 404 error)
     */
    public string $httpHeaderMessage = '';

    /**
     * @var string $errorWebpageView View to display as an error webpage (defaults to an empty view containing nothing
     * but the error message specified in the $errorWebpageData attribute)
     */
    public string $errorWebpageView = 'errors/empty.phtml';

    /**
     * @var array $errorWebpageData Data to fill in into the error views.
     */
    public array $errorWebpageData = [];

    /**
     * Function called by an exception handler, responsible for generating an error webpage out of the error code and message
     * @param int $errorCode Error code (system specific for this system; a 6-digit number, first 3 contain HTTP response
     * code, the other 3 numbers are like IDs for the specific error; if the last 3 numbers are 000, a generic error of
     * the given type has occurred)
     * @param string|null $errorMessage Specific error message to display, optional and might be ignored
     * @return bool TRUE, if the error webpage was successfully composed, FALSE otherwise (in case of an unknown error)
     */
    public function processError(int $errorCode, ?string $errorMessage = null) : bool
    {
        switch ($errorCode / 1000) {
            case 404:
                //Not found
                $this->httpHeaderCode = $errorCode;
                $this->httpHeaderMessage = 'Not Found';
                switch ($errorCode) {
                    case 404000:
                        $this->errorWebpageView = "errors/error404.html";
                        return true;
                }
                break;
            case 500:
                //Internal server error
                $this->httpHeaderCode = $errorCode;
                $this->httpHeaderMessage = 'Internal Server Error';
                switch ($errorCode) {
                    case 500000:
                        $this->errorWebpageView = "errors/error500.html";
                        return true;
                    case 500001:
                        //Couldn't sanitize a variable of the given type
                        $this->errorWebpageData['errorMessage'] = $errorMessage;
                        return true;
                    case 500002:
                        //No URL to process was provided to the router
                        $this->errorWebpageData['errorMessage'] = $errorMessage;
                        return true;
                }
                break;
            case 501:
                //Not implemented
                $this->httpHeaderCode = $errorCode;
                $this->httpHeaderMessage = 'Not Implemented';
                switch ($errorCode) {
                    //No errors yet
                    case 501000:
                        break;
                }
                break;
            case 503:
                //Service unavailable
                $this->httpHeaderCode = $errorCode;
                $this->httpHeaderMessage = 'Service Unavailable';
                switch ($errorCode) {
                    //No errors yet
                    case 503000:
                        break;
                }
                break;
            case 507:
                //Insufficient storage
                $this->httpHeaderCode = $errorCode;
                $this->httpHeaderMessage = 'Insufficient Storage';
                switch ($errorCode) {
                    //No errors yet
                    case 507000:
                        break;
                }
                break;
            case 508:
                //Loop detected
                $this->httpHeaderCode = $errorCode;
                $this->httpHeaderMessage = 'Loop Detected';
                switch ($errorCode) {
                    //No errors yet
                    case 508000:
                        break;
                }
                break;
            case 509:
                //Bandwidth limit exceeded
                $this->httpHeaderCode = $errorCode;
                $this->httpHeaderMessage = 'Bandwidth Limit Exceeded';
                switch ($errorCode) {
                    //No errors yet
                    case 509000:
                        break;
                }
                break;
        }

        return false;
    }
}

