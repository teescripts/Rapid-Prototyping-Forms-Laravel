<?php
namespace Teescripts\RptForms;

use Teescripts\RptForms\main;
use Teescripts\RptForms\data;

class form extends main
{
	function __construct() {
		parent::__construct();
	}
	
	public function form_text($name="", $value="", $attribute="", $validate="") {
		return $this->form_textarea($name, $value, $attribute, $validate);
	}
	
	public function form_textarea($name="", $value="", $attribute="", $validate="") {
		return $this->form("textarea", $name, "", $value, $attribute, $validate);
	}
	
	public function form_select($name="", $value_list="", $value_init="", $attribute="", $validate="") {
		return $this->form("select", $name, $value_list, $value_init, $attribute, $validate);
	}
	
	public function form_input($type="", $name="", $value_init="", $value_list="", $attribute="", $validate="") {
		return $this->form($type, $name, $value_list, $value_init, $attribute, $validate);
	}
	
	public function input($type="", $name="", $values="", $input_class="", $input_id="", $attrib="", $validate="", $return="") {
		$values	=stripslashes($values);
		$values	=str_replace(',', '[cm]', $values);
		
		$value	="";
		$list	=$values;
		if (strstr($values, "*")) {
			$array	=$this->txt("*", $values);
			$value	=$this->arrayKey(0, $array);
			$list	=$this->arrayKey("ext", $array);
		}

		if ($type=="jmenu" && !strstr($attrib, 'href=')) $attrib='href="'.$attrib.'"';
		if ($input_id && !stristr($attrib, 'id="')) $attrib='id="'.$input_id.'" '.$attrib;
		if ($input_class && !stristr($attrib, 'class="')) $attrib='class="'.$input_class.'" '.$attrib;

		$input	=$this->form($type, $name, $list, $value, $attrib, $validate);
		
		if ($return) {
			return $input;
		}
		else {
			echo $input;
		}
	}
	
