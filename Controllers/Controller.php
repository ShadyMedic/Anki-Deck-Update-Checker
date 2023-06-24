<?php

namespace AnkiDeckUpdateChecker\Controllers;

use AnkiDeckUpdateChecker\Models\Sanitizable;
use InvalidArgumentException;

/**
 * Abstract class that all controllers need to extend
 * @author Jan Štěch
 */
abstract class Controller
{
    private const VIEWS_DIRECTORY = 'Views';
    protected const CONTROLLERS_DIRECTORY = 'Controllers';

    /**
     * @var array $data
     * Array containing data to use to fill the blanks in the views.
     * This variable should be an associative array of arrays, where keys in the outer array are the names
     * of the views into which the data from the inner arrays should be inserted.
     * Variables that shouldn't be sanitized against XSS attacks should start with a lowercase letter
     * For example:
     * [
     *     "layout" => [
     *         "Username" => "Admin",
     *         "year" => "2023"
     *     ],
     *     "article" => [
     *         "Title" => "Header",
     *         "article" => "<p>This is the text.</p>"
     *     ]
     * ]
     */
    protected static array $data = [];

    /**
     * @var array $views
     * Array containing the list of views that should be used to generate the webpage for which
     * the controller is responsible.
     * The order of the views matters, they will be uses sequentially.
     */
    protected static array $views = [];

    /**
     * @var array $cssFiles
     * Array containing the list of CSS stylesheets that should be used to generate the webpage for which
     * the controller is responsible.
     * All CSS files specified here are included at the same place, in their respective order
     */
    protected static array $cssFiles = [];

    /**
     * Method responsible for getting the data from models and then generating the webpage.
     * @param array $args Array of arguments for the function, not all controller need to use this, default empty array
     * @return int HTTP response code to return to the client
     */
    public abstract function process(array $args = []): int;

    /**
     * Method unpacking the view data and loading all the views.
     * THIS METHOD IS WRITING OUTPUT INTO THE WEBPAGE.
     * Because of this, it should be called at the very end of the request processing protocol.
     * @return bool FALSE in case an error has occurred during the data-sanitization process.
     * @throws InvalidArgumentException If anti-XSS sanitization fails
     */
    public function generate(): bool
    {
        $this->unpackViewData();
        return true;
    }

    /**
     * Method sanitizing and then unpacking the data for views into individual variables.
     * When done, it loads the outermost view and all subsequent views too.
     * No other methods should be called after this method is called, because output into the webpage is written.
     * @return void
     * @throws InvalidArgumentException In case the sanitization of at least one variable fails.
     */
    private function unpackViewData(): void
    {
        $sanitizedValues = [];
        foreach (self::$data as $viewName => $viewData) {
            foreach ($viewData as $key => $value) {
                //Sanitize against XSS attack
                if (ord($key[0]) <= 90 && ord($key[0]) >= 65) { //Uppercase letters
                    $sanitizedValue = $this->antiXssSanitizazion($value);
                    $sanitizedValueName = $viewName.'_'.strtolower($key[0]).substr($key, 1); //Convert key name to camelCase
                }
                else {
                    $sanitizedValue = $value;
                    $sanitizedValueName = $viewName.'_'.$key; //Convert key name to camelCase
                }
                $sanitizedValues[$sanitizedValueName] = $sanitizedValue;
            }
        }

        //Unpack the array
        extract($sanitizedValues);

        //Start loading the views
        require self::VIEWS_DIRECTORY.'/'.array_shift(self::$views).'.phtml';
    }

    /**
     * Method sanitizing the provided argument against XSS attack
     * @param mixed $data Variable to sanitize
     * @return int|double|bool|string|Sanitizable The sanitized value
     * @throws InvalidArgumentException If the provided value couldn't be sanitized
     */
    private function antiXssSanitizazion(mixed $data): float|Sanitizable|bool|int|string
    {
        switch (gettype($data)) {
            case 'integer':
            case 'double':
            case 'boolean':
                return $data;
            case 'string':
                return htmlspecialchars($data, ENT_QUOTES);
            case 'object':
                if ($data instanceof Sanitizable) {
                    $data->sanitize();
                } else {
                    throw new InvalidArgumentException('Couldn\'t sanitize object of type '.get_class($data).' because it doesn\'t implement the "sanitize()" method.', 500002);
                }
                return $data;
            default:
                throw new InvalidArgumentException('Couldn\'t sanitize variable of type '.gettype($data).' against XSS attack.', 500001);
        }
    }
}

