<?php


namespace Roistat\AmoCRM_Wrap;


use http\Exception;

class Token {
    const FILE_NAME = __DIR__.'/../token_info.json';

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var string
     */
    private $redirectUri;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var string
     */
    private $refreshToken;

    /**
     * @var int
     */
    private $timeToLive;

    /**
     * @var string
     */
    private $domain;

    /**
     * Token constructor.
     * https://www.amocrm.ru/oauth?client_id={client_id}&state=state&mode=post_message
     * @param string $_domain
     * @param string $_clientId
     * @param string $_clientSecret
     * @param string $_accessToken
     * @param string $_redirectUri
     * @throws \Exception
     */
    public function __construct($authData) {
        $this->domain = $authData['domain'];
        $this->initToken($authData);
    }

    private function getTokenName($domain)
    {
        return __DIR__ . "/../token_{$domain}_info.json";
    }

    /**
     * @param $authData
     * @throws \Exception
     */
    protected function initToken($authData)
    {
        $tokenInfo = $this->getTokenInfo();
        if (!$tokenInfo) {
            if(empty($_GET['code'])) {
                echo <<<EOS
                    <a href='https://www.kommo.com/oauth?client_id={$authData['client_id']}&state=state&mode=post_message'>
                        <img src='https://www.amocrm.ru/static/assets/developers/files/oauth/button.png'/>
                    </a><br>
EOS;
                die;
            } else {
                $this->firstAuth(
                    $authData['client_id'],
                    $authData['client_secret'],
                    $_GET['code'],
                    $authData['redirect_uri']
                );
            }
        } else {
            $this->refreshToken = $tokenInfo['refresh_token'];
            $this->accessToken  = $tokenInfo['access_token'];
            $this->timeToLive   = $tokenInfo['time_to_live'];
            $this->clientId     = $tokenInfo['client_id'];
            $this->clientSecret = $tokenInfo['client_secret'];
            $this->redirectUri  = $tokenInfo['redirect_uri'];

            if ($this->timeToLive < time()) {
                $this->refreshToken();
            }
        }
        return null;
    }

    /**
     * Получение файла с токеном
     * @return false|mixed
     */
    private function getTokenInfo()
    {
        $tokenFileName = $this->getTokenName($this->domain);
        if(is_file($tokenFileName)) {
            $tokenFile = file_get_contents($tokenFileName);
            return json_decode($tokenFile, true);
        }
        return false;
    }

    /**
     * @return string
     */
    public function getToken() {
        return $this->accessToken;
    }

    /**
     * @param string $clientId
     * @param string $clientSecret
     * @param string $accessToken
     * @param string $redirectUri
     * @throws \Exception
     */
    private function firstAuth($clientId, $clientSecret, $code, $redirectUri) {
        $requestData = [
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => $redirectUri,
        ];

        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri  = $redirectUri;

        return $this->execute($requestData);
    }

    /**
     * @throws \Exception
     */
    private function refreshToken() {
        $requestData = array(
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type'    => 'refresh_token',
            'refresh_token' => $this->refreshToken,
            'redirect_uri'  => $this->redirectUri,
        );

        return $this->execute($requestData);
    }

    /**
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    private function cUrl($data){
        $link = 'https://'.$this->domain.'.amocrm.ru/oauth2/access_token';

        $curl = curl_init();
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
        curl_setopt($curl,CURLOPT_URL, $link);
        curl_setopt($curl,CURLOPT_HTTPHEADER,['Content-Type:application/json']);
        curl_setopt($curl,CURLOPT_HEADER, false);
        curl_setopt($curl,CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
        $response   = curl_exec($curl); //Инициируем запрос к API и сохраняем ответ в переменную
        $code       = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        /** Если код ответа не успешный - возвращаем сообщение об ошибке  */
        if ($code < 200 || $code > 204) {
            throw new \Exception($this->checkResponseCode($code), $code);
        }

        return json_decode($response,true);
    }

    /**
     * Проверка ответа сервера Амо
     * @param $code
     * @return string
     */
    private function checkResponseCode($code)
    {
        $errors = [
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not found',
            500 => 'Internal server error',
            502 => 'Bad gateway',
            503 => 'Service unavailable',
        ];
        return isset($errors[$code]) ? $errors[$code] : 'Undefined error';
    }

    /**
     * @param array $requestData
     * @throws \Exception
     */
    private function execute(array $requestData)
    {
        $tokenInfo = $this->cUrl($requestData);
        $this->refreshToken = $tokenInfo['refresh_token'];
        $this->accessToken  = $tokenInfo['access_token'];
        $this->timeToLive   = time() + (int) $tokenInfo['expires_in'];

        return $this->saveToken();
    }

    /**
     * Сохранить токен
     * @return false|int|null
     */
    private function saveToken()
    {
        if ($this->clientId !== null) {
            $tokenInfo = array(
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri'  => $this->redirectUri,
                'time_to_live'  => $this->timeToLive,
                'refresh_token' => $this->refreshToken,
                'access_token'  => $this->accessToken,
                'domain'        => $this->domain,
            );
            return file_put_contents($this->getTokenName($this->domain), json_encode($tokenInfo));
        }
        return null;
    }

}