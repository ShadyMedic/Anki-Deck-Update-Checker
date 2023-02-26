<?php

$packageId = @$_GET['id']; //At least 1
$currentVersion = @$_GET['current']; //First version is 1
$accessCode = @$_GET['key']; //Access code (private packages only)

if (empty($packageId) || empty($currentVersion)) {
    header("HTTP/1.0 400 Bad Request");
    die();
}

require 'Db.php';
$db = Db::connect();

$query = (empty($accessCode)) ?
    'SELECT version AS "v",download_link AS "l" FROM package WHERE package_id = ? AND access_key IS NULL LIMIT 1;' :
    'SELECT version AS "v",download_link AS "l" FROM package WHERE package_id = ? AND access_key = ? LIMIT 1;';
$parameters = (empty($accessCode)) ?
    array($packageId) :
    array($packageId, $accessCode);

$statement = $db->prepare($query);
$statement->execute($parameters);
$packageData = $statement->fetch();

if (empty($packageData)) {
    readfile('img/not-found.png');
    die();
}

header ('Content-Type: image/png');

if ($currentVersion >= $packageData['v']) {
    readfile('img/up-to-date.png');
    die();
} else {
    readfile('img/outdated.png');
    $downloadLink = $packageData['l'];
    die();
}