	public function form($form_type="", $name="", $value_list="", $value_init="", $attribute="", $validate="") {
		$class	="";
		$type	=$form_type;
		if (!$type) $type="text";
		if (!$name) $name=$type;
		$attrib	=strip_tags($attribute);
		$attrib	=trim($attrib);
		$tags	=$this->arrayFormat($attrib);
		$multi	=strstr($attrib, "multiple");

		$wrapped=$this->arrayKey("wrap", $tags);
		$prefix	=$this->arrayKey("prefix", $tags);
		$suffix	=$this->arrayKey("suffix", $tags);
		$vtype	=$this->arrayKey("vtype", $tags, "form");
		$decimal=$this->arrayKey("data-decimal", $tags);
		if ($decimal==="") $decimal=$this->arrayKey("decimal", $tags);
		$ntype	=$this->arrayKey("type", $tags, $type);
		
		$get_id	=$this->arrayKey("id", $tags);
		$get_href	=$this->arrayKey("href", $tags);
		$get_class	=$this->arrayKey("class", $tags);
		$data_url	=$this->arrayKey("data-path", $tags);
		if (!$data_url) $data_url=$this->arrayKey("path", $tags);
		
		$isCSV	=(strstr($attrib, 'csv-')||strstr($attrib, 'number-')||strstr($attrib, 'money-')||strstr($attrib, 'numeric-'));
		$unset	="type,wrap,vtype,prefix,suffix,help";
		if (!$isCSV) $unset.=",decimal";
		$tags	=$this->arrayUnset($tags, $unset);
		$attrib	=$this->arrayTags($tags);
		if ($wrapped) {
			$form_type	="wrap";

			if ($vtype=="form" && $ntype) $type=$ntype;
			if (in_array($vtype, ["text", "select", "textarea"])) $type=$vtype;
		}

		$attrib1	=$attrib;
		$attrib2	="";
		if ($form_type=="jmenu") {
			$type	="select";
			$class	="* jump-Menu";
			#$unset	=",href";
			if (!$get_href) $attrib2=' href="'.$attrib.'"';
		}

		$type	=strtolower($type);
		$types	=["radio", "checkbox", "select", "text", "textarea", "hidden", "password", "file"];
		if ($type=="check") $type=$types[1];
		
		$options	=in_array($type, [$types[0], $types[1], $types[2]]);
		if ($type=="datalist") $options=true;
		
		$input_def	=stripslashes($value_init);
		$input_list	=$value_list;
		
		$array_data	=$this->listName($input_list, $data_url, $form_type);
		$list_name	=$this->arrayKey("name", $array_data);
		$list_path	=$this->arrayKey("path", $array_data);

		$data_list	="";
		if (in_array($form_type, ["grid", "combo", "tree"])) {
			$options=true;
			$type	="select";
			$class	="easyui-combogrid";
			if ($form_type=="tree") $class="easyui-combotreegrid";
			$attrib	=$this->gridList($array_data, $input_def, $multi);
			if ($attrib) $attrib2.=" {$attrib}";
			$input_def	="";
			$input_list	="";
		}
		else {
			if ($list_name) {
				$data_list	="list_{$list_name}";
				if ($data_url) $attrib2=str_replace($data_url, $list_path, $attrib2);
			}
		}
		#----------- 
		$array_list	=[];
		if ($options) {
			if ($data_list) {
				$input_list	=$this->varKey($data_list);
			}
			else {
				$data	=new data();
				$is_fx	=method_exists($data, "{$list_name}List");
				if ($is_fx) {
					$input_list	=$this->load("lists")->loadList("lists/array/{$list_name}");
				}
			}
			
			if (is_array($input_list)) {
				$array_list	=$input_list;
			}
			elseif ($input_list) {
				$input_list	=str_replace(',', '[cm]', $input_list);
				$input_list	=$this->fetchList($input_list);
				$array_list	=$this->arrayConvert($input_list, "tree");
			}
			if ($data_url && $input_def && !$input_list) {
				$attrib1	.=' data-value="'.$input_def.'"';
			}

			$array_init	=$this->arrayConvert($input_def, "keys");
		}
		else {
			if ($input_def=="" && $input_list!="") {
				$input_def	=$input_list;
			}
		}

		$count_input	=0;
		if (is_array($array_list)) $count_input=count($array_list);

		#----------- 
		$naming	=$this->naming($name);
		$new_name	=$naming["name"];
		$new_label	=$naming["label"];
		
		$form_name	=$new_name;
		if ($multi) $form_name="{$new_name}[]";
		
		$lower_name	=strtolower($new_name);
		$rule		=strtolower($validate);
		$arule		=str_split($validate);
		$required	=0;
		if ($this->arrayKey(0, $arule)=="r"||$rule=="yes") {
			$required	=1;
			$rule	=substr($rule, 1, strlen($rule));
			if ($rule=="yes") $rule="";				
			$attrib2	.=' data-required="true"';			
			if ($form_type!="grid") $attrib2.=' required="required"';
		}
		# derive color / date type from name
		$new_rule	="";
		$types_list	="color,colour,time,date,daterange,datetime,monthday,yearmonth,day,month,year";
		$types_array=explode(",", $types_list);
		foreach ($types_array as $ntype) {
			$xtype	=$ntype;
			if ($ntype=="colour") $xtype="color";
			if (strstr($lower_name, $ntype)) $new_rule=$xtype;
		}	
		# optional rule
		if (!$rule) $rule=$new_rule;

		$time_list	="date,datetime,daterange,drange,trange,dtrange,yearmonth,monthday,day,month,year";
		$time_array	=explode(",", $time_list);
		$is_date	=in_array($rule, $time_array);
		
		$new_list	="number,search,tel,url,email,datetime,date,month,week,time,range";#,datetime-local,color
		$new_array	=explode(",", $new_list);
		//if (in_array($rule, $new_array) && $type=="text") $type=$rule;
		
		if (strlen($input_def)=="7" && substr($input_def, 0, 1)=="#") $attrib2.=' style="background-color:'.$input_def.'"';
		#----------- 
		$new_id	=$this->new_id($name);

		$form_id	=$get_id;
		if (!$get_id) $form_id=$new_id;
		
		$vclass	="";
		$vrule	=$rule;
		if ($rule) {
			  
			if ($type!="hidden") {
				#start validation
				$custom	="email,integer,number,date,datetime,ipv4,url,zip,phone,alpha,alphaNum,alphaSpace,numSpace,spaces,alphaAccent,numAccent,accents";
				$custom	=explode(",", $custom);
				
				$vclass	=[];
				if ($required) $vclass[]="required";
				if (strstr($rule, "range") && !$is_date) {	
					$range	=str_replace("inrange", "", $rule);
					$chip	=explode("-", $range);
					$min	=$this->arrayKey(0, $chip);
					$max	=$this->arrayKey(1, $chip);
					$min_max	='integer],min['.$min.']';
					if ($max) $min_max.=',max['.$max;
					$vclass[]	=$min_max;
				}
				if (strstr($rule, "min")) {	
					$min	=str_replace("min", "", $rule);
					$vclass[]	='integer],min['.$min;
				}
				if (strstr($rule, "max")) {	
					$max	=str_replace("max", "", $rule);
					$vclass[]	='integer],max['.$max;
				}
				if (in_array($rule, $custom)) $vclass[]="custom[".$vrule."]";
				if ($rule=="confirm") {
					$field	=str_replace("confirm_", "", $form_id);
					$vclass[]="equals[".$field."]";
				}			
				if ($vclass) $vclass=implode(", ", $vclass);
			}
		}
		
		$alt_type	=["number", "month", "time", "date", "datetime", "url", "year", "color"];
		$ntype	=$this->inArray($type, $alt_type, $type);
		if ($type=="datalist") $ntype="text";

		$tclass	=$this->formKey("{$ntype}_class");
		if (!$tclass) $tclass=$this->formKey("text_class", "form-control");#
		
		$nclass	=$get_class;
		if (!$nclass) $nclass=$tclass;
		if (!$class) $class=$nclass;
		if ($is_date&&!$options) $class.=" pl_{$rule} date-icon";
		if ($rule=="time"&&!$options) $class.=" pl_{$rule} time-icon";
		if ($rule=="color"&&!$options) $class.=" color-icon";
		if (in_array($rule, ["numbers", "color"])) $class.=" {$rule}-input";
		if ($vclass) $class.=" validate[".$vclass."]";
		if ($rule=="number") $class.=" text-right";
		
		$form_class	=str_replace("*", $tclass, trim($class));

		$tips		=$this->placeholder($attrib, $name, $type);

		$attrib1	=$this->tagText($attrib1, $tips);
		$attrib2	=['id="'.$form_id.'"', 'class="'.$form_class.'"', $attrib2];
		$attrib2	=implode(" ", $attrib2);

		if (strstr($attrib1, 'id="')) $attrib1=str_replace('id="'.$get_id.'"', "", $attrib1);
		if (strstr($attrib1, 'class="')) $attrib1=str_replace('class="'.$get_class.'"', "", $attrib1);
		if ($attrib2) $attrib1="{$attrib2} {$attrib1}";
		# --------- wrappers
		$text_open	="";
		$text_close	="";
		$wrap_open	='';
		$wrap_close	='';
		# ---------- form text
		$text_form	="";

		if ($type=="select") {
			if (stristr($form_class, "material")) $attrib1.=' data-theme="material"';
			
			$text_open	='
			<select name="'.$form_name.'" '.$attrib1.'>';

			$text_close	.='
			</select>';
		}
		elseif ($form_type=="datalist") {
			$attrib1	+=' onfocusin="$(this).attr(\'type\', \'email\');" onfocusout="$(this).attr(\'type\', \'text\');"';	

			$text_open	='
			<input type="text" name="'.$form_name.'" list="'.$form_id.'_options" '.$attrib1.' />
			<datalist id="'.$form_id.'_options">';

			$text_close	.='
			</datalist>';
		}
		elseif (in_array($type, ["radio", "checkbox"]))  {
			if ($count_input>1) {
				$class_name	=$this->formKey("group_class_{$type}");
				$group_class="group-class-{$type}";
				if ($class_name) $group_class.=" {$class_name}";

				$text_open	='
			<div data-type="'.$type.'" class="group-class '.$group_class.'" id="'.$form_id.'">';

				$text_close	.='
			</div>';
			}
		}
		# --------------- file upload
		elseif ($type=="file" && $input_def) {
			if ($data_url) {
				$path	=$data_url;
				$npath	=$this->path_cdn($path);
				if (strstr($npath, $path)) $npath=$path;
				$attrib1	=str_replace($path, $npath, $attrib1);
			}

			$text_open	='<div class="img_block mg-t-5 mg-b-5">';
			$text_open	.='
				<input type="radio" name="'.$form_name.'" value="'.$input_def.'" checked="checked" id="rs_'.$form_id.'" /> <label for="rs_'.$form_id.'" class="img_set d-inline">'.$this->lang("Maintain").'</label>';
			$text_open	.='
				<input type="radio" name="'.$form_name.'" value="" id="rm_'.$form_id.'" /> <label for="rm_'.$form_id.'" class="img_set d-inline">'.$this->lang("Remove").'</label>';
			$text_open	.='
			</div>';
			$attrib1	.=' data-value="'.$input_def.'" capture="environment"';
			$input_def	="";
		}
		# -------------- password field	
		elseif ($type=="password") {
			$hash	=$this->formKey("password_hash");
			$attrib1	.=' autocomplete="off"';

			$text_open	="";
			if ($hash && $input_def) {
				$attrib3	=str_replace($form_id, "old_{$new_name}1", $attrib1);
				$attrib3	=str_replace($form_class, $form_class." reset_pass", $attrib3);

				$attrib4	=str_replace($form_id, "new_{$new_name}1", $attrib1);
				$attrib4	=str_replace($form_class, $form_class." reset_pass", $attrib4);
				
				if ($hash==1) $text_open='
				<input type="password" name="old_'.$form_name.'" value="" placeholder="'.$this->lang("old_password").'" '.$attrib3.' /><br />';

				$text_open	.='
				<input type="password" name="new_'.$form_name.'" value="" placeholder="'.$this->lang("new_password").'" '.$attrib4.' />';

				$type	="hidden";
				$attrib1	=str_replace($form_class, $tclass, $attrib2);
			 }
			 else {
				if ($hash) {
					$attrib3	=str_replace($form_id, "confirm_{$new_name}1", $attrib1);
					$attrib3	=str_replace($form_class, "{$form_class} validate[optional, equals[{$new_name}1]] reset_pass", $attrib3);
					$text_close	='<br>
					<input type="password" name="confirm_'.$form_name.'" value="" placeholder="'.$this->lang("confirm_password").'" '.$attrib3.' />';
					$required	=0;
				 }	
			 }				 
		}
		# ------ wrap with prefix + suffix
		if ($form_type=="wrap") {
			$wrap_open	='
			<div class="input-group input-append input-prepend id-'.$form_id.'">';

			$wrap_open	.=$this->addon($prefix, $input_def, 1, $form_class);
			$wrap_close	=$this->addon($suffix, $input_def, 2, $form_class);

			$wrap_close	.='
			</div>';

		}

		if (strstr("1 2 3", $vtype)) $type="readonly";
		# ---------- loop values for: select, radio, checkbox
		if ($options) {
			$text_form	.=$this->optionList($array_list, $array_init, $type, $form_name, $form_class, $form_id, $attrib1);
		}
		# ------ textarea
		elseif ($type=="textarea") {		
			$text_form	="
				<".$type.' name="'.$form_name.'" '.$attrib1.">".$input_def."</{$type}>";		
		}
		# ------ textarea
		elseif (in_array($type, ["submit", "button", "reset"])) {		
			$text_form	='
				<button type="'.$type.'" name="'.$form_name.'" value="'.$input_def.'" '.$attrib1.">".$input_def."</button>";		
		}
		else {
			#------------ finally render the field
			$input_def =htmlspecialchars_decode($input_def, ENT_QUOTES);
			#$input_def =strip_tags($input_def);
			$input_def =htmlentities($input_def, ENT_COMPAT);
			if (is_array($input_def)) $input_def=json_encode($input_def);
			if ($decimal!=="" && $input_def) $input_def=number_format((float)$input_def, $decimal);
			$text_form	.='
				<input type="'.$type.'" name="'.$form_name.'" value="'.$input_def.'" '.$attrib1." />";				
		}
		
		if ($type=="readonly") {
			$dclass	=$get_class;
			if (!$dclass) $dclass="*";
			if ($vtype==1) {
				$wrap_open	="";
				$wrap_close	="";
				$prefix	=$this->addonIcon($prefix, $input_def);
				$suffix	=$this->addonIcon($suffix, $input_def);
	
				$dclass	="nobr-alt";
			}
			else {
				$prefix	="";
				$suffix	="";
			}
			$tclass	=$this->formKey("text_class");
			$dclass	=str_replace("*", $tclass, $dclass);
			$dclass	=str_replace("richtext", "ignore", $dclass);
			$dclass	=str_replace("elastic-input", "", $dclass);
			$dclass	=str_replace("select-input", "", $dclass);
			$dclass	=trim($dclass);
			
			$text_form	=strip_tags($text_form);
			$text_form	=trim($text_form);
			if ($input_def&&!$text_form) $text_form=$input_def;	

			$text_open	='<div class="'.$dclass.'">';
			if ($input_def && $prefix) $text_open.="{$prefix} ";
			
			$text_close	="";
			if ($input_def && $suffix) $text_close="{$suffix}";
			$text_close	.='</div>';
		}

		$text_open	=$wrap_open.$text_open;
		$text_close	=$text_close.$wrap_close;

		$text_form	=str_replace("[s]", ";", $text_form);
		$text_form	=str_replace("[cm]", ",", $text_form);

		if ($wrapped) {
			if (strstr("2 3", $wrapped)) {
				$form_group	=$text_form;
				for ($form=2; $form<=$wrapped; $form++) {
					$text_icon	=$this->arrayKey("prefix{$form}", $tags);
					$form_group	.=$this->addon($text_icon, $input_def, 1, $form_class);
					$form_group	.=$text_form;
				}
				$text_form	=$form_group;
			}
			if ($text_form=="") $text_form='&nbsp;';
		}

		$form_text	=$text_open;
		$form_text	.=$text_form;
		$form_text	.=$text_close;
		
		return $form_text;
	}

