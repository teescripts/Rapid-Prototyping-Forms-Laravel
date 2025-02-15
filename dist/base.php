<?php
namespace Teescripts\RptForms;

use Illuminate\Support\Facades\DB;
use Teescripts\RptForms\init;

class base extends init {
	function __construct() {
		parent::__construct();
	}
	

	public function pluginConnect() {
		$array_users=[[
			$this->isConst("DB_USER", "root"), 
			$this->isConst("DB_PASS", "root")
		]];
		
		$array_db	=[
			"type"=>$this->isConst("DB_TYPE", "mysql"), 
			"host"=>$this->isConst("DB_HOST", "localhost"), 
			"name"=>$this->isConst("DB_NAME"), 
			"persist"=>true
		];
		$connect	=$this->connecter($array_db, $array_users);#
		return $connect;
	}

	static function load($class="", $method="", $array="") {
		if ($class=="") $class="main";
		if (!is_object($class)) {
			$chip	=$class;
			if (!is_array($class)) {
				$chip	=[$class];
				if (strstr($class, "/")) $chip=explode("/", $class);
			}

			$count	=count($chip);
			if ($count>=1) $class=array_shift($chip);
			if ($count>=2) $method=array_shift($chip);
			if ($count>=3) $array=$chip;

		}
		if (strstr($method, "/")) {
			$chip	=explode("/", $method);
			$count	=count($chip);
			if ($count>=1) $method=array_shift($chip);
			if ($count>=2) $array=$chip;
		}
		if (!is_array($array)) {
			if (strstr($array, "/")) {
				$array	=explode("/", $array);
			}
			elseif (strstr($array, ",")) {
				$array	=explode(",", $array);
			}
			elseif (strstr($array, "[")||strstr($array, "{")) {
				$array	=json_decode($array, true);
			}
			else {
				$array	=[$array];
			}
		}

		$object	="";//$this->varKey("ts_{$class}");
		if (!is_object($object)||!isset($object)||empty($object)) {
			$class_name	="Teescripts\RptForms\\".$class;
			$object	=new $class_name();
		}
		
		if (is_object($object)) {
			$GLOBALS["ts_{$class}"]	=$object;

			if ($method) {
				if ($array) {
					$object	=call_user_func_array([$object, $method], $array);
				}
				else {
					$object	=call_user_func($method, $object);
				}
			}
			return $object;
		}
	}
	
	public function prefix($text) {
		$prefix1	=$this->constKey("app.ext.prefix1", "tee_");
		$prefix2	=$this->constKey("app.ext.prefix2");
		$prefix3	=$this->constKey("app.ext.prefix3", $prefix2);
		
		$text	=str_replace("#_", $prefix2, $text);
		$text	=str_replace("#t_", $prefix1, $text);
		$text	=str_replace("#1_", $prefix2, $text);
		$text	=str_replace("#2_", $prefix3, $text);
		$text	=str_replace("#3_", $prefix1, $text);
		$text	=str_replace("`#", "`{$prefix2}", $text);
		return $text;
	}
	
