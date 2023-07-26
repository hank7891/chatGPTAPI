<?php
include_once(dirname(__FILE__) . '/OpenAi.php');

try {
    define('API_TOKEN', '');
    $openAi = new OpenAi(API_TOKEN);
    $msg = $openAi->getResponse('MLB 2020 世界大賽冠軍是誰？');

} catch (Exception $e) {
    echo $e->getMessage();
}