	function addonIcon($icon, $value) {
		$text	=urldecode($icon);
		if (strstr($icon, " fa-")) $text='<i class="'.$icon.'"></i>';
		if (strstr($icon, "@icon ")) $text='<i class="'.str_replace('@icon ', '', $icon).'"></i>';
		if (strstr($icon, 'src-')) $text='<img src="'.str_replace('src-', '', $icon).'" alt="#" class="wd-15 bg-grey" />';
		$text	=str_replace("#", $value, $text);
		$text	=stripslashes($text);
		return $text;
	}

	function addon($icon, $value="", $type=1, $class="") {
		$text	="";
		$icon	=$this->addonIcon($icon, $value);
		if ($icon) {
			$position	="prefix";
			if ($type==2) $position="suffix";

			if (stristr($class, "bs5")) {
				$text	='
				<span class="input-group-text span-'.$position.'">'.$icon.'</span>';
			}
			else {
				$class	="prepend";
				if ($type==2) $class="append";
				$text	='
				<div class="input-group-'.$class.' input-group-addon span-'.$position.'">
					<span class="input-group-text">'.$icon.'</span>
				</div>';
			}
		}
		return $text;
	}

	function new_id($text) {
		$name	=str_replace("[]", "", $text);
		$name	=str_replace("][", "_", $name);
		$name	=str_replace("[", "_", $name);
		$name	=str_replace("]", "", $name);
		if ($name==$text) $name="{$name}1";
		return $name;
	}

