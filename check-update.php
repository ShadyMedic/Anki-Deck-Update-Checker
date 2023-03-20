<?php

use Models\Package;
use Models\PackageManager;

require 'autoloader.php';

$packageId = $_GET['id'] ?? null; //At least 1
$currentVersion = $_GET['current'] ?? null; //First version is 1
$accessCode = $_GET['key'] ?? null; //Access code (private packages only)

if (is_null($packageId) || is_null($currentVersion)) {
    header("HTTP/1.0 400 Bad Request");
    die();
}

$package = new Package();
$authenticator = new PackageManager();
$packageFound = $package->load($packageId) && $authenticator->checkReadAccess($packageId, $accessCode);

header ('Content-Type: image/svg+xml');

if (!$packageFound) {
    readfile('img/not-found.svg');
    die();
}

if ($currentVersion >= $package->getVersion()) {
    readfile('img/up-to-date.svg');
} else {
    readfile('img/outdated.svg');
}
