<?php
require __DIR__ . '/vendor/autoload.php';
use Google\Service\Drive;


function queryGdrive($sharedDriveId){
    session_start();

    $client = new Google_Client();
    $client->setRedirectUri('http://localhost/8000');
    $client->setScopes(Drive::DRIVE);
    $client->setAuthConfig('google-credentials.json');

    $driveService = new Drive($client);
    $parameters = [
        'corpora' => 'drive',
        'includeItemsFromAllDrives' => true,
        'driveId' => $sharedDriveId,
        'supportsAllDrives' => true,
        'q' =>  "'{$sharedDriveId}' in parents and mimeType = 'application/vnd.google-apps.folder'",
        'spaces' => 'drive',
        'fields' => 'files(id, name)',
    ];

    $results = $driveService->files->listFiles($parameters);

    $foldersDatas = [];

    try {
        foreach ($results->getFiles() as $file) {
            $baseurl = "https://drive.google.com/drive/folders/";
            $datas = [];
            $datas["name"] = $file->getName();
            $datas["url"] = $baseurl.$file->getId();
            $foldersDatas[] = $datas;
        }
    } catch (Exception $e) {
        echo "An error occurred: " . $e->getMessage()."\n";
    }
    return $foldersDatas;
}





