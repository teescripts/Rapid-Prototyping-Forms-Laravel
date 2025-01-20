<?php
namespace Teescripts\RptForms;

use Teescripts\RptForms\base;

class main extends base
{
    public $sql;
    public $lang;
    public $sess;

	function __construct() {
		parent::__construct();
		$this->sql	=[];//$this->load("sql");
	}
	
	public function cmsCache($inc_file, $name="left", $tab="", $cache_time=15) {
		$cache_file	=$this->cacheFile($name, $tab);

		$cache_time	=($cache_time * 60);
		$elapsed	=(time() - $cache_time);
		$modified	=0;
		if (is_file($cache_file)) $modified	=filemtime($cache_file);
		
		if (is_file($cache_file) && ($elapsed < $modified)) {
			include $cache_file;
		}
		else {
			if (is_file($inc_file)) {
				extract($GLOBALS);
				
				ob_start();
					include $inc_file;
					$text	=ob_get_contents();
					if ($text) file_put_contents($cache_file, $text);
				ob_end_flush();
			}
		}
	}
	
	public function cacheName($name="left", $tab="", $key="") {
		global $is_mobile, $language, $siteid, $this_role, $get_mod, $get_token;
		$name	=[$name];
		if ($language) $name[]=$language;
		if ($siteid&&strlen($siteid)<8) $name[]=$siteid;
		if ($this_role) $name[]=$this_role;
		if ($get_token) $name[]=$get_token;
		if ($get_mod) $name[]=$get_mod;
		if ($tab) $name[]=$tab;
		if ($is_mobile) $name[]="mobile";
		if ($key) $name[]=$this->textNorm($key,2, 1);
		$name	=implode("_", $name);
		$name	=str_replace(" ", "_", $name);
		$name	=str_replace("=", "_", $name);
		$name	=str_replace("/", "_", $name);
		$name	=strtolower($name);
		return $name;
	}
	
	public function cacheFile($name="left", $tab="", $key="") {
		$cache	=varKey("base_cache");
		$temp	=varKey("base_temp");
		$path	=$cache."admin/";
		$name	=$this->cacheName($name, $tab, $key);

		$file	=$path.basename($temp)."_{$name}.html";
		$file	=str_replace("?", "-", $file);

		if ($path && !is_dir($path)) mkdir($path, 0777, 1);

		return $file;
	}

	public function link($link, $activate=1) {
		$appid	=varKey("app_extension");
		if (!strstr($link, "token")) $appid.=$appid;
		$link	=str_replace("??", "?", $link);
		$link	=str_replace(".php/", ".php?", $link);
		$link	=str_replace(".php&", ".php?", $link);
		$chip	=explode("?", $link);
		$plain	=arrayKey(0, $chip);
		$query	=arrayKey(1, $chip);

		$nquery	=str_replace("action=", "", $query);
		$nquery	=str_replace("&icon=", "/", $nquery);
		$nquery	=str_replace("/icon=", "/", $nquery);
		$nquery	=str_replace("/icon/", "/", $nquery);
		$nquery	=str_replace("&icon/", "/", $nquery);
		$nquery	=str_replace("icon=", "", $nquery);
		$nquery	=str_replace("url=", "", $nquery);
		
		if ($activate==1) {
			$last	=$this->chip($plain, "/", "last");
			if (strstr("{$last}.", ".php.")) {
				$nlast	=str_replace(".php", "", $last);
				$plain	=str_replace($last, "asp/{$nlast}", $plain);
			}
			
			$nquery	=str_replace("&", "/", $nquery);
			$nquery	=str_replace("=", "/", $nquery);
			$nquery	=str_replace("/t/", "/title/", $nquery);
			$nquery	=preg_replace("/\s\s+/i", " ", $nquery);
			$nquery	=str_replace(" ", "-s-", $nquery);
			
			$link	=$plain;
			if ($query) $link.="/".($nquery);
			$link	=ltrim($link, "/");
		}
		else {
			$nquery	=str_replace("/id/", "&id=", $nquery);
			$nquery	=str_replace("/id=", "&id=", $nquery);
			$nquery	=str_replace("&id/", "&id=", $nquery);
			$nquery	=str_replace("/t/", "&t=", $nquery);
			$nquery	=str_replace("/t=", "&t=", $nquery);
			$nquery	=str_replace("&t/", "&t=", $nquery);
			$nquery	=str_replace("&t=", "&title=", $nquery);
			
			$link	=$plain;
			if ($query) $link.="?url=".$nquery;
		}
		return $link;
	}
	
