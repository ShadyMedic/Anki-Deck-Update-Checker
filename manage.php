<?php

use AnkiDeckUpdateChecker\Models\PackageManager;

require 'autoloader.php';

$key = $_POST['key'] ?? null;
$manager = new PackageManager();
if (!is_null($key)) {
    $packages = $manager->getOwnedPackages($key);
}

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
        input[type=password] {
            font-family: monospace;
            width: 31ch;
        }
    </style>
</head>
<body>
<header>
    <h1>Manage your Anki Decks</h1>
</header>
<article>
    <section>
        <form id="manage-form" method="post">
            <label for="key-input">Enter your editing key:</label>
            <input type="password" name="key" id="key-input" maxlength="31" required/>
            <button id="load-key-button">Load last used</button><br>
            <input id="submit-button" type="submit"/>
        </form>
    </section>
    <hr />
    <section>
        <p>This is a list of Anki decks registered in our database, that were created with the editing key you provided.</p>
        <p>Click any of the decks below to upload a new version.</p>

        <?php if (empty($packages)) : ?>
            <p><strong>No packages created with the editing key you provided found.</strong></p>
        <?php else : ?>
            <table>
                <tr>
                    <th class="txt-l">Deck name</th>
                    <th>Version number</th>
                    <th>Last updated at</th>
                </tr>
                <?php foreach ($packages as $package) : ?>
                    <tr>
                        <td class="txt-l">
                            <a href="/upload.php?id=<?= $package['package_id'].(empty($package['access_key']) ? '' : '&access-key='.$package['access_key']) ?>">
                                <?= $package['filename'] ?>
                            </a>
                        </td>
                        <td><?= $package['version'] ?></td>
                        <td><?= $package['updated_at'] ?></td>
                    </tr>
                <?php endforeach ?>
            </table>
        <?php endif ?>
    </section>
</article>
</body>

<script>
    document.getElementById("load-key-button").addEventListener('click', function (event) {
        event.preventDefault()

        document.getElementById("key-input").value = window.localStorage.getItem('key')
    })

    function formSubmitted(event) {
        event.preventDefault()

        let key = document.getElementById("key-input").value
        window.localStorage.setItem('key', key)
        console.log("saving " + key)

        document.getElementById("manage-form").submit()
    }

    document.getElementById("manage-form").addEventListener('submit', formSubmitted)

    window.onkeydown = function(event){
        if(event.keyCode === 13) {
            event.preventDefault()
            formSubmitted(event)
        }
    }
</script>

</html>