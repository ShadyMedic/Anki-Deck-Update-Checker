<?php

use Models\Package;
use Models\PackageManager;
use Models\UserException;

require 'autoloader.php';

$errors = array();

if (!empty($_POST)) {
    $deckName = trim($_POST['deck-name']);
    $author = trim($_POST['author']);
    $public = isset($_POST['public']);
    $key = trim($_POST['key']);

    $tools = new PackageManager();
    try {
        $tools->validateName($deckName);
        $tools->validateAuthor($author);
        $tools->validateEditKey($key);
    } catch (UserException $e) {
        $error = $e->getMessage();
    }

    if (!isset($error)) {
        if (!$public) {
            try {
                $accessKey = $tools->generateAccessKey();
            } catch (Exception $e) {
                $error = 'Something went wrong while creating an access key for your private deck.<br>'.
                    'Either make the deck public, or try again later please.';
                $accessKey = null;
            }
        } else {
            $accessKey = null;
        }

        $package = new Package();
        $package->create(array(
            'name' => $deckName,
            'author' => $author,
            'accessKey' => $accessKey,
            'editKey' => $key
        ));

        $packageId = $package->getId();

        $url = '/upload.php?id='.$packageId.(($public) ? '' : '&access-key='.$accessKey);
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

        input[maxlength="122"] {
            width: 122ch;
        }

        small {
            display: block;
        }
    </style>
</head>
<body>
<header>
    <h1>Upload new Anki package</h1>
    <h4>Use this <b>only</b> to create packages that you have never uploaded to this service before!</h4>
    <noscript>
        <h2>You need to enable JavaScript for this webpage to work. Sorry.</h2>
    </noscript>
</header>
<article>
    <form method="post" id="create-form">
        <fieldset>
            <label for="deck-name-input">Deck name:</label>
            <input type="text" name="deck-name" id="deck-name-input" maxlength="122"
                   placeholder="Medical School__Genetics__Genomic Imprinting" value="<?= $deckName ?? '' ?>" required/>
            <small>
                When your package is downloaded, it'll have this name.
                <strong>Do not include the <code>.apkg</code> extension in this field.</strong>
            </small>
        </fieldset>
        <fieldset>
            <label for="author-input">Author:</label>
            <input type="text" name="author" id="author-input" value="<?= $author ?? 'Anonymous' ?>" maxlength="31"
                   required/>
            <small>This name will be displayed next to the deck name. You can keep it as "Anonymous".</small>
        </fieldset>
        <fieldset>
            <label for="public-input">Keep package public?</label>
            <input type="checkbox" name="public" id="public-input"
                   <?php if (($public ?? true) !== false) : ?>checked<?php endif ?>/>
            <small>We recommend keeping all decks public – it won't hurt you and might help others.</small>
            <small>
                Private packages will have a generated access code assigned to them. This code will be contained within
                the link that you'll be able to share with your users. Only users with this link will be able to
                download this package and it will not be listed on the <a href="/browse.php">browse webpage</a>.
            </small>
        </fieldset>
        <fieldset>
            <label for="key-input">Editing key:</label>
            <input type="text" name="key" id="key-input" maxlength="31" value="<?= $key ?? '' ?>" required/>
            <button id="generate-key-button">Generate</button>
            <button id="load-key-button">Load last used</button>
            <small>
                Editing key is something like a password that is necessary to update this package in the future.
                Take care of the key and keep it private, as anyone with access to it can modify your package and
                push destructive updates to your users.
                Also note, that the key cannot be recovered if forgotten, unless you personally know the webmaster
                of this website.
                You are supposed to use the same key for all of your decks in order to make the work with the system
                easier, but you need to ensure it is strong enough.<br>
                <strong>Do not set the editing key to the same value as any of your passwords to other services!</strong>
                The editing key is not protected by encryption for technical reasons and while we do our best to keep
                the information private, we can never completely prevent data theft.
            </small>
        </fieldset>
        <fieldset>
            <button id="submit-button">Proceed</button>
            <small>
                After checking all the information entered above, click the button.<br>
                <strong>You won't be able to change any of the information above later.</strong>
            </small>
        </fieldset>
        <div style="color: red">
            <?= $error ?? '' ?>
        </div>
    </form>
</article>
</body>

<script>
    document.getElementById("generate-key-button").addEventListener('click', function (event) {
        event.preventDefault()
        let characters = [
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'
        ]
        let pass = ''
        for (let i = 0; i < 31; i++) {
            pass += characters[Math.floor(Math.random() * characters.length)]
        }

        document.getElementById("key-input").value = pass
    })

    document.getElementById("load-key-button").addEventListener('click', function (event) {
        event.preventDefault()

        document.getElementById("key-input").value = window.localStorage.getItem('key')
    })

    document.getElementById("submit-button").addEventListener('click', function (event) {
        event.preventDefault()

        let key = document.getElementById("key-input").value;
        window.localStorage.setItem('key', key)

        document.getElementById("create-form").submit()
    })
</script>

</html>
