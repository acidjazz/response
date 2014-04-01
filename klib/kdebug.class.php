<?

class kdebug {
	
	private static $errors = null;
	private static $codewindow = 10;

	public function __construct() {

    if (defined('KDEBUG_JSON') && KDEBUG_JSON == true) {
      exit;
    }

    if (defined('KDEBUG_EGPCS') && KDEBUG_EGPCS != false && $this->egpcs_set() || self::$errors != null) {
		  echo $this->headers();
    }

    if (defined('KDEBUG_EGPCS') && KDEBUG_EGPCS != false) {
		  echo $this->egpcs();
    }

    if (defined('KDEBUG_SQL') && KDEBUG_SQL != false) {
		  echo $this->database(kdb::$debug);
    }

    if (self::$errors != null) {
		  echo self::$errors;
      echo "</div>";
    }

	}

	public static function init() {
		new kdebug;
	}

	private function headers() {

		$sql_border =  '#7f9948';
		$sql_background = '#a6c85e';
		$sql_hover = '#3665b3';

    $error_bg = '#fb8d8d';
    $error_border = '#c06c6c';
    $error_hover = '#b33636';

    $egpcs_border = '#6c99c0';
		$egpcs_bg = '#8dc8fb';
		$egpcs_hover = '#3665b3';

		$black_border = '#cdcdcd';
		$black_background = '#fff';
		$black_alter = '#efefef';

		$code_error = '#f9fbbc';

		$curve = 3; 

		return <<<HTML

<style type="text/css">

.clear {
	clear: both;
	display: block;
	overflow: hidden;
	visibility: hidden;
	width: 0;
	height: 0;
}

.kdebug_container {
	font-family: 'lucida grande', tahoma, verdana, arial, sans-serif;
	font-size: 13px;
	color: #333;
  cursor: pointer;
  box-shadow: 0px 0px 2px 0px rgba(0, 0, 0, 0.5);
  margin: 3px 0;
}

.kdebug_main_container {
  margin: 5px;
}

.kdebug_top {
  position: absolute;
  width: 98%;
  top: 10px;
}

.kdebug_sql {
	border: 1px solid $sql_border;
}

.kdebug_db_title {
  padding: 10px;
	background-color: $sql_background;
}

.kdebug_egpcs {
	background-color: $egpcs_bg;
  border: 1px solid $egpcs_border;
}

.kdebug_error {
	background-color: $error_bg;
  border: 1px solid $error_border;
}

.kdebug_egpcs, .kdebug_error {
}

.kdebug_error .kdebug_handler_title:hover { 
  background-color: $error_hover; 
  color: #fff;
}

.kdebug_egpcs .kdebug_vars_title {
  border-bottom: 1px solid $egpcs_border;
}

.kdebug_egpcs .kdebug_vars_title:hover { 
  background-color: $egpcs_hover; 
  color: #fff;
}

.kdebug_sql .kdebug_db_title:hover { 
  background-color: $sql_hover; 
  color: #fff;
}

.kdebug_handler_title,.kdebug_vars_title {
	padding: 10px;
}

.kdebug_query_summary {
  padding: 10px;
  border-bottom: 1px solid #777;
}

.kdebug_query_summary:hover {
  background-color: #efefef;
}

.kdebug_rows, .kdebug_db_queries {
	display: none;
}

.kdebug_rows {
  overflow-x: auto;
	background-color: #dfdfdf;
}

.kdebug_db_queries {
  background-color: #fff;
}

.kdebug_rows table {
  margin: 5px 5px 5px 20px;
	border: 1px solid $sql_border;
}

.kdebug_rows th, .kdebug_rows td {
	font-size: 12px;
	padding: 2px 10px 2px 10px;
  max-width: 100%;
  overflow: hidden;
}

.kdebug_rows th {
	color: #fff;
	background-color: $sql_border;
}

.kdebug_rows_alter {
	background-color: #f4f4f4;
}
.kdebug_rows_inner {
	background-color: #fff;
}

.kdebug_rows div {
	padding: 10px 0 10px 20px;
  overflow: wrap;
}

.kdebug_right {
	float: right;
	font-size: 13px;
}

.kdebug_code {
	border-left: 1px solid $error_bg;
	border-right: 1px solid $error_bg;
	border-bottom: 1px solid $error_bg;
	display: none;
}

.kdebug_code ul {
	list-style-type: none;
	margin: 0;
	padding: 0;
}

.kdebug_code li {
	border-bottom: 1px solid #efefef;
	padding: 2px;
  font-size: 14px;
  font-family: Fixed, monospace;
}

.kdebug_code_alter {
	background-color: #f4f4f4;
}

.kdebug_code_error {
	background-color: $code_error; 
}

.kdebug_code_line {
	background-color: #fff;
}

.kdebug_code_error {
  font-weight: bold;
}

.kdebug_code_linenum {
	float: left;
	width: 30px;
	text-align: right;
	padding-right: 5px;
	color: #777;
}


.kdebug_vars_title {
	padding: 10px;
}

.kdebug_key {
	text-align: right;
	padding-right: 5px;
	margin-right: 5px;
	overflow: hidden;
	float: left;
	border-right: 1px dotted $black_border;
  color: #333;
}

.kdebug_key_more {
  font-weight: bold;
}

.kdebug_var {
	background-color: $black_background;
  padding: 10px 20px;
	margin: 0 0 0 5px;
  color: #333;
}

.kdebug_var:hover {
  background-color: #c9e3f9;

}

.kdebug_vars {
	display: none;
  padding-left: 2px;
}

.kdebug_vars_first {
	margin: 0px 5px 5px 5px;
}

.kdebug_var_alter {
	background-color: $black_alter;
}

.kdebug_black_over {
	background-color: $code_error;
	cursor: pointer;
}

.kdebug_var_top { border-top: 1px solid $black_border; }
.kdebug_var_bottom { border-bottom: 1px solid $black_border; }

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

</style>


<script type="text/javascript">

(function() {

var jQuery;

if (window.jQuery === undefined || window.jQuery.fn.jquery !== '1.4.2') {
    var script_tag = document.createElement('script');
    script_tag.setAttribute("type","text/javascript");
    script_tag.setAttribute("src",
    	"//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js");
    script_tag.onload = scriptLoadHandler;
    script_tag.onreadystatechange = function () { // same thing but for IE
    	if (this.readyState == 'complete' || this.readyState == 'loaded') {
            scriptLoadHandler();
        }
    };
    (document.getElementsByTagName("head")[0] || document.documentElement).appendChild(script_tag);
} else {
    jQuery = window.jQuery;
    main();
}

function scriptLoadHandler() {
    jQuery = window.jQuery.noConflict(true);
    main(); 
}

function main() { 
  jQuery(document).ready(function($) { 

    $('.kdebug_db_query').hover(function(event) { $(this).toggleClass('kdebug_over_blue'); });
    $('.kdebug_db_query').click(function(event) { $(this).next('.kdebug_rows').toggle(100); });
    $('.kdebug_db_title').hover(function(event) { $(this).toggleClass('kdebug_over_blue'); });
    $('.kdebug_db_title').click(function(event) { $(this).next('.kdebug_db_queries').toggle(100); });

    $('.kdebug_handler_title').click(function(event) { $(this).next('.kdebug_code').toggle(0); });
    $('.kdebug_vars_title, .kdebug_array').click(function(event) { $(this).next('.kdebug_vars ').toggle(0); });
    $(document).keydown(function(e) {

      if (e.ctrlKey && e.altKey && e.keyCode == 75) {
        $('.kdebug_main_container').toggleClass('kdebug_top');
      }

    });
  });
}

})(); 

</script>
<div class="kdebug_main_container">
HTML;


	}

