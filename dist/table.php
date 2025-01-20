<?php
namespace Tee\RptForms;

use Tee\RptForms\render;

class table extends render
{
	function __construct() {
		parent::__construct();
	}
	
	function easyuiTable($fields, $attribute=[]) {
		$default=array("pagination"=>"true", "pageSize"=>20, "multiSort"=>"true", "fitColumns"=>"false");
		
		if (!is_array($attribute)) {
			$attrib	=varKey("table_attrib");
			if ($attrib) $attribute=$this->arrayTags($attrib);
		}
		$class	=varKey("table_class1");
		$options=arrayKey("data-options", $attribute, []);
		$config	=array_merge($default, $options);
		array_unique($config);
		ksort($config);
		$settings	=[];
		foreach ($config as $key=>$value) {
			$bool	=in_array($value, array("true", "false"));
			$text	=($bool||is_numeric($value))?$value:"'{$value}'";#is_string($value)
			$settings[]	=$key.':'.$text;
		}
		$settings	=implode(",", $settings);
		$settings	='data-options="'.$settings.'" class="easyui-datagrid '.$class.'" style="width:98%; height: 350px;"';

		$array	=arrayKey("fields", $fields);
		if (!is_array($array)) {
			$fields	=$this->columns($fields);
			$array	=$fields["fields"];
		}
		
		$row	=[];
		if ($array) {
			$mcount	=0;
			$init	=[];
			foreach ($array as $col_count=>$keys) {
				$name	=arrayKey("name", $keys);
				$label	=arrayKey("label", $keys);
				$field	=in_array($name, $init)?$name.$col_count:$name;
				$options="field:'{$field}'";
				if ($field==$name) $options.=",resizable:true,sortable:true";
				$width	='max';
				if ($keys["class"]=="cell_empty") {
					$width	='min';
					$mcount+=1;
				}	
				$attr	='width="'.$width.'"';
				$thead	='<th data-options="'.$options.'" '.$attr.'>'.$label.'</th>';
				
				if ($col_count<2){
					$row["freeze"][]=$thead;
				}
				else {
					$row["data"][]	=$thead;
				}
				$row["init"][]	=$field;
			}
		}# end if result
		
		$thead	="";
		$frozen	=arrayKey("freeze", $row);
		
		if ($frozen) $thead='
			<thead data-options="frozen:true">
				<tr>'.implode("\r\n\t\t\t\t\t", $row["freeze"]).'
				</tr>
			</thead>';

		$table	=' 
        <table title="'.$attribute["title"].'" '.$settings.'>'.$thead.'
            <thead>
                <tr>'.implode("\r\n\t\t\t\t\t", $row["data"]).'
                </tr>
            </thead>
        </table>';
		
		$count	=count($array);
		$xcount	=($count-$mcount);
		$xwidth	=(100/$xcount);
		$mwidth	=((40/(1100/$count))*$xwidth);
		$msize	=($mcount*$mwidth);
		$xwidth	=($xwidth-($msize/$xcount));
		$table	=str_replace('width="min"', 'width="'.$mwidth.'%"', $table);
		$table	=str_replace('width="max"', 'width="'.$xwidth.'%"', $table);
		echo $table;
	}
	
	# ------------- new function
	function data($results, $fields, $pair="", $type="fields") {
		$data	=[];
		if (is_array($results)) {
			$total	=count($results);
			if (!is_array($fields)) $fields=$this->columns($fields, $type);
			foreach ($results as $row_count=>$row) {
				$data[]	=$this->dataRows($fields, $row, $row_count, $pair, $total);
			}
		}# end if result
		return $data;
	}
	
