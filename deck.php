<?php
$packageId = @$_GET['id']; //At least 1
$accessKey = @$_GET['key']; //Filled in only for protected decks

if (empty($packageId)) {
    header("HTTP/1.0 400 Bad Request");
    die();
}

if (empty($accessKey)) {
    $accessKey = null;
    $query = 'SELECT filename,download_link,updated_at FROM package WHERE package_id = ? AND access_key IS NULL LIMIT 1;';
    $arguments = array($packageId);
} else {
    $query = 'SELECT filename,download_link,updated_at FROM package WHERE package_id = ? AND access_key = ? LIMIT 1;';
    $arguments = array($packageId, $accessKey);
}

require 'Db.php';
$db = Db::connect();

$statement = $db->prepare($query);
$statement->execute($arguments);
$packageData = $statement->fetch();

?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Anki Package Update Checker</title>
</head>
<body>
<header>
    <h1>Update Your Anki Deck</h1>
</header>
<article>
    <p>To download new version of this Anki deck, click the button below.</p>
    <p>After downloading, just import the new package to your Anki client. None of your cards or planning will be
        overwritten. Anki will simply check which of your cards were changed in this version and updates their
        content.</p>
    <p>
        File name: <code><?= $packageData['filename'] ?></code><br>
        File size: <code><?= number_format(filesize('decks/'.$packageId.'.apkg') / 1000000, 2) ?> MB</code><br>
        File updated at: <code><?= $packageData['updated_at'] ?></code>
    </p>
    <button style="display: block; background-color:limegreen; font-size: x-large;">
        <a style="color: inherit; text-decoration: none;"
           href="/download-local.php?id=<?= $packageId ?><?php if (!empty($accessKey)) : ?>&key=<?= $accessKey ?><?php endif ?>">
            Download the file
        </a>
    </button>
</article>
</body>
</html>