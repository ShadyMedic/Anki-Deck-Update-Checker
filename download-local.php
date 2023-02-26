<?php
$packageId = @$_GET['id']; //At least 1
$accessKey = @$_GET['key']; //Filled in only for protected decks

if (empty($packageId)) {
    header("HTTP/1.0 400 Bad Request");
    die();
}

if (empty($accessKey)) {
    $accessKey = null;
    $query = 'SELECT filename, version FROM package WHERE package_id = ? AND access_key IS NULL LIMIT 1;';
    $arguments = array($packageId);
} else {
    $query = 'SELECT filename, version FROM package WHERE package_id = ? AND access_key = ? LIMIT 1;';
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

if ($packageData['version'] === 0) {
    header("HTTP/1.0 406 Not Acceptable");
    die();
}

if (!file_exists('decks/'.$packageId.'.apkg') || empty($packageData)) {
    header("HTTP/1.0 404 Not Found");
    die();
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $packageData['filename'] . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize('decks/'.$packageId.'.apkg'));
readfile('decks/'.$packageId.'.apkg');