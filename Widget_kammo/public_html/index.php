<?php


use Roistat\AmoCRM_Wrap\BaseEntity;
use Roistat\AmoCRM_Wrap\Token;
use Roistat\AmoCRM_Wrap\AmoCRM;
use Roistat\AmoCRM_Wrap\Contact;
use Roistat\AmoCRM_Wrap\Lead;


require_once __DIR__.'/NewAmoWrap/autoload.php';


try{
    /**
     * @var $authData // Конфиг файл API amoCRM
     */
    $amo  = new AmoCRM($authData['domain'], new Token($authData));

} catch (Exception $e){
    writeToLog($e->getMessage(), 'catch error',$_SERVER['DOCUMENT_ROOT'].'/roistat/logs/error.log');
    echo "Ошибка в получении токена: {$e->getMessage()}";
}


$lead_id = 2952012;

$lead = new Lead($lead_id);

