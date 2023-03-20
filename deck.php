<?php

use Models\Package;
use Models\PackageManager;

require 'autoloader.php';

$packageId = $_GET['id'] ?? null; //At least 1
$accessKey = $_GET['key'] ?? null; //Filled in only for protected decks

if (is_null($packageId)) {
    header("HTTP/1.0 400 Bad Request");
    die();
}

$authenticator = new PackageManager();
$authenticator->checkReadAccess($packageId, $accessKey);

$package = new Package();
$packageFound = $package->load($packageId);

if (!$packageFound) {
    header("HTTP/1.0 404 Not Found");
    die();
}

if ($package->getVersion() === 0) {
    header("HTTP/1.0 406 Not Acceptable");
    die();
}

$queryString = "?id=$packageId&amp;current=<span style=\"color: gold;\">".$package->getVersion()."</span>".(empty($accessKey) ? '' : "&amp;key=$accessKey");

?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Anki Package Update Checker</title>
</head>
<body>
<header>
    <h1>Update <?= mb_substr($package->getName(), 0, mb_strlen($package->getName()) - 5) ?></h1>
</header>
<article>
    <p>To download new version of this Anki deck, click the button below.</p>
    <p>After downloading, just import the new package to your Anki client. None of your cards or planning will be
        overwritten. Anki will simply check which of your cards were changed in this version and updates their
        content.</p>
    <p>
        File name: <code><?= $package->getName() ?></code><br>
        File size: <code><?= number_format(filesize('decks/'.$packageId.'.apkg') / 1000000, 2) ?> MB</code><br>
        Version number: <code><?= $package->getVersion() ?></code><br>
        File updated at: <code><?= $package->getUpdatedDate()->format('Y-m-d H:i:s'); ?></code>
    </p>
    <button style="display: block; background-color:limegreen; font-size: x-large;">
        <a style="color: inherit; text-decoration: none;"
           href="/download-local.php?id=<?= $packageId ?><?php if (!empty($accessKey)) : ?>&key=<?= $accessKey ?><?php endif ?>">
            Download the file
        </a>
    </button>
    <h2>If you're updating an existing deck and not importing this one for the first time:</h2>
    <p>After importing the new file, replace the description of the deck with the following code to reset the update status for the new version.</p>
    <pre style="white-space: normal; padding: 4px; background-color: black; color: white;"><code>&lt;div style="margin: 30px auto; left: 0; right: 0; border: 3px solid black; border-radius: 5px; text-align: center; width: fit-content; padding: 15px;"&gt;&lt;h2&gt;Update check&lt;/h2&gt;&lt;h6&gt;This might not work on other than desktop versions of Anki.&lt;/h6&gt;&lt;a href="http://anki-update-check.4fan.cz/update.php<?= $queryString ?>"&gt;&lt;img src="http://anki-update-check.4fan.cz/check-update.php<?= $queryString?>" alt="Update check failed. Check your internet connection or try again later." /&gt;&lt;/a&gt;&lt;/div&gt;</code></pre>
    <p>You need to do this, because updating your current deck will not overwrite the deck description, which holds the current version of your deck (the yellow numbers in the code above). Without updating the description, your Anki will still think you're using the outdated version.</p>
</article>
</body>
</html>
