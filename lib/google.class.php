<?

class google {

  public static $client_id = false;
  public static $client_secret = false;
  public static $redirect_uri = false;
  
  public static $access_token = false;

  public static $url_auth = 'https://accounts.google.com/o/oauth2/auth';
  public static $url_token = 'https://accounts.google.com/o/oauth2/token';

  public static $url_scope = 'https://www.googleapis.com/auth/';

  public static $scope = [
    'readonly' => 'https://www.googleapis.com/auth/youtube.readonly',
    'useremail' => 'https://www.googleapis.com/auth/userinfo.email',
    'userprofile' => 'https://www.googleapis.com/auth/userinfo.profile',
  ];

  public static $certs_url = 'https://www.googleapis.com/oauth2/v1/certs';
  public static $certs = [];

  // assign our app-specific information
  public function __construct($access_token=false, $client_id=G_CLIENT_ID, $client_secret=G_SECRET, $redirect_uri=G_REDIRECT_URI) {

    self::$access_token = $access_token;
    self::$client_id = $client_id;

    self::$client_secret = $client_secret;
    self::$redirect_uri = $redirect_uri;

    self::$certs = json_decode(file_get_contents(self::$certs_url), true);

  }

  // compile the url for the user to authorize us
  public function authURL($state='nostate', $hint=null) {

    $params = [
      'state' => $state, // google roundtrips this 
      'response_type' => 'code',
      'access_type' => 'offline',
      'client_id' => self::$client_id,
      'redirect_uri' => self::$redirect_uri,
      'login_hint' => $hint, // hint at which account we want
      'scope' => implode(' ', self::$scope)
    ];

    return self::$url_auth.'?'.http_build_query($params);

  }

  // verify our returned code
  public function codeVerify($code) {

    $params = [
      'code' => $code,
      'client_id' => self::$client_id,
      'client_secret' => self::$client_secret,
      'redirect_uri' => self::$redirect_uri,
      'grant_type' => 'authorization_code'
    ];

    $results = json_decode(self::get(self::$url_token, $params, 'post'), true);

    if (isset($results['access_token']) && !empty($results['access_token'])) {
      self::$access_token = $results['access_token'];
    } else {
      return ['success' => false, 'results' => $results];
    }

    if (isset($results['id_token']) && !empty($results['id_token'])) {
      $results['jwt'] = self::decode($results['id_token']);
    } else {
      return false;
    }

    return ['success' => true, 'results' => $results];

  }

  public function tokenVerify($token) {

    try {
      $results = self::decode($token);
    } catch(Exception $e) {
      return false;
      //return ['error' => $e->getMessage()];
    }

    if (isset($results)) {
      if (!isset($results['aud']) || $results['aud'] != self::$client_id) {
        return false;
        //return ['error' => 'invalid client'];
      }

      return $results;
    }

  }

  public function refresh($refresh_token) {

    $params = [
      'client_id' => self::$client_id,
      'client_secret' => self::$client_secret,
      'refresh_token' =>$refresh_token,
      'grant_type' => 'refresh_token'
    ];

    $results = json_decode(self::get(self::$url_token, $params, 'post'), true);

    if (isset($results['error'])) {
      return false;
    }

    if (isset($results['access_token']) && !empty($results['access_token'])) {
      self::$access_token = $results['access_token'];
    }

    if (isset($results['id_token']) && !empty($results['id_token'])) {
      $results['jwt'] = self::decode($results['id_token']);
    }

    return $results;

  }

  public function api($url, $params=array(), $type='get', $result='json') {

    $response = self::get($url, array_merge(['access_token' => self::$access_token], $params), $type);

    if ($result == 'json') {

      $json = json_decode($response, true);

      if ($json == null) {
        return false;
      }

      return $json;
    }

    return $response;
  }

  public static function get($url, $params, $type='get') {

    $handler = curl_init();

    curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);

    if ($type == 'post') {

      curl_setopt($handler, CURLOPT_POST, true);
      curl_setopt($handler, CURLOPT_POSTFIELDS, $params);

    } elseif ($type == 'delete') {
      curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "DELETE");
      curl_setopt($handler, CURLOPT_POSTFIELDS, $params);

    } else {

      $url .= '?'.http_build_query($params, null, '&');

    }

    curl_setopt($handler, CURLOPT_URL, $url);

    return curl_exec($handler);

  }

  // decode and verify a JWT (json web token) w/ googles certs
  public static function decode($jwt, $verify=true) {

    $tks = explode('.', $jwt);

    if (count($tks) != 3) {
      throw new UnexpectedValueException('Wrong number of segments');
    }

    list($headb64, $payloadb64, $cryptob64) = $tks;
    if (null === ($header = json_decode(self::urlb64decode($headb64)))) {
      throw new UnexpectedValueException('Invalid head segment encoding');
    }

    if (null === $payload = json_decode(self::urlb64decode($payloadb64), true)) {
      throw new UnexpectedValueException('Invalid payload segment encoding');
    }

    $sig = self::urlb64decode($cryptob64);

    if ($verify) {

      if (empty($header->alg)) {
        throw new DomainException('Empty algorithm');
      }

      $verified = false;

      foreach (self::$certs as $keyname=>$pem) {

        $parsed = openssl_x509_read($pem);

        $status = openssl_verify($headb64.'.'.$payloadb64, $sig, $parsed, "sha256");

        openssl_x509_free($parsed);

        if ($status == 1) {
          $verified = true;
        }

      }

      if ($verified != true) {
        throw new UnexpectedValueException('Signature verification failed');
        return false;
      }


    }

    return $payload;

  }

  public static function urlb64decode($input) {

    $remainder = strlen($input) % 4;

    if ($remainder) {
      $padlen = 4 - $remainder;
      $input .= str_repeat('=', $padlen);
    }

    return base64_decode(strtr($input, '-_', '+/'));
  }


}