	# ---------- start the row function
	function rowData($fields, $row, $row_count, $pair="", $total=0) {
		$init	=[];
		$array	=[];
		$multi	=($total>1);
		#------------
		$array_keys	=array_column($fields, "name");
		$array_keys	=array_flip($array_keys);
		$count	=0;
		foreach ($row as $name=>$value) {
			$array_key	=arrayKey($name, $array_keys);
			$array_colm	=arrayKey($array_key, $fields);
			if ($array_colm) {
				# ---------- format row data
				$array_colm	=$this->rowFormat($array_colm, $row, $count, $row_count, $multi);
				$value	=arrayKey("value", $array_colm);
			}
			# ------ close the container field
			$field	=$name;
			if (in_array($name, $init)) $field=$name.$count;
			
			$nvalue	=NULL;
			if ($value!=="") $nvalue=$value;

			if ($pair) {
				$array[$field]	=$nvalue;
			}
			else {
				$array[]	=$nvalue;
			}
			$init[]	=$field;
			$count++;
		}
		# end function 
		return $array;
	}
	
	# ---------- start the row function
	function dataRows($fields, $row, $row_count, $pair="", $total=0) {
		$init	=[];
		$array	=[];
		$multi	=($total>1);
		#------------
		foreach ($fields as $count=>$array_colm) {
			# ---------- format row data
			$array_colm	=$this->rowFormat($array_colm, $row, $count, $row_count, $multi);

			$name	=arrayKey("name", $array_colm);
			$value	=arrayKey("value", $array_colm);
			# ------ close the container field
			$field	=$name;
			if (in_array($name, $init)) $field=$name.$count;
			
			$nvalue	=NULL;
			if ($value!=="") $nvalue=$value;

			if ($pair) {
				$array[$field]	=$nvalue;
			}
			else {
				$array[]	=$nvalue;
			}
			$init[]	=$field;
		}
		# end function 
		return $array;
	}
	
	# ---------- dt query function
	public function dtQuery($col_array, $sql_where="", $type="") {
				
		$data		=array_key_exists("sEcho", $_POST)?$_POST:$_GET;
		$col_count	=count($col_array);
		$get_start	=$data["iDisplayStart"];
		$get_max	=$data["iDisplayLength"];
		
		$get_colms	=$data["iSortingCols"];
		
		# pagination
		$sLimit = "";
		if (isset($get_start)&&$get_max!='-1')	{
			$sLimit = "LIMIT ".$get_start.", ".$get_max;
		}
		
		# ordering
		$sOrder	="";
		if (isset($get_colms))	{
			$order	=array();
			for ($i=0;$i<$get_colms;$i++) {
				$get_sortCol	=$data["iSortCol_".$i];
				$get_sortDir	=$data["sSortDir_".$i];
				$get_sortable	=$data["bSortable_".$i];
				if ($get_sortable=="true"&&$get_sortDir) {
					$colField	=$col_array[$get_sortCol]["name"];
					$order[]	="`$colField` ".strtoupper($get_sortDir);
				}
			}
			$order	=implode(", ", $order);
			if ($order) $sOrder="ORDER BY $order";
		}
		
		#filtering
		$sWhere =$sql_where;
		$where	=array();
		for ($i=0;$i<$col_count;$i++) {
			$colField		=$col_array[$i]["name"];
			$get_phrase		=$data["sSearch_".$i];
			$get_searchable	=$data["bSearchable_".$i];
			$get_dataProp	=$data["mDataProp_".$i];
			if ($get_searchable=="true"&&$get_phrase) {
				$where[]=$this->sqlMany($colField, $search_phrase);
			}
		}
		$where	=implode(" AND ", $where);
		if ($where){
			$sWhere .=($sWhere)?"AND":"WHERE";
			$sWhere .=" ($where)";
		}
		$array	=array("where"=>$sWhere, "order"=>$sOrder, "limit"=>$sLimit);
		return $array;
	}
	
