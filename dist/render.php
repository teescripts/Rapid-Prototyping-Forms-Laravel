<?php

namespace Tee\RptForms;

use PhpParser\Node\Stmt\TraitUseAdaptation\Alias;
use Tee\RptForms\main;

class render extends main
{
	function __construct() {
		parent::__construct();
	}
	
    public function columns($fields, $type="", $plain="") {
		$line	=[];		
		$keys	=["view", "print", "update", "delete", "btn", "vb"];
		$fields	=$this->replace($fields);# remove commas in the fields
		$split	=explode(",", $fields);
		$comma	=varKey("comma");
		
		$count_keys	=0;
		$count_cols	=count($split);
		foreach ($keys as $key) {
			if (stristr($fields, ":{$key}:")) $count_keys++;
		}
		$count_hide	=substr_count($fields, ":hidden");
		$count_all	=($count_cols - $count_keys - $count_hide);
		
		$width_key	=3;
		$width_keys	=($width_key * $count_keys);
		$width_cols	=(100 - $width_keys);
		$width_col	=$width_cols;
		if ($count_all>0) $width_col=($width_cols / $count_all);
		$width_col	=round($width_col, 1);
		
		foreach ($split as $col_count=>$array_colm) {
			$text	=str_replace($comma, ",", $array_colm);
			$text	=str_replace("(key)", $col_count, $text);
			$file	=explode(":", $text);
			#--------------- field name
			$line_name	=$this->colonKey(0, $file);
			$block_2	=$this->colonKey(1, $file);
			
			if (strstr(".{$block_2}", ".view_")) {
				$ntype	=varKey($block_2);
				if ($ntype) {
					if (!$plain) {
						$nline	=$this->replace($ntype);
						$nline	=str_replace($comma, ",", $nline);
						$ntext	=str_replace($block_2, $nline, $text);
						if ($text!=$ntext) $file=explode(":", $ntext);
						$block_2	=$this->colonKey(1, $file);
					}
				}
				else {
					$block_2	='';
				}
			}
			
			$block_3	=$this->colonKey(2, $file);
			$block_4	=$this->colonKey(3, $file);
			$block_5	=$this->colonKey(4, $file);
			$block_6	=$this->colonKey(5, $file);
			
			$attrib	=$block_4;
			if ($block_2=="" && $block_3) $attrib=$block_3;
			if ($block_2=="input") $attrib=$block_6;
			$tags	=$this->arrayFormat($attrib);

			$is_button	=in_array($block_2, $keys);
			$naming	=$this->naming($line_name);
			$label	=$naming["label"];
			$name	=$naming["name"];
			$alias	=$naming["alias"];
			if ($is_button) $label="&nbsp;";
			if ($block_3=="hidden") $label="";

			$right	="";
			if (strstr($block_2, "number_format")) $right=1;
			if (strstr($block_3, "decimal=")) $right=1;
			if (strstr($block_4, "number")) $right=1;
			if (strstr($block_4, "-right")) $right=1;
			if (strstr($block_6, "-right")) $right=1;

			$align	="left";
			if ($right) $align="right";
			$align	=arrayKey("align", $tags, $align);
			
			$class	="cell_th";
			if ($is_button) $class="cell_empty";
			if ($block_2=="vb") $class="cell_vb";
			if ($block_2=="btn") $class="cell_btn";
			if ($block_2=="check") $class="cell_check";
			if ($block_3=="hidden") $class="cell_hide hidden";
			if ($align=="right") $class.=" tx-right-f";

			$width	=round($width_col, 2);
			if ($block_3=="hidden") $width=0;
			if (in_array($block_2, $keys)) $width=$width_key;
			if (strstr($attrib, 'width=')) {
				$width	=arrayKey("width", $tags);
				$text	='width="'.$width.'"';
				$block_3	=str_replace($text, "", $block_3);
				$block_4	=str_replace($text, "", $block_4);
				$block_6	=str_replace($text, "", $block_6);
			}

			$array	=[];
			$array["name"]	=$name;
			$array["label"]	=$label;
			$array["alias"]	=$alias;
			$array["class"]	=$class;
			$array["width"]	=$width;
			$array["align"]	=$align;
			if ($block_2) $array["type"]=$block_2;
			if ($block_3) $array["input"]=$block_3;
			if ($block_4) $array["validate"]=$block_4;
			if ($block_5) $array["value"]=$block_5;
			if ($attrib) $array["attrib"]=$attrib;
			
			$line["array"][]=$array;
			$line["head"][]	='<th class="'.$class.'"><span class="nobr">'.$label.'</span></th>';
			$line["json"][]	='{ "sTitle": "'.$label.'", "mDataProp": "'.$name.'", "bSortable": '.($class=="cell_hide"?"false":"true").', "bSearchable": '.($class=="cell_hide"?"false":"true").', "bVisible": '.($class=="cell_hide"?"false":"true").', "sClass": "'.$class.'" }';
			//"sName": "'.$name.'", "asSorting": ["asc"], "sCellType": "td", "sType": "html", "sWidth": "5%", "mDataProp": "'.$name.'.inner.0"
		}
		$head	=implode("\r\n\t\t\t", $line["head"]);
		$json	=implode(", ", $line["json"]);
		$array	=["fields"=>$line["array"], "head"=>$head, "json"=>$json];
		$array	=arrayKey($type, $array, $array);
		return $array;
    }
	
	# ------ rows, columns, blocks
	function rowCols($colms, $blocks="", $count="") {
		if (!$blocks) {
			if ($colms) {
				if (!is_array($colms)) {
					$array	=explode(",", $colms);
				}
				else {
					$span	=arrayKey(0, $colms, 12);
					$block	=0;
					if (is_array($span)) {
						if (count($span)==3) $block=1;
					}
					
					if ($block) {
						$blocks	=$colms;
					}
					else {
						$array	=[$span, 1, $count];
					}
				}
			}
			else {
				$array	=[4, 1, $count];
			}

			if (!$blocks) {
				if ($count>7) $array=[4, 1, 2];
				if ($count>11) $array=[4, 1, 3];
				if ($count>15) $array=[3, 1, 4];
				
				$span	=arrayKey(0, $array, 12);
				$cols	=arrayKey(1, $array, 1);
				$max	=arrayKey(2, $array, $count);
				
				if ($span<2) $span=(12/$span);
					
				if ($count>=$max) {
					$box	=(12 / $span);
					$rsize	=($count / $box);
				}
				else {
					$box	=$max;
					$rsize	=($count / $max);
				}

				$blocks	=[];
				$remain	=$count;
				for ($block=1; $block<=$box; $block++) {
					$rows	=ceil($rsize);
					if ($block==$max) $rows=floor($rsize) + 2;
					if ($remain>0) $blocks[]=[$span, $cols, $rows];
					$remain	=($remain - $rows);
				}
			}
		}
		
		$array	=[];
		$numCol	=$count;
		$remain	=$count;
		foreach ($blocks as $key=>$row) {
			if (!is_array($row)) {
				$row	=str_replace(" ", "", $row);
				$row	=explode(",", $row);
			}
			$span	=arrayKey(0, $row, 1);
			$cols	=arrayKey(1, $row, 1);
			$rows	=arrayKey(2, $row, $numCol);

			if (!is_array($cols)) {
				$cols	=str_replace(" ", "", $cols);
				$cols	=explode(",", $cols);
			}
			
			$size	=[];
			foreach ($cols as $row1) {
				if (is_array($row1)) {
					$size[]	=$row1;
				}
				else {
					$rsize	=floor(12/$row1);
					$block	=[];
					for ($x=1; $x<=$row1; $x++) {
						$block[]	=$rsize;
					}
					$size[]	=$block;
				}
			}
			$cols	=1;
			$cols	=$size;
			
			$numCol	=($numCol - $rows);
			if ($remain>0) $array[$key]	=["span"=>$span, "rows"=>$rows, "cols"=>$cols];
			#$remain	=($remain - $cols);
		}
		
			//echo ":".'<pre>'.print_r($array, 1).'</pre>';
		return $array;	
	}

