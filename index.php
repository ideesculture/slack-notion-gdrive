<?php 

require('notion.php');
require('slack.php');
require('google-drive.php');
require("config.php");

function createLinks($SLACK_BEARER_TOKEN, $notionDbId, $sharedDriveId){
    $slackChans = getAllSlackChannels($SLACK_BEARER_TOKEN);
    generateNotionLinks($SLACK_BEARER_TOKEN, $slackChans, $notionDbId);
    generateGdriveLinks($SLACK_BEARER_TOKEN, $slackChans, $notionDbId, $sharedDriveId);
};

function generateNotionLinks($token, $slack, $notionDbId){
    $notion = queryNotionDb($notionDbId);
    foreach($notion as $n){
        foreach($slack as $s){
            if(isset($n["slack-channel"]) && $s["slack-channel-name"] && substr($n["slack-channel"], 1) == $s["slack-channel-name"]){
                $target = "notion";
                $v= checkBookmarks($token, $s["slack-channel-id"], $target);
                if(!$v){
                    try{
                        createBookmark($token, $s["slack-channel-id"], $n["notion-url"], $target);
                        echo("Bookmark notion ".$n["notion-url"]." créé \n");
                    }catch(Exception $e){
                        echo("ERROR : Bookmark notion ".$n["notion-url"]." non créé : ".$e."\n");
                    }
                }else{
                    echo("Bookmark notion : ".$n["notion-url"]." déjà existant\n");
                }
            }
        }
    }
}

function generateGdriveLinks($token, $slack, $notionDbId,  $sharedDriveId ){
    $notion = queryNotionDb($notionDbId);
    $gdrive = queryGdrive( $sharedDriveId );
    foreach($gdrive as $gd){
       foreach($notion as $n){
        if(isset($n["gdrive-name"]) && isset($gd["name"]) && $n["gdrive-name"] == $gd["name"] ){
            foreach($slack as $s){
                if(isset($n["slack-channel"]) && $s["slack-channel-name"] && substr($n["slack-channel"], 1) == $s["slack-channel-name"]){
                    $target = "gdrive";
                    $v= checkBookmarks($token, $s["slack-channel-id"], $target);
                    if(!$v){
                        try{
                            createBookmark($token, $s["slack-channel-id"], $gd["url"], $target);
                            echo("Bookmark gdrive : ".$gd["name"]." créé\n");
                        }catch(Exception $e){
                            echo ("ERROR : Bookmark gdrive : ".$gd["name"]." non créé : ".$e."\n");
                        }
                    }else{
                        echo("Bookmark google drive : ".$gd["name"]." déjà existant\n");
                    }
                }
            }
        }
       }

    }
}

createLinks($SLACK_BEARER_TOKEN, $notionDbId, $sharedDriveId);