	public function query($query_var, $array_bind=[], $type="", $format="", $reconnect="") {
		$query_text	=$query_var;
		if (is_array($query_var)) {
			# if query includes bound values
			$array_key1	=$this->arrayKey(0, $query_var);
			$array_key2	=$this->arrayKey(1, $query_var, $array_bind);
			if (!is_array($array_key1) && is_array($array_key2)) {
				$query_text	=$array_key1;
				$array_bind	=$array_key2;
			}
		}

		if ($query_text && !is_array($query_text)) {

			$bound	=[];
			if (is_array($array_bind) && $array_bind) {
				foreach ($array_bind as $field=>$value) {
					$nvalue	=$value;
					if (is_array($value)) $nvalue=implode(",", $value);

					$bound[$field]	=$nvalue;
				}
			}
		
			$query_text	=$this->prefix($query_text);
			
			$lower	=trim($query_text);
			$lower	=strtolower($lower);
			$lower	=preg_replace("/\s\s+/i", " ", $lower);
			$array	=["insert into", "update", "delete", "replace into"];

			$select	=0;
			foreach ($array as $query) {
				$match	=stristr(".{$lower}", ".{$query} ");
				if ($match) $select++;
			}

			if ($select==0) {
				if (!$type) $type=3;
				$result	=DB::select($query_text, $bound);#statement, select, unprepared
					
				$results	=array_map(function($item){
					return (array) $item;
				}, $result);
			}
			elseif (stristr(".{$lower}", ".update ")) {
				$results	=true;
			}
			elseif (stristr(".{$lower}", ".insert into ")) {
				$lastid	=DB::insert($query_text)->insertGetId($bound);
				$results	=$lastid;
			}
			
			return $results;
		}
		else {
			#print_r($query);
		}
	}
	
	
	public function exec($query) {
		if (is_array($query)) {
			$nquery	=$this->arrayKey(0, $query);
			$bind	=$this->arrayKey(1, $query, []);
		}
		else {
			$nquery	=$query;
			$bind	=[];
		}
		
		$connect=$this->pluginConnect();
		$nquery	=$this->prefix($nquery);
		$result	=$connect->prepare($nquery);

		if (is_array($bind)) {
			foreach ($bind as $field=>$value) {
				$nvalue	=$value;
				if (is_array($value)) {
					$nvalue	=implode(",", $value);
				}
				$result->bindValue(":{$field}", $nvalue);
			}
		}
		
		try {
			$result->execute();
			throw new PDOException();
		}
		catch (PDOException $handle) {
			$error	=$this->errorMsg($result, "html", $nquery, $handle);
			echo $this->arrayKey("error", $result);
		}

		if ($error) $result=$error;
			
		$connect	=null;
		return $result;
	}
	
	function execute($query, $html="", $array_bind=[]) {
		if ($query) {
			$result	=$this->query($query, $array_bind, "", $html);
			if ($result) {
				$return	=$this->lang("success");
				if ($html=="hide") $return="";
				if ($html=="html") $return=$this->msg(1, $return);	
			}
			else {
				if (is_array($query)) $query=$this->arrayKey(0, $query);
				$reference	=$this->abbr($query, " ", 2);
				$return	=$this->varKey("error_{$reference}");
			}
			echo $return;
		}
	}
	
	public function result($query, $bind="") {
		if ($query) {
			if (is_array($query)) {
				$nquery	=$this->arrayKey(0, $query);
				$bind	=$this->arrayKey(1, $query, $bind);
			}
			else {
				$nquery	=$query;
			}
			if (!stristr($nquery, " LIMIT ")) $nquery=$nquery." LIMIT 0, 1";
			$result	=$this->query($nquery, $bind);
			if (is_array($result)) $result=$this->arrayKey(0, $result, []);
			
			return $result;
		}
	}
	
	public function view($query, $bind="", $join=" ", $format="") {
		if ($query) {
			$response	="";
			$result	=$this->result($query, $bind);
			if (is_array($result)) {
				if ($result) {
					$return	=[];
					foreach ($result as $key=>$value) {
						$nvalue	=htmlspecialchars_decode($value);
						$return[]	=$nvalue;
					}
					$response	=implode($join, $return);
				}
			}
			else {
				$response	=$result;
			}
			if (!$response) {
			}
			return $response;
		}
	}
	
	public function errorMsg($handle, $format="text", $query="", $except="") {
		$text	="";
		if ($handle) {
			$array	=$handle->errorInfo();
			$error	=$this->arrayKey(0, $array);
			$type	=$this->arrayKey(1, $error);
			$text	=$this->arrayKey(2, $array);
		}

		$result	=[];
		if ($text) {
			$text	=strtolower($text);
			$text	=str_replace("_", " ", $text);
			$text	=str_replace("duplicate entry", "Repeated value ", $text);
			$text	=str_replace("for key", " for field: ", $text);
			$text	=str_replace("null", "is empty", $text);
			$text	=str_replace("in 'field list'", "in table", $text);
			$text	=str_replace("column", "field", $text);
			$text	=str_replace("cannot", "should never", $text);
			$text	=str_replace("mysql server", "the server", $text);
			$text	=str_replace("has gone away", "froze", $text);
			$text	=str_replace("doesn't match", "!=", $text);
			$text	=str_replace("you have an error in your sql syntax; check the manual that corresponds to your the page version for the right syntax to use", "Incorrect syntax", $text);
			$text	=str_replace("at line 1", "", $text);

			$html	=$text;
			if (!stristr($_SERVER["HTTP_HOST"], "local")) $html='Whew! The data operation failed.';
			
			if ($format=="html") {
				$icon	='<i class="far fa-grin-beam-sweat fa-1x"></i> ';#meh, frown, grimace, grin-beam-sweat
				$html	=$icon."OOps: ".$text.'<hr />'.$query;
				$html	=$this->msg(4, $html);
			}

			$trace	=$except->getTrace();
			$main	=$this->arrayKey(0, $trace);
			$file	=$this->arrayKey("file", $main);
			$line	=$this->arrayKey("line", $main);

			if ($main) $text.=" on line: {$line} in {$file}";

			trigger_error("{$text}: {$query}", E_USER_ERROR);

			$reference	=$this->abbr($text, " ", 2);
			$GLOBALS["error_{$reference}"]	=$error;
			$result	=["error"=>$html, "query"=>$query];
		}
		return $result;
	}
	