	public function rowFields($fields, $start=0, $limit=1) {
		$array	=[];
		$end	=($start + $limit);
		for ($count=$start; $count<$end;) {
			$row	=arrayKey($count, $fields);
			$row	=arrayKey("{$count}", $fields, $row);
			
			$step	=0;
			if ($row) {
				$type	=arrayKey("input", $row);
				if (in_array($type, ["filex", "textareax"])) {
					$limit++;
					$array["t2"][]	=$row;
				}
				else {
					$array["t1"][]	=$row;
				}
			}
			$count++;
		}
		$result	=["fields"=>$array, "start"=>$count];
		return $result;
	}

	public function htmlTable($fields_list, $layout="div", $axis="vertical", $results="", $query="") {
		$text_global	='form_method,form_path,form_tags,form_class,table_class,table_id,table_attr,table_header,table_footer,table_body,table_foot,thead_class,ts_extra_row,ts_extra_vrow,th_class,style,btn_value,btn_class,btn_prefix,colors,tip_class,item,get_order,get_col,this_url,app_extension,mobile_device,screen_size';
		
		$global	=$this->globalVars($text_global);
		extract($global);
			
		$row_colms	=varKey("row_cols");
		$row_blocks	=varKey("row_blocks");
		$row_class	=varKey("row_class", "row no-gutters");
		$span_class	=varKey("span_class", "col-md-");
		$block_class=varKey("block_style");
		# -------------- display and number of columns
		$array_layout	=["div", "table"];
		$array_axis		=["vertical", "horizontal"];
		if (!in_array($layout, $array_layout)) $layout=$array_layout[0];
		if (!in_array($axis, $array_axis)) $axis=$array_axis[0];
		
		# alter vertical table layout cols to div
		$templates	=$this->load("template")->template();
		$array_types=array_keys($templates);
		$temp_form	="B4";
		foreach ($array_types as $type) {
			if (strstr($form_class, $type)) $temp_form=$type;
		}

		$temp_alt	=inArray("{$temp_form}-ALT", $array_types, "ALT");
		$temp_btn	=inArray("{$temp_form}-BTN", $array_types, $temp_alt);
		$array_temp	=[
			arrayKey($temp_form, $templates), 
			arrayKey($temp_alt, $templates), 
			arrayKey($temp_btn, $templates)
		];
		
		$GLOBALS["temp_array"]	=$array_temp;
		$alter_layout	=($axis=="vertical"&&!is_array($row_colms));
		$material_form	=stristr($temp_form, "M");
		$material_field	=stristr(varKey("select_class"), "material");
		if ($alter_layout&&($material_form||$mobile_device||$material_field)) $row_colms=[$row_colms];
		
		if (is_array($row_colms)&&$axis=="vertical") {
			//$layout	="div";
		}
		# ---------- submit button
		$is_form	=!in_array($form_tags, ["no", "false", "none", "hide", '0']);
		if (!$btn_class) $btn_class="btn btn-primary";
		if (!$btn_value) $btn_value="Save";
		if ($btn_value=="hide") $btn_value="";
		
		$load_data	=($btn_value==$this->lang("load_data"));
		$count_input=(count(explode(":input:", $fields_list))>count(explode("display=", $fields_list)));
		if ($count_input&&$btn_value!=""&&$axis!="horizontal") {
			if ($load_data) $load_data=1;
			$fields_list	.=',send:btn:'.$btn_value.':class="'.$btn_class.'"';
		}
		$field_array	=$this->columns($fields_list, "fields");
		
		$count_fields	=count($field_array);
		$half_fields	=($count_fields/2);
		if ($screen_size) {
			if ($screen_size<=600) $row_blocks=[[6, 1, floor($half_fields)], [6, 1, ceil($half_fields)]];
			if ($screen_size<=300) $row_blocks=[[12, 1, ($count_fields)]];
		}

		$line_path	="";
		foreach ($field_array as $array) {
			$name	=arrayKey("name", $array);
			$type	=arrayKey("type", $array);
			$input	=arrayKey("input", $array);
			$attrib	=arrayKey("attrib", $array);
			
			if ($type=="check"&&$input) $line_path=$input."&column=".$name.$app_extension;
		}
		//echo '<pre>'.print_r(($field_array),1).'</pre>';

		$array_global	=["form_method"=>$form_method, "form_path"=>$form_path, "form_class"=>$form_class, "is_form"=>$is_form, "count_input"=>$count_input, "load_data"=>$load_data, "btn_value"=>$btn_value, "btn_class"=>$btn_class, "axis"=>$axis, "line_path"=>$line_path, "tip_class"=>$tip_class, "btn_prefix"=>$btn_prefix, "colors"=>$colors];
		
		$array_form	=$this->formTags($array_global);

		$table_array	=["field_list"=>$fields_list, "layout"=>$layout, "axis"=>$axis, "global"=>$text_global];

		if ($axis=="horizontal") {
			$table_array["row_cols"]	=$row_colms;
			$table_array["field_array"]	=$field_array;
			$text_block	=$this->table2($table_array, $results, $query);
		}
		else {
			$ct_hidden	=substr_count($fields_list, ":hidden");
			$ct_submit	=substr_count($fields_list, ":submit");
			$count_form	=($count_fields - $ct_hidden - $ct_submit);

			$array_block	=$this->rowCols($row_colms, $row_blocks, $count_form);
			$count_blocks	=count($array_block);

			$span_cols	=ceil(12 / $count_blocks);//[3, 4, 4, 4, 6];
			
			$class_alt	=$block_class;
			if (!$block_class) $class_alt=["shadow-base bg-white", "bd bd-1 bg-white"];

			$row_start	=0;
			$text_block	='
			
<div class="'.$row_class.'-not form-start">
		';
			
			$row_large	=[];
			foreach ($array_block as $block=>$row_block) {
				$row_count	=arrayKey("rows", $row_block);
				$row_cols	=arrayKey("cols", $row_block);
				$span_cols	=arrayKey("span", $row_block, 1);
				$array_span	=[
					$span_cols, 
					$span_cols, 
					$span_cols, 
					min(($span_cols + 1), 12), 
					min(($span_cols + 2), 12), 
					min(($span_cols + 3), 12)
				];
				if (!strstr($span_class, "col-")) $array_span=[$span_cols];
				$new_class	=$this->colSpan($span_class, $array_span);

				$array_row	=$this->rowFields($field_array, $row_start, $row_count);
				$row_start	=$array_row["start"];
				$row_array	=$array_row["fields"];
				$row_field	=$row_array["t1"];
				if ($row_field) {
					$row_extra	=arrayKey("t2", $row_array, []);
					$row_large	=array_merge($row_large, $row_extra);
				}
				else {
					$row_field	=$row_large;
				}
				$alt_class	=$class_alt[($block%2)];
				$GLOBALS["next_block"]	=$class_alt[(($block + 1) % 2)];
				$table_array["row_cols"]	=$row_cols;
				$table_array["field_array"]	=$row_field;

				$pad_class	="{$alt_class} pd-0";
				if ($layout=="table") $pad_class="{$alt_class} pd-5";

				$text_block	.='
	<div class="'.$new_class.' mg-b-10">
		<div class="'.$pad_class.'">';
			
				$text_block	.=$this->table2($table_array, $results);

		$text_block	.='
		</div>
	</div>';
			}
			$text_block	.='
</div>
	';
		}

		$table_html	=$array_form["open"];
		$table_html	.=$text_block;
		$table_html	.=$array_form["button"];# put a submit all button
		$table_html	.=$array_form["close"];
		echo $table_html;
	}

