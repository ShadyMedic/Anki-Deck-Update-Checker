<?php

use Models\Package;
use Models\PackageManager;
use Models\UserException;

require 'autoloader.php';

$packageId = $_REQUEST['id'] ?? null;
$key = $_POST['key'] ?? null; //This will be filled in only when submitting the upload form
$accessKey = $_REQUEST['access-key'] ?? null;

if (is_null($packageId)) {
    header("HTTP/1.0 400 Bad Request");
    die();
}

$package = new Package();
$package->load($packageId);
$nextVersion = $package->getVersion() + 1;
$queryString = "?id=$packageId&amp;current=$nextVersion".((empty($accessKey)) ? '' : '&amp;key='.$accessKey);

if (!empty($_POST)) {
    $authenticator = new PackageManager();

    //Do authentication
    if (!$authenticator->checkWriteAccess($packageId, $key)) {
        header("HTTP/1.0 401 Unauthorized");
        die();
    }

    try {
        $authenticator->checkFileUpload($_FILES['package']);
    } catch (UserException $e) {
        $error = $e->getMessage();
    }

    if (!isset($error)) {
        move_uploaded_file($_FILES['package']['tmp_name'], 'decks/'.$packageId.'.apkg');

        $authenticator->update($packageId);

        $url = '/uploaded.php?id='.$packageId.((empty($accessKey)) ? '' : '&key='.$accessKey);
        header('Location: '.$url);
        exit();
    }
}

?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Anki Package Update Checker</title>
    <style>
        fieldset {
            border: 1px solid black;
        }

        small {
            display: block;
        }
    </style>
</head>
<body>
<header>
    <h1>Edit your Anki package</h1>
    <noscript>
        <h2>You need to enable JavaScript for this webpage to work. Sorry.</h2>
    </noscript>
</header>
<article>
    <p>You now need to insert (or update) autoupdater code into the description of your Anki deck.</p>
    <p>This will allow users of your deck to receive update check and be notified when you upload a new version.</p>
    <p>Don't worry, it's not complicated, just follow the instruction below:</p>
    <fieldset id="instructions-spoiler" style="margin-bottom: 1em;">
        <p>
            <button id="display-instructions" style="display: none;">Display instructions</button>
            <button id="hide-instructions">Hide instructions</button>
        </p>
        <ol id="instructions">
            <li>
                Select your deck inside your desktop Anki client.<br>
                <img src="img/tutorial-1.jpg" alt="Tutorial screenshot n. 1"/>
            </li>
            <li>
                Click the "Description" button.<br>
                <img src="img/tutorial-2.jpg" alt="Tutorial screenshot n. 2"/>
            </li>
            <li>
                Paste the following text into the text field and make sure the checkbox is unchecked.<br>
                <strong>You need to do this everytime you upload a new version, because the code changes slightly
                    between versions!</strong><br>
                <pre style="white-space: normal; margin:0; padding: 4px; background-color: black; color: white;"><code>&lt;div style="margin: 30px auto; left: 0; right: 0; border: 3px solid black; border-radius: 5px; text-align: center; width: fit-content; padding: 15px;"&gt;&lt;h2&gt;Update check&lt;/h2&gt;&lt;h6&gt;This might not work on other than desktop versions of Anki.&lt;/h6&gt;&lt;a href="http://anki-update-check.4fan.cz/update.php<?= $queryString ?>"&gt;&lt;img src="http://anki-update-check.4fan.cz/check-update.php<?= $queryString ?>" alt="Update check failed. Check your internet connection or try again later." /&gt;&lt;/a&gt;&lt;/div&gt;</code></pre>
            </li>
            <li>
                Press OK and exit the dialog box.<br>
                <img src="img/tutorial-3.jpg" alt="Tutorial screenshot n. 3"/>
            </li>
            <li>
                You should now see something like this (the text will be green, if you're updating an existing package,
                or
                red, if you're uploading the first version of a new package).
                Export the package as usual and upload it in the form below.<br>
                <img src="img/tutorial-4.jpg" alt="Tutorial screenshot n. 4"/>
            </li>
        </ol>
    </fieldset>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="package-id" value="<?= $packageId ?>"/>
        <input type="hidden" name="key" id="key-input" value=""/> <!-- Value will be filled in by JavaScript -->
        <input type="hidden" name="access-key" id="access-key-input" value="<?= $accessKey ?>"/>
        <fieldset>
            <label for="file-input">Package file:</label>
            <input type="hidden" name="MAX_FILE_SIZE" value="8388608"/>
            <input type="file" accept=".apkg" name="package" id="file-input" required/>
            <small>
                <strong>The file size mustn't exceed 8 MB.</strong><br>
                If your Anki package is larger, and you compressed your media,
                contact me on Discord (<code>Shady#2948</code>) or e-mail me at
                <code>jan [dot] stech [at] posteo [dot] net</code>. You'll still be able to use this service to share
                your package, you'll just need to upload the file elsewhere and your users will be redirected to such
                external webpage from here, when they click the update notification.
            </small>
        </fieldset>
        <fieldset>
            <strong>Warning:</strong> Uploading a new version will permanently delete the previous version from our
            servers.<br>
            <p><input type="submit" value="Upload"/></p>
            <small>
                By clicking the "Upload" button, you confirm that the uploaded Anki package doesn't contain copyrighted
                material.
            </small>
        </fieldset>
        <div style="color: red">
            <?= $error ?? '' ?>
        </div>
    </form>
</article>
</body>

<script>
    document.getElementById("key-input").value = window.localStorage.getItem('key')

    document.getElementById("display-instructions").addEventListener('click', function () {
        document.getElementById("display-instructions").style.display = "none"
        document.getElementById("hide-instructions").style.display = "inline-block"
        document.getElementById("instructions").style.display = "block"
    })

    document.getElementById("hide-instructions").addEventListener('click', function () {
        document.getElementById("hide-instructions").style.display = "none"
        document.getElementById("display-instructions").style.display = "inline-block"
        document.getElementById("instructions").style.display = "none"
    })
</script>

</html>
