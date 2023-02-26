<?php

$accessKey = null;
$query = 'SELECT package_id,filename,author,version,updated_at FROM package WHERE access_key IS NULL AND version > 0 AND download_link IS NOT NULL ORDER BY updated_at DESC;';

require 'Db.php';
$db = Db::connect();

$statement = $db->prepare($query);
$statement->execute(array());
$packages = $statement->fetchAll();

?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Anki Package Update Checker</title>
    <style>
        th, td {
            text-align: center;
            padding: 0 16px;
        }

        .txt-l {
            text-align: left;
        }
    </style>
</head>
<body>
<header>
    <h1>Public Anki Decks</h1>
</header>
<article>
    <p>This is the list of all public Anki decks uploaded to this webserver.</p>
    <p>All decks uploaded are considered public, if they don't have an access key specified.</p>
    <p>To view more information about any file or to download it, simply click it's name.</p>

    <table>
        <tr>
            <th class="txt-l">Deck name</th>
            <th class="txt-l">Author</th>
            <th>Version number</th>
            <th>Last updated at</th>
        </tr>
        <?php foreach ($packages as $package) : ?>
            <tr>
                <td class="txt-l">
                    <a href="/deck.php?id=<?= $package['package_id'] ?>">
                        <?= $package['filename'] ?>
                    </a>
                </td>
                <td class="txt-l"><?= $package['author'] ?></td>
                <td><?= $package['version'] ?></td>
                <td><?= $package['updated_at'] ?></td>
            </tr>
        <?php endforeach ?>
    </table>
</article>
</body>
</html>