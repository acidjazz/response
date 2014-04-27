<?

class jade {

  public static $templatedir = 'tpl/';

  public static function c($template, $array=array(), $return=false) {

    $stamp = microtime(true);

    $path = G_PATH.self::$templatedir;

    $array['_c'] = get_defined_constants(true)['user'];
    $array['pretty'] = true;
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

    $result = node::post('jade', 'http://localhost:3000/', ['file' => $path.$template], $array);

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


}