	public function msgFormat($array="", $info="", $list="") {
		# globals
		$text_global	='alert_class,alert_others,closeable,base_assets';
		$global	=$this->globalVars($text_global);
		extract($global);
		# array
		$icons	=[1=>"check-circle", 2=>"info-circle", 3=>"warning", 4=>"times-circle"];
		$images	=[1=>"success", 2=>"info", 3=>"warning", 4=>"error"];
		$classes=[1=>"success", 2=>"info", 3=>"warning", 4=>"danger"];
		
		if (is_array($array)) {
			$class	=$this->arrayKey(0, $array, 2);
			$text	=$this->arrayKey(1, $array, $info);
		}
		else {
			$class	=$array;	
			$text	=$info;
		}

		$icon	=$this->arrayKey($class, $icons);
		$image	=$this->arrayKey($class, $images);

		$icon_image	=$this->fileImage("alert_{$image}.png", $base_assets."images/script-icons/", 1);

		$alert_icon	="";
		if ($icon_image) $alert_icon='<img src="'.$icon_image.'" class=" mr-1" alt="'.$image.'" /> ';
		if ($icon) $alert_icon='<i class="fas fa-'.$icon.' fa-1x label-icon"></i> ';

		$alert_text	="{$alert_icon}{$text}";
		
		if ($list) {
			$alert_text	=str_replace(";", "</li><li>", $alert_text);
			$alert_text	="<ul><li>".$text."</li></ul>";
		}
		if ($closeable&&in_array($class, [1, 3, 4])) $alert_text.=$closeable;#
		
		if ($alert_class) $classes=$alert_class;
		$alert_css	=$this->arrayKey($class, $classes, $class);
		if (!$alert_class) $alert_css="alert alert-{$alert_css}";
		if ($alert_others) $alert_css.=" {$alert_others}";

		$result	=["class"=>$alert_css, "text"=>$alert_text];
		return $result;
	}
	
	public function msg($array="", $info="", $list="") {
		$result	=$this->msgFormat($array, $info, $list);
		$text	=$this->arrayKey("text", $result);
		$class	=$this->arrayKey("class", $result);
		$html	='<div class="'.$class.'" role="alert">'.$text."</div>";
		return $html;
	}
	
	public function msgSpan($array="", $info="", $list="") {
		$result	=$this->msgFormat($array, $info, $list);
		$text	=$this->arrayKey("text", $result);
		$class	=$this->arrayKey("class", $result);
		$class	=$this->textMsg($class, 1);
		$html	='<span class="'.$class.'">'.$text."</span>";
		return $html;
	}
	
	public function msgText($array="", $info="", $list="", $span="") {	
		$result	=$this->msgFormat($array, $info, $list);
		$text	=$this->arrayKey("text", $result);
		$class	=$this->arrayKey("class", $result);
		$class	=$this->textMsg($class, $span);
		$text	=str_replace(' label-icon', $class, $text);
		return $text;
	}

	function textMsg($text, $span="") {
		$text	=str_replace('alert-soft-', 'text-', $text);
		$text	=str_replace('alert-', 'text-', $text);
		$text	=str_replace('alert ', 'tx-light mg-5 ', $text);
		if ($span) $text=str_replace('div', 'span', $text);
		return $text;
	}

