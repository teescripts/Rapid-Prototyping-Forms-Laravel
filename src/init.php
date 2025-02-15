<?php
namespace Teescripts\RptForms;

class init {
	
	function __construct() {
	}
	
	function extracter($array, $prefix="") {
		return $this->extract($array, $prefix);
	}
	function merger($source, $array) {
		return $this->merge($source, $array);
	}
	function array_merger($source, $array) {
		return $this->merge($source, $array);
	}

	function isConst($key, $value="") {
		return $this->constKey($key, $value);
	}

	function arrayValue($key, $array, $value="") {
		return $this->arrayVal($key, $array, $value);
	}

	function inArray($key, $array, $value="") {
		return $this->arrayVal($key, $array, $value);
	}

	# if the variable is not global, set the given value
	function varKey($key, $value="") {
		return $this->formKey($key, $value);
	}

	function isVar($key, $value="") {
		return $this->varKey($key, $value);
	}
	
	# if the variable is an object, set the given value
	function objKey($key, $array, $value="") {
		return $this->arrayKey($key, $array, $value);
	}

	# function for checking array keys
	function arrayVal($key, $array=[], $value="") {	
		# if the array is not an array
		if (!is_array($array)) $array=[];
		# if the key exists in the array
		if (in_array($key, $array)) {
			# fetch the array data
			$value    =$key;
		}
		return $value;
	}
	
	# function for checking array keys
	function arrayKey($key, $array=[], $value="") {	
		# if the array is not an array
		if ($array && isset($key)) {
			# if key exists
			if (is_array($key)) $key=implode("", $key);
			
			if (is_object($array)) {
				# if the property exists in the object
				$text   =property_exists($array, $key);
				if ($text) $value=$array->$key;
			}
			elseif (is_array($array)) {
				# if the key exists in the array
				$text   =array_key_exists($key, $array);
				# fetch the array data
				if ($text) $value=$array[$key];
			}
		}
		return $value;
	}
		
	function jsonKey($text, $key="") {	
		$array	=json_decode($text, true);
		$value	=$array;
		if ($key) $value=$this->arrayKey($key, $array);
		return $value;
	}
		
	function formKey($key, $default="") {
		$var	=trim($key);
		$nkey	=str_replace("_", ".", $var);
		$value	=config("forms.{$nkey}");
		if (!$value) $value=config("forms.{$var}");
		
		if (!$value) $value=$this->arrayKey($var, $GLOBALS);
		if (!$value) {
			$value	=$default;
			config(["forms.{$nkey}", $value]);
		}
		return $value;			  
	}
	function propKey($key, $object="", $value="") {
		if (property_exists($object, $key)) $value=$object->$key;
		return $value;
	}


	function constKey($key, $value="") {
		$nvar	=str_replace("app.", "", $key);
		$nvar	=str_replace(".", "_", $nvar);
		$nkey	=strtoupper($nvar);
		$nvalue	=config($key);
		if (!$nvalue) $nvalue=env($nkey);#ROOT_BASE
		if (!$nvalue && defined($nkey)) $nvalue=constant($nkey);
		if (!$nvalue) $nvalue=$value;
		return $nvalue;
	}

	function post($var, $opt="") {
		$new_var	=str_replace(".", "_", $var);
		$post_val	=$this->arrayKey($new_var, $_POST);
		if (!is_array($post_val)) $post_val=$this->stripTags($post_val, $opt);
		return $post_val;
	}
	
	function get($var, $opt="") {
		global $url_array;
		$new_var	=str_replace(".", "_", $var);
		$get_val	=$this->arrayKey($new_var, $_GET);
		if (!$get_val) $get_val=$this->arrayKey($new_var, $url_array);
		if (!is_array($get_val)) $get_val=urldecode($get_val);
		return $this->stripTags($get_val, $opt);
	}

	function extract($array, $prefix="") {
		$result	=[];
		$keys	=array_keys($array);
		if ($keys) {
			foreach ($keys as $key) {
				$value	=$this->arrayKey($key, $array);
				$value	=html_entity_decode($value);
				if (!stristr(".{$key}", ".{$prefix}_")) $key="{$prefix}_{$key}";
				$GLOBALS[$key]	=$value;
				$result[$key]	=$value;
			}
		}
		return $result;
	}

	function merge($source, $array) {
		if (is_array($array)) {
			foreach ($array as $key=>$value) {
				$option =$this->arrayKey($key, $source);
				if ($option) {
					$keys	=is_array($option);
					if ($keys) {
						$source[$key][]  =$value;
					}
					else {
						$source[$key]  =$value;
					}
					unset($array[$key]);
				}
			}
		}
		return $source;
	}

