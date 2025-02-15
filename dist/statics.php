<?php
namespace Teescripts\RptForms;

use Teescripts\RptForms\lists;

class statics extends lists {

	function __construct() {
		parent::__construct();
	}

	function as_view($module="", $args="") {
		$query	=$this->loadList($module, $args);
		if (stristr($module, "status")) $query.=";20=>Flagged";
		$result	=$this->map($query, $args);
		return $result;
	}


	function as_nest($module="", $args="") {
		$list	=$this->loadList($module, $args);
		$array	=$this->arrayConvert($list, "tree");
		return $array;
	}

	function as_tree($module="", $args="") {
		$list	=$this->as_query($module, $args);
		$array	=$list;
		if (!is_array($array)) $array=$this->arrayTree($list);
		return $array;
	}

	function as_array($module="", $args="") {
		$list	=$this->loadList($module, $args);
		$array	=$list;
		if (!is_array($array)) $array=$this->arrayConvert($list, "tree");
		return $array;
	}

	function as_list($module="", $args="") {
		$list	=$this->loadList($module, $args);
		$array	=$list;
		if (!is_array($array)) $array=$this->arrayConvert($list, "list");
		return $array;
	}

	function as_text($module="", $args="") {
		$text	=$this->as_query($module, $args);
		if (is_array($text)) {
			$row	=$this->arrayKey(0, $text);
			$cols	=array_keys($row);
			$key	=$this->inArray("id", $cols, $cols[0]);
			$name	=$this->inArray("name", $cols, $cols[1]);
			$data	=array_column($text, $name, $key);
			$text	=json_encode($data);
			$text	=$this->flatten($text);
		}
		return $text;
	}

	function as_grid($module="", $args="") {
		header("Content-Type: application/json");
		$array	=$this->as_array($module, $args);

		$total	=count($array);
		$data	=["total"=>$total, "rows"=>$array];
		$text	=json_encode($data);
		return $text;
	}

	function as_select($module="", $args="") {
		header("Content-Type: application/json");
		$array	=$this->as_array($module, $args);
		$data	=["results"=>$array];
		$text	=json_encode($data);
		$text	=str_replace('"name"', '"text"', $text);
		return $text;
	}

	function as_suggest($module="", $args="") {
		header("Content-Type: application/json");
		$array	=$this->as_array($module, $args);
		$data	=["suggestions"=>$array];
		$text	=json_encode($data);
		return $text;
	}

	function as_json($module="", $args="") {
		header("Content-Type: application/json");
		$array	=$this->as_array($module, $args);
		$text	=json_encode($array);
		return $text;
	}

	function nestSelect($module="", $args="") {
		header("Content-Type: application/json");
		$array	=$this->as_tree($module, $args);
		$data	=["results"=>$array];
		$text	=json_encode($data);
		$text	=str_replace('"name"', '"text"', $text);
		return $text;
	}

	function nestGrid($module="", $args="") {
		header("Content-Type: application/json");
		$data	=$this->as_tree($module, $args);
		$text	=json_encode($data);
		return $text;
	}

	function nestJson($module="", $args="") {
		$text	=$this->nestGrid($module, $args);
		$text	=str_replace('"name"', '"text"', $text);
		return $text;
	}

	function nestArray($module="", $args="") {
		return $this->as_nest($module, $args);
	}

}