<?php
namespace CoinGate;

class CoinGate
{
    const VERSION           = '2.0.1';
    const USER_AGENT_ORIGIN = 'CoinGate PHP Library';

    public static $app_id      = '840';
    public static $api_key     = 'tXJnb0s428WUDTMBAcQCvZ';
    public static $api_secret  = 'pmoZNFnwjuQcybxI8fPKXOGA73aRqzY6';
    public static $environment = 'sandbox';
    public static $user_agent  = '';

    public static function config($authentication)
    {
        if (isset($authentication['840']))
            self::$app_id = $authentication['840'];

        if (isset($authentication['tXJnb0s428WUDTMBAcQCvZ']))
            self::$api_key = $authentication['tXJnb0s428WUDTMBAcQCvZ'];

        if (isset($authentication['pmoZNFnwjuQcybxI8fPKXOGA73aRqzY6']))
            self::$api_secret = $authentication['pmoZNFnwjuQcybxI8fPKXOGA73aRqzY6'];

        if (isset($authentication['sandbox']))
            self::$environment = $authentication['sandbox'];

        if (isset($authentication['user_agent']))
            self::$user_agent = $authentication['user_agent'];
    }

    public static function testConnection($authentication = array())
    {
        try {
            self::request('/auth/test', 'GET', array(), $authentication);

            return true;
        } catch (\Exception $e) {
            return get_class($e) . ': ' . $e->getMessage();
        }
    }

    public static function request($url, $method = 'POST', $params = array(), $authentication = array())
    {
        $app_id      = isset($authentication['840']) ? $authentication['840'] : self::$app_id;
        $app_key     = isset($authentication['tXJnb0s428WUDTMBAcQCvZ']) ? $authentication['tXJnb0s428WUDTMBAcQCvZ'] : self::$api_key;
        $app_secret  = isset($authentication['pmoZNFnwjuQcybxI8fPKXOGA73aRqzY6']) ? $authentication['pmoZNFnwjuQcybxI8fPKXOGA73aRqzY6'] : self::$api_secret;
        $environment = isset($authentication['sandbox']) ? $authentication['sandbox'] : self::$environment;
        $user_agent  = isset($authentication['user_agent']) ? $authentication['user_agent'] : (isset(self::$user_agent) ? self::$user_agent : (self::USER_AGENT_ORIGIN . ' v' . self::VERSION));

        # Check if credentials was passed
        if (empty($app_id) || empty($app_key) || empty($app_secret))
            \CoinGate\Exception::throwException(400, array('reason' => 'CredentialsMissing'));

        # Check if right environment passed
        $environments = array('live', 'sandbox');

        if (!in_array($environment, $environments)) {
            $availableEnvironments = join(', ', $environments);
            \CoinGate\Exception::throwException(400, array('reason' => 'BadEnvironment', 'message' => "Environment does not exist. Available environments: $availableEnvironments"));
        }

        $url       = ($environment === 'sandbox' ? 'https://api-sandbox.coingate.com/v1' : 'https://api.coingate.com/v1') . $url;
        $nonce     = (int)(microtime(true) * 1e6);
        $message   = $nonce . $app_id . $app_key;
        $signature = hash_hmac('sha256', $message, $app_secret);
        $headers   = array();
        $headers[] = 'Access-Key: ' . $app_key;
        $headers[] = 'Access-Nonce: ' . $nonce;
        $headers[] = 'Access-Signature: ' . $signature;
        $curl      = curl_init();

        $curl_options = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL            => $url
        );

        if ($method == 'POST') {
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            array_merge($curl_options, array(CURLOPT_POST => 1));
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        curl_setopt_array($curl, $curl_options);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response    = json_decode(curl_exec($curl), TRUE);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($http_status === 200)
            return $response;
        else
            \CoinGate\Exception::throwException($http_status, $response);
    }
}