	public function multi($values, $query, $url="", $class="", $full="") {
		global $tip_class, $btn_group, $btn_link, $icon;
		$txt	=[];
		$view	=in_array($icon, explode(",", "view,update,insert,print"))?1:0;
		if ($full) $view=$full;
		$array	=explode(",", $values);
		$many	=(count($array)>1);
		$grouped=($class==$btn_group && $class);
		$class	=($grouped)?$tip_class.' '.$btn_link:$tip_class;
		foreach ($array as $value) {
			$value	=trim($value);
			$query1	=str_replace("'#'", "'{$value}'", $query);
			$query1	=str_replace('[v]', $value, $query1);
			$query1	=str_replace('{v}', $value, $query1);
			$query1	=str_replace('(v)', $value, $query1);
			$name	=$this->view($query1);
			$link	=$this->link($url.$value);
			$title	=$name;
			if (!$view && $many) $title=$this->abbr($name);
			if (!$title) $title=$value;
			$text	='<span title="'.$name.'" class="'.$class.'">'.$title.'</span>';
			if ($url && $name) $text='<a href="'.$link.'" title="'.$name.'" class="'.$class.' text-muted">'.$title.'</a>';
			$txt[]	=$text;
		}
		$join	=", ";
		if ($view && $many && $full==2) $join="<br>";
		$text	=implode($join, $txt);
		if ($many) $text='<div class="">'.$text.'</div>';
		if ($grouped) $text='<div class="'.$btn_group.'">'.implode("", $txt).'</div>';
		
		return $text;
	}
	
	public function valueMap($values, $value, $type="") {
		return $this->map($values, $value, $type);
	}
	
	public function mapping($list, $value) {	
		return $this->viewType($value, $list);
	}
	
	public function map($values, $value, $type="", $extra="") {	
		if (is_string($values)) {
			if (strstr(".{$values}", ".list_")) {
				$values=varKey($values);
			}
		}
		$array	=$values;
		if (!is_array($array)) {
			$array	=$this->arrayConvert($values, "tree");
			$array	=$this->mapType($array);
		}
		
		$result	=[];
		$keys	=$this->arrayConvert($value, "keys");
		foreach ($keys as $key) {
			$key	=trim($key);
			$text	=arrayKey($key, $array, $key);
			if ($text) {
				$class	="";
				if ($type) $class=$this->btnClass($key, $type, $extra);
				if ($class) $text='<span class="'.$class.'">'.$text.'</span>';
				$result[]	=$text;
			}
		}

		$comma	=", ";
		if ($type) $comma="\n";

		$result	=implode($comma, $result);
		if (!$result) $result=$value;
		$result	=str_replace('[cm]', ",", $result);
		return $result;
	}
	
	public function tagExtract($tag, $text) {
		$tag_array	=$this->arrayFormat($text);
		$new_value	=arrayKey($tag, $tag_array);
		return $new_value;
	}
		
	public function viewType($data="", $list="", $type="", $extra="") {
		$text	=$data;
		$array	=$this->globalize($list);
		if ($array) {
			$array	=$this->mapType($array);
			$text	=$this->map($array, $data, $type, $extra);
		}
		return $text;
	}
		
	public function viewTag($data="", $list="", $type=1, $extra="") {
		$text	=$this->viewType($data, $list, $type, $extra);
		return $text;
	}
		
	public function globalize($list="") {
		$values	=varKey($list);
		if ($values) {
			$name	=str_replace("list_", "array_", $list);
			$array	=varKey($name);
			if (!is_array($array)) {
				$array	=$this->arrayConvert($values, "tree");
				$GLOBALS[$name]	=$array;
				$name	=$array;
			}
			return $array;
		}
	}
		
	public function mapType($array_type) {
		$text	=[];
		foreach ($array_type as $key) {
			$head	=arrayKey("name", $key);
			$child	=arrayKey("children", $key);
			if ($child) {
				foreach ($child as $row) {
					$value	=$row["id"];
					$name	=$row["name"];
					$text[$value]	="{$name} ({$head})";
				}
			}
			else {
				$value	=$key["id"];
				$text[$value]	=$head;
			}
		}
		return $text;
	}

	public function label($array, $fields=[], $temp="", $join=", ") {
		if (!$temp) $temp='{t}: {v}';
		$text	=[];
		$key1	=arrayKey(0, $fields);
		foreach ($fields as $key=>$field) {
			$value	=arrayKey($field, $array);
			if ($value) {
				if (strstr($key, '{v}')) {
					$ntemp	=$key;
					$name	=$field;
					$label	=$field;
				}
				else {
					$ntemp	=$temp;
					$name	=$key;
					$label	=$field;
				}

				$title	=$this->lang($label);
				if (!$key1) $title=$this->lang($name);
				$item	=str_replace(['{t}', '{v}'], [$title, $value], $ntemp);
				
				$text[]	=nl2br($item);
			}
		}
		$text	=implode($join, $text);
		return $text;
	}
	
	public function dateAdd($start, $period="P0D") {
		return $this->datePlusMin($start, $period, "+");
	}
	
	public function dateSub($start, $period="P0D") {
		return $this->datePlusMin($start, $period, "-");
	}
	