	function formTags($array_global) {
		extract($array_global);
		# format form
		$check_all	="&nbsp;";
		$btn_text	="&nbsp;";
		$submit_all	="";
		$form_start	="";
		$form_end	="";

		if ($is_form) {
			# check all + delete
			if ($line_path) {
				$button_data	='data-info="'.$this->lang("delete_info").'" data-yes="'.$this->lang("delete_yes").'" data-no="'.$this->lang("delete_no").'"';
				$check_all		='
			<button type="submit" formaction="'.$this->link($line_path).'" formmethod="post" class="delete_all '.$tip_class.' '.$btn_prefix.$colors[0].'" title="'.$this->lang("delete_all").'" '.$button_data.'><i class="fas fa-times-circle"></i> '.$this->lang("delete_selected").'</button>';
			}
			# table button
			if ($count_input>0&&!$load_data&&$btn_value) {
				$btn_text	=$this->form("submit", "send", $btn_value, $btn_class, "", "", "", true);
				$btn_text	.=$this->form_token();
			}
			# table footer
			$footer	=($btn_text!="&nbsp;"||$check_all!="&nbsp;");
			if ($footer && $axis=="horizontal") $submit_all='
		<div class="table-footer">
			<div class="select_all">'.$check_all.'</div>
			<div class="save_all">'.$btn_text.'</div>
		</div>';
		
			$form_class	="tee_validate tee_table ".$form_class;
			if (!$form_method) $form_method="post";
			if ($form_path) $form_path=$this->link($form_path);

			# form tags
			$form_start	='
<form method="'.$form_method.'" class="'.$form_class.'" action="'.$form_path.'" enctype="multipart/form-data" autocomplete="off">';
		$form_end	='
</form>';
			
		}
		
		$result	=["open"=>$form_start, "close"=>$form_end, "button"=>$submit_all];
		return $result;
	}

	public function table2($array, $result="", $query="") {	

		$axis	=arrayKey("axis", $array, "vertical");
		$layout	=arrayKey("layout", $array, "div");

		$row_cols	=arrayKey("row_cols", $array);
		
		$text_global	=arrayKey("global", $array);
		$fields_list	=arrayKey("field_list", $array);
		$array_field	=arrayKey("field_array", $array);
		#$	=arrayKey("", $array);

		$global	=$this->globalVars($text_global);
		extract($global);
		
		# if not loading results, just fields
		if (!is_array($result)) $result=[[]];		
		# -------------- display and number of columns
		if (is_array($row_cols)&&$axis=="vertical") {
			$row_lines	=count($row_cols);
		}
		else {
			$row_lines	=is_int($row_cols)?intval($row_cols):1;
			if ($row_lines>5) $row_lines=5;
		}
		$get_sort="";
		# ---------- build header
		$init	=[];
		$fields	="";
		$keys	=["view", "print", "update", "delete", "btn", "vb", "check"];
		
		foreach ($array_field as $col_count=>$array) {
			$line_name	=arrayKey("name", $array);
			$line_label	=arrayKey("label", $array);
			$line_type	=arrayKey("type", $array);
			$line_input	=arrayKey("input", $array);
			$line_class	=arrayKey("class", $array);
			$line_width	=arrayKey("width", $array);
			$line_valid	=arrayKey("validate", $array);
			# ---------- to add the order by clause
			$line_valid	=strtolower($line_valid);
			$text_label	=str_replace('"', "'", $line_label);
			$required	=(substr($line_valid, 0, 1)=="r"||$line_valid=="yes");
			#---------------- required 
			$sort_link	='[l][r]';
			$text_req	="";
			if ($required&&$line_input!="hidden") {
				$text_req	=' <span class="require_red" title="'.$text_label.' is required">*</span>';
			}

			if (is_array($get_sort) && $line_label!="&nbsp;") {
				$sort_order	=arrayKey(0, $direction);
				$sort_class	="order";			
				$sort_field	='';
				if (array_key_exists("col", $get_sort)) {
					if (array_key_exists($line_name, $get_sort["col"])) {
						$sort_order	=$get_sort["order"][$line_name];
						$sort_class	=$get_sort["col"][$line_name];				
						$sort_field	='<i class="fas fa-sort-'.$new_order.'"></i> ';
					}
				}
				
				$sort_link	='<a href="'.$sort_path.'" class="sort_[s] tx-dark" title="'.$this->lang("order_by").' [l] '.$this->lang($sort_order).'">[l][r]</a>';//[f]
				$sort_link	=str_replace('[n]', $line_name, $sort_link);
				$sort_link	=str_replace('[f]', $sort_field, $sort_link);
				$sort_link	=str_replace('[o]', $sort_order, $sort_link);
				$sort_link	=str_replace('[s]', $sort_class, $sort_link);
			}
			$sort_link	=str_replace('[r]', $text_req, $sort_link);
			$sort_link	=str_replace('[l]', $text_label, $sort_link);
			
			if ($sort_link!=$line_label) $sort_link='<span class="nobr-alt">'.$sort_link.'</span>';

			if ($line_type=="check") {
				$sort_link	='<input type="checkbox" id="check_all1" class="alt-check check-all '.$tip_class.'" title="Select all" />';
				$sort_link	.=' <label for="check_all1" class="lab-alt-check lab-check-all"></label>';
			}
			$line_field	=in_array($line_name, $init)?$line_name.$col_count:$line_name;			
			if ($layout=="div") {
				$fields	.='
			<div class="data-th '.$line_class.'">'.$sort_link.'</div>';
			}
			else {
				$width	=$line_width;
				if ($th_class) $line_class.=" {$th_class}";
				$align	=arrayKey("align", $array);

				$attrib	="field:'{$line_field}',width:'{$width}%'";
				if ($align!="left") $attrib.=",align:'{$align}'";

				$fields	.='
			<th class="'.$line_class.'" data-options="'.$attrib.'" field="'.$line_field.'" scope="col">'.$sort_link.'</th>';
			}
			$init[]	=$line_field;
		}# end column formats
		
		if (!isset($tr_class)) $tr_class="";
		if ($axis!="horizontal") $table_id="";
		if ($table_id) $table_id='id="'.$table_id.'"';
		$table_class	=varKey("table_class", "table");
		$table_class	.=" axis-{$axis}";
		if (strstr($table_class, "responsive"))	$table_class.=" axis-responsive";
		$table_head		="";
		if (!isset($table_foot)) $table_foot="";
		
		# table tags
		if ($layout=="div") {
			$table_class	=str_replace(varKey('class_table'), '', $table_class);
			$table_class	="data-view axis-{$axis}";

			if ($axis=="horizontal") {
				$table_start='
	<div class="'.$table_class.'">';
				
				$table_end='
	</div><!-- end data-view -->';
	
				$body_start	='
		<div class="data-body">';
		
				$body_end	='
		</div><!-- end data-body -->';

				$table_head	='
	<div class="data-head">
		<div class="data-row [row_style]">'.$fields.'
		</div>
	</div><!-- end data-head -->';
				if ($table_foot) $table_foot='
	<div class="data-foot">
		<div class="data-row [row_style]">'.$fields.'
		</div>
	</div><!-- end data-foot -->';
			}
			else {
				$table_start="\n\t\t\t".'<div class="form-start">';
				$table_end	="\n\t\t\t".'</div><!-- end data-view -->';
				$body_start	="\n\t\t\t\t".'<div class="pd-5-f ">';
				$body_end	="\n\t\t\t\t".'</div><!-- end data-body -->';

				$table_start="";
				$table_end	="";
				$body_start	="";
				$body_end	="";
			}
		}
		else {
			$table_attr	.=$table_id.' class="'.$table_class.' cols'.$row_lines.'" cellspacing="0" cellpadding="0"';

			$table_start='
<table '.$table_attr.'>';
					
			$table_end	='
</table>';
			$body_start	='
	<tbody>';
			$body_end	='
	</tbody>';

			if ($axis=="horizontal") {#
				if ($thead_class=="") $thead_class='[row_style]';

				$table_head	='
	<thead>[table_head]
		<tr class="'.$thead_class.'">'.$fields.'
		</tr>
	</thead>';

				$table_head	=str_replace('[table_head]', "\n\t\t{$table_header}", $table_head);

			}
		}
		
		$html_footer	="";
		# extra table row
		$next_row	=($ts_extra_row&&$axis=="horizontal")?$ts_extra_row:$ts_extra_vrow;
		if ($layout=="table") {
			if ($table_foot) $html_footer='<tr class="[row_style]">'.$fields.'</tr>';
			if ($next_row) $html_footer=$next_row;
			if ($html_footer) $html_footer='<tfoot>'.$html_footer.'</tfoot>';
			if ($table_footer) $html_footer=$table_footer;
		}

		$trow_class	=arrayKey(0, $style);
		if ($tr_class) $trow_class.=" {$tr_class}";
		$table_head	=str_replace('[row_style]', $trow_class, $table_head);
		$table_foot	=str_replace('[row_style]', $trow_class, $html_footer);
		
		$table_html	=$table_start;	
		$table_html	.=$table_head;
		$table_html	.=$body_start;
			
		$total_rows	=count($result);
		if (is_array($result)) {
			# loop results
			foreach ($result as $key=>$row_data) {
				$row_count	=($key + 1);
				# table row
				$table_html	.=$this->row($array_field, $axis, $layout, $row_data, $row_count, $row_cols, $total_rows);
			}
		}
		$table_html	.=$table_body;# table body
		$table_html	.=$body_end;# end table body
		$table_html	.=$table_foot;# table footer
		$table_html	.=$table_end;# end the table tag	
		return $table_html;
	}
	