	public function placeholder($text, $name="", $type="") {
		$no_option	=$this->formKey("no_option");
		$array_tags	=$this->arrayFormat($text);
		$form_class	=$this->formKey("select_class");
		$old_title	=$this->arrayKey("placeholder", $array_tags);
		$new_title	=$old_title;

		if ($old_title=="none") $no_option=1;
		if ($no_option!=1 && !$old_title) {
			$new_title	=$no_option;
			if ($no_option<1) {
				$new_title	=$this->lang($name);
			}
		}
		
		if ($type=="select" && !$new_title) $new_title=" -------- ";
		$new_title	=" {$new_title} ...";
		$new_title	=strip_tags($new_title);
		if (stristr($form_class, "material")) {
			$new_title	=" ";
		}
		$titles	=[$new_title, $old_title, $no_option];
		return $titles;
	}

	public function tagText($text_tags, $array_tags) {
		$new_title	=$this->arrayKey(0, $array_tags);
		$old_title	=$this->arrayKey(1, $array_tags);
		$hide_option=$this->arrayKey(2, $array_tags);
		$text_tags	=str_replace('placeholder="'.$old_title.'"', '', $text_tags);
		if ($new_title!=" ") {
			if ($hide_option!=1) $text_tags.=' placeholder="'.$new_title.'"';
		}
		$text_tags	=trim($text_tags);
		return $text_tags;
	}