	public function datePlusMin($start, $period="P0D", $dir="+") {
		#'P7Y5M4DT4H3M2S'=Period/Date part: 7years 5 months 4 days, Time part: 4 hrs 3 min 2 seconds
		$format	="Y-m-d";
		if (strlen($start)>10) $format.=" H:m:s";
		$plus	=($dir=="+");
		$basic	=strstr($period, " ");
		if ($basic) {
			$created	=date_create($start);
			$interval	=date_interval_create_from_date_string($period);
			if ($plus) {
				date_add($created, $interval);
			}
			else {
				date_sub($created, $interval);
			}
			$text	=date_format($created, $format);
		}
		else {
			$created	=new DateTime($start);
			$interval	=new DateInterval($period);
			if ($plus) {
				$created->add($interval);
			}
			else {
				$created->sub($interval);
			}
			$text	=$created->format($format);
		}
		return $text;
	}
	
	public function dateArray($start, $end, $type="array") {
		$start	=date_create($start);
		$end	=date_create($end);
		$interval=date_diff($end, $start);//y,m,d,h,i,s,days
		
		$seconds	=$interval->s;
		$minutes	=$interval->i;
		$hours		=$interval->h;
		$days		=$interval->days;#d
		$months		=$interval->m;
		$years		=$interval->y;

		$day	=$days;
		if (!$months && $years) $months=($years%12);
		if ($days>=30) $day=($days%30);
		$weeks	=floor($day/7);
		if ($day>=7) $day=($day%7);
		
		$interval	=array("year"=>$years, "month"=>$months, "week"=>$weeks, "day"=>$day, "days"=>$days, "hour"=>$hours, "minute"=>$minutes, "second"=>$seconds);
		
		$result	=arrayKey($type, $interval, $interval);
		return $result;
	}
	
	public function dateDiff($start, $end, $type="", $decimal="") {
		$array	=$this->dateArray($start, $end, "array");
		$result	=arrayKey($type, $array);
		if (!$result) $result=arrayKey("hour", $array);

		$result	=($decimal)?number_format((float)$result, 2):round($result);
		return $result;
	}
	
	public function days($date, $from="now") {
		return $this->dateArray($date, $from, "days");
	}
	
	public function age($date, $labels="days,hour,minute,second", $max="") {
		#century,decade,quarter,fortnight,week
		$interval	=$this->dateArray($date, 'now', "array");
		
		$labels	=explode(",", $labels);
		$array	=array();
		foreach ($interval as $label=>$value) {
			$title	=$label;
			if ($label=="day") $title="day";
			if ($value>1) $title.="s";
			$text	="{$value} {$title}";
			if ($value&&in_array($label, $labels)) $array[]=$text;
			if ($max&&$array) return "Over {$text}";
		}
		$text	=implode(", ", $array);
		if (substr($date, 0, 4)=="0000") $text="";
		return $text;
	}
	
	public function conCat($column="", $row="") {
		$label	=arrayKey($column, $row);
		if ($column&&!strstr(strtoupper($column), "CONCAT")) {
			$fields	=explode(",", $column);
			if (count($fields)>1) {
				$quotes	=str_split("()[]{}:-#<>= ", 1);
				$label	=[];
				foreach ($fields as $field) {
					$value	=arrayKey($field, $row);

					$trim	=trim($field);
					if ($field==" ") $trim=$field;

					$text	="{$value} ";
					if (in_array($trim, $quotes)) $text=$field;
					$label[]=$text;
				}
				$label	=implode("", $label);
				$label	=trim($label);
			}
		}
		return $label;
	}
	
	public function dataList($query, $key_column="", $value_column="") {
		if (strstr($query, '$ts_sql')) {
			$text	=str_replace('$ts_sql->', '$this->sql->', $query);
			if (!strstr($text, '->get_select()')) $text=$text.'->get_select()';
			$query	=eval("return {$text};");
		}
		$value_column	=str_replace("%", ",", $value_column);
		$key_column		=(!$key_column&&!$value_column)?0:$key_column;
		if (is_numeric($key_column)||$key_column=="0") {
			$method	=PDO::FETCH_NUM;
		}
		else {
			$method	=PDO::FETCH_ASSOC;
		}
		$results	=$this->query($query, "", $method);
		
		$row_value		=[];
		if (is_array($results)) {
			foreach ($results as $key=>$row) {
				$row_key	=arrayKey($key_column, $row);
				$row_label	=$this->conCat($value_column, $row);
				$row_key	=str_replace("*", "x", $row_key);
				$row_label	=str_replace("*", "x", $row_label);	
				($value_column)?$row_value[$row_key]=$row_label:$row_value[]=$row_key;
			}
		}
		else {
			$row_value	=$results;
		}
		
		$result	=$row_value;
		return $result;
	}
	