	#--------------- start the row function
	protected function rowFormat($array, $row_data=[], $col_count="", $row_count="", $multi_rows="") {
		$name		=arrayKey("name", $array);
		$label		=arrayKey("label", $array);
		$type		=arrayKey("type", $array);
		$input		=arrayKey("input", $array);
		$validate	=arrayKey("validate", $array);
		$value		=arrayKey("value", $array);
		$attrib		=arrayKey("attrib", $array);
		#--------------- count replace
		$encode	=json_encode($row_data);
		$encode	=addslashes($encode);

		$array_new	=['(row)'=>$encode, '(ct)'=>$row_count, '(key)'=>$col_count, '(multi)'=>$multi_rows, '(n)'=>$name, '(l)'=>$label];#
		$label		=$this->replaceRow($label, $array_new);
		$type		=$this->replaceRow($type, $array_new);
		$input		=$this->replaceRow($input, $array_new);
		$validate	=$this->replaceRow($validate, $array_new);
		$value		=$this->replaceRow($value, $array_new);
		$attrib		=$this->replaceRow($attrib, $array_new);
		
		# ---------- format row data
		$array_row	=["attrib"=>$attrib, "name"=>$name, "label"=>$label, "input"=>$input, "value"=>$value, "validate"=>$validate, "attrib"=>$attrib];
		
		$array_form	=["type"=>$type, "data"=>$row_data, "row"=>$row_count, "col"=>$col_count, "multi"=>$multi_rows];
		$array_form	=array_merge($array, $array_row, $array_form);
		$new_value	=$this->format($array_form);

		$array_form["value"]	=$new_value;
	
		return $array_form;
	}
	