	public function optionList($list, $default="", $type="select", $name="", $class="", $input_id="", $attrib="") {
		$group_field	=$this->formKey("group_fieldset_{$type}");
		$group_title	=$this->formKey("group_legend_{$type}");
		$group_block	=$this->formKey("group_block_{$type}");
		$group_wrap		=$this->formKey("group_wrap_{$type}");
		
		if (!$group_field) $group_field=$this->formKey("group_fieldset");
		if (!$group_title) $group_title=$this->formKey("group_legend");
		if (!$group_block) $group_block=$this->formKey("group_block");
		if (!$group_wrap) $group_wrap=$this->formKey("group_wrap");

		if (!$group_field) $group_field="group-fieldset-{$type}";
		if (!$group_title) $group_title="group-legend-{$type}";
		if (!$group_block) $group_block="group-block-{$type}";
		if (!$group_wrap) $group_wrap="group-wrap-{$type}";

		if (stristr($group_wrap, "grid-size")&&!$group_title) $group_title=substr($group_wrap, 0, -1)."12";
		#------- options, radio, checkbox
		$array	=is_array($list)?$list:$this->arrayConvert($list, "tree");
		$item	=[];
		#------- select	group
		if ($type=="select") {
			$placeholders	=$this->placeholder($attrib, $name, $type);
			$placeholder	=$this->arrayKey(0, $placeholders);
			$hide_option	=$this->arrayKey(2, $placeholders);
	
			if ($placeholder && $hide_option!=1) {
				$item[]='
					<option value="" class="placeholder">'.$placeholder."</option>";
			}
		}
		#------- loop values options, radio, checkbox
		if (is_array($array)) {
			foreach ($array as $mkey=>$array_main) {	
				$avalue	=$this->arrayKey("id", $array_main);
				$alabel	=$this->arrayKey("name", $array_main);
				$achild	=$this->arrayKey("children", $array_main);
				# -------------- if multi dimensional
				if (is_array($achild)) {
					$option	="";
					foreach ($achild as $rkey=>$array_row) {
						$value	=$this->arrayKey("id", $array_row);
						$label	=$this->arrayKey("name", $array_row);
						$option	.=$this->inputGroup($value, $label, $default, $type, $name, $class, $input_id, $attrib);
					}
														
					# ------------ select group
					if ($type=="select") {
						$item[]	='
					<optgroup title="Options under:'.htmlentities($avalue, ENT_COMPAT).'" class="option_group" label="'.htmlentities($alabel, ENT_COMPAT).'">'.$option.'
					</optgroup>';	
					}
					else {	
						$item[]	='
					<fieldset class="group-fieldset '.$group_field.'">
						<legend class="group-legend '.$group_title.'">'.$alabel.'</legend>
						<div class="group-block '.$group_block.'">'.$option.'</div>
					</fieldset>';
					}
					#-------- end else
				}
				else {
					$item[]	=$this->inputGroup($avalue, $alabel, $default, $type, $name, $class, $input_id, $attrib);
				}
			}
		}
		$options	=implode("\n", $item);
		return $options;
	}