	function sqlHaving($phrase, $query) {
		$nquery	=preg_replace("/\s\s+/", " ", $query);
		if ($phrase) {
			$array	=[
				"having("=>" HAVING({$phrase} AND ", 
				"having "=>" HAVING {$phrase} AND ", 
				#"group by"=>" HAVING({$phrase}) ", 
				"order by "=>" HAVING({$phrase}) ORDER BY ", 
				"limit"=>" HAVING({$phrase}) LIMIT"
			];

			$count	=count($array);
			$keys	=array_keys($array);
			for ($key=0; $key<$count; $key++) {
				$word	=arrayKey($key, $keys);
				$replace=arrayKey($word, $array);
				$upper	=strtoupper($word);
				$nquery	=str_ireplace($word, $upper, $nquery);
				$chip	=explode($upper, $nquery);
				if (count($chip)>1) {
					$last	=array_key_last($chip);
					$text	=arrayKey($last, $chip);
					$nquery	=str_replace($upper.$text, $replace.$text, $nquery);
					$key	=$count;
				}
			}
			if (!stristr($nquery, "HAVING")) $nquery=$nquery."HAVING({$phrase})";
		}
		return $nquery;
	}
	
	public function sqlIn($list) {
		$where	=array();
		$array	=$this->arrayConvert($list, "keys");
		$where	=implode("', '", $array);		
		return $where;
	}
	
	public function sqlBool($text="", $strip="") {
		global $sql_where;
		if (!$text) $text=$sql_where;
		$text	=trim($text);
		$exists	=stristr(".{$text}", "where");
		$join	=($exists&&!$strip)?"AND":"WHERE";
		$exists	=stristr(".{$text}", ".{$join}");

		$bool	="";
		if (!$exists) $bool=" {$join} ";
		return $bool;	
	}
	
	public function sqlMany($field="", $value="") {
		if (!strstr($field, "`")) $field="`{$field}`";
		$nvalue	=addslashes($value);
		if (!strstr($value, '`')) $nvalue="'{$value}'";
		$text	="(#=@ OR # LIKE CONCAT(@, ',%') OR # LIKE CONCAT('%,', @) OR # LIKE CONCAT('%,', @, ',%'))";
		$text	=str_replace("#", $field, $text);
		$text	=str_replace("@", $nvalue, $text);
		return $text;
	}
					
	public function fetchList($text, $process=1, $extend="") {
		$list	=$text;
		$border	="JHRoaXMtPm";
		if ($process&&strstr($list, $border)) {
			$strings=explode("*", $text);
			$inits	=arraykey(0, $strings);
			$value	=arraykey(1, $strings);
			$side	=[];
			if ($value) {
				$side[]	=$inits;
				$values	=$value;
			}
			else {
				$values	=$inits;
			}
			
			$coded	=strstr($values, $border);
			if ($coded) {
				$array	=[];
				$blocks	=explode(";", $values);
				foreach ($blocks as $block) {
					$decode	=base64_decode($block);
					$valid1	=(substr($block, 0, strlen($border))==$border);
					$valid2	=(substr($decode, 0, 12)=='$this->load(');
					$valid	=($valid1 && $valid2);
					$result	="";
					if ($valid) {
						if ($extend) {
							$result	=$decode;
						}
						else {
							$block	='return '.$decode;
							//$result	=($block);
							$result	=@eval($block);
						}
						if ($result) $array[]=$result;
					}
				}
				$values	=implode(";", $array);
			}
			if ($values) $side[]=$values;
			$list	=implode("*", $side);
		}
		return $list;	
	}
		
	public function sqlType($field="", $list="") {
		$list_type	=varKey($list);
		$array_type	=$this->arrayConvert($list_type, "list");
		$type	=$field;
		foreach ($array_type as $key=>$value) {
			$type	="IF({$key} IN({$field}), '{$value}', {$type})";
		}
		return $type;
	}
	
	public function levelFilter($user_type, $user_column, $group_column, $group_value) {
		global $level_user, $this_role;
		$array_access	=array(
			3=>array($user_type=>[$user_column, $level_user]), 
			2=>array($this_role=>[$group_column, $group_value])
		);
		$result	=$this->accessFilter($array_access);
		return $result;
	}
	
	public function accessFilter($access_array) {
		global $perm_access, $perm_type, $perm_supercede, $this_role, $sql_where;
		$supercede	=($perm_supercede!=1 && ($perm_access==1||$perm_type==1));
		$perm_types	=arrayKey($perm_access, $access_array);
		
		if (!$perm_types&&$supercede) $perm_types=arrayKey(2, $access_array);
		
		$result	=$sql_where;
		if ($perm_types) {
			$access	=arrayKey($this_role, $perm_types);
			$column	=arrayKey(0, $access);
			$value	=arrayKey(1, $access);
			$where	=arrayKey(2, $access);
			$flip	=arrayKey(3, $access);
				
			$bool	="";
			if ($perm_access>=3) $bool="equal";

			$where1	="";
			if ($column&&$value) $where1=$this->where($column, $value, $bool, $flip);
			if ($where1) $result.=$this->sqlBool($result).$where1;
			if ($where) $result.=$this->sqlBool($result).$where;
		}
		return $result;
	}
	