	#--------------- start the row function
	private function row($array_field, $axis, $layout, $row_data, $row_count, $row_cols, $total_rows=0) {
		global $style, $tr_class, $cell_class, $row_class, $temp_array;
		
		# ---------- columns per row
		$full_row	=0;
		$count_col	=1;
		$count_row	=0;
		if (is_array($row_cols)&&$axis=="vertical") {
			//$layout	="div";
			$array_cols	=$row_cols;
		}
		else {
			$array_cols	=$row_cols;
			if (!is_array($row_cols)) $array_cols=explode(",", $row_cols);
		}
	
		$array_temp	=arrayKey(0, $temp_array);
		$form_temp1	=arrayKey("form", $array_temp);
		$form_label1=arrayKey("label", $array_temp);

		$array_temp	=arrayKey(1, $temp_array);
		$form_temp2	=arrayKey("form", $array_temp, $form_temp1);
		$form_label2=arrayKey("label", $array_temp, $form_label1);

		$array_temp	=arrayKey(2, $temp_array);
		$form_temp3	=arrayKey("form", $array_temp, $form_temp2);

		$trow_class	='[row_style]';
		if ($tr_class) $trow_class.=" {$tr_class}";
		if ($layout=="div") {
			$block_class	="data-row {$trow_class}";
			if ($axis=="vertical") {
				$block_class	="mt0 mb0 row no-gutters {$trow_class}";
				if ($row_class) $block_class=$row_class;
			}

			$row_start	="\n\t\t\t".'<div class="'.$block_class.'">';
			$row_end	="\n\t\t\t"."</div><!-- end data-row -->\n";
		}
		else {
			$row_start	='
		<tr class="'.$trow_class.'">';
			$row_end	="
		</tr>";
		}
		
		# ---------- row style
		$mod_key	=($row_count%2);
		$trow_class	="row".$row_count." ".arrayKey($mod_key, $style);
		$row_text	=str_replace('[row_style]', $trow_class, $row_start);
		# ---------- start row
		$cell_html	=$row_text;
		
		$last_key	=0;
		$multi_rows	=($axis=="horizontal" || $total_rows>1);
		if (varKey("btn_value")=="hide" && $total_rows==1) $multi_rows=0;
		
		$count_cols	=count($array_field);
		# ---------- end ajax stuff
		$keys	=["view", "print", "update", "delete"];
		foreach ($array_field as $col_count=>$array_colm) {
			# ---------- format row data
			$array_keys	=$this->rowFormat($array_colm, $row_data, $col_count, $row_count, $multi_rows);
			
			$line_name	=arrayKey("name", $array_keys);
			$line_label	=arrayKey("label", $array_keys);
			$line_type	=arrayKey("type", $array_keys);
			$line_input	=arrayKey("input", $array_keys);
			$line_valid	=arrayKey("validate", $array_keys);
			$line_value	=arrayKey("value", $array_keys);
			$line_class	=arrayKey("class", $array_keys);
			$line_attrib=arrayKey("attrib", $array_keys);
			
			$is_button	=in_array($line_type, $keys);
			$is_input	=in_array($line_type, ["input", "btn"]);
			#  --------- cell class
			$td_class	="{$line_class}";# overflow-auto
			if ($cell_class) $td_class.=" {$cell_class}";
			
			#----------- data value
			$row_value	=arrayKey($line_name, $row_data);
			//if (is_array($row_value)) $row_value=implode(", ", $row_value);
			if (!is_array($row_value)) $row_value=stripslashes($row_value);
			$row_value	=$this->colons($row_value);
			# ---------- row style
			$line_array	=arrayKey(0, $array_cols);

			$key_no		=($line_array)?($count_row + 1):$row_count;
			$row_key	=($key_no%2);
			$trow_class	="row".$count_row." ".arrayKey($row_key, $style);
			$start_row	=str_replace('[row_style]', $trow_class, $row_start);
			
			if (is_array($line_array)) {
				$row_lines	=count($line_array);
				$rowspan	=arrayKey($last_key, $line_array);
				//if (is_array($line_array)) $rowspan=arrayKey(0, $rowspan);
				$last_key++;
			}
			else {
				$row_lines	=$line_array;
				$row_size	=12;
				if ($row_lines>0) $row_size	=(12/ $row_lines);
				
				$col_mod	=($row_size - floor($row_size)) * $row_lines;
				$rowspan	=floor($row_size);
				if ($col_count==0) $rowspan=ceil($row_size);
				if ($col_count==($row_lines - 1)&&$col_mod>1) $rowspan=ceil($row_size);
			}
	
			# ---------- determine cell type
			$tmp_class	="";
			$is_block	=($line_value==="");
			$is_input	=($is_input||stristr($line_value, '<input'));
			if (!$is_input) {
				$is_block	=(strip_tags($line_value, '<i><a><img><div><button><p><span>')==$line_value);
				if (strstr($line_value, 'btn-')) $is_block=2;
				if (stristr($line_value, '<div')) $is_block=2;
				if (in_array($line_type, ["image", "photo", "preview"])) $is_block=4;
			}
			elseif ($line_type=="btn") {
				$is_block	=4;
				$tmp_class	="tx-right";
				$form_temp2	=$form_temp3;
			}
			elseif (strstr($line_value, "richtext")) {
				$is_block	=4;
			}
			else {
				#if (stristr($line_value, 'date')) $is_block=4;
				#if (!stristr($line_value, 'value=""')) $is_block=1;
				if (in_array($line_input, ["select", "file", "radio", "checkbox", "grid", "tree", "combo", "excel"])) $is_block=4;
			}
			
			if ($axis!="vertical") $is_block=0;

			# ---------- select layout template
			if ($is_block) {
				$html_text		=$form_temp2;
				$nlabel_class	=$form_label2;
				
				if ($is_block>2) $html_text=str_replace([" bd-b", " bb"], ["", ""], $html_text);
				if ($is_block>3) $html_text=str_replace([" pd-y-10", " p5"], [" pd-b-10", " pd-b-10"], $html_text);
			}
			else {
				$html_text		=$form_temp1;
				$nlabel_class	=$form_label1;
			}

			# indentation 
			$pad_indent	="\n\t\t\t\t\t\t";
			$form_indent="\n\t\t\t";
			$html_text	=str_replace("\n", $form_indent, $html_text);
			
			$strip_value=strip_tags($line_value, '<a><span><div><p>');
			$form_html	=$line_value;
			if ($line_input=="textarea") {
				$form_html	=str_replace("<textarea", "\t\t".'<textarea', $form_html);
			}
			elseif ($line_type==""||$strip_value==$line_value) {
				if ($strip_value==="") $form_html="&nbsp;";
				$form_html	='<div class="is-text">'.$form_html.'</div>';
			}
			else {
				$form_html	=str_replace("\n", $form_indent, $form_html);
			}
			
			$form_html	=$form_html.$form_indent."\t\t\t";
			
			if ($line_value=='[head]') {
				$form_html	="";
				$html_text	='
			<div class="card-header">
				<h5 class="card-title mb-0">'.$line_label.'</h5>
			</div>';
			}
			
			# ---------- build cell
			$th_start	="";

			if ($layout=="div") {
				if ($axis=="vertical") {
					$xpan_class	=varKey("span_class", "col-sm-");
					$colm_class	=$xpan_class.$rowspan;

					if ($line_value=='[close]') {
						$next_block	=varKey("next_block");

						$pad_class	="{$next_block} mg-t-15 pd-b-20";

						$form_html	="";
						$td_start	='';
						$th_start	='</div>
		</div>
		<div class="'.$pad_class.'">
			<div class="d-none">';

						$td_end	='';
					}
					else {	
						$td_start	=str_replace("\n", "\n\t\t", $html_text);
						$th_start	="\n\t\t\t\t".'<div class="'.$colm_class.'">';
						$td_end		="\n\t\t\t\t".'</div><!-- end data-item -->';
					}
				}
				else {
					$td_start	='
			<div class="data-td [tc]" id="[dv]">';			
					$td_end		="
			</div><!-- end data-cell -->";
				}
			}
			else {
				if ($axis=="vertical") {	
					$th_start	='
			<th class="[tc]" valign="top">[fl]</th>';
				}
				$td_start	='
			<td class="[tc]" valign="top" id="[dv]">';
				$td_end		="[jx]
			</td>";
			}

			# ---------- assemble row html
			if (!strstr($td_start, '[ff]'))	$td_start.="[ff]";
			$row_html	=$th_start.$td_start.$td_end;
			
			$colm_name	=$line_label;
			$text_label	=strip_tags($line_label);
			$label_attr	='for="[ln]'.$row_count.'"';
			# ---------- required 
			$line_valid	=strtolower($line_valid);
			$required	=(substr($line_valid, 0, 1)=="r"||$line_valid=="yes");

			$th_lower	=strtolower($line_label);
			$no_label	=($line_type=="btn"||in_array($th_lower, ["", "no_label"]));#, "space", "wspace", "&nbsp;"
			
			$th_label	="&nbsp;";
			if ($line_label) $th_label='<label class="[lc]" [la]>[lt]</label>';
			if ($no_label) $th_label='';
			
			if ($required && $line_input!="hidden") $colm_name.=$pad_indent."\t".'<span class="require_red" title="'.$text_label.' is required">*</span>'.$pad_indent;

			$ajax_div	="ajax_[ln]";
			if ($multi_rows) $ajax_div.="_".$row_count;
			
			# ---------- ajax element
			$ajax_text	=($axis=="vertical")?"\n\t\t\t\t\t".'<span id="[ln]_text"></span>':"";

			# ---------- replace html
			$row_html	=str_replace('[ff]', $form_html, $row_html);
			$row_html	=str_replace('[fl]', $th_label, $row_html);
			$row_html	=str_replace('[la]', $label_attr, $row_html);
			$row_html	=str_replace('[lc]', $nlabel_class, $row_html);
			$row_html	=str_replace('[tc]', $td_class, $row_html);
			$row_html	=str_replace('[rc]', $tmp_class, $row_html);
			$row_html	=str_replace('[jx]', $ajax_text, $row_html);
			$row_html	=str_replace('[dv]', $ajax_div, $row_html);
			$row_html	=str_replace('[lt]', $colm_name, $row_html);
			$row_html	=str_replace('[ln]', $line_name, $row_html);
			
			if (in_array($line_type, ["text", "code", "raw", "pre"])) $row_html=str_replace("\t", "", $row_html);
			# ---------- display fields
			$cell_html .=$row_html;
	
			if ($count_col==$row_lines&&$axis=="vertical") {
				$full_row	=1;
				if (count($array_cols)>1) $row_lines=array_shift($array_cols);
			}
			# make rows per row
			if ($full_row&&$count_col<$count_cols) {
				$cell_html .=$row_end;
				$cell_html .=$start_row;

				$last_key	=0;
				$count_col	=0;
				$full_row	=0;
				$count_row++;
			}
			$count_col++;
				
		}# end foreach
		if (!$full_row && $axis=="vertical" && $count_col>1) {
			$rem_col	=($row_lines - $count_col);
			for ($col=0; $col<=$rem_col; $col++) {
				$row_html	=$th_start.$td_start.$td_end;
				$row_html	=str_replace('[ff]', "", $row_html);
				$row_html	=str_replace('[fl]', "", $row_html);
				$row_html	=str_replace('[jx]', "", $row_html);
				$row_html	=str_replace('[tc]', "", $row_html);
				$row_html	=str_replace('[dv]', "", $row_html);
				$cell_html .=$row_html;
			}
		}
		$cell_html .=$row_end;

		return $cell_html;
	}# end function 
		