	public function isJson($value) {
		$open	=(strstr(".{$value}", '.[')&&strstr("{$value}.", '].'));
		$close	=(strstr(".{$value}", '.{"')&&strstr("{$value}.", '}.'));
		$json	=($open||$close);
		return $json;
	}
	
	public function text($text, $type=1) {
		return $this->textNorm($text, $type, 2);
	}
	
	public function textNorm($text, $type=1, $lower=1) {
		$ntext	=strip_tags($text);
		$ntext	=trim($ntext);
		if ($type==1) $ntext=htmlspecialchars($ntext, ENT_HTML5, "", false);
		if ($type==2) $ntext=urlencode($ntext);
		if ($lower==1) $ntext=strtolower($ntext);
		$text	=preg_replace("/[\\()\[\]\-\+\=\/\"\?_.#,`;:&%']+/i", " ", $ntext);
		$text	=preg_replace("/\s\s+/i", " ", $text);
		$text	=trim($text);
		$text	=str_replace(" ", "_", $text);
		return $text;
	}
	
	public function abbr($text, $space=" ", $case=1) {
		$abbr	="";
		$text=preg_replace("/[()\[\]\-%!'`_.=:;,]+/i", " ", $text);
		$text=preg_replace("/\s\s+/i", " ", $text);
		$words	=explode($space, $text);
		foreach ($words as $word) {
			$txt	=trim($word);
			$txt	=substr($txt, 0, 1);
			if ($case==1) $txt=strtoupper($txt);
			if ($case==2) $txt=strtolower($txt);
			$abbr	.=$txt;
		}
		return $abbr;
	}
	
	public function letters($text, $length="") {
		$length	=($length)?$length:45;
		$strip	=strip_tags($text);
		$ntext	=preg_replace("/\s\s+/", " ", $strip);
		$ntext	=html_entity_decode($ntext);
		$ntext	=trim($ntext);
		$ntext	=strlen($ntext);
		$ntext	=substr($ntext, 0, $length);
		
		$text	=($strlen>=$length)?$ntext.'&hellip;':$strip;
		
		return $text;
	}
	
	public function words($text, $count="", $tags="") {
		$count	=($count)?$count:10;
		$ntext	=str_replace("\n", ", ", $text);
		$ntext	=str_replace("<br>", ", ", $text);
		$ntext	=preg_replace("/\s\s+/", " ", $ntext);
		$ntext	=trim($ntext);
		$ntext	=strip_tags($ntext, $tags);
		
		$words	=explode(" ", $ntext);
		$chunk	=array_chunk($words, $count);
		$text	=implode(" ", $chunk[0]);
		$text	.=(count($words)>$count)?"...":"";
		return $text;
	}
	
	public function chip($text="", $exploder=".", $key="array") {
		return $this->txt($exploder, $text, $key);
	}
	
	public function parts($text="", $exploder=".", $key="array") {
		return $this->txt($exploder, $text, $key);
	}
	
	public function txt($exploder=".", $text="", $key="array") {
		if ($text) {
			$bits	=$text;
			if (!is_array($text)) $bits=explode($exploder, $text);
			$count	=count($bits);	
			$lkey	=$count - 1;	
			$first	=$this->arrayKey(0, $bits);	
			$last	=$this->arrayKey($lkey, $bits);
			$ext	=$last;
			if ($text==$ext) $ext="";
			$name	=str_replace($exploder.$ext, "", $text);
			$array	=["name"=>$name, "first"=>$first, "ext"=>$ext, "count"=>$count, "last"=>$last];
			$array	=array_merge($array, $bits);
			$array["array"]	=$array;
			if ($key!=="") $result=$this->arrayKey($key, $array);
			return $result;
		}
	}
	
	
	function prural($count, $name, $alt="") {
		$size	=strlen($name);
		$last	=$name[($size - 1)];

		$one	=rtrim($name, "s");
		$many	="{$name}s";

		if ($last=="s") {
			$many	=$name;
		}
		else {
			$one	=$name;
		}

		if ($alt) {
			$size	=strlen($alt);
			$last	=$alt[($size - 1)];

			if ($last=="s") {
				$many	=$alt;
			}
			else {
				$one	=$alt;
			}
		}
		
		$result	=$many;
		if ($count==1) $result=$one;
		
		return $result;
	}
	