	public function where($column, $value, $bool="", $reverse="") {
		global $my_operator, $level_bool;	
		
		if (!$bool) $bool=$level_bool;
		$operator	=($my_operator)?$my_operator:"OR";
		
		$column	=trim($column);
		$escape	=(!strstr($column, "`")&&!strstr($column, ".")&&!strstr($column, " "));
		if ($escape) $column=str_replace($column, "`{$column}`", $column);
		
		if ($bool=="equal") {
			$values	=$this->sqlIn($value);
			$number	=preg_replace("/[0-9,\s\']+/", "", $values);
			if ($number!=""||$values=="") $values="'{$values}'";
			$where	="{$column} IN({$values})";
			if ($reverse) $where="({$values}) IN({$column})";
		}
		else {
			$where	=$this->sqlMany($column, $value);
		}
		#$where	="{$operator} {$where}";
		return $where;
	}

	function autoNo($total) {
		$token	="tsp_total";
		if (!$total) {
			$total	=varKey($token, 0);
			$total	=($total + 1);
			$GLOBALS[$token]	=$total;
		}
		$format	=str_pad($total, 2, "0", STR_PAD_LEFT);
		return '<div class="d-iblock tx-center wd-20 pd-0 mg-0">'.$format.'</div>';
	}
		
	function floatVal($value) {
		return $this->intVal($value);
	}
		
	function intVal($value, $default="") {
		$value	=str_replace(",", "", $value);
		$value	=strip_tags($value);
		$value	=doubleval($value);
		if (!$value && $default) $value=$default;
		return $value;
	}

	function postInt($fields, $post="") {
		if (!$post) $post=$_POST;
		$array	=explode(",", $fields);
		foreach ($array as $field) {
			$amount	=arrayKey($field, $post);
			if ($amount) {
				$amount	=$this->intVal($amount);
				$post[$field]	=$amount;
			}
		}
		$_POST	=$post;
		return $post;
	}
	
	public function commaColon($text) {
		$text	=str_replace('` ', '`', $text);
		$text	=str_replace(' `', '`', $text);
		$text	=str_replace('`,', '`[cm]', $text);
		$text	=str_replace(',`', '[cm]`', $text);
		$text	=str_replace('],', '][cm]', $text);
		$text	=str_replace(',[', '[cm][', $text);
		$text	=str_replace(',%', '[cm]%', $text);
		$text	=str_replace('%,', '%[cm]', $text);
		$text	=str_replace("',", "'[cm]", $text);
		return $text;
	}

	public function arrayFill($array_list="", $data="", $ignore="") {
		if (!is_array($array_list)) $array_list	=explode(",", $array_list);
		$array_key1	=array_keys($array_list);
		$array_vals	=array_values($array_list);

		$count	=0;
		$array	=[];
		foreach ($array_vals as $key=>$col_fm) {
			$key1	=arrayKey($key, $array_key1);
			$number	=preg_replace("/[0-9]+/", "", $key1);

			if ($number=="") {
				$col_db	=$col_fm;
				$count++;
			}
			else {
				$col_db	=$key1;
			}
			
			$value	=arrayKey($col_fm, $data);
			if ($value!=""||strstr($ignore, $col_fm)) {#
				$array[$col_db]	=$value;
			}
		}
		//$array	=[$text_key2];
		return $array;
	}

	public function arrayToList($array) {
		$ntext	=json_encode($array);
		$ntext	=str_replace('":', '=>', $ntext);
		$ntext	=str_replace(',"', ";", $ntext);
		$ntext	=str_replace('"', "", $ntext);
		$ntext	=str_replace('{', "", $ntext);
		$ntext	=str_replace('}', "", $ntext);
		$ntext	=str_replace('[', "<", $ntext);
		$ntext	=str_replace(']', ">", $ntext);
		return $ntext;
	}

	public function arrayMerge($data="", $values="") {
		$merge	=array_merge($data, $values);
		$keys	=array_keys($merge);
		$array	=array();
		foreach ($keys as $colm) {
			$value	=arrayKey($colm, $data);
			$value	=arrayKey($colm, $values, $value);
			$array[$colm]	=$value;#if ($value) 
		}
		return $array;
	}
	