	public function inputGroup($value="", $label="", $default="", $type="select", $name="", $class="", $input_id="", $attrib="") {
		
		# ------ 
		$state	="";
		if ((is_array($default)&&in_array($value, $default))||$value==$default) {
			$state	=($type=="select")?"selected":'checked="checked"';
		}
		# ---------- 
		$group_wrap	=$this->formKey("group_wrap_{$type}");
		if (!$group_wrap) $group_wrap=$this->formKey("group_wrap");
		if (!$group_wrap) $group_wrap="group-wrap-{$type}";
		
		$group_label	=$this->formKey("group_label_{$type}");
		if (!$group_label) $group_label=$this->formKey("group_label");
		if (!$group_label) $group_label="group-label-{$type}";
		
		$opt_value	=htmlentities($value, ENT_COMPAT);
		$inputId	=$this->textNorm($input_id.$opt_value);
		
		$label		=str_replace("[c]", ":", $label);
		$attrib		.=' id="'.$input_id.'"';
		$lab_class	=($class)?' class="group-label '.$group_label.' lab-'.$class.'"':"";
		$attrib		=str_replace('id="'.$input_id.'"', 'id="'.$inputId.'"', $attrib);

		$break	=$this->formKey("break");
		# ---------- form text
		if ($type=="radio") {
			$option	='
				<input type="'.$type.'" name="'.$name.'" value="'.$opt_value.'" '.$attrib.' '.$state.' /> <label for="'.$inputId.'"'.$lab_class.'>'.$label.'</label>'.$break;	
			if ($group_wrap) $option='<div class="group-wrap '.$group_wrap.'">'.$option.'</div>';
		}
		if ($type=="checkbox") {
			$option	='
				<input type="'.$type.'" name="'.$name.'" value="'.$opt_value.'" '.$attrib.' '.$state.' /> <label for="'.$inputId.'"'.$lab_class.'>'.$label.'</label>'.$break;	
			if ($group_wrap) $option='<div class="group-wrap '.$group_wrap.'">'.$option.'</div>';
		}
		if ($type=="select") {
			$option	='
					<option value="'.$opt_value.'" '.$state.">".$label."</option>";
		}
		if ($type=="datalist") {
			$option	='
					<option value="'.$opt_value.'" '.$state.">".$label."</option>";
		}

		if ($type=="readonly") {
			$option	=$label;
			if (!$state) $option="";
		}
		return $option;
	}
	
