<?

class youtube {

  public $access_token = false;
  public $goo = false;
  public $error = false;

  const id_regex = "/^[a-zA-Z0-9_-]{11}$/";

  public function __construct($access_token=false) {
    $this->access_token = $access_token;
    $this->goo = new google($this->access_token);
  }

  public static function validId($id) {
    return preg_match(self::id_regex, $id) > 0;
  }

  public function uploads($token=null) {

    $channels = $this->goo->api(
      'https://www.googleapis.com/youtube/v3/channels', 
      ['part' => 'contentDetails', 'mine' => 'true']);

    if (isset($channels['error'])) {
      $this->error = $channels['error'];
      return false;
    }

    foreach ($channels['items'] as $channel) {

      $uploadsListId = $channel['contentDetails']['relatedPlaylists']['uploads'];

      // TODO: loop support for page offseting for 50+ video uploads

      $ids = [];
      $playlistitems = $this->goo->api(
        'https://www.googleapis.com/youtube/v3/playlistItems', 
        ['part' => 'contentDetails', 'playlistId' => $uploadsListId, 'maxResults' => 50, 'pageToken' => $token]);

      // grab our pagination info
      $info = $playlistitems['pageInfo'];
      foreach (['nextPageToken', 'prevPageToken'] as $tokens) {
        if (isset($playlistitems[$tokens])) {
          $info[$tokens] = $playlistitems[$tokens];
        }

      }

      foreach ($playlistitems['items'] as $plitem) {
        $ids[] = $plitem['contentDetails']['videoId'];
      }

      $videos = $this->goo->api(
        'https://www.googleapis.com/youtube/v3/videos',
        ['part' => 'snippet,contentDetails', 'maxResults' => 50, 'id' => implode(',', $ids)]);

      // append a readable duration
      foreach ($videos['items'] as $index=>$video) {

        if ($video['contentDetails']['duration'] == 'PT0S') {
          unset($videos['items'][$index]);
          continue;
        }

        $videos['items'][$index]['contentDetails']['duration_readable'] =
          self::duration($video['contentDetails']['duration']);

      }

      return ['info' => $info, 'videos' => $videos];

    }

  }

  public function video($id,$scrape=false) {

    $params = ['part' => 'snippet,contentDetails,status,statistics', 'id' => $id];

    if ($scrape) {
      $params['key'] = G_APIKEY;
    }

    $videos = $this->goo->api('https://www.googleapis.com/youtube/v3/videos',$params);

    if (isset($videos['error'])) {
      $this->error = $videos['error'];
      return false;
    }

    if (count($videos['items']) > 0) {
      return $videos['items'][0];
    }

    if (isset($video['items'])) {
      return -1;
    }

    return false;

  }

  public static function duration($ytDuration) {

    $di = new DateInterval($ytDuration);
    $string = '';

    if ($di->h > 0) {
      $string .= $di->h.':';
    }

    return $string.$di->i.':'.sprintf('%02s',$di->s);
  }

}