	public function arrayText($text) {
		if (is_array($text)) {
			$option	=[];
			foreach ($text as $akey=>$arow) {
				if (is_array($arow)) {
					$avalue	=arrayKey("id", $arow);
					$alabel	=arrayKey("name", $arow);
					$child	=arrayKey("children", $arow);
					if (is_array($child)) {
						$item	=array();
						foreach ($child as $key=>$row) {
							$value	=$row["id"];
							$label	=$row["name"];
							$label	=str_replace(";", urlencode(";"), $label);
							$item[]	=$value.'=>'.$label;
						}
						$items	=implode(";", $item);
					}
					else {
						$child	=str_replace(";", urlencode(";"), $child);
						$items	=$child;
					}
					$text	='<'.$avalue.'=>'.$alabel.';'.$items.'>';
				}
				else {
					$arow	=str_replace(";", urlencode(";"), $arow);
					$text	=$akey.'=>'.$arow;
				}
				$option[]	=$text;
			}
			$text	=implode(";", $option);
		}
		else {
			$text	=$this->arrayKeys($text);
		}
		return $text;
	}
	
	public function arrayStandard($text) {
		$option	="";
		if (is_array($text)) {
			foreach ($text as $akey=>$arow) {
				if (is_array($arow)) {
					$alabel	=$arow[0];
					$child	=$arow[1];
					$item	=array("p#{$akey}"=>$alabel);
					if (is_array($child)) {
						foreach ($child as $value=>$label) {
							$item[$value]	=$label;
						}
					}
					$option[$akey]	=$item;
				}
				else {
					$option[$akey]	=$arow;
				}
			}
		}
		return $option;
	}
	
	public function arrayFix($array, $ignore_key="") {
		$item_array	=[];
		$list_array	=$array;
		if (!is_array($array)) $list_array=$this->arrayConvert($array, "list");	
		if (is_array($list_array)) {
			foreach ($list_array as $key=>$value) {			
				if (is_array($value)) $value=implode(",", $value);
				$value	=strip_tags($value);
				$value	=$this->words($value, 12);
				$value	=str_replace("&amp;", ' & ', $value);
				$value	=str_replace("*", "x", $value);
				$value	=str_replace('"', "'", $value);
				$value	=str_replace("&raquo;", "»", $value);
				$value	=str_replace("  ", " ", $value);
				$value	=str_replace(":", "[c]", $value);
				$value	=str_replace(",", '[cm]', $value);
				
				$list_item	=$key."=>".$value;
				if ($ignore_key) $list_item=$value;
				if ($ignore_key=="key") $list_item=$key;
				$item_array[]	=$list_item;
			}
		}
		$list_items	=implode(";", $item_array);
		return $list_items;
	}
	
	public function arrayUnset($array, $unset="") {
		if ($unset) {
			if (!is_array($unset)) $unset=explode(",", $unset);
			foreach ($unset as $key) {
				if (arrayKey($key, $array)) unset($array[$key]);
			}
		}
		return $array;
	}
	
	public function arrayTags($array, $unset="") {
		$array	=$this->arrayUnset($array, $unset);

		$list	=[];
		if (is_array($array)) {
			foreach ($array as $key=>$value) {
				$list[]	=$key.'="'.$value.'"';
			}
		}
		$list	=implode(" ", $list);
		return $list;
	}
	
	public function arrayFormat($list) {
		$array	=trim($list);
		$array	=str_replace('=""', '=[]', $array);
		$array	=str_replace("''", 'inch', $array);
		$array	=str_replace('""', 'inch', $array);
		$array	=trim($array, '"');
		if (strstr($array, "='")) {
			$array	=trim($array, "'");
			$array	=str_replace("='", '=>', $array);
		}
		$array	=str_replace('[]', '"', $array);
		$array	=str_replace('="', '=>', $array);
		$array	=str_replace(",", '[cm]', $array);
		$array	=str_replace('&nbsp;', ' ', $array);
		$array	=preg_replace('/\"\s+/', ';', $array);
		$array	=str_replace('; ', ';', $array);
		$array	=preg_replace("/\s\s+/", ' ', $array);
		$array	=$this->arrayConvert($array, "list");
		return $array;
	}
	
	public function arrayKeys($list) {
		$new	=[];
		#$list	=str_replace('[cm]', ",", $list);
		$list	=str_replace(",", ";", $list);
		$array	=explode(";", $list);
		foreach ($array as $value) {
			$keys	=explode('=>', $value);
			$key	=arraykey(0, $keys);
			$label	=arraykey(1, $keys, $key);
			$text	=$key.'=>'.$label;
			$new[]	=$text;
		}
		$array	=implode(';', $new);
		return $array; 
	}
	
	
	public function toJson($list) {
		$list	=$this->fetchList($list);
		$list	=$this->arrayKeys($list);
		
		//$list	=html_entity_decode($list);
		//$list	=str_replace("&#039;", "'", $list);
		//$list	=str_replace("'", "\'", $list);
		if (!strstr($list, '":"')) {
			//$list	=str_replace("\\\'", "\'", $list);
			$list	=str_replace(",", '[cm]', $list);
			$list	=str_replace('=>', '":"', $list);
			$list	=str_replace(';', ',', $list);
			$list	=str_replace(',', '","', $list);
			$list	=str_replace('[cm]', ",", $list);
			
			$list	='"'.$list.'"';
			if (strstr($list, '<')) {
				$list	='['.$list.']';
				$list	=str_replace('"<', '{"p#', $list);
				$list	=str_replace('>"', '"}', $list);
			}
			else {
				if (strstr($list, '":"')) {
					$list	='{'.$list.'}';
				}
				else {
					$list	='['.$list.']';
				}
			}
			$list	=str_replace(',""}:""}', '}', $list);
			$list	=str_replace("\n", " ", $list);
		}
		return $list;
	}
	