	public static function handler($errno, $errstr, $errfile, $errline) {

	$errortype = array(
		E_ERROR							=> 'Error',
		E_WARNING						=> 'Warning',
		E_PARSE							=> 'Parsing Error',
		E_NOTICE        	  => 'Notice',
		E_CORE_ERROR				=> 'Core Error',
		E_CORE_WARNING			=> 'Core Warning',
		E_COMPILE_ERROR			=> 'Compile Error',
		E_COMPILE_WARNING		=> 'Compile Warning',
		E_USER_ERROR				=> 'User Error',
		E_USER_WARNING			=> 'User Warning',
		E_USER_NOTICE  		  => 'User Notice',
		E_STRICT        	  => 'Runtime Notice',
		E_RECOVERABLE_ERROR => 'Recoverable Error',
		E_DEPRECATED				=> 'Deprecated',
		E_USER_DEPRECATED		=> 'User Deprecated',
		420									=> 'KDB'
	);

  if ($errfile != 'Unknown') {
	  $code = explode('<br />', highlight_file($errfile, true));
  } else {
    $code = array();
  }

	self::$errors .= <<<HTML

<div class="kdebug_container kdebug_error">
	<div class="kdebug_handler_title">
		<div class="kdebug_right">$errfile:$errline</div>
		<b>{$errortype[$errno]}</b>: $errstr
	</div>
	<div class="kdebug_code">
		<ul>
HTML;

	for ($i = (($errline-self::$codewindow < 1) ? 1 : $errline-self::$codewindow); $i != $errline+self::$codewindow; $i++) {
		$linecolor = (($i == $errline) ? 'error' : (($i%2) ? 'alter' : 'line'));
		if (isset($code[$i])) {
			self::$errors .= <<<HTML
			<li class="kdebug_code_{$linecolor}"><div class="kdebug_code_linenum">$i</div>&nbsp;{$code[$i-1]}</li>
HTML;
		}
	}

	self::$errors .= <<<HTML
		</ul>
	</div>
</div>

HTML;

	}

