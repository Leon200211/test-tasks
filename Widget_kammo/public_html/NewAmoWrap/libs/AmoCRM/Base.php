<?php
/**
 * Created by PhpStorm.
 * User: Roistat
 * Date: 2019-01-04
 * Time: 01:13
 */

namespace Roistat\AmoCRM_Wrap;

use DateTime;
use Roistat\AmoCRM_Wrap\Helpers\Config;
use stdClass;

/**
 * Class Base
 * @package Roistat\AmoCRM_Wrap
 */
abstract class Base
{
    /**
     * @var string
     */
    protected static $domain;

    /**
     * @var Token
     */
    protected static $token;

    /**
     * @var bool
     */
    protected static $authorization = false;

    /**
     * @param string        $url
     * @param array         $data
     * @param DateTime|null $modifiedSince
     * @param bool          $ajax
     * @param string        $method
     *
     * @return stdClass|null
     *
     * @throws AmoWrapException
     */
    protected static function cUrl($url, $data = array(), DateTime $modifiedSince = null, $ajax = false, $method = 'GET')
    {
        $url = 'https://' . self::$domain . '.kommo.com/' . $url;

        $curl = curl_init();
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl,CURLOPT_USERAGENT,'Roistat AmoCRM_Wrap/v' . AmoCRM::VERSION);
        curl_setopt($curl,CURLOPT_URL, $url);
        curl_setopt($curl,CURLOPT_HEADER, false);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

        $headers = array();
        if (!empty($data) and count($data) > 0) {
            curl_setopt($curl, CURLOPT_POST, true);
            if ($ajax) {
                $headers[] = 'X-Requested-With: XMLHttpRequest';
                $dataStr = $data;
            } else {
                $headers[] = 'Content-Type: application/json';
                $dataStr = json_encode($data);
            }
            curl_setopt($curl, CURLOPT_POSTFIELDS, $dataStr);
        }

        if ($modifiedSince !== null) {
            $headers[] = 'IF-MODIFIED-SINCE: ' . $modifiedSince->format(DateTime::RFC1123);
        }

        $accessToken = Base::$token->getToken();
        $headers[] = "Authorization: Bearer {$accessToken}";

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $json = curl_exec($curl);
        curl_close($curl);

        $result = json_decode($json);
        if (isset($result->response->error) || (isset($result->title) && $result->title === 'Error')) {
            $errorCode = isset($result->status) ? (int)$result->status : (int)$result->response->error_code;
            $errorMessage = isset(Config::$errors[$errorCode]) ? Config::$errors[$errorCode] : $result->response->error;
            throw new AmoWrapException($errorMessage, $errorCode);
        }
        return $result;
    }

    /**
     * @param string $var
     *
     * @return string
     */
    protected static function onlyNumbers($var)
    {
        return preg_replace('/\D/', '', $var);
    }
}