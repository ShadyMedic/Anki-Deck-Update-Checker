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
        Version number: <code><?= $packageData['version'] ?></code><br>
        File updated at: <code><?= $packageData['updated_at'] ?></code>
    </p>
    <button style="display: block; background-color:limegreen; font-size: x-large;">
        <a style="color: inherit; text-decoration: none;"
           href="/download-local.php?id=<?= $packageId ?><?php if (!empty($accessKey)) : ?>&key=<?= $accessKey ?><?php endif ?>">
            Download the file
        </a>
    </button>
    <p>After importing the new file, replace the description of the deck with the following code to reset the update status for the new version.</p>
    <pre style="white-space: normal; padding: 4px; background-color: black; color: white;"><code>&lt;div style="margin: 30px auto; left: 0; right: 0; border: 3px solid black; border-radius: 5px; text-align: center; width: fit-content; padding: 15px;"&gt;&lt;h2&gt;Update check&lt;/h2&gt;&lt;h6&gt;This might not work on other than desktop versions of Anki.&lt;/h6&gt;&lt;a href="http://anki-update-check.4fan.cz/update.php?id=<?= $packageId ?>&amp;current=<span style="color: gold;"><?= $packageData['version'] ?></span>"&gt;&lt;img src="http://anki-update-check.4fan.cz/check-update.php?id=<?= $packageId ?>&amp;current=<span style="color: gold;"><?= $packageData['version'] ?></span>" alt="Kontrola selhala, zkontroluj připojení k internetu, nebo to zkus později znovu." /&gt;&lt;/a&gt;&lt;/div&gt;</code></pre>
    <p>You need to do this, because updating your current deck will not overwrite the deck description, which holds the current version of your deck (the yellow numbers in the code above). Without updating, your Anki will still thing you're using the outdated version.</p>
</article>
</body>
</html>
