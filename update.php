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

if (!$packageFound) {
    header("HTTP/1.0 404 Not Found");
    die();
}

if ($currentVersion < $package->getVersion()) {
    header('Location: '.$package->getDownloadLink());
    die();
}

?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Anki Package Update Checker</title>
</head>
<body>
<header>
    <h1>Your Anki Deck Is up-to-date</h1>
</header>
<article>
    <p>You got to this webpage, because you clicked the update status of a deck that is currently up-to-date.</p>
    <p>Once an update is available, you'll be redirected to its download page from here.</p>
    <p>There's nothing more for you now. Close this webpage and return to Anki.</p>
</article>
</body>
</html>