	#-------------- format cells function
	function format($array) {
		global $axis, $table, $app_extension, $tip_class, $icon, $get_id, $btn_class;

		$col_name	=arrayKey("name", $array);
		$block_1	=arrayKey("label", $array);
		$block_2	=arrayKey("type", $array);
		$block_3	=arrayKey("input", $array);
		$block_4	=arrayKey("validate", $array);
		$block_5	=arrayKey("value", $array);
		$block_6	=arrayKey("attrib", $array);
		
		$row_data	=arrayKey("data", $array);
		$row_count	=arrayKey("row", $array);
		$col_count	=arrayKey("col", $array);
		$multi_rows	=arrayKey("multi", $array);
		
		$form_label	=$block_1;
		$cell_type	=$block_2;
		$text_link	=$block_3;
		$text_tags	=$block_4;
		
		$form_name	=$col_name;
		$form_type	=$block_3;
		$form_valid	=$block_4;
		$form_value	=$block_5;
		$form_attrib=trim($block_6);

		$text_tags	=$this->rowData($text_tags, $row_data);
		$row_value	=arrayKey($col_name, $row_data);
		
		$raw_value	=$row_value;
		if (is_array($row_value)) $raw_value=implode(",", $raw_value);
		$new_value	=htmlspecialchars_decode($raw_value);
		$new_value	=$this->censor($new_value);
		$slash_value=addslashes($new_value);
		$strip_value=strip_tags($new_value);

		$str_count	=strlen($text_link);
		$last	="";
		if ($str_count) $last=$text_link[($str_count-1)];
		$slash	="";
		if ($last!="="&&$last!="/") $slash="=";
		$link_href	=$text_link.$slash.$new_value.$app_extension;
		$link_tags	=$this->arrayFormat($text_tags);
		
		$text_block	="view,print,update,delete,vb,link,image,download,input,btn,wrap,check";
		$array_types=explode(",", $text_block);

		$view_static=strstr(".{$cell_type}", ".view_");
		$view_custom=!in_array($cell_type, $array_types);

		$view_format="";
		$link_attrs	=[];
		if ($cell_type=="view") {
			# -------------- view
			$link_attrs	=["href"=>$this->link($link_href), "class"=>$tip_class." rec_view d-block tx-center", "title"=>$this->lang("view_record")];
			$link_title	='<i class="fad fa-file-alt text-dark fa-1x"></i>';
		}
		elseif ($cell_type=="print") {
			# -------------- view
			$link_attrs	=["href"=>$this->link($link_href), "class"=>$tip_class." rec_print d-block tx-center", "title"=>$this->lang("print_record")];
			$link_title	='<i class="fad fa-print text-dark fa-1x"></i>';
		}
		elseif ($cell_type=="insert") {
			# -------------- update
			$link_attrs	=["href"=>$this->link($link_href), "class"=>$tip_class." rec_insert d-block tx-center", "title"=>$this->lang("insert_record")];
			$link_title	='<i class="fad fa-plus-circle text-dark fa-1x"></i>';
		}
		elseif ($cell_type=="update") {
			# -------------- update
			$link_attrs	=["href"=>$this->link($link_href), "class"=>$tip_class." rec_update d-block tx-center", "title"=>$this->lang("update_record")." ".$strip_value];
			$link_title	='<i class="fad fa-pencil text-dark fa-1x"></i>';
		}
		elseif ($cell_type=="delete") {
			# ---------- delete
			$link_attrs	=["href"=>$this->link($link_href), "class"=>$tip_class." rec_delete d-block tx-center", "title"=>$this->lang("delete_record")];
			$link_title	='<i class="fad fa-trash-alt text-danger fa-1x"></i>';
			$link_tags	=["data-info"=>$this->lang("delete_info"), "data-yes"=>$this->lang("delete_yes"), "data-no"=>$this->lang("delete_no")];
			$link_attrs	=array_merge($link_attrs, $link_tags);
		}
		elseif ($cell_type=="link") {
			# ---------- link
			$link_attrs	=["href"=>$this->link($link_href), "class"=>$tip_class." rec_link", "title"=>$this->lang("link_record")." ".$strip_value];
			$link_title	=$new_value;
		}
		elseif ($cell_type==="" && $text_tags) {
			# ---------- plain link
			$link_attrs	=["href"=>$this->link($link_href), "class"=>$tip_class." rec_goto", "title"=>$this->lang("goto_record")." ".$strip_value];
			$link_title	=$link_href;
		} 
		elseif (in_array($cell_type, ["head", "title", "header", "heading", "h1", "h2", "h3", "h4", "h5"])) {
			# ---------- html
			$link_attrs	="";
			$link_title	="";
			$view_format='[head]';
		}
		elseif ($col_name=="|"||in_array($cell_type, ["end", "close", "box", "separate", "separator"])) {
			# ---------- html
			$link_attrs	="";
			$link_title	="";
			$view_format='[close]';
		}
		elseif (in_array($cell_type, ["text", "raw"])) {
			# ---------- html
			$link_attrs	="";
			$link_title	="";
			$view_format=htmlspecialchars($raw_value);
		}
		elseif (in_array($cell_type, ["code", "pre"])) {
			# ---------- html
			$link_attrs	="";
			$link_title	="";
			$view_format='<pre class="code">'.$raw_value.'</pre>';
		}
		elseif (in_array($cell_type, ["html"])) {
			# ---------- html
			$link_attrs	="";
			$link_title	="";
			$view_format=$new_value;
		}
		elseif (in_array($cell_type, ["include", "require"])) {
			# ---------- include
			$get_text	="";
			$link_href	="{$text_link}/{$new_value}";

			if (is_file($link_href)) {
				ob_start();
					extract($GLOBALS);
					include $link_href;
					$get_text	=ob_get_contents();
				ob_end_clean();
			}
			$link_attrs	="";
			$link_title	="";
			$view_format	=$get_text;
		}
		elseif ($cell_type=="btn") {
			# ---------- button
			$text_tags	='class="'.$btn_class.'"';
			$view_format=$this->form("submit", $col_name, $block_3, "", $text_tags);
			$view_format.="\r\n\t\t\t".$this->form_token();
		}
		elseif ($cell_type=="wrap") {
			# ---------- wrap form
			$form_tags	='wrap="1" vtype="2" '.$block_3;
			$form_tags	=trim($form_tags);
			$form_tags	=$this->rowData($form_tags, $row_data);
			$view_format=$this->form("text", $col_name, "", $new_value, $form_tags);
		}
		elseif ($cell_type=="check") {
			# ---------- check
			$input_name	='check['.$col_name.'][]';
			$input_id	='check'.$row_count.'_'.$col_count;
			$view_format='<input type="checkbox" name="'.$input_name.'" value="'.$strip_value.'" class="alt-check" id="'.$input_id.'" '.$text_tags.' />';
			$view_format.=' <label for="'.$input_id.'" class="lab-alt-check"></label>';
		}
		elseif ($cell_type=="vb") {
			# ---------- visual basic
			$class	="row";
			$text	=$new_value;
			if ($get_id==$new_value) {//$col_count==0||
				$class	="this";
				$text	='<i class="fad fa-play-circle"></i>';
			}
			$link_title	='<div class="tee_vb '.$class.'"></div>';
			$link_attrs	=["href"=>$this->link($link_href), "class"=>$tip_class." rec_vb", "title"=>$this->lang("vb_record").$strip_value];
		}
		elseif (in_array($cell_type, ["image", "base64", "photo", "preview", "media", "audio", "video", "file", "pdf", "rtf", "download", "embed"])) {
		  	# ---------- file
			$link_href	="";
			if ($text_link) $link_href=$text_link."/";
			$link_href	.=$new_value;
			$file_exists=is_file($link_href);
		
			# ---------- path info
			$file_info	=pathinfo($new_value);
			$file_title	=arrayKey("filename", $file_info);
			$file_ext	=arrayKey("extension", $file_info);
			$file_ext	=strtolower($file_ext);
			
			if ($file_exists) {
				$item_class	=arrayKey("class", $link_tags);
				$file_type	=filetype($link_href);
			
				$media	=explode(",", "mp3,wma,mp2,wav,mid,ord,webm,ogv,wmv,mp4,mpg,mpeg,avi,dat,vid,vob,swf,flv");	
				$is_media	=in_array($file_ext, $media);
				if (!$is_media) $is_media=in_array($cell_type, ["media", "audio", "video"]);
			
				if (strstr($file_type, "image/")||strstr("jpg,gif,png,jpeg,webp", $file_ext)) {
					$link_cdn	=$this->path_cdn($link_href);
					if (strstr($link_cdn, $link_href)) $link_cdn=$link_href;
					
					if ($cell_type=="base64") {
						$link_title	="";
						$img_text	=file_get_contents($link_href);
						$view_format	="data:image/{$file_ext};base64,".base64_encode($img_text);
					}
					else {
						$link_cdn	.="?time=".time();
						if ($cell_type=="embed") $cell_type="photo";
						if (in_array($cell_type, ["photo", "preview"])) {
							$link_attrs	="";
							$link_title	="";
							$img_class	='img-fluid '.$col_name.'-preview '.$item_class.'';
							$img_attr	='class="'.$img_class.'" alt="'.$col_name.'"';
							$view_format	='<img src="'.$link_cdn.'" '.$img_attr.' />';
						}
						else {
							# ---------- image file
							$img_class	=arrayKey("class2", $link_tags, "img-3d");
							$title	=arrayKey("title", $link_tags, $file_title);
											
							$link_tags	=["class"=>$tip_class." rec_image", "title"=>$title];
							$link_attrs	=["href"=>"(h)", "data-featherlight"=>$link_cdn];#"rel"=>"ibox"
							# ---------- image size
							list($width, $height)=@getimagesize($link_href);
							$file_width	=($width>100)?100:$width;
							$image_tags	=$text_tags;
	
							$img_class	.=" img-fluid {$col_name}-preview";
							$img_attr	='class="'.$img_class.'" alt="'.$file_title.'"';
							if ($item_class=="") $item_class="ts_photo";
							$link_title	='<div class="'.$item_class.'"><img src="'.$link_cdn.'" '.$img_attr.' /></div>';
						}
					}
				}
				elseif ($is_media) {
					# ---------- video / audio file
					$file_width	=($icon=="view")?"":200;
					if ($item_class=="") $item_class="wd-100p ht-100p";
					$view_format=$this->video($new_value, $text_link, $file_width, "", $item_class);
				}
				elseif (in_array($cell_type, ["pdf", "rtf", "embed"])) {
					# ---------- embed file
					if ($item_class=="") $item_class="d-block wd-100p ht-400";
					$view_format='<embed src="'.$link_href.'" type="application/pdf" class="'.$item_class.'"></embed>';
				}
				elseif ($cell_type=="download") {
					# ---------- download
					$icon_file	='holder.js/50x70?text='.strtoupper($file_ext);
					$icon_file	='fad fa-download';
					if ($table) $link_href=$new_value;#$ts_files->download($table, $col_name, $text_link, $new_value);
					$link_attrs	=["href"=>$this->link($link_href), "class"=>$tip_class." rec_popup d-block txt-center", "title"=>$this->lang("download_popup"), "target"=>"download"];
					$link_title	='<i class="'.$icon_file.'"></i>';
				}
				else {
					# ---------- downloadable file			
					$link_attrs	=["href"=>$link_href, "class"=>$tip_class." rec_download d-block tx-center", "title"=>$this->lang("download_record").$col_name, "target"=>"download"];
					$link_title	='<div class="fa-stack"><i class="fat fa-square fa-stack-2x"></i><i class="fad fa-cloud-download fa-stack-1x"></i></div>';
				}
			}
			else {
				if ($row_value) $view_format='<div title="'.$file_title.$this->lang("lost_file").'" class="'.$tip_class.' rec_none tee_none"></div>';
			}
		}
		elseif ($view_custom && $cell_type) {
			if ($view_static) $cell_type=varKey($cell_type, $cell_type);
			# --------------- customised
			if (substr($cell_type, 0, 3)=="[f]") {
				# ---------- Process the customised function provided
				$new_text	=$cell_type;
				$new_text	=str_replace("[f]", "return ", $new_text);
				$new_text	=str_replace("[/f]", ";", $new_text);
					
				$new_text	=str_replace('->', '")->', $new_text);
				$new_text	=str_replace('::', '")->', $new_text);
				$new_text	=str_replace('")")->', '")->', $new_text);
				$new_text	=str_replace('tsp_', '$this->load("', $new_text);
				$new_text	=str_replace('$ts_', '$this->load("', $new_text);

				$new_text	=str_replace('$this")->', '$this_web->', $new_text);
				$new_text	=str_replace('$this_web->load")->', '$this_web->load->', $new_text);
				$new_text	=str_replace('")->format', '->format', $new_text);
				
				$new_text	=$this->rowData($new_text, $row_data);
				$new_text	=$this->prefix($new_text);
				$new_text	=str_replace("#", $slash_value, $new_text);
				$new_text	=str_replace('(h)', '#', $new_text);
		
				//$view_format=$cell_type;
				$view_format=$new_text;
				$view_format=@eval($view_format);
				if ($text_link) {
					$link_attrs	=["href"=>$this->link($link_href), "class"=>$tip_class." text-primary rec_goto", "title"=>$this->lang("goto_record")." ".$strip_value];
					$link_title	=$view_format;
				}
			}
			else {
				# ---------- show the value and link it
				$link_href	=$text_link.$new_value;
				$link_attrs	=["href"=>$this->link($link_href), "class"=>$tip_class." rec_goto", "title"=>$this->lang("goto_record")." ".$strip_value];
				$link_title	=$cell_type;
				if ($axis=="horizontal" && strlen($cell_type)>15) $link_title='<small>'.$link_title.'</small>';
			}
		}
		elseif ($cell_type=="input") {
			# -------------- form field
			$new_value	=$row_value;
			if (is_array($row_value)) $new_value=implode(";", $row_value);
			
			#has a default
			if (strstr($form_value, "*")) {
				$kval	=$this->txt("*", $form_value);
				$form_def	=$kval[0];
				$form_list	=$kval["ext"];
			}
			else {
				$form_def	="";
				$form_list	=$form_value;
			}

			if ($new_value) $form_def=$new_value;# put a db default

			if ($form_type=="text"&&strlen($new_value)>255) $form_type="textarea";# convert type to textarea
			# ---------- sorting ajax issues
			$input_id	=$form_name.$row_count;
			$ajax_count	=($row_count + 1);
			if ($multi_rows) {
				$input_id	.="_{$col_count}";
				$ajax_count	.="_{$col_count}";
				$form_name	.="[{$row_count}]";
				$form_attrib=str_replace("loadAjax('", "loadAjax('{$row_count}!", $form_attrib);
			}

			$multi_tag	='multiple="multiple"';
			$form_attrib=str_replace('multiple ', "{$multi_tag} ", $form_attrib);
			if (strstr('multiple.', "{$form_attrib}.")) $form_attrib=str_replace('multiple', $multi_tag, $form_attrib);

			if ($form_type=="jmenu") $form_attrib='href="'.$form_attrib.'"';
			$form_attrib	=$this->rowData($form_attrib, $row_data);
			
			if (!strstr($form_attrib, 'id=')) $form_attrib.=' id="'.$input_id.'"';
			if (!strstr($form_attrib, 'placeholder=')&&$form_label) $form_attrib.=' placeholder="'.$form_label.'"';
			
			$view_format	=$this->form($form_type, $form_name, $form_list, $form_def, $form_attrib, $form_valid);
		}
		else {
			# ---------- default
			$new_text	=$new_value;
			$new_text	=str_replace(",", ", ", $new_text);
			$new_text	=strip_tags($new_text, "<p><div><span><strong><b><i><h><ul><ol><li><hr><br><img><iframe><video><blockquote>");
			$view_format=$new_text;              
		}		
		# -------------- assemble link
		if ($link_attrs && $link_title) {
			if (!is_array($link_tags)) $link_tags=[$link_tags];
			if (is_array($link_attrs)) $link_tags=array_merge($link_attrs, $link_tags);
			$link_attr	=[];
			foreach ($link_tags as $tag_key=>$tag_value) {
				$def_tag	=arrayKey($tag_key, $link_attrs);
				if ($def_tag && $def_tag!=$tag_value) $tag_value.=' '.$def_tag;
				if ($tag_value) $tag_value=$this->rowData($tag_value, $row_data);
				$tag_value	=str_replace('#', $new_value, $tag_value);
				$tag_value	=str_replace("(h)", '#', $tag_value);
				
				if ($tag_key) {
					$link_attr[]=$tag_key.'="'.$tag_value.'"';
				}
			}
			$link_attr	=implode(' ', $link_attr);
			$view_format='<a '.$link_attr.'>'.$link_title.'</a>';
		}
		#if ($view_format==="") $view_format="&nbsp;	";	
		return $view_format;
	}
	
