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
    public function processError(int $errorCode, ?string $errorMessage = null): bool
    {
        switch (floor($errorCode / 1000)) {
            case 400:
                //Bad request
                $this->httpHeaderCode = 400;
                $this->httpHeaderMessage = 'Bad Request';
                switch ($errorCode) {
                    case 400000:
                        $this->errorWebpageView = "errors/error400.html";
                        return true;
                    case 400001:
                        //No ID was specified for a deck info page
                        $this->errorWebpageData['errorMessage'] = $errorMessage;
                        return true;
                    case 400002:
                        //No ID was specified for a deck download request
                        $this->errorWebpageData['errorMessage'] = $errorMessage;
                        return true;
                    case 400003:
                        //No ID was specified for the upload page
                        $this->errorWebpageData['errorMessage'] = $errorMessage;
                        return true;
                }
                break;
            case 401:
                //Unauthorized
                $this->httpHeaderCode = 401;
                $this->httpHeaderMessage = 'Unauthorized';
                switch ($errorCode) {
                    case 401000:
                        $this->errorWebpageView = "errors/error401.html";
                        return true;
                    case 401001:
                        //Editing key for the given deck isn't valid on the upload page
                        $this->errorWebpageData['errorMessage'] = $errorMessage;
                        return true;
                    case 401002:
                        //Access key for the given private deck isn't correct or is missing
                        $this->errorWebpageData['errorMessage'] = $errorMessage;
                        return true;
                }
                break;
            case 404:
                //Not found
                $this->httpHeaderCode = 404;
                $this->httpHeaderMessage = 'Not Found';
                switch ($errorCode) {
                    case 404000:
                        $this->errorWebpageView = "errors/error404.html";
                        return true;
                    case 404001:
                        //Package with a given ID was not found when generating a deck info page
                        $this->errorWebpageData['errorMessage'] = $errorMessage;
                        return true;
                    case 404002:
                        //Package with a given ID was not found when triggering download of the package file
                        $this->errorWebpageData['errorMessage'] = $errorMessage;
                        return true;
                    case 404003:
                        //Package file with a given ID in its filename was not found in the file system when download was requested
                        $this->errorWebpageData['errorMessage'] = $errorMessage;
                        return true;
                }
                break;
            case 406:
                //Not acceptable
                $this->httpHeaderCode = 406;
                $this->httpHeaderMessage = 'Not Acceptable';
                switch ($errorCode) {
                    case 406000:
                        $this->errorWebpageView = "errors/error406.html";
                        return true;
                    case 406001:
                        //Package with the given ID has version equal to 0 and its info page can't be generated
                        $this->errorWebpageData['errorMessage'] = $errorMessage;
                        return true;
                    case 406002:
                        //Package with the given ID has version equal to 0 and the request to download it cannot be fulfilled
                        $this->errorWebpageData['errorMessage'] = $errorMessage;
                        return true;
                }
            case 500:
                //Internal server error
                $this->httpHeaderCode = 500;
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
                $this->httpHeaderCode = 501;
                $this->httpHeaderMessage = 'Not Implemented';
                switch ($errorCode) {
                    //No errors yet
                    case 501000:
                        break;
                }
                break;
            case 503:
                //Service unavailable
                $this->httpHeaderCode = 503;
                $this->httpHeaderMessage = 'Service Unavailable';
                switch ($errorCode) {
                    //No errors yet
                    case 503000:
                        break;
                }
                break;
            case 507:
                //Insufficient storage
                $this->httpHeaderCode = 507;
                $this->httpHeaderMessage = 'Insufficient Storage';
                switch ($errorCode) {
                    //No errors yet
                    case 507000:
                        break;
                }
                break;
            case 508:
                //Loop detected
                $this->httpHeaderCode = 508;
                $this->httpHeaderMessage = 'Loop Detected';
                switch ($errorCode) {
                    //No errors yet
                    case 508000:
                        break;
                }
                break;
            case 509:
                //Bandwidth limit exceeded
                $this->httpHeaderCode = 509;
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