	function stripTags($return, $opt="") {
		if (!is_array($return)) {
			if (is_int($opt)) $opt="";
			$return	=strip_tags($return, $opt);
			if (!$opt) $return=htmlspecialchars($return, ENT_QUOTES);
		}
		return $return;
	}

	function setter($array="", $fields="") {
		if (!is_array($fields)) $fields=explode(",", $fields);
		if (is_array($fields)) {
			$values	=array_values($fields);
			foreach ($array as $key=>$value) {
				$nkey	=$this->arrayKey($key, $fields);
				if (!$nkey) $nkey=in_array($key, $values);
				if (!$nkey) unset($array[$key]);
			}
		}
		return $array;
	}

	function flip($array) {
		$data	=[];
		foreach ($array as $key=>$value) {
			if (!is_array($value)) $data[$value]=$key;
		}
		return $data;
	}

	function unsetter($data="", $fields="") {
		return $this->unset($data, $fields);
	}

	function unset($data="", $fields="") {
		if ($fields) {
			$array	=$fields;
			if (!is_array($array)) $array=explode(",", $fields);

			$narray	=$this->flip($data);
			foreach ($array as $key) {
				$value	=$this->arrayKey($key, $data);
				if ($value) {
					unset($data[$key]);
				}
				else {
					$value	=$this->arrayKey($key, $narray);
					if ($value) unset($data[$value]);
				}
			}
		}
		return $data;
	}
		
	function api_hash($api_data, $api_user, $api_pass) {
		$data_text	=json_encode($api_data);
		$data_hash	=hash("sha256", $data_text.$api_pass);
		$data_array	=["api_key"=>$api_user, "api_hash"=>$data_hash, "api_data"=>$api_data];
		return $data_array;
	}
	
	function sendCurl($url, $data=[], $no_ssl=0) {
		$result	=$this->curl($url, $data, "json", $no_ssl, "post");
		if (!$result && $no_ssl) $result=$this->curl($url, $data, "json", "", "post");
		return $result;
	}

	function curl_send($url, $data="", $type="", $noverify=1, $method="", $options="", $header="") {
		return $this->curl($url, $data, $type, $noverify, $method, $options, $header);
	}

	function curl($url, $data="", $type="", $noverify=1, $method="", $options="", $header="") {
		$post	=false;
		$text	=$data;
		if ($type=="post") $post=true;
		$method	=strtoupper($method);
		if (!in_array($method, ["GET", "POST", "PUT", "DELETE"])) $method="POST";
		if ($type=="json") {
			$encode	=json_encode($data);
			$length	=strlen($encode);
			$ceil	=ceil($length / 2);
			#if (is_array($data)) $text=($data);#json_encode
			$headers	=["HTTP/1.1 200 OK", "Accept: application/json", "Content-Type: application/json"];#, "Content-Length: " . $ceil
		}
		else {
			if (is_array($data)) $text=$this->build_url($data);
			$headers	=["HTTP/1.1 200 OK", "Content-Type: application/x-www-form-urlencoded"];//text/plain, multipart/form-data			
		}

		if ($header) $headers=$header;

		$array	=array(
			CURLOPT_URL=>$url, 
			CURLOPT_HEADER=>false, 
			CURLOPT_TIMEOUT=>0, 
			CURLOPT_HTTPHEADER=>$headers, 
			CURLOPT_CUSTOMREQUEST=>$method, 
			CURLOPT_RETURNTRANSFER=>true
		);
		
		if ($text) {
			if ($post) $array[CURLOPT_POST]=$post;
			$array[CURLOPT_POSTFIELDS]=$text;
		}
		if ($noverify) {
			$array[CURLOPT_SSL_VERIFYHOST]=false;
			$array[CURLOPT_SSL_VERIFYPEER]=false;
		}
		
		$array	=$this->merge($array, $options);
		
		$curl	=curl_init();
		curl_setopt_array($curl, $array);
		$response	=curl_exec($curl);
		$curl_error	=curl_errno($curl);
		
		if ($curl_error) {
			$response	=["response"=>curl_error($curl), "status"=>$curl_error];
		}
		curl_close($curl);
		
		$result	=$response;
		if (!is_array($response)) $result=json_decode($response, 1);
		
		$log_data   =["url"=>$url, "file"=>"curl", "item"=>"curl_send", "response"=>$result, "request"=>$array];#, "options"=>$headers
        $this->logToFile($log_data);

		return $result;
	}

	function logToFile($log_data) {
		/*
		register: insertUser, addCompany 

		getMerchant, getAgent, getProfile

		addUser, updateAPI
		*/
		$item	=$this->arrayKey("item", $log_data);
		$file	=$this->arrayKey("file", $log_data, "all");
		$item	=strtolower($item);
		$array	=["curl_send"];#, logsession, lpoitems, copypdt, "checkData", "postData" ,orderinvoice,  companychain, finance, orderlpo, consolidation

		if (in_array($item, $array)) {
			$log_data["time"]	=date("Y-m-d H:i:s");
			$log_data["path"]	=$_SERVER["QUERY_STRING"];
			$this->log($file, $log_data, 1);
		}
	}

