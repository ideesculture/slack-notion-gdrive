<?php
//       _   _       _   _             
//      | \ | |     | | (_)            
//      |  \| | ___ | |_ _  ___  _ __  
//      | . ` |/ _ \| __| |/ _ \| '_ \ 
//      | |\  | (_) | |_| | (_) | | | |
//      \_| \_/\___/ \__|_|\___/|_| |_|

require("config.php");

// ***************** GET NOTION LISTE ORGANISATION DB ****************

function queryNotionDb($dbId){
    $notionDB= "https://api.notion.com/v1/databases/";
    $NOTION_AUTH = "secret_CBFKHdZXvcF7J54TqRyha6zU52IHrRYqQHcTzQPrvBi";
    
    $c = curl_init($notionDB.$dbId."/query");
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    $headers = array(
        'Authorization: Bearer '.$NOTION_AUTH,
        'Notion-Version: 2022-06-28',
        'Content-Type: application/json'
    );
    curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($c, CURLOPT_POST, true);
    $notionDBs = json_decode(curl_exec($c), true);
    curl_close($c);

    $notionDatas = [];

    foreach($notionDBs["results"] as $no){
        $tmp = [];
        $tmp["notion-url"] = $no["url"];
        if (isset($no['properties']['canal Slack'])) {
            $richTextArray = $no['properties']['canal Slack']['rich_text'];
            foreach ($richTextArray as $item) {
                if (isset($item['text']['content'])) { 
                    $tmp["slack-channel"] = $item['text']['content'];
                }               
            }
        }; 
        if (isset($no['properties']['dossier client Google Drive'])) {
            $richTextArray = $no['properties']['dossier client Google Drive']['rich_text'];
            foreach ($richTextArray as $item) {
                if (isset($item['text']['content'])) { 
                    $tmp["gdrive-name"] = $item['text']['content'];
                }               
            }
        };      
        $notionDatas[] = $tmp;
    };
    return $notionDatas;
}