	# ---------- easyui query function
	public function easyuiQuery($sql_order="", $sql_where="") {
				
		$data	=array_key_exists("page", $_POST)?$_POST:$_GET;	
		
		# pagination
		$get_page	=$data["page"];
		$get_rows	=$data["rows"];
		$sLimit = "";
		if ($get_page&&$get_rows)	{
			$query_start	=($get_page*$get_rows)-$get_rows;//(($get_page-1)*$get_rows);
			$sLimit = "LIMIT {$query_start}, ".$get_rows;
		}
		
		# ordering
		$get_sort	=$data['sort'];
		$get_order	=$data['order'];
		$sOrder	=$sql_order;
		if ($get_sort&&$get_order) {
			$exp_sort	=explode(",", $get_sort);
			$exp_order	=explode(",", $get_order);
			$order	=array();
			foreach ($exp_sort as $key=>$field) {
				if ($field) $order[]	="`{$field}` ".strtoupper($exp_order[$key]);
			}
			$sOrder="ORDER BY ";
			$sOrder.=implode(", ", $order);
		}
		
		#filtering
		$get_search	=$data['query'];
		$get_field	=$data['qtype'];
		$sWhere =$sql_where;
		if ($get_field&&$get_search) {
			$sWhere .=$this->sqlBool()." `{$get_field}` LIKE '%{$get_search}%'";
		}
		$array	=array("where"=>$sWhere, "order"=>$sOrder, "limit"=>$sLimit);
		return $array;
	}
	
	# ---------- flexi query function
	public function flexiQuery($sql_order="", $sql_where="") {
				
		$data	=array_key_exists("page", $_POST)?$_POST:$_GET;	
		# pagination
		$get_page	=$data["page"];
		$get_rows	=$data["rp"];
		$sLimit = "";
		if ($get_page&&$get_rows)	{
			$query_start	=($get_page*$get_rows)-$get_rows;//(($get_page-1)*$get_rows);
			$sLimit = "LIMIT {$query_start}, ".$get_rows;
		}
		
		# ordering
		$get_sort	=$data['sortname'];
		$get_order	=$data['sortorder'];
		$sOrder	=$sql_order;
		if ($get_sort&&$get_order) {
			$sOrder	="ORDER BY `{$get_sort}` ".strtoupper($get_order);
		}
		
		#filtering
		$get_search	=$data['query'];
		$get_field	=$data['qtype'];
		$sWhere =$sql_where;
		if ($get_field&&$get_search) {
			$sWhere.=$this->sqlBool()." `{$get_field}` LIKE '%{$get_search}%'";
		}
		$array	=["where"=>$sWhere, "order"=>$sOrder, "limit"=>$sLimit, "page"=>$get_page];
		return $array;
	}
	
	
	function ftHead($fields, $text="") {
		$row	=[];
		if (!is_array($fields)) $fields=$this->columns($fields);
		$array	=$fields["fields"];
		if (is_array($array)) {
			$row["init"]	=[];
			foreach ($array as $col_count=>$keys) {
				$name	=$keys["name"];
				$label	=$keys["label"];
				$class	=$keys["class"];
				$field	=$name;
				if (in_array($name, $row["init"])) $field=$name.$col_count;
				
				$brPoint	='';
				if ($col_count>1) $brPoint='xs';
				if ($col_count>3) $brPoint.=' sm';
				if ($col_count>5) $brPoint.=' md';
				if ($col_count>7) $brPoint.=' lg';
				$json	=["name"=>$field, "title"=>$label];
				$attr	='field="'.$field.'"';
				if ($brPoint) {
					$attr	.=' data-breakpoints="'.$brPoint.'"';
					$json["breakpoints"]	=$brPoint;
				}
				if ($class=="cell_hide"||$class=="cell_empty") {
					$attr	.=' data-sorting="false" data-filterable="false"';
					$json["sorting"]	=false;
					$json["filterable"]	=false;
					if ($class=="cell_hide") {
						$attr	.=' data-visible="false"';
						$json["visible"]=false;
					}
					else {
						$attr	.=' style="width:20px;"';
						$json["style"]	="{'width':'20px','maxWidth':'20px'}";
					}
				}
				$thead	='<th '.$attr.'>'.$label.'</th>';
				$row["json"][]	=$json;				
				$row["head"][]	=$thead;
				$row["init"][]	=$field;
			}
		}# end if result
				
		if ($text) {
			$thead	='
            <thead>
                <tr>'.implode("\r\n\t\t\t\t\t", $row["head"]).'
                </tr>
            </thead>';
		}
		else {
			$thead	=json_encode($row["json"]);
		}
		echo $thead;
	}
	