	public function listName($list="", $path="", $type="") {
		$nList	="";
		
		if (!$list) $list=$path;
		if (!$path) $path=$list;

		$ntype	=$type;
		$nPath	=$path;
		$array	=["list_", "array_", "grid/", "tree/", "select/"];#, "json/", "excel/"
		foreach ($array as $text) {
			if (strstr($text, "/")) $ntype=trim($text, "/");
			if (strstr($list, $text)) $nList=str_replace($text, "", $list);
			if (strstr($nPath, $text)) $nPath=str_replace($text, "", $nPath);
		}

		$var_url	=[];
		$value	=$nList;

		if ($value) {
			if (!$ntype) $ntype="select";
			$nPath	="{$ntype}/{$value}";
			if ($type=="json") $nPath="json/{$value}";
			if ($type=="excel") $nPath="excel/{$value}";
			if (in_array($type, ["grid", "tree"])) $nPath="grid/{$value}";
		
			parse_str("list={$value}", $var_url);
			$_GET	=array_merge($_GET, $var_url);
		}
		$name	=$this->arrayKey("list", $var_url);
		$result	=["name"=>$name, "type"=>$type, "path"=>$nPath];
		return $result;
	}
	
	public function listData($name="") {
		$count	=0;
		$type	="";
		$key_col	=[];
		$key_col	="";
		$key_name	="";

		$keys	=[];
		if (strstr($name, "array_")) {
			$array	=$this->formKey($name);
			if ($array) {
				$type	="array";
				$row	=$this->arrayKey(0, $array);
				$keys	=array_keys($row);
				$key_col	=$this->arrayKey(0, $keys);
				$key_name	=$this->arrayKey(1, $keys);
				if (in_array("id", $keys)) $key_col="id";
				if (in_array("name", $keys)) $key_name="name";
				
				$count	=count($keys);
			}
		}
		else {
			$text_query	=$this->load("lists")->as_query($name);
			if ($text_query) {
				$type	="grid";
				$array_sql	=explode(":", $text_query);
				$sql_query	=$this->arrayKey(0, $array_sql);
				$key_col	=$this->arrayKey(1, $array_sql, "id");
				$key_name	=$this->arrayKey(2, $array_sql, "name");
				$key_name	=str_replace('[c]', ":", $key_name);
				
				if (strstr($sql_query, '->table("')) {
					$sql_query	='return '.$sql_query.'->get();';
					$sql_query	=eval($sql_query);
				}
				
				if (strstr($sql_query, "SELECT ")) {
					$result	=$this->result($sql_query);
					$count	=count($result);
					$keys	=array_keys($result);
				}
				else {
					$keys	=explode(",", "{$key_col},{$key_name}");
					$count	=count($keys);
				}
			}
		}
		
		$result	=["type"=>$type, "keys"=>$keys, "value"=>$key_col, "label"=>$key_name, "count"=>$count];
		return $result;
	}
	
