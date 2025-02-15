<?php
namespace Teescripts\RptForms;

use Teescripts\RptForms\main;

class tsp_lists extends main
{
	public $page, $rows, $array, $search, $data;
	
	function __construct() {
		$this->array="";
		$this->data	=$_GET;
		$this->page	=$this->arrayKey("page", $this->data);
		$this->rows	=$this->arrayKey("rows", $this->data);
		$this->search	=$this->arrayKey("q", $this->data);
		
		if (!$this->page) $this->page=1;
		if (!$this->rows) $this->rows=40;
    }
	
    public function getArray($module="") {
		$item	=$this->arrayKey("array", $_GET);
		$result	=[];
		if ($module==$item) {
			$result	=$this->varKey("array_{$item}");
			$this->array=1;
		}
		return $result;
    }
	
    public function listLoader($module="", $args="") {
		$arguments	=$args;
		$list_text	=$this->varKey($module);
		if ($list_text) {
			$list_text	=$this->fetchList($list_text, 1, 1);
			$list_text	=str_replace('->label(', '->splitLabel(', $list_text);

			$result	=[$list_text];
			if (strstr($list_text, '"')||strstr($list_text, "(")) {
				if (strstr($list_text, '=>')&&strstr($list_text, ";")) {
					$result	=$this->arrayConvert($list_text, "list");//tree
				}
				else {
					$result	="return {$list_text}";
					$result	=@eval($result);
				}
				//echo print_r($result,1)."\n";
			}
			return $result;
		}
    }
	
    public function viewLoader($module="", $args="", $replace=1) {
		$view_text	=$this->arrayKey($module, $GLOBALS);

		if ($view_text) {
			$text_map	='$this->splitMap';
			$text_view	='$this->splitView';
			
			$view_text	=str_replace('->', '")->', $view_text);
			$view_text	=str_replace('")")', '")', $view_text);
			$view_text	=str_replace('$this")->', '$this->', $view_text);
			$view_text	=str_replace('$ts_', '$this->load("', $view_text);
			$view_text	=str_replace('$this->load("main")', '$this', $view_text);
			$view_text	=str_replace('$this->view', $text_view, $view_text);
			if (in_array($replace, [2, 4])) {
				$view_text	=str_replace('$this->map', $text_map, $view_text);
				$view_text	=str_replace('$this->valueMap', $text_map, $view_text);
			}
			$text	=str_replace('[f]', "", $view_text);
			$array	=explode('[/f]', $text);
			
			$query	=$this->arrayKey(0, $array);
			$other	=$this->arrayKey(1, $array);
			$other	=ltrim($other, ":");

			$array	=explode(':', $other);
			$link	=$this->arrayKey(0, $array);
			$attrib	=$this->arrayKey(1, $array);
			$array	=[$query, $link, $attrib];
			
			$result	=$query;
			$ignore	=strstr($text, '"')||strstr($text, "(");
			if ($ignore&&in_array($replace, [1, 2])) {
				$query	=str_replace('"#"', "\"{$args}\"", $query);
				$query	=str_replace("'#'", "'{$args}'", $query);
				$result	="return {$query};";
				$result	=eval($result);
			}
			return $result;
		}
	}
	
    public function loadList($module="", $args="") {
		$array	=$this->getArray($module);
		if ($this->array==1) {
			$query	=$array;
			$module	="array_{$module}";
		}
		else {
			$module	="list_{$module}";#"{$module}List";
			$query	=$this->listLoader($module, $args);
		}
		return $query;
    }
	
    public function loadView($module="", $args="", $replace=1) {
		#$module	="{$module}View";
		$module	="view_{$module}";
		$result	=$this->viewLoader($module, $args, $replace);
		if (is_array($result)) $result=json_encode($result);
		return $result;
    }
	
	public function splitData($query, $column_key="", $column_value="", $decrypt="") {
		$query1	=str_replace(":", "[c]", $query);
		$query1	.=":{$column_key}:{$column_value}";
		$result	=[$query1];
		return $result;
	}
	
    public function splitLabel($query1, $query2="", $query3="", $decrypt="") {
		$result	=[$query1, $query2, $query3];
		return $result;
	}
	
    public function splitView($query, $separate="", $format="") {
		$result	=[$query, $separate, $format];
		return $query;
	}
	
    public function splitMap($list, $value="") {
		$result	=[$list, $value];
		return $result;
	}
	
    public function loadItem($module="", $args="") {
		$query	=$this->listLoader($module, $args);
		return $query;
    }
	
