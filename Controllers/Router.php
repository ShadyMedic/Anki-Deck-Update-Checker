<?php

namespace AnkiDeckUpdateChecker\Controllers;

use BadMethodCallException;
use UnexpectedValueException;

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
        $urlPath = trim($urlPath, '/'); //Remove the trailing slash
        $urlPath = '/'.$urlPath;

        self::$views[] = 'layout';

        $controllerName = $this->loadRoutes($urlPath);
        $nextController = new ('AnkiDeckUpdateChecker\\'.self::CONTROLLERS_DIRECTORY.'\\'.$controllerName)();
        return $nextController->process();
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