	public function gridList($list="", $value="", $multi=0) {
		$form_type	=$this->arrayKey("type", $list);
		$list_name	=$this->arrayKey("name", $list);
		$list_path	=$this->arrayKey("path", $list);

		$result	=$this->listData($list_name);
		$list_type	=$this->arrayKey("type", $result);
		$array_keys	=$this->arrayKey("keys", $result);
		$count_keys	=$this->arrayKey("count", $result);
		$key_col	=$this->arrayKey("value", $result);
		$key_name	=$this->arrayKey("label", $result);
		
		$attrib	="";
		if ($array_keys) {
			$count	=($count_keys - 1);
			$width	=(100/($count + 0.5));
			$width1	=floor($width);
			$width2	=ceil(($width * 1.5));
			
			$key	=0;
			$colm	=[];
			$ignore	="()[]-:;";
			foreach ($array_keys as $col) {
				if (!strstr($ignore, $col)) {
					if ($key==0) {
						$key_col	=$col;
					}
					else {
						$width	=$width1;
						if ($key==1) {
							$key_name	=$col;
							$width	=$width2;
						}
						$colm[]	=["field"=>$col, "title"=>$this->lang($col), "width"=>"{$width}%", "formatter"=>"grid_photo"];
					}
					$key++;
				}
			}
			$json	=json_encode($colm);
			$colms	=str_replace('"', "'", $json);
			$colms	=str_replace("'grid_photo'", "grid_photo", $colms);

			$width	="50";
			if ($count>3) $width="70";
			if ($count>5) $width="95";

			$link	=$this->path_lists($list_path);
			if (strstr($list_type, "array")) $link.="&array={$list_name}";

			$col_name	="text";
			if ($form_type=="tree") $col_name="tree";

			$option	="method: 'get', mode: 'remote', fitColumns: true, panelHeight: 310, nowrap:false";
			$option	.=", panelWidth: '{$width}%', panelMinWidth: '250px', panelMaxWidth: '900px'";
			$option	.=", idField: '{$key_col}', {$col_name}Field: '{$key_name}'";
			$option	.=", pagination: true, pageSize: 20, url:'{$link}'";

			$nvalue	=str_replace("&#039;", "'", $value);
			$nvalue	=addslashes($nvalue);
			if ($multi) {
				$nvalue	=str_replace(",", "', '", $nvalue);
				$option.=", value:['{$value}'], multiple:true";
			}
			else {
				$option.=", value:'{$nvalue}'";
			}
			$option	=str_replace('"', '\"', $option);
			$option	.=", columns:[{$colms}]";

			$attrib	='data-options="'.$option.'" style="width:100%;"';
		}
		return $attrib;
	}

	
	public function wrap($type="text", $name="", $values="", $value="", $attribute="", $validate="") {
		
		$tags	=$this->arrayFormat($attribute);
		$type	=$this->arrayKey("type", $tags, $type);
		$attrib	='wrap="1" '.$attribute;

		$text	=$this->form($type, $name, $values, $value, $attrib, $validate);
		return	$text;
	}

}
