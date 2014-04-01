<?

require_once 'cfg/config.php';

date_default_timezone_set("UTC");

(new kctl($_SERVER['REQUEST_URI']))->start();

