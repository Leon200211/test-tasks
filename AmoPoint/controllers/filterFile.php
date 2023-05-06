<?php


//$symbol = htmlspecialchars(stripslashes(trim($_REQUEST['symbol'])));


$symbol = $_REQUEST['symbol'];


$file = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/AmoPoint/file/test.txt', true);


$chars = preg_split("/$symbol/", $file);

$result = [];

foreach ($chars as $key => $char){
    $result[$key] = [
        'str' => $char,
        'Ncount' => preg_match_all( "/[0-9]/", $char)
    ];
}


$result = json_encode($result);
echo $result;