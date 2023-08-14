<?php
namespace AnkiDeckUpdateChecker;

use AnkiDeckUpdateChecker\Controllers\Controller;
use AnkiDeckUpdateChecker\Controllers\Router;
use AnkiDeckUpdateChecker\Models\ErrorProcessor;
use Throwable;

//Renew session and set encoding
session_start();
mb_internal_encoding('UTF-8');

//Check for secure connection and redirect if necessary (DOESN'T WORK ON LOCALHOST)
/*if (!(isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] === "https")) {
        array('uri' => $_SERVER['REQUEST_URI'], 'ip' => $_SERVER['REMOTE_ADDR']));
    header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
    header('Connection: close');
    exit();
}*/

//Define and set the autoloader
function autoloader(string $name): void
{
    //Replace backslashes (used in namespace path) with forward slashes (used in directory path)
    $name = str_replace('\\', '/', $name);
    //Remove root directory (in which this file is located)
    if (str_contains($name, '/')) {
        $folders = explode('/', $name);
        unset($folders[0]);
        $name = implode('/', $folders);
    }
    $name .= '.php';
    require $name;
}
spl_autoload_register('AnkiDeckUpdateChecker\\autoloader');

//Define and set uncaught exceptions handler
function fatalExceptionHandler(Throwable $e) : void
{
    $errorCode = $e->getCode();
    $errorMsg = $e->getMessage();
    $errProc = new ErrorProcessor();
    $errorFound = $errProc->processError($errorCode, $errorMsg);

    if (!$errorFound) {
        //Unknown error – just display it hard TURN THIS OFF ON PRODUCTION
        throw $e;
    }

    $headerCode = $errProc->httpHeaderCode;
    $headerMessage = $errProc->httpHeaderMessage;
    $errorView = $errProc->errorWebpageView;
    extract($errProc->errorWebpageData);

    header("HTTP/1.0 $headerCode $headerMessage");
    require Controller::VIEWS_DIRECTORY.'/'.$errorView.'.phtml';
}
set_exception_handler('AnkiDeckUpdateChecker\\fatalExceptionHandler');

//Load the URL and process the request (get data for the views)
$rooter = new Router();
$rooter->process(array($_SERVER['REQUEST_URI']));

//Load the views
$rooter->generate();
