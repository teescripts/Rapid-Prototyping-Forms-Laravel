<?php
namespace Teescripts\RptForms;

use Teescripts\RptForms\main;
use Teescripts\RptForms\data;

class lists extends main {
	function __construct() {
		parent::__construct();
	}

	function count($query) {
		return $this->load("page")->pageCount($query);
	}

	function loadQuery($module="", $args="") {
		return $this->loader($module, $args);
	}
	
	function loadList($text) {
		$array	=explode("/", $text);
		$var_list	=$this->arrayKey(0, $array);
		$var_type	=$this->arrayKey(1, $array);
		$var_fxn	=$this->arrayKey(2, $array);
		$var_name	="as_{$var_type}";
		$results	=$this->$var_name($var_fxn);
		return $results;
	}

	function quotes($text="") {
		$text	=str_replace('[', '"', $text);
		$text	=str_replace(']', '"', $text);
		return $text;
	}

	function search() {
		$search	=$this->arrayKey("q", $_GET);
		$search	=addslashes($search);
		$search	=strip_tags($search);
		return $search;
	}

	function load_fx($function="", $args=[]) {
		$data	=new data();
		if (method_exists($data, $function)) {
			$result	=$data->$function($args);
			return $result;
		}
	}

	function loader($module="", $args="") {
		$module	=$module.'List';
		$query	=$this->load_fx($module, $args);
		return $query;
	}

	function loadView($module="", $args="") {
		$module	=$module.'View';
		$query	=$this->load_fx($module, $args);
		$result	=$this->view($query);
		if (!$result && $args) $result=$args;
		return $result;
	}

	function loadArray($module="", $args="") {
		$query	=$this->loader($module, $args);
		$data	=$query;
		if (stristr($query, "select ")) $data=$this->query($query);
		return $data;
	}

	function loadGrid($module="", $args="") {
		header("Content-Type: application/json");
		header("Access-Control-Allow-Origin: *");

		$search	=$this->search();

		$query	=$this->loadQuery($module, $args);
		$query	=str_replace(":phrase", $search, $query);

		$get_page	=$this->arrayKey("page", $_GET, 1);
		$get_rows	=$this->arrayKey("rows", $_GET, 20);
		
		$total	=$this->count($query);
		$start	=($get_rows * $get_page)- $get_rows;
		if ($query) $query.=" LIMIT {$start}, {$get_rows}";
		$data	=$this->query($query);

		$array	=["total"=>$total, "rows"=>$data];
		$text	=($array);
		return $text;
	}

	function loadJson($module="", $args="") {
		header("Content-Type: application/json");
		header("Access-Control-Allow-Origin: *");
		
		$search	=$this->search();

		$query	=$this->loadQuery($module, $args);
		$query	=str_replace(":phrase", $search, $query);

		$get_page	=$this->arrayKey("page", $_GET, 1);
		$get_rows	=$this->arrayKey("rows", $_GET, 20);

		$total	=$this->count($query);
		$start	=($get_rows * $get_page) - $get_rows;
		if ($query) $query.=" LIMIT {$start}, {$get_rows}";
		$data	=$this->query($query);

		$text	=json_encode($data);
		return $text;
	}

	function loadSuggest($module="", $args="") {
		header("Content-Type: application/json");
		header("Access-Control-Allow-Origin: *");
		
		$search	=$this->search();

		$query	=$this->loadQuery($module, $args);
		$query	=str_replace(":phrase", $search, $query);

		$query	=str_replace(" AS `id`", " AS `value`", $query);
		$query	=str_replace(" AS `name`", " AS `data`", $query);
		$query	=str_replace(" AS `title`", " AS `html`", $query);
		$query	=str_replace(":phrase", $search, $query);

		if ($query) $query.=" LIMIT 0, 20";
		$data	=$this->query($query);#, $bind

		$array	=["suggestions"=>$data];
		$text	=json_encode($array);
		return $text;
	}

	function loadSelect($module="", $args="") {
		header("Content-Type: application/json");
		header("Access-Control-Allow-Origin: *");
		
		$search	=$this->search();

		$query	=$this->loadQuery($module, $args);
		$query	=str_replace(":phrase", $search, $query);

		$get_rows	=20;
		$get_page	=$this->arrayKey("page", $_GET, 1);
		
		$total	=$this->count($query);
		$start	=($get_rows*$get_page)-$get_rows;
		if ($query) $query.=" LIMIT {$start}, {$get_rows}";
		$data	=$this->query($query);

		$array	=["results"=>$data, "pagination"=>["more"=>true]];
		$text	=json_encode($array);
		$text	=str_replace('"name"', '"text"', $text);
		return $text;
	}

	function loadText($module="", $args="") {
		$text	="";
		$data	=$this->loadArray($module, $args);
		$row	=$this->arrayKey(0, $data);
		if ($row) {
			$cols	=array_keys($row);
			$key	=$this->inArray("id", $cols, $cols[0]);
			$name	=$this->inArray("name", $cols, $cols[1]);
			$data	=array_column($data, $name, $key);
			$text	=json_encode($data);
			$text	=$this->flatten($text);
		}
		return $text;
	}