	private function database($conns) {

		$return = null;

    if (!empty($conns) && count($conns) > 0) {
		foreach ($conns as $key=>$conn) {
			$total_runtime = round($conn['total_runtime'], 4);

			$return .= <<<HTML
<div class="kdebug_container kdebug_sql">

	<div class="kdebug_db_title" title="{$conn['stats']}"><u>{$key}</u> {$conn['query_count']} queries in $total_runtime seconds</div>

	<div class="kdebug_db_queries">
HTML;

			foreach ($conn['queries'] as $query=>$data) {
        if (strlen($query) < 5000) {
  				$query = highlight($query, 'sql', true);
        } else {
  				$query = highlight(substr($query, 0, 1000).'.. ( truncated '.number_format(strlen($query)).' characters )', 'sql', true);
        }
        if (isset($data['runtime'])) {
				  $runtime = round($data['runtime'], 4);
        } else {
				  $runtime = 0;
        }
				$return .= <<<HTML
	<div class="kdebug_db_query">
		<div class="kdebug_right">{$data['rows']} returned {$data['affected']} affected in $runtime seconds</div>
		<div class="kdebug_query_summary">{$query}</div>
	</div>
	<div class="kdebug_rows">
    <div>{$query}</div>
HTML;

				if ($data['rows'] > 0) {
					$return .= <<<HTML
			<table border="0" cellspacing="0" cellpadding="0">
				<tr>
HTML;
				}
				if (isset($data['data'][0])) {
          foreach (array_keys($data['data'][0]) as $field) {
            $return .= "<th>$field</th>";
          }
				$return .= "</tr>";
        }

				foreach ($data['data'] as $key=>$row) {
					$return .= (($key%2) ? '<tr class="kdebug_rows_inner">' : '<tr class="kdebug_rows_alter">');
					foreach ($row as $name=>$value) {

            if (strlen($value) > 1000) {
						  $return .= '<td>'.substr($value, 0, 100).'.. (truncated '.number_format(strlen($value)).' characters ) </td>';
            } else {
						  $return .= "<td>$value</td>";
            }

					}
					$return .= "</tr>";
				}


				if ($data['rows'] > 0) {
					$return .= '</table>';
				}
				$return .= '</div>';
			}

			$return .= '</div>';
		}
    }
			$return .= '</div>';


		return $return;
	
	}

  private function egpcs_globals() {

	  global $_OTHER;

		$superglobals = array(
			'$_ENV' => &$_ENV,
			'$_GET' => &$_GET,
			'$_POST' => &$_POST,
			'$_COOKIE' => &$_COOKIE,
			'$_SESSION' => &$_SESSION,
			'$_FILES' => &$_FILES,
			'$_OTHER' => &$_OTHER
		);

    return $superglobals;

  }