    public function arrayRow($array_items="", $args="", $type="", $array_tree=null, $loops=1, $loop=0) {
		$loop++;
		$item	=array_shift($array_items);
		
		if (is_array($item)) {
			$chip	=$this->arrayKey(0, $item);
			if (!$chip) $chip=array_keys($item);
		}
		else {
			$chip	=explode(":", $item);
		}
		$block	=$this->arrayKey(0, $chip);
		$col_key=$this->arrayKey(1, $chip);
		$col_val=$this->arrayKey(2, $chip);
		
		$block	=str_replace('[c]', ":", $block);
		$col_val=str_replace('[c]', ":", $col_val);
		
		if ($col_key&&!$col_val) $col_val=$col_key;
		if (!$col_key) $col_key="id";
		if (!$col_val) $col_val="name";
		$acolms	=explode(",", $col_val);
		
		if ($this->array==1) {//print_r($item);
			//$array_rows	=$item;
			//if (is_array($array_rows)) $total=count($array_rows);
		}
		else {
		}
		//exit;
		$total	=0;
		if (is_array($item)) {
			$array_rows	=$item;
			if (is_array($array_rows)) $total=count($array_rows);
		}
		else {
			if (strstr($item, '=>')) {
				$array_rows	=$this->arrayConvert($item);
				if (is_array($array_rows)) $total=count($array_rows);
			}
			else {
				$special="-_:;.,+[](){}|/'\"&*&^%#@!~`<>?=";
				$builder='$this->load("sql")';
				$query	=str_replace("{v}", $args, $block);
				$query	=str_replace("[v]", $args, $query);
				$query	=str_replace('$ts_sql->', $builder.'->', $query);
				if (strstr($query, $builder)) {
					$nquery	=str_replace("'v'", "{$args}", $query);
					$nquery	=str_replace('\"', '"', $nquery);
					$nquery	=str_replace(".#", "{$args}", ".{$nquery}");
					$nquery	=ltrim($nquery, ".");
					if ($this->filter) {
						foreach ($acolms as $colm) {
							$colm	=trim($colm);
							if (!strstr($special, $colm)) {
								$nquery	.='->having_or("'.$colm.'", "'.$this->search.'", "like")';
							}
						}
					}
					$nquery	=trim($nquery, ";");
					$nquery	='return '.$nquery.'->get_select();';
					$nquery	=@eval($nquery);
				}
				else {
					$nquery	=str_replace("'v'", "'{$args}'", $query);
					$nquery	=str_replace("'#'", "'{$args}'", $nquery);
					if ($this->filter&&$query) {
						$phrase	=[];
						foreach ($acolms as $colm) {
							$colm	=trim($colm);
							if (!strstr($special, $colm)) {
								$phrase[]	="`{$colm}` LIKE '%{$this->search}%'";
							}
						}
						$phrase	=implode(" OR ", $phrase);
						if ($phrase) $phrase="({$phrase})";
						$nquery	=$this->sqlHaving($phrase, $nquery);
					}
				}

				$array_rows	=[];
				if ($query) {
					$total	=$this->load("page")->pageCount($nquery);
					
					$start	=($this->rows * $this->page) - $this->rows;
					if ($total && $this->page) $nquery.=" LIMIT {$start}, {$this->rows}";
					$array_rows	=$this->query($nquery);
				}
			}
		}
		
		$tree	=[];
		$steps	=[];
		if (is_array($array_rows)) {
			
			$count_row	=($total - 1);
			foreach ($array_rows as $num1=>$array_row) {
				if (is_array($item)) {
					$row_key	=$num1;
					$row_val	=$array_row;
				}
				else {
					$row_key	=$this->arrayKey($col_key, $array_row);
					$row_val	=$this->conCat($col_val, $array_row);					
				}
				if (!$row_val) $row_val=$row_key;
				$row_val	=str_replace('[cm]', ",", $row_val);

				$rows_child	=[];
				if ($array_items) $rows_child=$this->arrayRow($array_items, $row_key, $type, $array_tree, $loops, $loop);
				$value_child	=$this->arrayKey("step", $rows_child);
				$array_child	=[];
				if ($value_child) $array_child=$value_child;
				$count_child	=count($array_child);
				
				if ($type==1) {
					$array	=["p{$row_key}"=>$row_val];
					$array	=array_merge([$row_key=>$array], $array_child);
					$tree	=array_merge($tree, $array);	
					$steps	=$tree;
				}
				elseif ($type==2) {
					$text	="{$row_key}=>{$row_val}";
					
					if ($loops>1) {
						$child	=implode(";", $array_child);
						if ($loop==1) $tree["<{$row_key}"]	="{$row_val};{$child}>";
					}
					else {
						$tree[$row_key]	=$row_val;
					}
					$steps[]	=$text;
				}
				elseif ($type==3) {
					$array	=["id"=>$row_key, "name"=>$row_val];
					$array_row	=array_merge([$array_row], $array_child);
					$tree	=array_merge($tree, $array_row);
					$steps	=$array;
				}
				elseif ($type==4) {
					$array	=["id"=>$row_key, "name"=>$row_val];
					if ($count_child) {
						$array["label"]	=$row_val;
						$array["optgroup"]	=$row_key;
					}
					$array_row	=array_merge([$array_row], $array_child);
					$tree	=array_merge($tree, $array_row);
					$steps	=$array;
				}
				else {
					$array	=["id"=>$row_key, "name"=>$row_val];
					if ($count_child) $array=array_merge($array, ["count"=>$count_child, "children"=>$array_child]);
					
					/*
					$label	=$row_val;
					$parent	=$row_key;
					if ($count_child) {
						if ($row_key!=$parent) {
							$array["label"]	=$label;
							$array["group"]	=$parent;
							$array["parent"]=$parent;
						}
					}
					*/
					$tree[]	=$array;	
					$steps	=$tree;
				}
				
			}
		}
		
		$array	=["total"=>$total, "rows"=>$tree, "step"=>$steps];
		
		return $array;
    }
	