	function loadNestJson($module="", $args="") {
		header("Content-Type: application/json");
		header('Access-Control-Allow-Origin: *');

		$array	=[];
		$result	=$this->nestJsonRow($module, $args, $array);
		$text	=json_encode($result);
		return $text;
	}

	function loadNestGrid($module="", $args="") {
		return $this->loadNestJson($module, $args);
	}

	function loadNestSelect($module="", $args="") {
		header("Content-Type: application/json");
		header('Access-Control-Allow-Origin: *');

		$array	=[];
		$result	=$this->nestJsonRow($module, $args, $array);
		$array	=["results"=>$result, "pagination"=>["more"=>true]];
		$text	=json_encode($array);
		$text	=str_replace('"name"', '"text"', $text);
		return $text;
	}

	function loadNest($module="", $args="") {
		$array	=[];
		$result	=$this->nestRow($module, $args, $array);
		$text	=implode(";", $result);
		return $text;
	}

	function nestRow($module="", $args="", $tree=[]) {
		$search	=$this->search();

		$query	=$this->loadQuery($module, $args);
		$query	=str_replace(":phrase", $search, $query);
		$results=$this->query($query);

		$tree	=$this->nestData($module, $args, $results, $tree);
		return $tree;
	}

	function nestData($module="", $args="", $results="", $tree=[]) {
		if ($results) {
			foreach ($results as $key=>$row) {
				$value	=$row["id"];
				$text	=$row["name"];
				$args[1]=$value;
				
				$array	=[$value=>"{$value}=>{$text}"];
				$tree	=array_merge($tree, $array);
				$tree	=$this->nestRow($module, $args, $tree);
			}
		}
		return $tree;
	}

	function nestJsonRow($module="", $args="", $tree=[]) {
		$search	=$this->search();

		$query	=$this->loadQuery($module, $args);
		$query	=str_replace(":phrase", $search, $query);
		$results=$this->query($query);
		$tree	=$this->nestJsonData($module, $args, $results, $tree);
		return $tree;
	}

	function nestJsonData($module="", $args="", $results="", $tree=[]) {
		if ($results) {
			foreach ($results as $key=>$row) {
				$value	=$row["id"];
				$args[1]=$value;
				
				$array	=[];
				$result	=$this->nestJsonRow($module, $args, $array);
				$array	=$row;
				if ($result) {
					$count	=count($result);
					$array["count"]	=$count;
					$array["children"]	=$result;
				}
				$tree[]	=$array;
			}
		}
		return $tree;
	}

	function treeView($module, $args, $tree=[]) {
		$results	=$this->loadArray($module, $args);
		foreach ($results as $row) {
			$value	=$row["id"];
			$array	=$row;
			$result	=$this->treeView($module, $value, $array);
			if ($result) $tree[]=$result;
		}
		return $tree;
	}
	function flatten($text) {
		$text	=str_replace('":null', '":"N/A"', $text);
		$text	=str_replace('":"', '=>', $text);
		$text	=str_replace('}{', ';', $text);
		$text	=str_replace('"]["', ';', $text);
		$text	=str_replace('[{"', '', $text);
		$text	=str_replace('"}]', '', $text);
		$text	=str_replace('"]', '', $text);
		$text	=str_replace('["', '', $text);
		$text	=str_replace('{"', '', $text);
		$text	=str_replace('"}', '', $text);
		$text	=str_replace('","', ';', $text);
		$text	=str_replace('\/', '/', $text);
		$text	=str_replace('":', '=>', $text);
		$text	=str_replace(':"', '=>', $text);
		$text	=str_replace(',"', ';', $text);
		$text	=str_replace('",', ';', $text);
		$text	=str_replace('{', '', $text);
		$text	=str_replace('}', '', $text);
		return $text; 
	}

	# ---- references
	function as_index($name="", $args="") {
		return $this->as_array($module, $args);
	}

	function as_query($name="", $args="") {
		$text	="lists/query/".$name;
		return $this->loadQuery($name, $args);
	}

	function as_item($name="", $args="") {
		return $this->loader($name, $args);
	}

	function as_get($name="", $dataid="") {
		return $this->loadView($name, $dataid);
	}

	function as_view($name="", $dataid="") {
		return $this->loadView($name, $dataid);
	}

	function as_array($name="", $args="") {
		return $this->loadArray($name, $args);
	}

	function as_text($name="", $args="") {
		return $this->loadText($name, $args);
	}

	function as_nest($name="", $args="", $type="") {
		return $this->loadNest($name, [$args, $type]);
	}

	function as_json($name="", $args="") {
		echo $this->loadJson($name, $args);
	}

	function as_grid($name="", $args="") {
		echo $this->loadGrid($name, $args);
	}

	function as_select($name="", $args="") {
		echo $this->loadSelect($name, $args);
	}

	function as_suggest($name="", $args="") {
		echo $this->loadSuggest($name, $args);
	}

	function as_nestJson($name="", $args="", $type="") {
		echo $this->loadNestJson($name, [$args, $type]);
	}

	function as_nestGrid($name="", $args="", $type="") {
		echo $this->loadNestGrid($name, [$args, $type]);
	}

	function as_nestSelect($name="", $args="", $type="") {
		echo $this->loadNestSelect($name, [$args, $type]);
	}

}