<?

class k {

  public static function hpr($obj, $return=false) {

    $output = '';

    if (PHP_SAPI != 'cli') {

    $output = <<<HTML
    <pre style="
      font-size: 13px;
      font-family: 'lucida grande', tahoma, verdana, arial, sans-serif;
      color: #333;
      border: 1px solid #d0d0d0; 
      background-color: #efefef;
      border-radius: 5px;
      margin: 5px; 
      padding: 5px;
    ">
HTML;

  }

    ob_start();
    var_dump($obj);
    $output .= ob_get_contents();
    ob_end_clean();
    
    if (PHP_SAPI != 'cli') {
      $output .= '</pre>';
    }

    if ($return == true) {
      return $output;
    }

    echo $output;

  }

  public static function highlight($text,$type='php',$nopre=false) {

    $text = preg_replace("/^\n/i", "", $text);

    $temp = tmpfile();
    $file = tempnam(sys_get_temp_dir(), 'highlight_');
    $handle = fopen($file, 'w');
    fwrite($handle, $text);
    fclose($handle);
    $highlighted = shell_exec("highlight -f --style neon --syntax=$type < $file");
    unlink($file);
    if ($nopre == false) {
      return str_replace("\n", '<br />', $highlighted);
    }

    return $highlighted;

  }

  public static function cpr($code, $type='sql', $return=false, $br=false) {

    $data = null;

    if ($type == 'xml') {
      $code = xmlindent($code);
    }

    $highlighted  = highlight($code,$type);
    $highlighted = str_replace("\t", '&nbsp;&nbsp;', $highlighted);

    $data = <<<HTML
    <style type="text/css">
    .num  { color:#7D26Cd; }
    .esc  { color:#ff00ff; }
    .str  { color:#888; }
    .dstr { color:#818100; }
    .slc  { color:#838183; font-style:italic; }
    .com  { color:#838183; font-style:italic; }
    .dir  { color:#008200; }
    .sym  { color:#528B8B; }
    .line { color:#555555; }
    .kwa  { color:#222299; font-weight:bold; }
    .kwb  { color:#830000; }
    .kwc  { color:#000000; font-weight:bold; }
    .kwd  { color:#010181; }
    .kdebug_cpr {
      border-radius: 5px;
      border: 1px solid #e7e7e7;
      font-family: 'lucida grande', tahoma, verdana, arial, sans-serif;
      font-size: 13px;
      margin: 5px;
      padding: 5px;
      background-color: #efefef;
    }

    </style>
    <div class="kdebug_cpr">
HTML;

    $data .= $highlighted;
    $data .= '</div>';

    if ($return == true) {
      return $data;
    }

    echo $data;

  }

}