    public function nestArray($array="", $args="", $type="", $count=1) {
		$filter	=false;
		if ($this->search&&!in_array($type, [1, 2])) $filter=true;
		$this->filter	=$filter;

		if ($this->array==1) {
			$result	=["rows"=>$array, "total"=>count($array)];
		}
		else {
			$result	=$this->arrayRow($array, $args, $type, [], $count);
		}
		$data	=$this->arrayKey("rows", $result, []);
		$total	=$this->arrayKey("total", $result);

		$next	="";
		$page	=$this->page;
		$rows	=$this->rows;
		$pages	=ceil($total / $rows);
		if ($page < $pages) $next=($page + 1);
		
		$array	=["total"=>$total, "each"=>$rows, "pages"=>$pages, "page"=>$page, "next"=>$next, "rows"=>$data];
		if ($type==2) $array=($data);
		//echo print_r($array,1)." $total \n";
		return $array;
    }
	
    public function loadFull($module="", $args="") {
		$type	=$this->arrayKey("type", $this->data);
		$module	=$this->getQuery($module, $args);
		$tree	=$this->nestArray($module, $args);
		
		$result	=$tree;
		if ($type) $result=$this->arrayKey($type, $tree, $tree);
		$text	=json_encode($result);
		
		return $text;
	}
	
    public function loadGrid($module="", $args="") {
		header("Content-Type: application/json");

		$module	=$this->getQuery($module, $args);
		$tree	=$this->nestArray($module, $args, 3);
		$text	=json_encode($tree);
		
		return $text;
	}
	
    public function loadJson($module="", $args="") {
        return $this->loadExcel($module, $args);
    }
	
    public function loadExcel($module="", $args="") {
		header("Content-Type: application/json");
		
		$module	=$this->getQuery($module, $args);
		$tree	=$this->nestArray($module, $args, 3);

		$rows	=$this->arrayKey("rows", $tree);
		$text	=json_encode($rows);

		$row1	=$this->arrayKey(0, $rows);
		$keys	=array_keys($row1);
		$col1	=$this->arrayKey(0, $keys);
		$col2	=$this->arrayKey(1, $keys);

		$text	=str_replace('"'.$col1.'"', '"id"', $text);
		$text	=str_replace('"'.$col2.'"', '"name"', $text);
		#value, text, title, image, group, color
        return $text;
    }
	
    public function loadSuggest($module="", $args="") {
		header("Content-Type: application/json");
		
		$module	=$this->getQuery($module, $args);
		$tree	=$this->nestArray($module, $args, 3);

		$rows	=$this->arrayKey("rows", $tree);

		$array	=array("suggestions"=>$rows);
		$text	=json_encode($array);

		$text	=str_replace('"id"', '"value"', $text);
		$text	=str_replace('"name"', '"data"', $text);
		$text	=str_replace('"title"', '"html"', $text);

        return $text;
    }
	
