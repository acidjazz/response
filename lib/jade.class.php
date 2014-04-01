<?

class jade {

  public static $templatedir = 'tpl/';

  public static function c($template, $array=array(), $return=false) {

    $stamp = microtime(true);

    $path = G_PATH.self::$templatedir;

    $constants = get_defined_constants(true);

    $array['_c'] = $constants['user'];

    if (!isset($array['pretty'])) {
      $array['pretty'] = true;
    }
    $array['self'] = true;

    foreach (array('s' => isset($_SESSION) ? $_SESSION : array(), 'g' => $_GET, 'p' => $_POST, 'r' => $_REQUEST) as $k=>$v) {
      $array['_'.$k] = $v;
    }

    if (!is_file($path.$template)) {
      $template = $template.'.jade';
    }

    if (!is_file($path.$template)) {
      trigger_error('Template not found: "'.$path.$template.'"');
      return false;
    }

    $result = self::post('http://localhost:3000/', $path.$template, $array);


    if ($result['data'] == false && self::checkProcess() == false) {

      self::startProcess();
      $result = self::post('http://localhost:3000/', $path.$template, $array);

    }

    if ($result['status'] == 500) {

      if (preg_match('/on line ([0-9]+)/i', $result['data'], $matches)) {
        kdebug::handler(E_ERROR, '<b>[Jade]</b> '.$result['data'], $path.$template, $matches[1]);
      } elseif (preg_match('/^(.*?):([0-9]+)/i', $result['data'], $matches)) {
        $lines = explode("\n", trim($result['data']));
        kdebug::handler(E_ERROR, '<b>[Jade]</b> '.end($lines), $matches[1], $matches[2]);
      } else {
        trigger_error("<b>[Jade]</b> compilation error: <pre>".$result['data']."</pre>");
      }

      return false;

    }

    if ($return) {
      return $result['data'];
    }

    echo $result['data'];

  }

  private static function post($url, $file, $data) {

    $handler = curl_init();
    $headers = array( 'Content-Type: text/html');

    $params = ['file' => $file, 'data' => $data];

    curl_setopt($handler, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handler, CURLOPT_POST, true);
    curl_setopt($handler, CURLOPT_POSTFIELDS, json_encode($params));

    curl_setopt($handler, CURLOPT_URL, $url);

    $data = curl_exec($handler);

    return ['status' => curl_getinfo($handler, CURLINFO_HTTP_CODE), 'data' => $data];

  }

  private static function checkProcess() {
    exec('pgrep -xlf "node srv/jade_web.js"', $output);
    if (count($output) < 1) {
      return false;
    }
    return true;
  }

  private static function startProcess() {
    exec('node srv/jade_web.js > /dev/null 2>&1 &', $output, $return);
    sleep(1);
  }

}