  private function egpcs_set() {

    foreach ($this->egpcs_globals() as $key=>$value) {

      if (is_array($value) && count($value) > 0) {
        return true;
      }

      if (!is_array($value) && !empty($value)) {
        return true;
      }

    }

    return false;
  }

	public function egpcs() {

    $superglobals = $this->egpcs_globals();
	
		$return = <<<HTML
	<div class="kdebug_container kdebug_egpcs">
HTML;
		$found = false;

		foreach ($superglobals as $key=>$value) {

			if (!empty($value)) {

				$found = 1;

				$dimlen_format = $this->array_dimlen_format($value);

				$return .= <<<HTML
		<div class="kdebug_vars_title">
			{$key} {$dimlen_format}
		</div>
		<div class="kdebug_vars kdebug_vars_first">
HTML;

			
				$return .= $this->array_tree($value, true).'</div>';
			}
		}

		$return .= <<<HTML
		<!--</div>-->
	</div>
HTML;

		if ($found) {
			return $return;
		}
		return false;

	}

	private function array_tree($array, $top=false) {

		$count = count($array);
		$first = true;
    $return = '';
		foreach ($array as $key=>$value) {

			$count--;

			$return .= '<div class="kdebug_var'.
				($top ? ' kdebug_var_top' : '').
	//			(!$count ? ' kdebug_var_bottom' : '').
				(is_array($value) ? ' kdebug_array' : '').
				($count%2 ? ' kdebug_var_alter' : '').'">';

			$first = $top = false;

			if (is_array($value)) {
				$return .= "<div class=\"kdebug_key kdebug_key_more\" title=\"$key\"> $key </div>";
				$return .= $this->array_dimlen_format($value).'</div>';
				$return .= '<div class="kdebug_vars">'.$this->array_tree($value, false).'</div>';
			} else {
        if (strlen($value) < 1) {
				  $return .= "<div class=\"kdebug_key\" title=\"$key\"> $key </div>(empty)</div>";
        } else {
				  $return .= "<div class=\"kdebug_key\" title=\"$key\"> $key </div>$value</div>";
        }
			}

			$return .= '<div class="clear">&nbsp;</div>';
		}

		return $return;

	}

	private static function array_dimlen_format($array) {
		list($count, $elements) = self::array_dimlen($array);
		return "($count dimension".($count == 1  ? '' : 's').", $elements element".($elements == 1 ? '' : 's').")";
	}


	private static function array_dimlen($array,$count=0,$elements=false) {

		if (is_array($array)) {

			$elements += count($array);

			foreach ($array as $key=>$value) {
				if (is_array($value)) {
					list($count, $elements) = self::array_dimlen($value, $count, $elements);
				}
			}

		}

		return array(++$count, $elements);

	}

	public static function array_generate($array=null) {

		$elements = rand(4,10);

		for ($i=0; $i != $elements; $i++) {

			list($dimlen) = self::array_dimlen($array);
			if ($array != null && $dimlen > 3) {
				return $array;
			}

			$array[self::string_generate(true)] = self::string_generate();

			if (!rand(0,$elements)) {
				$array[self::string_generate(true)] = self::array_generate();
				continue;
			}


		}

		return $array;

	}

	public static function string_generate($short=false) {

		$chars = rand(2,15);
		if ($short) {
			$chars = rand(2,8);
		}

		$string = null;

		if (rand(0,1)) {
			
			for ($i = 0; $i != $chars; $i++) {
				$string .= chr(rand(97,122));
			}

			return (string) $string;

		} else {

			for ($i = 0; $i != $chars; $i++) {
				$string .= rand(0,9);
			}

			return ($string + 1);

		}

	}
	private function str_format($string) {

		$return = null;

		foreach (explode('  ', $string) as $key=>$value) {
			$values = explode(':', $value);
			$return .= $values[0] . '<span class="kdebug_grey">:</span><b>' . number_format(trim($values[1])).'</b> ';
		}

		return $return;

	}

}