	function custom($name="", $data=[], $type="", $array=[]) {
		$array	=array_merge($array, ["type"=>$type, "name"=>$name, "data"=>$data]);
		$value	=$this->format($array);
		return $value;
	}
	
	# ------ column span
	function colSpan($class, $size=4) {
		$span	=[];
		$array	=$size;
		if (!is_array($size)) $array=explode(",", $size);

		$sizes	=["xxl", "xl", "lg", "md", "sm", "xs"];
		foreach ($array as $key=>$type) {
			$size	=arrayKey($key, $sizes);
			$span[]	=str_replace("-md-", "-{$size}-", $class.$type);
		}
		$span	=implode(" ", $span);
		return $span;
	}
	#-------------- rowData function
	private function rowData($input, $row_data) {
		if (is_array($row_data)) {
			$array	=array_keys($row_data);
			foreach ($array as $name) {
				$temp	='['.$name.']';
				if (strstr($input, $temp)) {
					$value	=arrayKey($name, $row_data);
					$input	=str_replace($temp, $value, $input);
				}
			}
		}
		return $input;
	}
	
	#-------------- replace row
	private function replaceRow($text, $replace_array) {
		foreach ($replace_array as $key=>$value) {
			$text	=str_replace($key, $value, $text);
		}
		return $text;
	}
	
