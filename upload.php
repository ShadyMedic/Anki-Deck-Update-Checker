<?php
$errors = array();
$packageId = @$_REQUEST['id'];
$key = @$_REQUEST['key'];
$accessKey = @$_REQUEST['access-key'];

if (empty($packageId)) {
    header("HTTP/1.0 400 Bad Request");
    die();
}

require 'Db.php';
$db = Db::connect();
$statement = $db->prepare('SELECT version FROM package WHERE package_id = ? LIMIT 1');
$statement->execute(array($packageId));
$nextVersion = $statement->fetch()['version'] + 1;
$queryString = "?id=$packageId&amp;current=$nextVersion".((empty($accessKey)) ? '' : '&amp;key='.$accessKey);

if (!empty($_POST)) {

    //Do authentication
    $statement = $db->prepare('SELECT COUNT(*) AS "cnt" FROM package WHERE package_id = ? AND edit_key = ? LIMIT 1');
    $statement->execute(array($packageId, $key));
    if ($statement->fetch()['cnt'] !== 1) {
        header("HTTP/1.0 401 Unauthorized");
        die();
    }

    $fileSize = $_FILES['package']['size'];
    $tmpFileName = $_FILES['package']['tmp_name'];
    $uploadError = $_FILES['package']['error'];

    if ($uploadError === UPLOAD_ERR_INI_SIZE || $uploadError === UPLOAD_ERR_FORM_SIZE || $fileSize > 8388608) {
        $errors[] = "Your package file is too large – maximum allowed size is 8 MB for now.";
        /*
        header("HTTP/1.0 413 Payload Too Large");
        die();
        */
    } else if ($uploadError === UPLOAD_ERR_NO_FILE) {
        $errors[] = "No file was selected.";
    } else if (!empty($uploadError)) {
        $errors[] = "An error occurred while uploading the file. Please try again later.";
    }

    if (empty($errors)) {

        move_uploaded_file($_FILES['package']['tmp_name'], 'decks/'.$packageId.'.apkg');

        $statement = $db->prepare('UPDATE package SET download_link = ?, version = version + 1 WHERE package_id = ?');
        $statement->execute(array('/deck.php?id='.$packageId, $packageId));

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

        input[type=text] {
            font-family: monospace;
        }

        input[maxlength="31"] {
            width: 31ch;
        }

        input[maxlength="58"] {
            width: 58ch;
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
    <ol>
        <li>
            Select your deck inside your desktop Anki client.<br>
            <img src="img/tutorial-1.jpg" alt="Tutorial screenshot n. 1" />
        </li>
        <li>
            Click the "Description" button.<br>
            <img src="img/tutorial-2.jpg" alt="Tutorial screenshot n. 2" />
        </li>
        <li>
            Paste the following text into the text field and make sure the checkbox is unchecked.<br>
            <pre style="white-space: normal; margin:0; padding: 4px; background-color: black; color: white;"><code>&lt;div style="margin: 30px auto; left: 0; right: 0; border: 3px solid black; border-radius: 5px; text-align: center; width: fit-content; padding: 15px;"&gt;&lt;h2&gt;Update check&lt;/h2&gt;&lt;h6&gt;This might not work on other than desktop versions of Anki.&lt;/h6&gt;&lt;a href="http://anki-update-check.4fan.cz/update.php<?= $queryString ?>"&gt;&lt;img src="http://anki-update-check.4fan.cz/check-update.php<?= $queryString ?>" alt="Update check failed. Check your internet connection or try again later." /&gt;&lt;/a&gt;&lt;/div&gt;</code></pre>
        </li>
        <li>
            Press OK and exit the dialog box.<br>
            <img src="img/tutorial-3.jpg" alt="Tutorial screenshot n. 3" />
        </li>
        <li>
            You should now see something like this. Export the package as usual and upload it in the form below.<br>
            <img src="img/tutorial-4.jpg" alt="Tutorial screenshot n. 4" />
        </li>
    </ol>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="package-id" value="<?= $packageId ?>"/>
        <input type="hidden" name="key" id="key-input" value="<?= $key ?>"/>
        <input type="hidden" name="access-key" id="access-key-input" value="<?= $accessKey ?>"/>
        <fieldset>
            <label for="file-input">Package file:</label>
            <input type="hidden" name="MAX_FILE_SIZE" value="8388608"/>
            <input type="file" accept=".apkg" name="package" id="file-input" required/>
            <small>
                The file size mustn't exceed 8 MB. If your Anki package is smaller, and you compressed your media,
                contact me on Discord (<code>Shady#2948</code>) or e-mail me at
                <code>jan [dot] stech [at] posteo [dot] net</code>.
            </small>
        </fieldset>
        <fieldset>
            <input type="submit" value="Upload"/>
            <small>
                By clicking the button, you confirm that the uploaded Anki package doesn't contain copyrighted material.
            </small>
        </fieldset>
        <ul style="color: red">
            <?php foreach ($errors as $error) : ?>
                <li><?= $error ?></li>
            <?php endforeach ?>
        </ul>
    </form>
</article>
</body>

<script>
    document.getElementById("key-input").value = window.localStorage.getItem('key')
</script>

</html>
