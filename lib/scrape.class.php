<?

class scrape {

  public static $handle = false;
  const lockfile = '/tmp/scrape.lock';
  public $user = false;

  public function __construct() {
    //$id = '110930371186022920716';
    //$this->user = new user(user::findOne(['id' => $id]));
  }

  public function scrape() {

    if (!$this->lock()) {
      $this->stat(false,'unable to aquire lock');
      return false;
    }

    $this->stat(true, 'file lock aquired');

    $videos = video::find();
    $total = $videos->count();

    $yt = new youtube();

    $i = 0;

    foreach ($videos as $key=>$value) {

      $i++;
      sleep(1);

      $tick = "[$i\t/$total] ";
      $video = new video($value);
      $this->stat(true, $tick.'Fetching latest data for "'.$video->title.'" ('.$video->id.')');

      $ytv = $yt->video($video->id, true);

      if ($ytv === false) {

        if ($yt->error != false) {
          $this->stat(false, 'Error retrieving : "'.$video->title.'" ('.$video->id.')');
          print_r($yt->error);
          exit;
        }

        // no error? probably gone, lets delete it
        $this->stat(false, 'Detected deleted video: "'.$video->title.'" ('.$video->id.')');
        $video->remove();
        $this->stat(false, 'Video Removed: "'.$video->title.'" ('.$video->id.')');
        continue;
      }

      $video->title = $ytv['snippet']['title'];
      $video->description = $ytv['snippet']['description'];
      $video->thumbnails = $ytv['snippet']['thumbnails'];

      if (isset($ytv['snippet']['tags'])) {
        $video->tags = $ytv['snippet']['tags'];
      }

      // statistics part, cast to integer for proper sorting
      $stats = [];
      foreach ($ytv['statistics'] as $key=>$stat) {
        $stats[$key] = (int) $stat;
      } 
      $video->stats = $stats;

      $video->save();

      $this->stat(true, $tick.'Update Successful "'.$ytv['snippet']['title'].'" ('.$video->id.')');

    }

  }

  public function cat_clean() {

    $clib = new categories();
    $vlib = new videos();

    $cursor = $clib->get();

    $cats = [];

    foreach ($cursor as $catdata) {
      $category = new category($catdata);
      $cats[$category->id(true)] = $category->name;
    }

    foreach ($vlib->get([], [], 0) as $videodata) {
      $video = new video($videodata);
      $vcats = $video->categories;
      $missing = false;
      foreach ($vcats as $key=>$category) {
        if (!in_array($category, $cats)) {
          $this->stat(true, '('.$video->id.')'. $video->title . 'invalid -> ' . $category."\r\n");
          $missing = true;
          unset($vcats[$key]);
        } else {
          $vcats[$key] = trim($category);
        }
      }

      if ($missing || true) {
        $video->categories = array_values($vcats);
        $video->save();
      }

    }

  }

  private static function fp() {

    if (!self::$handle) {
      self::$handle = fopen(self::lockfile, 'w+');
    }

    return self::$handle;

  }

  public function lock() {

    if (flock(self::fp(), LOCK_EX | LOCK_NB)) {
      return true;
    }

    return false;

  }

  public function unlock() {

    if (flock(self::fp(), LOCK_UN)) {
      fclose (self::fp());
      return true;
    }

    fclose (self::fp());
    return false;

  }

  private function stat($type, $message) {

    $status = $type ? 'DEBUG' : 'ERROR';
    $echo = '[ '.date('c')."] [$status] $message\r\n";
    echo $echo;

  }

  public function __destruct() {

    if (!$this->unlock()) {
      $this->stat(false,'Unable to unlock');
      return false;
    }

    $this->stat(true, 'file unlock success');

  }

}
