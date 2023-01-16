<?php

$packageId = $_GET['id']; //At least 1
$currentVersion = $_GET['current']; //Current package ID

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
    header("HTTP/1.0 404 Not Found");
    die();
}

if ($currentVersion < $packageData['v']) {
    $downloadLink = $packageData['l'];
    header("Location: $downloadLink");
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