	public function listName($list="") {
		$name	=str_replace("list_", "", $list);
		$name	=str_replace("json/", "", $name);
		$name	=str_replace("grid/", "", $name);
		$name	=str_replace("array_", "", $name);
		$name	=str_replace("select/", "", $name);
		
		$path	=$name;
		if (strstr($list, "array_")) $path.="&array={$name}";
		return $path;
	}

	function jExcelColms($columns) {
		$base_ajax	=varKey("base_ajax");
		$selects	=["select", "radio", "checkbox", "grid", "tree", "combo", "excel"];
		$array_text	=["hidden"=>"hidden", "number"=>"numeric", "select"=>"dropdown", "grid"=>"dropdown", "tree"=>"dropdown", "combo"=>"dropdown", "excel"=>"dropdown", "password"=>"password"];
		$array_fields	=$this->columns($columns, "fields");

		$cols	=array();
		$count	=count($array_fields);
		$size_w	=floor(1000/$count);
		foreach ($array_fields as $key=>$array) {
			$name	=arrayKey("name", $array);
			$label	=arrayKey("label", $array);
			$itype	=arrayKey("type", $array);
			$input	=arrayKey("input", $array, "text");
			$valid	=arrayKey("validate", $array);
			$extra	=arrayKey("attrib", $array);
			$value	=arrayKey("value", $array);
			$width	=arrayKey("width", $array);
			$align	=arrayKey("align", $array, "left");

			$required	=false;
			if ($valid) {
				if ($valid=="yes") {
					$required=true;
					$valid	="";
				}
				elseif (stristr(".{$valid}", ".r")) {
					$required	=true;
					$valid	=str_ireplace(".r", "", ".{$valid}");
				}
			}

			$is_select	=in_array($input, $selects);
			$type	=arrayKey($input, $array_text, $input);
			if ($itype!="input") $type="";
			#type: 'autonumber', primaryKey: true
			
			$tags	=[];
			if ($itype!="input") $extra=$input;
			if ($extra) $tags=$this->arrayFormat($extra);
			$token	=arrayKey("tags", $tags);
			$path	=arrayKey("path", $tags);
			$prefix	=arrayKey("prefix", $tags);
			$suffix	=arrayKey("suffix", $tags);
			$mask	=arrayKey("mask", $tags);
			$class	=arrayKey("class", $tags);
			$sort	=arrayKey("sort", $tags, false);
			$render	=arrayKey("render", $tags);
			$format	=arrayKey("decimal", $tags);

			$dwidth	=floor($size_w);
			if ($key==0) $dwidth=ceil($size_w);

			if (!$width&&$dwidth>30) $width=$dwidth;
			if (!$width) $width=100;
			if ($width<30) $width=($width*10);

			$path1	=$this->listName($path);
			$path2	=$this->listName($value);
			$npath	=$path1;
			if ($path2&&$path2!=$value) {
				$value	="";
				$npath	=$path2;
			}
			
			$link	="";
			if ($npath) {
				$link	=$this->path_lists("excel/{$npath}");
				if (!strstr($npath, "&rows=")) $link.="&rows=200";
			}
			
			if (!$label) $label="&nbsp;";

			$title	=$label;
			if ($class) $title='<span class="'.$class.'">'.$label.'</span>';
			if ($required&&$input!="hidden") $title.=' <span class="require_red" title="'.strip_tags($label).' is required">*</span>';

			$option	=[];
			$attrib	=["name"=>$name, "title"=>$title];
			
			if ($input=="file") {
				$type	="image";
			}
			if ($input=="textarea") {
				$type	="html";
			}
			if ($input=="radio") {
				if ($value) $attrib["value"]	=[$value];
			}
			if ($input=="checkbox") {
				if ($value) $attrib["value"]	=[$value];
			}
			if ($input=="color"||$valid=="color") {
				$type	="color";
				$render	="square";
			}	
			if ($input=="date"||$valid=="date") {
				$type	="calendar";
				$option	=["type"=>"picker", "format"=>"YYYY-MM-DD", "today"=>true, "time"=>false];
			}
			if ($input=="time"||$valid=="time") {
				$type	="calendar";
				$option	=["type"=>"picker", "format"=>"HH24:MI:ss", "today"=>true, "time"=>true];
			}
			if ($input=="datetime"||$valid=="datetime") {
				$type	="calendar";
				$option	=["type"=>"picker", "format"=>"YYYY-MM-DD HH24:MI:ss", "today"=>true, "time"=>true, "fullscreen"=>true];
			}
			if ($input=="number"||$valid=="number") {
				$align	="right";
				$type	="numeric";
				if (!$mask) $format="0";
			}
			if ($format!="") {
				$align	="right";
				if ($format==0) $mask="#,##0";
				if ($format==1) $mask="#,##0.0";
				if ($format==2) $mask="#,##0.00";
				//$attrib["decimal"]	=".";
			}
			if ($mask && ($prefix||$suffix)) {
				//if ($prefix) $mask=$prefix." ".$mask;
				//if ($suffix) $mask=$mask." ".$suffix;
			}

			if (stristr($extra, "readonly")||$itype!="input") {
				$attrib["readOnly"]	=true;
			}
			if ($is_select && $value) {
				$value	=$this->arrayConvert($value, "tree");
				$attrib["source"]	=$value;
				if (count($value)>2) $attrib["autocomplete"]=true;
			}
			
			if (stristr($extra, "multiple")) $attrib["multiple"]=true;
			if ($input=="select" && $link) {
				$attrib["url"]	=$link;
				$type	="autocomplete";
			}
			if ($input=="radio") {
				//$type	="dropdown";#radio available now
				//$option["type"]	="picker";
			}
			
			#primaryKey: true
			if ($type) $attrib["type"]=$type;
			if ($sort) $attrib["sort"]=true;
			if ($mask) $attrib["mask"]=$mask;
			if ($token) $attrib["tags"]=$token;
			if ($width) $attrib["width"]="{$width}";
			if ($option) $attrib["options"]	=$option;
			if ($render) $attrib["render"]=$render;
			if ($itype) {
				$text	="input view update delete print";
				if (!strstr($text, $itype)) $attrib["custom"]=$itype;
			}
			$attrib["align"]	=$align;
			
			$cols['colm'][]	=$attrib;
			$cols['field'][]=$name;
			if ($input=='hidden') $cols['hide'][]=$key;
		} 
		return $cols;
	}

