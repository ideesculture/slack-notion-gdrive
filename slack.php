<?php

//      _____ _       ___  _____  _   __
//     /  ___| |     / _ \/  __ \| | / /
//     \ `--.| |    / /_\ \ /  \/| |/ / 
//      `--. \ |    |  _  | |    |    \ 
//     /\__/ / |____| | | | \__/\| |\  \
//     \____/\_____/\_| |_/\____/\_| \_/

$curl = curl_init();
require("config.php");

// ************** LISTER TOUS LES CHANNELS  **************

function getAllSlackChannels($SLACK_BEARER_TOKEN){
    $urlAllChannels = "https://slack.com/api/conversations.list";
    $chanel = curl_init();
    curl_setopt($chanel, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
        'Authorization: Bearer '.$SLACK_BEARER_TOKEN,
        'Content-Type: application/json'
        );

    $params = http_build_query([
        'types' => 'public_channel,private_channel',
        "limit" => 999
     ]);


    curl_setopt($chanel, CURLOPT_URL, $urlAllChannels . '?' . $params);
    curl_setopt($chanel, CURLOPT_HTTPHEADER, $headers);
    $allSlackChannels = curl_exec($chanel);
    curl_close($chanel);

    $chans = json_decode($allSlackChannels, true);
    $allDatas = extractSlackChannels($chans);
    return $allDatas;
}

function extractSlackChannels($chans){
    $allDatas = [];
    foreach($chans["channels"] as $c){
        $tmp = [];
        $tmp["slack-channel-name"] = $c["name"];
        $tmp["slack-channel-id"] = $c["id"];
        $allDatas[] = $tmp;
    };
    return $allDatas;
}

function checkBookmarks($SLACK_BEARER_TOKEN, $channelId, $target){
    $url = "https://slack.com/api/bookmarks.list";

    $headers = array(
        'Authorization: Bearer '.$SLACK_BEARER_TOKEN,
        'Content-Type: application/x-www-form-urlencoded'
        );

        $params = http_build_query([
            "channel" => $channelId
         ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        $verif = false;

        if($data["ok"] == true && $target == "notion"){
            $verif = checkIfNotionLink($data);
        }else if($data["ok"] == true && $target == "gdrive"){
            $verif = checkIfGdriveLink($data);
        };
      
        return $verif;

}

function checkIfNotionLink($data){
    $verif = false;
    if($data["ok"] == true){
        foreach($data["bookmarks"] as $d){
            if($d["title"] == "Lien vers Notion"){
                $verif = true;
            }
        }
    }
  
    return $verif;
}

function checkIfGdriveLink($data){
    $verif = false;
    if($data["ok"] == true){
        foreach($data["bookmarks"] as $d){
            if($d["title"] == "Lien vers Google Drive"){
                $verif = true;
            }
        }
    }
 
    return $verif;
}

// **************  Epingler des messages ************** 

function createBookmark($SLACK_BEARER_TOKEN, $SLACK_CHANNEL_ID, $url, $target){
    $urlAddBookmark = "https://slack.com/api/bookmarks.add";
    $channel = curl_init($urlAddBookmark);
    curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($channel, CURLOPT_POST, true);
    $headers = array(
        'Authorization: Bearer '.$SLACK_BEARER_TOKEN,
        'Content-Type: application/json'
    );

    if($target == "notion"){
        $postFields = array(
            "channel_id" => $SLACK_CHANNEL_ID,
            "title" => "Lien vers Notion",
            "type" => "link",
            "link" => $url
        );
    }else if($target == "gdrive"){
        $postFields = array(
            "channel_id" => $SLACK_CHANNEL_ID,
            "title" => "Lien vers Google Drive",
            "type" => "link",
            "link" => $url
        );
    }

    curl_setopt($channel, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($channel, CURLOPT_POSTFIELDS, json_encode($postFields));
    $response = curl_exec($channel);
    curl_close($channel);

    echo($response."\n");
}