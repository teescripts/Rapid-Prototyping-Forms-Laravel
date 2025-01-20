<?php
namespace Tee\RptForms;

use Tee\RptForms\table;
class layout extends table
{
	function __construct() {
		parent::__construct();
	}
	
	function table($fields, $layout="div", $axis="vertical", $results="", $query="") {
		$result	=$this->render($fields, $layout, $axis, $results, $query);
		return $result;
	}

	function render($fields, $layout="div", $axis="vertical", $results="", $query="") {
		if (in_array($layout, ["data", "rows"])) {
			$result	=$this->data($fields, $results, 1);
		}
		elseif (in_array($layout, ["easyui", "eui"])) {
			$result	=$this->easyuiTable($fields, $attribute);
		}
		elseif (in_array($layout, ["excel", "jexcel", "jspread"])) {
			$result	=$this->jExcelColms($fields);
		}
		elseif (in_array($layout, ["hot", "hson", "handson"])) {
			$result	=$this->hsonColms($fields);
		}
		else {
			$result	=$this->htmlTable($fields, $layout, $axis, $results, $query);
		}
		return $result;
	}

	function colms($type, $columns, $option="fields") {
		if (in_array($type, ["excel", "jexcel", "jspread"])) {
			$result	=$this->jExcelColms($columns);
		}
		elseif (in_array($type, ["easyui", "eui"])) {
			$result	=$this->easyuiColms($columns, $option);
		}
		elseif (in_array($type, ["hot", "hson", "handson"])) {
			$result	=$this->hsonColms($columns, $option);
		}
		else {
			$result	=$this->columns($columns, $option);
		}
		return $result;
	}

	function getQuery($type, $order="", $where="") {
		if (in_array($type, ["easyui", "eui"])) {
			$result	=$this->easyuiQuery($order, $where);
		}
		elseif (in_array($type, ["dt", "dtables", "datatable", "datatables"])) {
			$result	=$this->dtQuery($order, $where);
		}
		else {
			$result	=$this->flexiQuery($order, $where);
		}
		return $result;
	}


	function euiTable($fields, $attribute=[]) {
		return $this->render($fields, "easyui", $attribute);
	}

	function euiColms($columns) {
		return $this->colms("easyui", $columns);
	}

	function euiQuery($order="", $where="") {
		return $this->getQuery("easyui", $order, $where);
	}
	#----------- end methods
}