	public function arrayConvert($list, $type="all") {
		if (is_array($list)) {
			$pair	=1;
			$array	=$this->arrayStandard($list);
		}
		else {
			$json	=$this->toJson($list);
			$array	=json_decode($json, true);
			$pair	=strstr($json, '":"');
			//echo "<hr> ".print_r($json, 1);
		}

		if (is_array($array)) {
			$result	=$this->arrayTree($array, $pair);
			$result =arrayKey($type, $result, $result);
		}
		else {
			$result =$list;
		}
		return $result;
	}
	
	public function arrayTree($array, $pair="") {
		#"all", "tree", "key", "value", "item", "group", "main"
		if (is_array($array)) {
			foreach ($array as $akey=>$avalue) {
				if (is_array($avalue)) {
					$count	=1;
					$total	=count($avalue);
					foreach ($avalue as $key=>$label) {
						$key	=str_replace('p#', '', $key);
						$array	=array("id"=>$key, "name"=>$label);
						if ($count==1) {
							$parent	=$key;
							$title	=$label;
							$group	=$array;
							$data["child"]	=array();
						}	
						else {
							$data["item"][]		=$array;
							$data["pair"][$key]	="{$label}";# ({$title})
							$data["child"][]	=$array;
						}
						if ($count==$total&&$parent) {
							$data["pair"][$key]	=$label;
							$data["main"][$parent]	=$group;
							$group["children"]		=$data["child"];
							$data["tree"][]	=$group;
						}
						$data["key"][]		=$key;
						$data["value"][]	=$label;
						$data["all"][$key]	=$array;
						$count++;
					}
				}
				else {
					if (!$pair) $akey=$avalue;
					$array	=array("id"=>$akey, "name"=>$avalue);
					$data["key"][]		=$akey;
					$data["value"][]	=$avalue;
					$data["pair"][$akey]	=$avalue;
					$data["all"][$akey]		=$array;
					$data["main"][$akey]	=$array;
					$data["item"][]	=$array;
					$data["tree"][]	=$array;
				}
			}
			$result	=array("all"=>$data["all"], "tree"=>$data["tree"], "list"=>$data["pair"], "parents"=>$data["main"], "children"=>$data["item"], "keys"=>$data["key"], "values"=>$data["value"]);
			return $result;
		}
	}
	
	function fileImage($file, $folder="", $type="", $strict="") {
		$images	=["jpg", "gif", "png", "jpeg"];
		$file	="{$folder}/".$file;
		
		$source		="";
		$source		=$file;
		if (!$type) $type=3;
		if (!is_file($file)) {
			$types	=[1=>"male", 2=>"female", 3=>"other", 4=>"food", 5=>"dummy"];
			$file	=arrayKey($type, $types, $type);#no_image
			$file	="{$file}.png";
			$ext	=pathinfo($file, PATHINFO_EXTENSION);
			$path	=varKey("base_docs");
			$file	=$path."stock/no_photo/{$file}";

			if (in_array(strtolower($ext), $images)) $source=$file;
			if ($strict) $source="";
		}
		return $source;
	}
		
	public function color($data="", $type=1) {
		if (is_array($data)) $colors=$data;
		$colors	=$this->getColor($type);
		$count	=count($colors);
		if ($count>0) $count=($count - 1);
		
		$color	="";
		if ($data!="") {
			$ndata	=(int) $data;
			$is_num	=preg_replace("/[0-9]+/", "", $ndata);
			if (!$is_num && ($data!="{$ndata}")) {
				$ndata	=strlen($data);
				$ndata	=($ndata % $count);
			}
			$color	=arrayKey($ndata, $colors);
		}
		$result	=arrayKey(0, $colors);
		if ($color) $result=$color;
		
		return $result;
	}

	public function spanWrap($text, $color=1, $type=2) {
		$text	='<span class="'.$this->btnClass($color, $type).'">'.$text.'</span>';
		return $text;
	}
		
	public function getColor($type="") {
		$colors	="primary,success,info,warning,danger,secondary,dark";
		$colors	=varKey("btn_colors", $colors);
		if ($type==1) $colors=varKey("boot_colors", $colors);
		if (!is_array($colors)) $colors=explode(",", $colors);
		return $colors;
	}
		
