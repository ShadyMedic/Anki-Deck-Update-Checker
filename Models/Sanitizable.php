<?php

namespace AnkiDeckUpdateChecker\Models;

/**
 * Interface that all automatically-santizable classes need to implement.
 * @author Jan Štěch
 */
interface Sanitizable
{
    /**
     * Method responsible for sanitizing all applicable attributes of the current instance.
     * The after this method is called, the state of the instance should no longer be changed and no other
     * methods should be called.
     * @return void
     */
    public function sanitize(): void;
}