	function wordSpace($text) {
		$array	=str_split($text);
		$text	="";
		foreach ($array as $letter) {
			if ($letter==strtoupper($letter)) $text.=" ";
			$text	.=$letter;			
		}
		$text	=str_replace("_", " ", $text);
		$text	=str_replace(".", " ", $text);
		$text	=preg_replace("/\s\s+/", " ", $text);
		return trim($text);
	}
	
	public function textArray($text) {
		$text	=$this->wordSpace($text);
		return explode(" ", $text);
	}
	
	function casing($text, $type="") {
		$ntext	=$this->wordSpace($text);
		$words	=explode(" ", $ntext);
		$first	=array_shift($words);
		$words	=implode(" ", $words);
		if ($type=="lower") $text=strtolower($text);
		if ($type=="words") $text=ucwords($ntext);
		if ($type=="first") $text=ucfirst($text);
		if ($type=="upper") $text=strtoupper($text);
		if ($type=="peak") $text=$first.ucfirst($words);
		if ($type=="camel") $text=strtolower($first).ucwords($words);
		$text	=str_replace(" ", "", $text);
		return $text;
	}
	
	public function textFirst($text="") {
		return $this->casing($text, "first");
	}
	
	public function textCamel($text="") {
		return $this->casing($text, "camel");
	}
	
	public function textWords($text="") {
		return $this->casing($text, "words");
	}
	
	public function textHash($text, $start=2, $end=2, $mask="*") {
		$array	=str_split($text);
		$count	=count($array);
		$ntext	=[];
		foreach ($array as $key=>$char) {
			$nchar	=$mask;
			if ($key<$start||($count - $key)<=$end) $nchar=$char;
			$ntext[]	=$nchar;
		}
		$text	=implode("", $ntext);
		return $text;
	}
	
	public function dateFormat($date, $format="") {
		if (!$format) $format="l dS F, Y";
		$new_date	=$date;
		if (strlen($date)>=5) {
			$new_date=date_create($new_date);
			$new_date=date_format($new_date, $format);
		}
		return $new_date;
	}
			
	public function inWords($number, $type="") {
		$places		=array("", "thousand", "million", "billion", "trillion", "quadrillion");
		$part		=explode(".", $number);
		$number		=$this->arrayKey(0, $part);
		$decimal	=$this->arrayKey(1, $part);
		
		$csv_number	=number_format((int)$number);
		$csv_blocks	=explode(",", $csv_number);
		$count_csv	=count($csv_blocks);
		
		$defined	=array();
		foreach ($csv_blocks as $position=>$csv_block) {
			$text	=$this->inPart($csv_block);
			$block_key	=($count_csv - ($position+1));
			$csv_place	=$this->arrayKey($block_key, $places);
			if ($csv_place) $csv_place=" $csv_place";
			
			if ($text) $defined[]=$text.$csv_place;
		}
		$words		=[];	
		$words[]	=implode(", ", $defined);
		
		if ($type && $decimal>0) {
			$words[]="point";
			$words[]=$this->inPart($decimal, $type);
		}
		$words	=implode(" ", $words);
		return 	$words;
	}
	
