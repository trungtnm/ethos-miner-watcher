<?php
include "./config.php";

$info = json_decode(file_get_contents($CONFIG['rig_panel_url']), 1);
if (json_last_error() != JSON_ERROR_NONE) {
    return alert();
}

if (
    $info['alive_gpus'] < $CONFIG['number_of_gpus']
    || $info['total_hash'] < $CONFIG['minimum_hashrate']
    || isExceedTemp($info)
) {
    return alert();
}
echo "Nothing happen";

function alert() {
    global $CONFIG, $info;

    $message = "<b>Your rig {$CONFIG['rig_name']} has issue</b>";
    $message .= "\r\nGPUs: <b>{$info['alive_gpus']}</b>/{$info['total_gpus']}";
    $message .= sprintf("\r\nHash rates:  %s", $info['rigs'][$CONFIG['rig_name']]['miner_hashes']);
    $message .= sprintf("\r\nTemps:  %s", $info['rigs'][$CONFIG['rig_name']]['temp']);

    file_get_contents("https://api.telegram.org/bot{$CONFIG['bot_token']}/sendMessage?chat_id={$CONFIG['chat_id']}&parse_mode=HTML&text=" . urlencode($message));
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
