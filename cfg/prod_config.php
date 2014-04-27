<?

// rsync command
// rsync -v -zar -e ssh -p --delete /Users/k/by/ ubuntu@256.sh:/var/www/by/

define('G_PATH', '/var/www/response/');
define('LIB_PATHS', G_PATH.'klib/,'.G_PATH.'mdl/,'.G_PATH.'ctl/,'.G_PATH.'lib/');
define('G_URL', 'http://response.com/');
define('G_DOMAIN', 'response.com');
define('G_STYLUS', false);

/* kdebug */
define('KDEBUG', false);
define('KDEBUG_HANDLER', false);
define('KDEBUG_EGPCS', false);

/* https://github.com/acidjazz/summon */
define('SUMMON_SECRET', 1234567890);

/* google/youtbe API v3 */
// response.com
define('G_CLIENT_ID', '874436849137-13pi2glah5j9t96o0d8iiq9bakv9q4kg.apps.googleusercontent.com');
define('G_SECRET', 'q0TyqAXce7y0X8fk1AVboVY');
define('G_REDIRECT_URI', G_URL.'auth/callback');
define('G_APIKEY', 'AIzaSyAlR7ooxOZZIcHK8kfxoFmr2sJm3fIxyl4');

/* mongo config */
define('MONGO_HOST','mongodb://localhost:27017/');
define('MONGO_DB','response');
//define('MONGO_REPLICA_SET', 'replica3');
define('MONGO_DEBUG', true);

/* jade */
define('JADE_PATH', '/usr/local/bin/jade');

/* ignore past this line */

/* set our include path(s) */
set_include_path(get_include_path().PATH_SEPARATOR.G_PATH);

/* autoload libs/classes in their specific folders */
spl_autoload_register(function($class) { 

	foreach (explode(',', LIB_PATHS) as $libdir) {
  	foreach (array('.class.php','.interface.php') as $file) {
			if (is_file($libdir.$class.$file)) {
				return require_once $libdir.$class.$file;
			}
		}
	}

	return false;

});

/* load our debuger if turned on */
if (defined('KDEBUG') && KDEBUG == true && php_sapi_name() != 'cli') {
	if (!defined('KDEBUG_JSON') || KDEBUG_JSON == false) {
		register_shutdown_function(array('kdebug', 'init'));
		if (defined('KDEBUG_HANDLER') && KDEBUG_HANDLER == true) {
			set_error_handler(array('kdebug', 'handler'), E_ALL);
		}
	}
}

/* debuger function wrappers */
function hpr() { return call_user_func_array(array('k','hpr'), func_get_args()); }
function cpr() { return call_user_func_array(array('k','cpr'), func_get_args()); }
function highlight() { return call_user_func_array(array('k','highlight'), func_get_args()); }
function xmlindent() { return call_user_func_array(array('k','xmlindent'), func_get_args()); }

/* post generated defines */
define('G_AUTHURL',(new google())->authURL());
