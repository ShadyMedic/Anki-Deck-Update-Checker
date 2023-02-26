<?php

$packageId = @$_GET['id']; //At least 1
$currentVersion = @$_GET['current']; //Current package ID
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
    header("HTTP/1.0 404 Not Found");
    die();
}

if ($currentVersion < $packageData['v']) {
    $downloadLink = $packageData['l'];
    $url = $downloadLink.((empty($accessCode)) ? '' : '&key='.$accessCode);
    header("Location: $url");
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