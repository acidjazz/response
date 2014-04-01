<?

class categories {

  // valid allowed characters
  const valid = '/^[a-z0-9 ]+$/i';

  public $total = false;
  public $error = false;
  public $data = false;

  private static function valid($string) {
    return preg_match(self::valid, $string);
  }

  public function add($name) {

    if (!self::valid($name)) {
      $this->error = 'Invalid name';
      return false;
    }

    if (category::findOne(['name' => $name]) != null) {
      $this->error = 'Category already exists';
      return false;
    }

    $category = new category();
    $category->name = trime($name);
    $category->main = false;
    $category->featured = false;
    $category->children = [];
    $category->save();

    return true;

  }

  public function addChild($id, $cid) {

    $category = new category($id);

    if (!$category->exists()) {
      $this->error = 'Category not found';
      return false;
    }

    $child = new category($cid);

    if (!$child->exists()) {
      $this->error = 'Child category not found';
      return false;
    }
    
    if ($child->main) {
      $this->error = 'Cannot make a main category a child';
      return false;
    }

    if ($child->_id == $category->_id) {
      $this->error = 'Cannot make your own category a child';
      return false;
    }

    $children = $category->children;

    if (!is_array($children)) {
      $children = [];
    }

    if (in_array($child->name, $children)) {
      $this->error = 'Child already exists';
      return false;
    }

    array_push($children, $child->name);

    $category->children = $children;
    $category->save();

    return true;

  }

  public function removeChild($id, $child) {

    if (!self::valid($child)) {
      $this->error = 'Invalid characters';
      return false;
    }

    $category = new category($id);

    if (!$category->exists()) {
      $this->error = 'Category not found';
      return false;
    }

    $children = $category->children;

    if (!in_array($child, $children)) {
      $this->error = 'Child not found';
      return false;
    }

    foreach ($children as $k=>$v) {
      if ($v == $child) {
        unset($children[$k]);
      }
    }

    $category->children = array_values($children);
    $category->save();

    return true;

  }

  public function orderChild($id, $child, $direction) {

    $category = new category($id);

    if (!$category->exists()) {
      $this->error = 'Category not found';
      return false;
    }

    $children = $category->children;

    if (!in_array($child, $children)) {
      $this->error = 'Child not found';
      return false;
    }

    $index = array_search($child, $children);

    $operator = $direction == 'right' ? 1 : -1;

    if ($direction == 'right') {

      if (!isset($children[$index+1])) {
        $operator = false;
        unset($children[$index]);
        array_unshift($children, $child);
      } 

    }

    if ($direction == 'left') {

      if ($index == 0) {
        $operator = false;
        unset($children[$index]);
        array_push($children, $child);
      }

    }

    if ($operator !== false) {
      $swapper = $children[$index+$operator];
      $children[$index+$operator] = $child;
      $children[$index] = $swapper;
    }

    $category->children = array_values($children);
    $category->save();

    return true;

  }

  public function delete($id) {


    $category = new category($id);

    if (!$category->exists()) {
      $this->error = 'Category not found';
      return false;
    }

    // first remove it from every video
    $videos = new videos();

    foreach ($this->get(['categories' => ['$in' => [$category->name]]]) as $id=>$video) {
      if (!$videos->removeCategory($id, $category->name)) {
        $this->error = $videos->error;
        return false;
      }
    }

    // now remove any existing children
    $cats = new categories();

    foreach (categories::get(['children' => ['$in' => [$category->name]]]) as $id=>$cat) {
      if (!$cats->removeChild($id, $category->name)) {
        $this->error = $cats->error;
        return false;
      }
    }

    $category->remove();
    return true;

  }

  public function update($id, $key, $value) {

    $category = new category($id);

    if (!$category->exists()) {
      $this->error = 'Category not found';
      return false;
    }

    switch ($value) {

      case 'true';
        $category->$key = true;
        break;
      case 'false';
        $category->$key = false;
        break;
      default :
        $category->$key = $value;
        break;
    }

    $category->save();

    return true;

  }

  public function get($query=[], $sort=[], $limit=0, $skip=0) {

    $all = category::find($query)->sort($sort);
    $this->total = $all->count();
    $cursor = $all->skip($skip)->limit($limit);
    $this->count = count($cursor);

    $categories = [];

    foreach ($cursor as $c) {
      $category = new category($c);
      $categories[$category->id(true)] = $category->data();
    }

    return $categories;

  }

}
