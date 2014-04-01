<?

class index_ctl {

  public $user = false;

  public function __construct() {

    if ($user = user::loggedIn()) {
      $this->user = $user->data();
    } 

  }

  public function index() {
    return jade::c('index', ['user' => $this->user]);
  }

  public function __call($method, $args) {

  }

}
