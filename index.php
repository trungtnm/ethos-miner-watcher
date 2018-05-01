<?php
include "./config.php";
while (1) {
    $info = json_decode(file_get_contents($CONFIG['rig_panel_url']), 1);
    if (
        json_last_error() != JSON_ERROR_NONE
        || $info['alive_gpus'] < $CONFIG['number_of_gpus']
        || $info['total_hash'] < $CONFIG['minimum_hashrate']
        || isExceedTemp($info)
    ) {
        alert();
    } else {
        echo "The rig is working normally.\r\n";
    }
    sleep(300);
}


function alert() {
    global $CONFIG, $info;

    $message = "Your rig {$CONFIG['rig_name']} has issue";
    $message .= "\r\nGPUs: {$info['alive_gpus']}/{$info['total_gpus']}";
    $message .= sprintf("\r\nHash rates:  %s", $info['rigs'][$CONFIG['rig_name']]['miner_hashes']);
    $message .= sprintf("\r\nTemps:  %s", $info['rigs'][$CONFIG['rig_name']]['temp']);
    $message .= "\r";

    echo $message;
    $url = "https://api.telegram.org/bot{$CONFIG['bot_token']}/sendMessage?chat_id={$CONFIG['chat_id']}&parse_mode=HTML&text=" . urlencode($message);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
}


function isExceedTemp($info) {
    global $CONFIG;
    $temps = explode(' ', $info['rigs'][$CONFIG['rig_name']]['temp']);
    if (count($temps)) {
        foreach ($temps as $temp) {
            if ($temp > $CONFIG['maximum_temp']) {
                return true;
            }
        }
    }

    return false;
}