	#-------------- colons function
	private function colons($values, $type=1) {
		$text1	=[varKey("comma"), varKey("colon")];
		$text2	=[',', ':'];
		$values	=str_replace($text2, $text1, $values);
		if ($type==1) $values=str_replace($text1, $text2, $values);
		return $values;
	}
		
	private function colonKey($key, $array, $type=1) {
		$value	=arrayKey($key, $array);
		$text	=$this->colons($value, $type);
		return $text;
	}
		
	# ------- replace function
	function replace($list) {
		# ------ patterns
		$array	=[',"', ',$', ",'", ", ", ",(", ",[", ",\\", ':"', ':$', ":'", ": ", ":(", ':"', '://'];
		foreach ($array as $key=>$char) {
			$nchar	=$this->colons($char, 2);
			$list	=str_replace($char, $nchar, $list);
		}
		$list	=str_replace(varKey("colon")."'.", ":'.", $list);
		# ------ functions
		$array	=explode('[/f]', $list);
		foreach ($array as $key=>$values) {
			$chip	=explode('[f]', $values);
			$value	=arrayKey(1, $chip);
			$new_val=$this->colons($value, 2);
			$list	=str_replace($value, $new_val, $list);
		}
		return $list;	
	}
	
	
	public function video($file, $path="", $width="", $height="", $class="") {
		$target	=$path."/".$file;
		$target	=is_file($target)?$target:$file;
		
		if (!$width) $width=480;//100%
		if (!$height) $height=($width/1.4);
		
		$type	=filetype($file);

		if (strstr($type, "video/")) {
			$poster	="holder.js/500x100?text=Live Stream ...";
			$poster	=public_path("images/script-icons/live_streaming.png");
			$nposter=public_path("docs/videos/stream.png");
					
			$header	='type="'.$type.'" src="'.$target.'"';			
			if (strstr($type, "/ogv")) $header.='" codecs="theora, vorbis"';

			$attrib	='controls';# width="'.$width.'" height="'.$height.'"
			$attrib	.=' poster="'.$nposter.'" class="'.$class.'" preload="true"';
				
			$player_script	='
		<video '.$attrib.'>
			<source '.$header.'>
			'.$this->lang("Your browser does not support video").'
		</video>';
		}

		elseif (strstr($type, "audio/")) {
			$player_script='
		<audio controls class="wd-100p">
			<source src="'.$target.'" type="'.$type.'" />
			'.$this->lang("Your browser does not support audio").'
		</audio> ';
		$player_script	='<embed src="'.$target.'" class="wd-100p ht-50" autostart="false"></embed>';

		}
		else {
			$player_script	='<embed src="'.$target.'" class="wd-100p" autostart="false" /> '.$file;
		}
		
		return $player_script;			  
	}
	
	public function globalVars($list="") {
		if (!$list) $list='cms_connect,exclude,c,control,root,site_base,base_path,cms_path,library,includes,sitename,session_code,session_data,siteid,site_name,site_root,db_sites,array_sites,this_role,this_auth,this_user,current_role,current_user,current_id,current_names,int_action,action,action_name,icon,get_id,icon_name,get_mod,module_name,get_title,mod_array,this_url,tip_class,path_icons,tables,module_sql,lang_array,session_language,is_lang,language,tab_permission,tab_names,tab_array,icon_list,btn_active,btn_other,tab_inner1,tab_inner2,tab_start,tab_end,tab_active,tab_other,print_pages,print_path,date';
		$array	=explode(",", $list);
		$vars	=[];
		foreach ($array as $var) {
			$var	=trim($var);
			#$value	=arrayKey($var, $GLOBALS);
			$nkey	=str_replace("_", ".", $var);
			$value	=config("forms.{$nkey}");
			${$var}	=$value;
			$vars[$var]	=$value;
		}
		return $vars;
	}

	public function form_token($return="") {
		$text	="";#$this->load("security")->tokenForm(1);
		return $text;			  
	}
	
	public function form($form_type, $form_name, $form_list="", $form_def="", $form_attrib="", $form_valid="") {
		$text	=$this->load("form")->form($form_type, $form_name, $form_list, $form_def, $form_attrib, $form_valid);
		return $text;			  
	}

	
	#----------- end methods
}