	public function inPart($number, $type="") {		
		$digits		=["zero", "one", "two", "three", "four", "five", "six", "seven", "eight", "nine"];
		$array_tens	=[10=>"ten", 11=>"eleven", 12=>"twelve", 13=>"thirteen", 15=>"fifteen", 2=>"twenty", 3=>"thirty", 5=>"fifty"];
		
		$array_text	=[];
		$array_no	=str_split($number, 1);
		if ($type=="") {
			$array_rev	=array_reverse($array_no);
			$one	=$this->arrayKey(0, $array_rev);
			$ten	=$this->arrayKey(1, $array_rev);
			$hundred=$this->arrayKey(2, $array_rev);
			
			$teens	="";
			$text_one	=$this->arrayKey($one, $digits);
			if ($one=="0") $text_one="";
			if ($ten!="") {
				$tens	=$ten.$one;
				$text_tens	=$this->arrayKey($tens, $array_tens);
				$text_ten	=$this->arrayKey($ten, $array_tens);
				$text_dten	=$this->arrayKey($ten, $digits);
				if ($ten=="0") $text_dten="";
				$text_ones	=" ".$text_one;
				if ($text_dten) $teens=$text_dten."ty".$text_ones;
				if ($text_ten) $teens=$text_ten.$text_ones;
				if ($ten==1) $teens=$text_one."teen";
				if ($text_tens) $teens=$text_tens;
				if ($teens=="") $teens=$text_one;
			}
			if ($hundred>0) $array_text[]=$this->arrayKey($hundred, $digits)." hundred";
			if ($teens) $array_text[]=$teens;
			if ($one!="0"&&!$teens) $array_text[]=$text_one;
		}
		else {
			foreach ($array_no as $x=>$digit) {
				$array_text[]	=$this->arrayKey($digit, $digits);
			}					
		}
		$text	=implode(" ", $array_text);
		$text	=str_replace("tt", "t", $text);
		return $text;
	}
		
	public function hashPassword($open_password, $hash="") {	
		$password	=hash("sha256", $open_password.$hash);
		return $password;
	}
	 
	public function randomText($length="", $type="") {
		//fallback to mt_rand if php < 5.3 or no openssl available
		$numbers='0123456789';
		$special='.-+*!_~?&@%$'; # /=(),;:|^[]<>{}\"'#
		$alpha1	='abcdefghijklmnopqrstuvwxyz';
		$alpha2	=strtoupper($alpha1);

		$text	=$alpha2.$numbers;
		if (strstr($type, "1")) $text.=$alpha1;
		if (strstr($type, "2")) $text.=$numbers;
		if (strstr($type, "3")) $text.=$alpha2;
		if (strstr($type, "4")) $text.=$special;

		$textLength	=strlen($text)-1;
		
		$string	="";
		//select some random characters
		for ($i=0; $i<$length; $i++) {
			$string	.=$text[mt_rand(0, $textLength)];
		}
		return $string;
	}
	 
	public function randomize($length="", $type="", $upper="") {
		$number	=1;
		if (!$length) $length =24;
		if (function_exists('openssl_random_pseudo_bytes')) {
			$salt		=openssl_random_pseudo_bytes($length, $number);
			$password	=base64_encode($salt);
			$string	=substr($password, 0, $length); //base64 is about 33% longer, so we need to truncate the result
		}
		else {
		}
		$string	=$this->randomText($length, $type);
		if ($upper) $string=strtoupper($string);

		return $string;
	}
	 
	public function newPassword($length="") {
		return $this->randomize($length, "1234");
	}

	public function globalVars($list="") {
		if (!$list) $list='cms_connect,exclude,c,control,root,site_base,base_path,cms_path,library,includes,sitename,session_code,session_data,siteid,site_name,site_root,db_sites,array_sites,this_role,this_auth,this_user,current_role,current_user,current_id,current_names,int_action,action,action_name,icon,get_id,icon_name,get_mod,module_name,get_title,mod_array,this_url,tip_class,path_icons,tables,module_sql,lang_array,session_language,is_lang,language,tab_permission,tab_names,tab_array,icon_list,btn_active,btn_other,tab_inner1,tab_inner2,tab_start,tab_end,tab_active,tab_other,print_pages,print_path,date';
		$array	=explode(",", $list);
		$vars	=[];
		foreach ($array as $var) {
			$var	=trim($var);
			$value	=$this->formKey($var);
			$vars[$var]	=$value;
		}
		return $vars;
	}
	public function lang($text, $from="", $target="en", $ntrans="") {
		$ntext=trans($text);
		if (!$ntrans) $ntrans=$text;
		if ($ntext==$text || !$ntext) $ntext=$this->translate($ntrans);
		return $ntext;
	}
	
	function translate($label) {
		$label	=$this->txt("[", $label, "first");
		$array	=["a_", "to_", "bool_"];
		foreach ($array as $word) {
			$label	=str_replace(".{$word}", "", ".{$label}");
		}

		$label	=trim($label, ".");
		$label	=str_replace("_", " ", $label);
		$label	=ucfirst($label);
		return $label;
	}

}