	function hsonColms($columns) {
		$base_ajax	=varKey("base_ajax");
		$selects	=array("select", "radio");#, "checkbox"
		$array_text	=array("hidden"=>"text", "number"=>"numeric", "select"=>"dropdown");
		$array_fields	=$this->columns($columns, "fields");

		$cols	=array();
		$count	=count($array_fields);
		$size_w	=floor(100/$count);
		foreach ($array_fields as $key=>$array) {
			$name	=arrayKey("name", $array);
			$label	=arrayKey("label", $array);
			$itype	=arrayKey("type", $array);
			$input	=arrayKey("input", $array);
			$valid	=arrayKey("validate", $array);
			$extra	=arrayKey("attrib", $array);
			$value	=arrayKey("value", $array);
			$width	=arrayKey("width", $array);
			$align	=arrayKey("align", $array, "left");

			$required	=false;
			if ($valid) {
				if ($valid=="yes") {
					$required=true;
					$valid	="";
				}
				elseif (stristr(".{$valid}", ".r")) {
					$required	=true;
					$valid	=str_ireplace(".r", "", ".{$valid}");
				}
			}

			$is_select	=in_array($input, $selects);
			$type	=arrayKey($input, $array_text, $input);

			$render		="html";
			$readonly	=false;
			if ($itype!="input") {
				$type		="text";
				$render		="html";#tspCells
				$readonly	=true;
				$extra		=$input;
			}
			
			$tags	=[];
			if ($extra) $tags=$this->arrayFormat($extra);
			$token	=arrayKey("tags", $tags);
			$path	=arrayKey("path", $tags);
			$prefix	=arrayKey("prefix", $tags);
			$suffix	=arrayKey("suffix", $tags);
			$mask	=arrayKey("mask", $tags);
			$class	=arrayKey("class", $tags);
			$filter	=arrayKey("filter", $tags, false);
			$sort	=arrayKey("sort", $tags, false);

			$dwidth	=floor($size_w);
			if ($key==0) $dwidth=ceil($size_w);
			if (!$width&&$dwidth) $width=$dwidth;
			$width	=($width * 10);
			
			if ($value&&!$path) {
				$npath	=$this->listName($value);
				if ($value!=$npath) {
					$path	="json/{$npath}";
					$value	="";
				}
			}
			
			if (!$label) $label="&nbsp;";

			$title	=$label;
			if ($class) $title='<span class="'.$class.'">'.$label.'</span>';
			if ($required&&$input!="hidden") $title.=' <span class="require_red" title="'.strip_tags($label).' is required">*</span>';

			$option	=[];
			$attrib	=array("data"=>$name, "label"=>$title);
			
			if ($input=="file") {
				$type	="text";
			}
			if ($input=="textarea") {
				$type	="text";
			}
			if ($input=="radio") {
				if ($value) $attrib["value"]	=$value;
			}
			if ($input=="checkbox") {
				if (!$value) $value	=1;
				$option	=["checkedTemplate"=>$value, "uncheckedTemplate"=>'0'];
			}
			if ($input=="color"||$valid=="color") {
				$type	="text";
			}	
			if ($input=="date"||$valid=="date") {
				$type	="date";
				$option	=["dateFormat"=>"YYYY-MM-DD", "correctFormat"=>false];
			}
			if ($input=="time"||$valid=="time") {
				$type	="time";
				$option	=["dateFormat"=>"HH:mm:ss", "correctFormat"=>true];
			}
			if ($input=="datetime"||$valid=="datetime") {
				$type	="date";
				$option	=["dateFormat"=>"YYYY-MM-DD HH:mm:ss", "correctFormat"=>false];
			}
			if ($input=="number"||$valid=="number") {
				$align	="right";
				$type	="numeric";
				if (!$mask) $mask="0,0.00";
				$option["numericFormat"]	=["pattern"=>$mask, "culture"=>'en-US'];
			}
			if ($mask&&($prefix||$suffix)) {
				if ($prefix) $mask=$prefix." ".$mask;
				if ($suffix) $mask=$mask." ".$suffix;
			}
			if ($input=="password"||$valid=="password") {
				$type	="password";
				//$option	=["hashLength"=>"20", "hashSymbol"=>"&#9632;"];
			}
			
			
			if ($filter) {
				$attrib["dropdownMenu"]	=$filter;
			}

			if (stristr($extra, "readonly")||$readonly) {
				$attrib["readOnly"]	=true;
			}
			else {
				if (!$required) {
					$attrib["allowEmpty"]	=true;
				}
			}

			if ($value) {
				$values	=$this->arrayConvert($value, "values");
				if ($is_select) {
					$type	="dropdown";# select:selectOptions, dropdown:autocomplete:source
					$option	=["source"=>$values, "allowInvalid"=>false];
					#, "strict"=>false, "visibleRows"=>4, "trimDropdown"=>false
					if (count($value)>2) $option["filter"]=true;
				}
			}
			
			if ($path) {
				$link	=$this->path_lists($path);
				
				$type	="autocomplete";
				$attrib["source"]	='function(query, process) {
					fetch("'.$link.'")
					  .then(response => response.json())
					  .then(response => process(response.data));
				  }';
			}
			
			$nclass	="htTop ht".ucfirst($align);
			//if ($class) $nclass.=" {$class}";

			
			if ($type) $attrib["type"]=$type;
			if ($sort) $attrib["sort"]=true;
			if ($mask) $attrib["mask"]=$mask;
			if ($token) $attrib["tags"]=$token;
			if ($width) $attrib["width"]="{$width}";
			if ($render) $attrib["renderer"]=$render;
			if (stristr($extra, "multiple")) $attrib["multiple"]=true;
			if ($option) $attrib=array_merge($attrib, $option);
			$attrib["className"]	=$nclass;
			
			$cols['colm'][]	=$attrib;
			$cols['field'][]=$name;
			if ($input=='hidden') $cols['hide'][]=$key;
		} 
		return $cols;
	}

	function easyuiColms($columns) {
		$base_ajax	=varKey("base_ajax");
		$selects	=array("select", "radio", "checkbox");
		$array_text	=array("text"=>"textbox", "select"=>"combogrid", "number"=>"numberbox", "file"=>"filebox", "textarea"=>"textbox", "datetime"=>"datetimespinner", "date"=>"datebox", "time"=>"timespinner", "color"=>"colorbox", "mask"=>"maskedbox", "password"=>"passwordbox", "radio"=>"combo", "search"=>"searchbox", "slider"=>"slider", "switch"=>"checkbox", "tag"=>"tagbox");#numberbox, 

		$array_fields	=$this->columns($columns, "fields");
		
		$cols	=array();
		$count	=count($array_fields);
		$size_w	=floor(100/$count);
		foreach ($array_fields as $key=>$array) {
			$name	=arrayKey("name", $array);
			$label	=arrayKey("label", $array);
			$itype	=arrayKey("type", $array);
			$input	=arrayKey("input", $array, "text");
			$valid	=arrayKey("validate", $array);
			$extra	=arrayKey("attrib", $array);
			$value	=arrayKey("value", $array);

			if (!$label) $label="&nbsp;";
			if ($itype!="input") $extra=$input;

			$width	=floor($size_w);
			if ($key==0) $width=ceil($size_w);

			$tags	=$this->arrayFormat($extra);
			$opt_min=arrayKey("min", $tags);
			$opt_max=arrayKey("max", $tags);
			$steps	=arrayKey("step", $tags, 1);
			$token	=arrayKey("tags", $tags);
			$mask	=arrayKey("mask", $tags);
			$prefix	=arrayKey("prefix", $tags);
			$suffix	=arrayKey("suffix", $tags);
			$align	=arrayKey("align", $tags, "left");
			$width	=arrayKey("width", $tags, $width);
			$path	=arrayKey("path", $tags);
			$is_select	=arrayKey($input, $selects);
			
			$init	="";
			$class	="";
			if ($value) {
				$values	=$this->txt("*", $value, "array");
				$init	=arrayKey(0, $values);
				$value	=arrayKey("last", $value);
				if (!$init) $value=$init;
				$value	=$this->arrayConvert($value, "tree");
			}
			$required	=false;
			if ($valid) {
				if ($valid=="yes") {
					$required=true;
					$valid	="";
				}
				elseif (stristr(".{$valid}", ".r")) {
					$required	=true;
					$valid	=str_ireplace(".r", "", ".{$valid}");
				}
			}

			$attrib	=array("field"=>$name, "title"=>$label, "width"=>"{$width}%");//rowspan, colspan,sortable, resizable, halign, checkbox
			$option	=[];#"label"=>$label, "labelPosition"=>"top", panelHeight:'auto', editable: false, multiple:true, value:[1,3],, iconWidth:22, icons:[{ iconCls:'icon-add' },{...}], showItemIcon: true, data: [{value:'add',text:'Add',iconCls:'icon-add'}, {}]

			//panelWidth: 500, idField: 'id', textField: 'name', fitColumns: true, url: $path, method: 'get', columns: [[ {field:'id',title:'ID', width:80}, {...} ]], class: "easyui-combogrid" 

			$validate	=[];
			if ($path) {
				$link	=$this->path_lists("grid/{$path}");

				$option	=["method"=>"get", "valueField"=>"id", "textField"=>"name", "groupField"=>'group'];
				$option["url"]	=$link;
				$option["remote"]	=true;
			}

			if (stristr($extra, "tag-input")) {
				$class	="tagbox";
				$attrib["hasDownArrow"]	=true;
				$attrib["limitToList"]	=true;
				$validate[]	="uniquetag";
			}
			if ($valid=="inrange") {
				$input	="slider";
				$range	=explode("-", $valid);
				$opt_min	=arrayKey(0, $range, $opt_min);
				$opt_max	=arrayKey(1, $range, $opt_max);
			}
			if ($input=="slider") {
				$opt_rule	=arrayKey("rule", $tags);
				$vertical	=arrayKey("vertical", $tags);
				$reverse	=arrayKey("reversed", $tags);
				$attrib["showTip"]	=true;
				if ($opt_rule) $attrib["rule"]=$opt_rule;
				if ($vertical) $attrib["mode"]="v";
				if ($reverse) $attrib["reversed"]=$reverse;
				if (strstr($init, ",")) $attrib["range"]=true;
			}
			if ($input=="password") {
				$attrib["iconWidth"]	="28";
				if (stristr($name, "_confirm")) {
					$label	="Confirm your {$label}";
					$nname	=str_ireplace("_confirm", "", $name);
					$validate	="confirmPass['#{$nname}1']";
				}
			}
			if ($input=="number"||$valid=="number") {
				$align	="right";
				$input	="number";
				$validate	="number";
				$option["precision"]	=2;
				$option["groupSeparator"]	=",";
				$option["decimalSeparator"]	=".";
				//spinAlign:horizontal,vertical,left,right
			}
			if ($input=="search") {
				$attrib["buttonText"]	=$label;
				$attrib["buttonAlign"]	="right";
				$attrib["buttonIcon"]	="icon-search";
			}
				
			if ($input=="date"||$valid=="date") {
				$input	="date";
				$class	="datepicker";//datespinner
			}
			if ($input=="time"||$valid=="time") {
				$input	="date";
				$class	="timepicker";//timespinner
				$option	=["panelWidth"=>350, "panelHeight"=> 350, "hour24"=>true, "showSeconds"=>true];
			}
			if ($input=="datetime"||$valid=="datetime") {
				$input	="datetime";
				$class	="datetimepicker";//datetimespinner
				$option	=["hour24"=> true, "showSeconds"=>true];
			}
			if ($input=="tree") {
				$input	="date";
				$class	="tree";
				$option	=["method"=>"get", "animate"=>true, "checkbox"=>true, "dnd"=>false, "lines"=>true];
				//onClick: function(node){ $(this).tree('beginEdit', node.target); }
			}
			if ($input=="switch") {
				$attrib["labelWidth"]	="120";
				$class	="switchbutton";
			}
			if ($input=="textarea") {
				$attrib["multiline"]	=true;
			}
			if ($input=="color"||$valid=="color") {
				$input	="color";
			}
			if ($input=="email"||$valid=="email") {
				$input	="text";
				$validate	="email";
			}
			if ($is_select) {
				$option	=["valueField"=>"id", "textField"=>"name"];
			}
			if ($input=="file") {
				$label	="Browse {$label}";
			}
			if ($input=="url"||$valid=="url") {
				$validate	="url";
			}
			if ($input=="radio") {
			}
			if ($input=="checkbox") {
				$option	=["on"=>$init, "off"=>""];
			}
			if ($input=="hidden") {
				$attrib["hidden"]	=true;
			}
			
			
			if ($mask) $input="mask";
			$type	=arrayKey($input, $array_text, $input);
			if (!$class) $class=$type;
			
			$option["prompt"]	="{$label} ...";
			if ($prefix) $option["prefix"]=$prefix;
			if ($suffix) $option["suffix"]=$suffix;
			if ($opt_min!="") $option["min"]=$opt_min;
			if ($opt_max!="") $option["max"]=$opt_max;
			if ($steps) $option["increment"]=$steps;
			if ($required) $option["required"]=true;
			if ($opt_min&&$opt_max) $validate[]="length[{$opt_min},{$opt_max}]";

			$attrib["align"]	=$align;
			//$attrib["class"]	="easyui-{$class}";
			if ($init) $attrib["value"]	=$init;
			if ($mask) $attrib["mask"]=$mask;
			if ($token) $attrib["tags"]=$token;
			if ($validate) $attrib["validType"]	=$validate;
			if (stristr($extra, "multiple")) $attrib["multiple"]=true;
			if (stristr($extra, "readonly")) $option["hidden"]=true;
			if ($itype=="input") $attrib["editor"]=["type"=>$type, "options"=>$option];

			//iconWidth: 22, icons: [{ iconCls:'icon-add', handler: function(e){ $(e.data.target).textbox('setValue', 'Something added!'); } },{...}]

			$cols[]	=$attrib;
		} 
		return $cols;
	}

}
