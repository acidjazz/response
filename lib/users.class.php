<?

class users {

  public static function get($params=[]) {

    $users = [];

    foreach (user::find($params) as $v) {
      $user = new user($v);
      $users[$user->id(true)] = $user->data();
    }

    return $users;

  }

  public function update($id, $key, $value) {

    if (!user::validId($id)) {
      $this->error = 'Invalid Mongo ID';
      return false;
    }

    $user = new user(New MongoId($id));

    if (!$user->exists()) {
      $this->error = 'User not found';
      return false;
    }

    switch ($value) {

      case 'true';
        $user->$key = true;
        break;
      case 'false';
        $user->$key = false;
        break;
      default :
        $user->$key = $value;
        break;
    }

    $user->save();

    return true;

  }

}