	function io($array, $type="") {
		$text	=json_encode($array, JSON_PRETTY_PRINT);
		if ($type=="x") $text=var_export($array, true);
		$text	=str_replace("\/", "/", $text);
		echo '<pre>'.$text.'</pre>';
	}

	function build_url($data) {
		$link	=http_build_query($data);
		return str_replace("%2C", ",", $link);
	}

	function logger($file="", $array="", $type="") {
		return $this->log($file, $array, $type);
	}

	function log($file="", $array="", $type="") {
		$root	=$this->varKey("base_root");
		$file	=strtolower($file);
		$file	=str_replace("::", "_", $file);
		$file	=$root.'logs/'.$file.'.json';
		$text1	="";
		if (is_file($file)) $text1=file_get_contents($file);

		$data	=["post"=>$_POST, "get"=>$_GET, "date"=>date("Y-m-d H:i:s")];
		if ($type) {
			$data	=$array;
			if (!is_array($data)) $data=[$array];
		}
		else {
			$data["return"]=$array;
		}
		$text2	=json_encode($data, JSON_PRETTY_PRINT);#FILE_APPEND
		$text2	=str_replace('\n', "\n", $text2);
		$text2	=str_replace('\r', "\r", $text2);
		$text2	=str_replace('\t', "\t", $text2);
		$text2	=str_replace('\/', "/", $text2);
		$text2	=str_replace('\"', '"', $text2);
		$text2	=str_replace('"{"', '{"', $text2);
		$text2	=str_replace('}"', "}", $text2);
		$text3  =implode(", \n\t", [$text2, $text1]);
		file_put_contents($file, $text3);
	}
		
	static function errorHandler($type, $message, $file, $line, $context="") {
		$contents	="";
		$var_filename	=$this->varKey("base_root")."logs/error.json";
		if (is_file($var_filename)) $contents=file_get_contents($var_filename);
		if (!$contents) $contents='[]';
		$array_text	=json_decode($contents, true);
		
		$array_types	=[1=>"E_ERROR", 2=>"E_WARNING", 4=>"E_PARSE", 8=>"E_NOTICE", 16=>"E_CORE_ERROR", 32=>"E_CORE_WARNING", 64=>"E_COMPILE_ERROR", 128=>"E_COMPILE_WARNING", 256=>"E_USER_ERROR", 512=>"E_USER_WARNING", 1024=>"E_USER_NOTICE", 2048=>"E_STRICT", 4096=>"E_RECOVERABLE_ERROR", 8192=>"E_DEPRECATED", 16384=>"E_USER_DEPRECATED", 32767=>"E_ALL"];
		$date	=date("Y-m-d");
		$nfile	="file_{$file}";
		$nline	="line_{$line}";
		$ntype	=$this->arrayKey($type, $array_types, "E_UNKNOWN");

		$array_date	=$this->arrayKey($date, $array_text);
		$array_type	=$this->arrayKey($type, $array_date);
		$array_file	=$this->arrayKey($file, $array_type);
		$array_line	=$this->arrayKey($line, $array_file);
		
		$date_last	=date("D d M H:i:s");#l dS F H:i:s
		$array_line	=["last"=>$date_last, "message"=>$message];#, "context"=>$context, "count"=>($count_line+1)
		$array_text[$ntype][$nfile][$nline]	=$array_line;//[$date]

		$contents	=json_encode($array_text, JSON_PRETTY_PRINT);
		$contents	=str_replace('\/', '/', $contents);
		$contents	=str_replace('","', '", "', $contents);
		$contents	=str_replace('\n', "  ", $contents);
		
		if (is_file($var_filename)) file_put_contents($var_filename, $contents);
	}
	
