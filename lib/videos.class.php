<?

class videos {

  public $error = 'Error not specified';
  public $total = false;
  public $count = false;
  public $user = false;
  public $query = false;
  public $skip = false;

  public $perpage = 10;

  public function __construct($user=false) {
    $this->user = $user;
  }

  public function get($query=[], $sort=[], $limit=50, $skip=0) {

    if ($limit == 1) {
      return (new video(video::findOne($query)))->data();
    }

    $all = video::find($query)->sort($sort);
    $this->total = $all->count();
    $cursor = $all->skip($skip)->limit($limit);
    $this->count = count($cursor);

    $videos = [];
    foreach ($cursor as $v) {
      $video = new video($v);
      $videos[$video->id(true)] = $video->data();
    }

    return $videos;

  }

  public function search($keyword) {

    $query['approved'] = true;

    $regex = new MongoRegex('/'.$keyword.'/i');

    $query['$and'][]['$or'] = [
      ['title'=> [ '$regex' => $regex]],
      ['description'=> [ '$regex' => $regex]]
    ];

    $total = video::find($query)->count();
    $results = $this->get($query, [], 4);

    return ['keyword' => $keyword, 'total' => $total, 'videos' => $results];

  }

  public function browse($p) {

    $query = ['approved' => true];
    $sort = ['stats.viewCount' => -1];
    $limit = $this->perpage;
    $skip = 0;

    $categories = [];
    if (isset($p['f']) && is_array($p['f'])) {
      $categories = $p['f'];
    } else {
      $p['f'] = [];
    }

    if (isset($p['c']) && is_array($p['c'])) {
      $categories = array_merge($p['f'], $p['c']);
    }

    if (count($categories) > 0) {
      $query['$and'][]['categories'] = ['$all' => $categories];
    }

    if (isset($p['s']) && isset($p['s'][0])) {

      switch ($p['s'][0]) {


        case 'New' :
          $sort = ['uploaded' => 1];
          break;

        case 'Most Viewed' :
        case 'Trending' :
          $sort = ['stats.viewCount' => -1];
          break;

        default: 
          $this->error = 'Invalid sorting parameter';
          return false;
          break;

      }

    }

    $skip = $p['p'] <= 1 ? 0 : $limit * ($p['p']-1);
    $this->skip = $skip;

    if (isset($p['k']) && is_array($p['k'])) {

      $regex = new MongoRegex('/'.$p['k'][0].'/i');

      $query['$and'][]['$or'] = [
        ['title'=> [ '$regex' => $regex]],
        ['description'=> [ '$regex' => $regex]]
      ];

    }

    $this->query = [$query, $sort, $limit, $skip];

    return $this->get($query, $sort, $limit, $skip);

  }

  public function getMontage() {
    $query = ['$and' => [
      ['montage' => ['$ne' => 0]],
      ['montage' => ['$exists' => true]]
    ]];
    return $this->get($query, ['montage' => 1]);
  }

  public function getRelated($args) {

    if (!youtube::validId($args['id']) ) {
      $this->error = 'Invalid YouTube ID';
      return false;
    }

    $id = $args['id'];
    $related = $filters = [];
    $video = $this->get(['id' => $id], [], 1);

    foreach( (new categories())->get(['main' => true]) as $filter) {
      $filters[] = $filter['name'];
    }

    foreach ($video['categories'] as $key=>$value) {
      if (!in_array($value, $filters)) {
        $related[] = $value;
      }
    }

    // linked categories that are not main (filters)
    if (count($video['categories']) > 0) {

      $results = $this->get(

        ['$and' => [
          ['id' => ['$ne' => $id]],
          ['approved' => true],
          ['categories' => ['$in' => $related]]
        ]],

        []);

      if ($this->count > 0) {
        shuffle($results);
        return array_splice($results, 0, 2);
      }
      
    }

    // TODO: linked tags and then eventuall the category -> tag relationship

    return [];

  }

  public function getTrending($id) {

    return $this->get(['approved' => true], ['stats.viewCount' => -1], 3);

  }

  // add a pulled youtube video
  public function import($id, $categories=[]) {

   if (count(self::get(['_userId' => $this->user->id(), 'id' => $id])) > 0) {
      $this->error = 'Video already added';
      return false;
    }

    $yt = new youtube($this->user->access_token);

    if (!$ytv = $yt->video($id)) {
      $this->error = 'Error retrieving video';
      return false;
    }

    if ($ytv['status']['privacyStatus'] == 'private') {
      $this->error = 'PRIVATE';
      return false;
    }

    $video = new video();

    // yt info
    $video->id = $ytv['id'];

    // snippet part
    $video->title = $ytv['snippet']['title'];
    $video->description = $ytv['snippet']['description'];
    $video->thumbnails = $ytv['snippet']['thumbnails'];
    $video->tags = $ytv['snippet']['tags'];
    $video->published = $ytv['snippet']['publishedAt'];

    // contentDetails part
    $video->duration = $ytv['contentDetails']['duration'];
    // hd or sd, sd videos have smaller thumbnails, hq you can hit maxresdefault.jpg
    $video->quality = $ytv['contentDetails']['definition'];

    // statistics part, cast to integer for proper sorting
    $stats = [];
    foreach ($ytv['statistics'] as $key=>$stat) {
      $stats[$key] = (int) $stat;
    } 
    $video->stats = $stats;


    // settings
    $video->approved = false;
    $video->featured = false;
    $video->problem = false;
    $video->solution = false;
    $video->categories = $categories;

    // user info
    $video->_userId = $this->user->id();

    $video->user = [
      'name' => $this->user->name,
      'picture' => $this->user->picture
    ];

    $video->save();

    return true;

  }

  public function update($id, $key, $value) {

    if (!video::validId($id)) {
      $this->error = 'Invalid Mongo ID';
      return false;
    }

    $video = new video(New MongoId($id));

    if (!$video->exists()) {
      $this->error = 'Video not found';
      return false;
    }

    switch ($value) {

      case 'true';
        $video->$key = true;
        break;
      case 'false';
        $video->$key = false;
        break;
      default :
        if (is_numeric($value)) {
          $video->$key = (int) $value;
        } else {
          $video->$key = $value;
        }
        break;
    }

    $video->save();

    return true;

  }

  public function addCategory($id, $category) {

    $video = new video($id);
    $category = new category($category);

    if (!$this->_existCheck($video, $category)) {
     return false;
    }

    $categories = $video->categories;

    if (!is_array($categories)) {
      $categories = [];
    }

    if (in_array($category->name, $categories)) {
      $this->error = 'Category arlready assigned';
      return false;
    }

    array_push($categories, $category->name);

    $video->categories = $categories;
    $video->save();

    return true;

  }

  public function removeCategory($id, $category) {

    $video = new video($id);
    $category = new category(category::findOne(['name' => $category]));

    if (!$this->_existCheck($video, $category)) {
     return false;
    }

    $categories = $video->categories;

    if (!is_array($categories) || !in_array($category->name, $categories)) {
      $this->error = 'Category not assigned';
      return false;
    }

    foreach ($categories as $k=>$v) {
      if ($v == $category->name) {
        unset($categories[$k]);
      }
    }

    $video->categories = array_values($categories);
    $video->save();

    return true;

  }

  private function _existCheck($video, $category) {

    if (!$video->exists()) {
      $this->error = 'Video not found';
      return false;
    }

    if (!$category->exists()) {
      $this->error = 'Category not found';
      return false;
    }

    return true;


  }

}
