<?php

$packageId = @$_GET['id']; //At least 1
$currentVersion = @$_GET['current']; //First version is 1

if (empty($packageId) || empty($currentVersion)) {
    header("HTTP/1.0 400 Bad Request");
    die();
}

require 'Db.php';
$db = Db::connect();

$statement = $db->prepare('SELECT version AS "v",download_link AS "l" FROM package WHERE package_id = ? LIMIT 1;');
$statement->execute(array($packageId));
$packageData = $statement->fetch();

if (empty($packageData)) {
    readfile('not-found.png');
    die();
}

header ('Content-Type: image/png');

if ($currentVersion >= $packageData['v']) {
    readfile('up-to-date.png');
    die();
} else {
    readfile('outdated.png');
    $downloadLink = $packageData['l'];
    //TODO display the link somewhere
    die();
}