    public function loadSelect($module="", $args="") {
		header("Content-Type: application/json");
		
		$module	=$this->getQuery($module, $args);
		$tree	=$this->nestArray($module, $args);

		$next	=$this->arrayKey("next", $tree);
		$rows	=$this->arrayKey("rows", $tree);
		$more	=($next)?true:false;

		$array	=array("results"=>$rows, "pagination"=>["more"=>$more]);
		$text	=json_encode($array);

		$text	=str_replace('"name"', '"text"', $text);
        return $text;
    }
	
    public function loadText($module="", $args="") {
		$module	=$this->getQuery($module, $args);
		$tree	=$this->nestArray($module, $args, 2);
		
		$rows	=$this->arrayKey("rows", $tree);
		$text	=implode(";", $rows);

		return $text;
    }
	
    public function loadArray($module="", $args="") {
		$module	=$this->getQuery($module, $args);
		$tree	=$this->nestArray($module, $args, 1);
		$rows	=$this->arrayKey("rows", $tree);
		#echo json_encode($rows);
		return $rows;
	}
	
    public function loadQuery($module="", $args="") {
		$query	=$this->getQuery($module, $args, 1);
		return $query;
    }
	
    public function getQuery($module="", $args="", $key="") {
		$module	=$this->loadList($module, $args);
		if ($key!=="") $module=$this->arrayKey($key, $module);
		return $module;
    }
	
    public function get($module="", $args="", $other="") {
		return $this->loadView($module, $args, $other);
    }
	
    public function view($module="", $bind="", $args="", $other="") {
		return $this->loadView($module, $args, $other);
    }
	
    public function item($module="", $args="") {
		return $this->loadItem($module, $args);
    }
	
    public function text($module="", $args="") {
		return $this->loadText($module, $args);
    }
	
    public function array($module="", $args="") {
		return $this->loadArray($module, $args);
    }
	
    public function full($module="", $args="") {
		return $this->loadFull($module, $args);
    }
	
    public function json($module="", $args="") {
		return $this->loadJson($module, $args);
    }
	
    public function grid($module="", $args="") {
		return $this->loadGrid($module, $args);
    }
	
    public function excel($module="", $args="") {
		return $this->loadExcel($module, $args);
    }
	
    public function select($module="", $args="") {
		return $this->loadSelect($module, $args);
    }
	
    public function suggest($module="", $args="") {
		return $this->loadSuggest($module, $args);
    }
	
	public function data($query, $column_key="", $column_value="", $decrypt="") {
		$column1	=str_replace(":", '[c]', $column_key);
		$column2	=str_replace(":", '[c]', $column_value);
		$query1		=str_replace(":", '[c]', $query);
		$query1		="{$query1}:{$column1}:{$column2}";

		$list	=$this->label($query1, "", "", $decrypt);
		return $list;
	}
	
	public function label($query1, $query2="", $query3="", $decrypt="") {
		$process	=$this->varKey("process");
		if (!$decrypt) $decrypt=$process;
		if ($decrypt==2) $decrypt=0;

		$count	=1;
		if ($query2) $count=2;
		if ($query3) $count=3;

		$array	=[$query1, $query2, $query3];
		if ($decrypt) {
			$result	=$this->nestArray($array, "", 2, $count);
			$rows	=$this->arrayKey("rows", $result);
			if ($rows) $result=implode(";", $rows);
			
			$list	=$this->arrayFix($result);

			$joiner	='[cm]';
			$list	=str_replace(",", $joiner, $list);
			$list	=str_replace(":", "[c]", $list);
			
			$list	=str_replace("&amp;", ' & ', $list);			
			$list	=str_replace("*", "x", $list);
			$list	=str_replace('"', "'", $list);
			$list	=str_replace("&raquo;", "»", $list);
			$list	=str_replace("  ", " ", $list);
		}
		else {
			$text	=[];
			foreach ($array as $key=>$query) {
				if (is_array($query)) {
					$query	=$this->arrayFix($query);
				}
				else {
					$query	=str_replace("'", "\'", $query);
					$query	=str_replace('->get_select()', '', $query);
					$query	=str_replace('$ts_sql->', '$this->load("sql")->', $query);
				}
				$text[]	="'{$query}'";
			}
			$text	=implode(",", $text);
			$text	='$this->load("lists")->label('.$text.',"1");';
			$list	=base64_encode($text);
		}
		return $list;	
	}
	# end methods
}