	public function btnClass($color="", $type="", $extra="") {
		$btn_mini	=varKey("btn_mini", "btn-sm");
		$btn_prefix	=varKey("btn_prefix", "btn-");
		$minify	="{$btn_mini} {$btn_prefix}";
		
		$array	=[1=>"text-", 2=>"badge rounded-pill bg-* badge-* badge-gradient-", 3=>$minify, 4=>"btn {$minify}", 5=>$btn_prefix];
		$prefix	=arrayKey($type, $array, $array[2]);
		$result	="";
		if ($prefix) $result=trim($prefix)."*";
		if ($extra) $result.=" {$extra}";
		$color	=$this->color($color, $type);
		$result	=str_replace("*", $color, $result);
		return $result;
	}

	function io($data, $option="pre") {
		$text	=$data;
		if (is_array($data)) {
			if (strstr($option, "json")) {
				$text	=json_encode($data, JSON_PRETTY_PRINT);
			}
			if (in_array($option, ["var", "export"])) {
				$text	=var_export($data);
			}
		}
		$text	='<pre>'.print_r($text, 1).'</pre>';
		return $text;
	}
	
	public function redirect($path, $timeout=0) {
		return '<script type="text/javascript">setTimeout("location.href=\''.$path.'\';",'.$timeout."000);</script>";
	}

	public function window($window_url, $target="Popup", $width=700, $height=550, $top=10, $left=10, $settings="") {
		if (!$settings) $settings='addressbar=no,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,copyhistory=no';

		$settings	.=", width={$width},height={$height},top={$top},left={$left},screenX={$left},screenY={$top}";

		return '<script type="text/javascript">open("'.$window_url.'", "'.$target.'", "'.$settings.'");</script>';//window.open();";
	}
	
	public function ajax($text, $next="", $new_next="") {	
		global $btn_value, $c;
		
		$ajax_part	=($btn_value	==$c['load_data'])?"ajaxpart":"ajax_part";	
		
		$text		=$this->commaColon($text);
		$split		=explode(",", $text);
		
		$table			=$split[0];
		$new_table		=str_replace('[cm]',",", $table);
		$query_field	=$split[1];
		$select_name	=$split[2];
		$select_value	=$split[3];
		$select_label	=$split[4];
		$next_div		=$split[5];
		
		$next_event		=str_replace(",", ";", $next);
		$new_next_event	=str_replace(",", ";", $new_next);
		$next_event		=($new_next)?$next_event.'[n]'.$new_next_event:$next_event;
		
		$n_event	="onclick=\"loadAjax('".base64_encode($new_table)."',this.value,'".$query_field."=>".$select_name.";".$select_value."=>".$select_label.";".$next_div."','".$ajax_part."','".addslashes($next_event)."');\"";
		
		return $n_event;
	}
	public function lang($text, $from="", $target="en", $ntrans="") {
		$ntext=trans($text);
		if (!$ntext) $ntext=$text;
		return $ntext;
	}

	public function censor($text="") {
		return $text;			  
	}

	public function naming($field) {
		$alias	="";
		$bits	=explode("=", $field);
		$name	=arrayKey(0, $bits);	
		$alias	=arrayKey(1, $bits);
		#$name	=str_replace(".", "_", $name);

		$key	=$name;	 		
		if (count($bits)>1) $key=$alias;
		$ignore	=strstr("{$key}.", "].");
		$label	=$this->labelFormat($key);
		if (!$ignore) $label=$this->lang($label, "en", "en", $label);
		
		$array	=["name"=>$name, "label"=>$label, "alias"=>$alias];
		return $array;
	}
	function labelFormat($label) {
		$label	=$this->txt("[", $label, "first");
		$array	=["a_", "to_", "bool_"];
		foreach ($array as $field) {
			$label	=str_replace(".{$field}", "", ".{$label}");
		}

		$label	=trim($label, ".");
		$label	=ucfirst($label);
		$label	=str_replace("_", " ", $label);
		return $label;
	}

	function path_cdn($script="") {
		$url_cdn	=constKey("CDN");
		$url_path	=str_replace("../", "", $script);
		$url_path	="{$url_cdn}/{$url_path}";
		return $url_path;
	}
	
	function path_url($script="") {
		$url_cdn	=constKey("CDN");
		$url_base	=constKey("URL");
		$url_temp	=varKey("base_temp");
		$url_this	=arrayKey("HTTP_HOST", $_SERVER);
		$url_script	=str_replace("../", "", $script);
		if (strstr($url_base, $url_this)) {
			$url_cdn	=$url_base;
			//$url_script	=str_replace($url_temp, "", $script);
		}
		//$url_path	="{$url_cdn}/{$url_script}";
		$url_path	=str_replace($url_temp, "{$url_cdn}/", $script);
		return $url_path;
	}
	
	function path_lists($list="") {
		$temp	=varKey("base_temp");
		$path	=$this->path_url($temp);
		$link	=$path."load.php?url=Load/Ajax&file={$list}";
		$link	=$path."pages/lists.php?url=Lists/{$list}";
		return $link;
	}
	

}
