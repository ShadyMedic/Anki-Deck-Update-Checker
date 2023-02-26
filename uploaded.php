<?php
$packageId = @$_GET['id']; //At least 1
$accessKey = @$_GET['key']; //Filled in only for protected decks

if (empty($packageId)) {
    header("HTTP/1.0 400 Bad Request");
    die();
}

if (empty($accessKey)) {
    $accessKey = null;
    $query = 'SELECT download_link,version FROM package WHERE package_id = ? AND access_key IS NULL LIMIT 1;';
    $arguments = array($packageId);
} else {
    $query = 'SELECT download_link,version FROM package WHERE package_id = ? AND access_key = ? LIMIT 1;';
    $arguments = array($packageId, $accessKey);
}

require 'Db.php';
$db = Db::connect();

$statement = $db->prepare($query);
$statement->execute($arguments);
$packageData = $statement->fetch();

if (empty($packageData)) {
    header("HTTP/1.0 404 Not Found");
    die();
}

$isHostedLocally = (strpos($packageData['download_link'], '/deck.php?') === 0); //Always TRUE for now
if ($isHostedLocally) {
    $downloadLink = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['SERVER_NAME'].$packageData['download_link'];
} else {
    $downloadLink = $packageData['download_link'];
}

$downloadLink = (empty($accessKey)) ? $downloadLink : $downloadLink.'&key='.$accessKey;
$version = $packageData['version'];

?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Anki Package Update Checker</title>
</head>
<body>
<header>
    <?php if ($version === 1) : ?>
        <h1>Successfully uploaded!</h1>
    <?php else : ?>
        <h1>Successfully updated!</h1>
    <?php endif ?>
</header>
<article>
    <?php if ($version === 1) : ?>
        <p>Your Anki package has successfully been uploaded to our servers.</p>
        <p>Now, it's time to share it with others! Use the following link to direct others to the download page:</p>
        <pre style="white-space: normal; padding: 4px; background-color: #ddd; color: #222;"><?= $downloadLink ?></pre>
        <?php if (!empty($accessKey)) : ?>
            <p>
                Your Anki package is set as private. This means that it won't be listed on the
                <a href="/browse.php">Deck List webpage</a> and only users using a link with the access code will be able
                to download the package.
            </p>
            <p>The access code is contained in the link above, so be careful with whom you share it.</p>
        <?php endif ?>
    <?php else : ?>
        <p>Your Anki package has successfully been updated to the new version.</p>
        <p>
            All users that use the previous (or any older) version of your deck will now see an update notification
            when they open it to study.
        </p>
        <p>
            After clicking the update notification, they'll be redirected to <a href="<?= $downloadLink ?>">this webpage</a>.
        </p>
    <?php endif ?>
</article>
</body>
</html>