	function connecter($array="", $pairs="") {
		$db_type	=$this->arrayKey("type", $array);
		$db_host	=$this->arrayKey("host", $array);
		$db_name	=$this->arrayKey("name", $array);
		$db_port	=$this->arrayKey("port", $array);
		$persist	=$this->arrayKey("persist", $array, false);

		$username	=$this->isConst("DB_USER", "root");
		$password	=$this->isConst("DB_PASS", "root");
		if (!$db_type) $db_type=$this->isConst("DB_TYPE", "mysql");
		if (!$db_host) $db_host=$this->isConst("DB_HOST", "localhost");
		if (!$db_name) $db_name=$this->isConst("DB_NAME", "test_all");
		if (!$db_port) $db_port="3306";

		$pdo_handle	="{$db_type}:host={$db_host};dbname={$db_name};charset=utf8";#:$db_port
		
		$array	=$pairs;
		if (!$pairs) $pairs=[[$username, $password]];
		$count	=count($pairs) - 1;
		foreach ($pairs as $key=>$pair) {
			$username	=$this->arrayKey(0, $pair);
			$password	=$this->arrayKey(1, $pair);
			
			try {
				$now	=new DateTime();
				$offset	=$now->format("P");

				$option	=[PDO::ATTR_PERSISTENT=>$persist, PDO::MYSQL_ATTR_LOCAL_INFILE=>true];
				$connect	=new PDO($pdo_handle, $username, $password, $option);

				$connect->exec("SET time_zone='{$offset}';");
				$connect->exec("SET sql_mode='';");
				$connect->exec("SET names utf8");
				return $connect;
			}
			catch (PDOException $e) {
				$text	=$e->getMessage();
				$text	="Connection has failed. Please refresh.";
				if ($key==$count) die($text);
			}
		}
	}
	
	function linkText($text, $dir=1) {
		$array	=["&"=>"[and]", "/"=>"[or]", "[eq]"=>"=", "[s]"=>" ", "-s-"=>" "];
		if ($dir==1) {
			$text	=str_replace(array_keys($array), array_values($array), $text);
		}
		else {
			$text	=str_replace(array_values($array), array_keys($array), $text);
		}
		return $text;
	}

	function uriString() {
		$decode	=$this->varKey("var_decode");
		$query	=$this->arrayKey("REQUEST_URI", $_SERVER);
		if (!strstr($query, ".php?")) {
			$join1	=".html";
			$join2	="{$join1}/";
			$join3	="asp/index/";
			$join4	="asp/print/";
			$join5	="asp/loader/";

			$border	=".php?";
			if (strstr($query, $join1)) $border=$join1;
			if (strstr($query, $join2)) $border=$join2;
			if (strstr($query, $join3)) $border=$join3;
			if (strstr($query, $join4)) $border=$join4;
			if (strstr($query, $join5)) $border=$join5;

			if (strstr($query, $border)) {
				$query	=str_replace("??", "?", $query);

				if (strstr($query, "?") && $border!="?") {
					$query	=str_replace("?", "/", $query);
					$query	=str_replace("=", "/", $query);
				}

				$split	=explode($border, $query);
				$query	=$this->arrayKey(1, $split);
				$nquery	=trim($query, "/");
			}
			else {
				$nquery	=$this->arrayKey("url", $_GET);
			}
			
			$query	=$nquery;
			$query	=urldecode($nquery);
			if ($decode) $query=$this->linkText($query, 2);
			$_GET["url"]=$query;
			$_GET["url_string"]=$query;
		}
	}

	function decode($query="") {
		$query	=$this->arrayKey("url", $_GET);
		$query	=strip_tags($query);
		$array	=[];
		if ($query) {
			$query	=trim($query, "/");
			$split	=explode("/", $query);
			$mkey	=$this->arrayKey(0, $split);
			$count	=count($split);
			$x	=0;
			while ($x < $count) {
				$key_name	=$this->arrayKey($x, $split);
				if ($x<2 && $mkey!="mod") {
					$key	="icon";
					if ($x==0) $key="action";
					$var	=$key;
					$text	=$key_name;
					$x	+=1;
				}
				else {
					$nkey	=($x+1);
					$var	=$key_name;
					$key	=$this->arrayKey($x, $split);
					$text	=$this->arrayKey($nkey, $split);
					$x	+=2;
				}
				$array[$var]	=$text;
			}
		}
		else {
			parse_str($_SERVER['QUERY_STRING'], $array);
		}
		$array	=array_merge($array, $_GET);
		$_GET	=$array;
		return $array;
	}
	
	function mobile() {
		$useragent  =$this->arrayKey("HTTP_USER_AGENT", $_SERVER);

		$useragent  =strtolower($useragent);
		$kindle     =stristr($useragent, "kfapwi");
		$mobile     =stristr($useragent, "mobile");
		$iphone     =stristr($useragent, "iphone");
		$android    =stristr($useragent, "android");
		$windows    =stristr($useragent, "windows phone");
		$result     =($windows||$android||$mobile||$iphone||$kindle);
		return $result;
	}
	
	function browser() {
		$text_keys  ="browser,kernel,op_sys,rv,version";
		$browser  =$this->arrayKey("HTTP_USER_AGENT", $_SERVER);

		$browser  =str_replace("(", ";", $browser);
		$browser  =str_replace(")", ";", $browser);
		$browser  =str_replace("; ", ";", $browser);
		$array_keys  =explode(",", $text_keys);
		$array_value =explode(";", $browser);
		$result  =array_combine($array_keys, $array_value);
		return $result;
	}
	
}
