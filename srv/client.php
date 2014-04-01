<?

require_once '../cfg/config.php';

$file = 'srv/test.jade';
$data = [
  'pretty' => true,
  'self' => true,
  'array' => range(1,500)
];

$stamp = microtime(true);

for ($i = 0; $i != 1000; $i++) {

  $stampb = microtime(true);

  $fp = @pfsockopen('unix:///tmp/jade.sock', -1, $errno, $errstr, 30);

  if (!$fp) {
    echo "erro yo: $errstr ($errno)\r\n";
  } else {
    fwrite($fp, json_encode(['file' => $file, 'data' => $data]));
    $result = '';
    while (!feof($fp)) {
      $result .= fgets($fp, 1024);
    }
    //print_r( json_decode($result, true))."\r\n";;
    fclose($fp);
  }

  echo "\r\n".(microtime(true) - $stamp) . "\t\t" . (microtime(true) - $stampb);

}
 echo "\r\n".(microtime(true) - $stamp) . "\t\t" . (microtime(true) - $stampb);
echo "\r\n";
