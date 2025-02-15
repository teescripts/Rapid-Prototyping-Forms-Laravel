<?php
namespace Teescripts\RptForms;

use Teescripts\RptForms\lists;

class data extends lists {
	function __construct() {
		parent::__construct();
	}

	function contTypeList($dataid="") {
		$temp	="`type_id`=':v' OR `type_code`=':v' OR `type_name`?";
		$where	="WHERE `type_status`=1";
		if ($dataid) $where.=" AND `type_id` IN({$dataid})";
		if ($this->search()) {
			$temp	=str_replace("?", " LIKE '%:v%'", $temp);
			$temp	=str_replace(":v", ":phrase", $temp);
			$where	.=" AND ({$temp})";
		}
		$join	="`#1_cont_types`";
		$colms	="`type_id` AS `id`, `type_name` AS `name`, `width` AS `width`";
		$colms	.=", `height` AS `height`, `type_summary` AS `summary`, `type_image` AS `image`";
		$query	="SELECT {$colms} FROM {$join} {$where} ORDER BY `rank`, `type_name` ASC";
		return $query;
	}

	function contTypeView($dataid="") {
		$where	="WHERE `type_id`='{$dataid}'";
		$query	="SELECT `type_name` FROM `#1_cont_types` {$where}";
		return $query;
	}

	function statusList($dataid="") {
		$query	="1=>Active;2=>Deactivated";
		return $query;
	}

	function statusView($dataid="") {
		return $this->map("list_status", $dataid, 2);
	}

	function dateView($dataid="") {
		return ($dataid)?$this->dateFormat($dataid, "l dS F Y"):"";
	}

	function lettersView($dataid="") {
		return $this->letters($dataid, "20");
	}

	function wordsView($dataid="") {
		return $this->words($dataid, "15");
	}

	function ageView($dataid="") {
		return $this->age($dataid, "year,month");
	}

	function numberView($dataid="") {
		return ($dataid)?number_format($dataid):"-";
	}


	function faIconView($dataid="") {
		return ($dataid)?"<i class=\"fas fa-".$dataid." fa-2x\"></i>":"-";
	}

	function actionsList($dataid="") {
		$query	="select=>View all;insert=>Add new;ie=>Edit;search=>Search;view=>View details;update=>Update;print=>Print;delete=>Delete";
		return $query;
	}

	function actionsView($dataid="") {
		return $this->tags($dataid,"list_actions", 2);
	}

}