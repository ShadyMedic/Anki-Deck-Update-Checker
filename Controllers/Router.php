<?php

namespace AnkiDeckUpdateChecker\Controllers;

use BadMethodCallException;
use UnexpectedValueException;

/**
 * @see Controller
 */
class Router extends Controller
{

    /**
     * @inheritDoc
     * The first element in the array needs to be the requested URL
     * @return int HTTP response code
     * @throws BadMethodCallException If no URL was passed in the parameter array
     */
    public function process(array $args = []): int
    {
        if (empty($args) || empty($args[0])) {
            throw new BadMethodCallException("No URL to process was provided", 500003);
        }

        $url = array_shift($args);
        $urlPath = parse_url($url, PHP_URL_PATH);
        $urlPath = trim($urlPath, '/'); //Remove the trailing slash (if present)
        $urlPath = '/'.$urlPath;

        self::$views[] = 'layout';
        self::$cssFiles[] = 'layout';

        $variables = array();
        $pathTemplate = $this->separateUrlVariables($urlPath, $variables);
        $controllerAndArguments = $this->loadRoutes($pathTemplate);
        $argumentsArr = explode('?', $controllerAndArguments);
        $controllerName = array_shift($argumentsArr);
        $finalVariables = array();
        foreach ($argumentsArr as $argument) {
            if (preg_match('/\<\d*\>/', $argument)) {
                $finalVariables[] = $variables[trim($argument, '<>')]; //Variable argument
            } else {
                $finalVariables[] = $argument; //Literal argument
            }
        }
        $nextController = new ('AnkiDeckUpdateChecker\\'.self::CONTROLLERS_DIRECTORY.'\\'.$controllerName)();
        return $nextController->process($finalVariables);
    }

    /**
     * Method separating absolute URL path values from variables and returning the URL path with variables replaced
     * with placeholders
     * @param string $urlPath URL path requested by the client
     * @param array $variables An empty array passed by reference, which is filled by the variable values during this
     * method's execution
     * @return string The URL path with variables replaced by <n> placeholders (where n is a whole number starting at 0)
     */
    private function separateUrlVariables(string $urlPath, array &$variables) : string
    {
        $urlPath = trim($urlPath, '/');

        if (empty($urlPath)) {
            $urlArguments = [];
        } else {
            $urlArguments = explode('/', $urlPath);
        }

        $variableslessUrl = '';
        foreach ($urlArguments as $urlArgument) {
            if (!is_numeric($urlArgument)) { //TODO: add support for non-numeric variables
                $variableslessUrl .= $urlArgument.'/';
            } else {
                $variableslessUrl .= '<'.count($variables).'>/';
                $variables[(string)count($variables)] = $urlArgument;
            }
        }

        return '/'.trim($variableslessUrl, '/'); //Remove the trailing slash
    }

    /**
     * Method loading the routes.ini file and searching for the correct controller to use, depending on the parameter
     * @param string $path The URL path of the request (used to search for the controller to use)
     * @return string Name of the controller to call (not the full class name)
     */
    private function loadRoutes(string $path): string
    {
        $routes = parse_ini_file('routes.ini', true);
        if (!isset($routes["Routes"][$path])) {
            throw new UnexpectedValueException("The given URL wasn't found in the configuration.", 404000);
        }
        return $routes["Routes"][$path];
    }
}

