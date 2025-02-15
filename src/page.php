<?php
namespace Teescripts\RptForms;

use Illuminate\Http\Request;
use Teescripts\RptForms\main;
class page extends main
{	public $session;
	function __construct() {
		parent::__construct();
		$request	=Request();
		$this->session	=$request->session();
	}

	public function pageCount($query, $array_bind="") {
		$count_query	=$this->varKey("count_query");
		$found_rows		="SQL_CALC_FOUND_ROWS";
		if ($count_query) $query=$count_query;
		$nquery	=str_ireplace(".select ", "SELECT {$found_rows} ", ".".trim($query));
		if (!strstr($query, $found_rows)) $query=$nquery;
		
		$result	=$this->view($query, $array_bind);
		$result	=$this->result("SELECT FOUND_ROWS() AS `rows`");
		$total	=$this->arrayKey("rows", $result, 0);
		return $total;
    }
		
	public function vals($item, $max="") {

		$index	=$this->prefix($item);
		$index	=strtolower(str_replace(" ", "_", $index));
		$get_max=$this->arrayKey("max_".$index, $_GET);
		$maxim	=$this->session->get("max", []);
		$max	=$this->arrayKey($index, $maxim, $max);		
		if ($get_max>0) {
			$max	=intval($get_max);
			$maxim[$index]	=$max;
			$this->sess->set("max", $maxim);
		}
		if ($max>500) $max=500;

		$get_page	=$this->session->get("page_".$index);
		if ($get_page<1) $get_page=1;
			
		$results	=["page"=>$get_page, "max"=>$max, "index"=>$index];
		return $results;		
	}
		
	public function results($query, $limit, $item="", $binding="") {
		$array	=$this->vals($item, $limit);
		$max	=$array["max"];
		$page	=$array["page"];
		
		if (is_array($query)) {
			$array_key1	=$this->arrayKey(0, $query);
			$array_key2	=$this->arrayKey(1, $query, $binding);
			if (!is_array($array_key1) && is_array($array_key2)) {
				$query	=$array_key1;
				$binding=$array_key2;
			}
		}
		# ------- build query
		$from	=($page * $max) - $max;  
		$query	=$query." LIMIT {$from}, {$max}";  
		$results=$this->query($query, $binding);
		return $results;
	}
	
	public function controls($query, $limit, $item, $forms, $page_type="small", $return=1, $binding="") {
		# globals
		$text_global	='btn_class,mini_class,select_class,page_block,page_id,page_class,page_ul,page_li,form_class,page_options';
		$global	=$this->globalVars($text_global);
		extract($global);
		
		if (is_array($query)) {
			$array_key1	=$this->arrayKey(0, $query);
			$array_key2	=$this->arrayKey(1, $query, $binding);
			if (!is_array($array_key1) && is_array($array_key2)) {
				$query	=$array_key1;
				$binding=$array_key2;
			}
		}
		$count	=$this->pageCount($query, $binding);
		$array	=$this->vals($item, $limit);
		$max	=$array["max"];
		$page	=$array["page"];
		$index	=$array["index"];
		#----------- base link
		$file_url	=$this->arrayKey( "REQUEST_URI", $_SERVER);
		$link_url	=$file_url;

		$base_link	=$link_url;
		$base_link	=str_replace("//", "", $base_link);
		$base_link	=$this->txt("max_{$index}", $base_link, "name");
		$base_link	=$this->txt("page_{$index}", $base_link, "name");
		$base_link	=rtrim($base_link, "/");

		$page_link	=$base_link."&page_".$index."=";
		$link_path	=$base_link."&max_".$index."=";

		$item_name	=$item;
		$form_path	="";#$base_link
	
		$table_class	=($page_type=="vb")?"page_vb":"page_table";
		$table_start	='
<div id="">
	<form method="get" action="'.$form_path.'" class="page_form '.$form_class.'">
	<table class="'.$table_class.'" border="0" cellpadding="0" cellspacing="0" style="width:100%">
		<tbody>
			<tr>'; 

		$table_end		='
			</tr>
		</tbody>
	</table>   
	</form>
</div>';

		$pages	=@ceil($count/$max);
		$from	=($page * $max)-$max;		
		$halfway=@floor($pages/2);
			
		$first	=1;
		$prev	=max($first, $page - 1);
		$next	=min($pages, $page + 1);
		$last	=$pages;
		$current=min($max, $count);
		
		$one	=$this->prural(1, $item_name, "Record");
		$many	=$this->prural(2, $item_name, "Record");
		# pagination count	
		$page_count	=$one.' '.($from+1).' - '.min($from+$max, $count).' of '.number_format($count).' Listed '.$many;
		# ---------- start pagination list items
		if (!$page_li) $li_class=' page-item';
		$li_class	=''.$page_li;#page_li 

		$link_class	=trim("page-link");
		
		$border	=($page<$halfway)?4:($last-3);
		if ($page<=$border||($border==$page-1)||($border==$page+1)) $border=0;
		
		$link_page	=[];
		$link_page["first"]	='<a href="'.url($page_link.$first).'" rel="first" class="'.$link_class.'" title="First page"><i class="fad fa-fast-backward"></i></a>';
		$link_page["prev"]	='<a href="'.url($page_link.$prev).'" rel="prev" class="'.$link_class.'" title="Previous page"><i class="fad fa-step-backward"></i></a>';
		$link_page["next"]	='<a href="'.url($page_link.$next).'" rel="next" class="'.$link_class.'" title="Next page"><i class="fad fa-step-forward"></i></a>';
		$link_page["last"]	='<a href="'.url($page_link.$last).'" rel="last" class="'.$link_class.'" title="Last page"><i class="fad fa-fast-forward"></i></a>';
		$link_page["all"]	='<a href="'.url($link_path.$count).'" rel="all" class="'.$link_class.'" title="View all">View all</a>';
		
		$list_text	='
	<div class="page_list '.$page_class.'">
		<ul class="page_ul '.$page_ul.'">';
		if ($page>1) {
			$list_text	.="\r\n\t\t\t".'<li class="first hide-mobile '.$li_class.'">'.$link_page["first"].'</li>';	
			$list_text	.="\r\n\t\t\t".'<li class="prev '.$li_class.'">'.$link_page["prev"].'</li>';
		} 
	
		for ($i=1; $i<=$pages; $i++) { 
			$hidden	=(($i!=$page-1&&$i!=$page&&$i!=$page+1)||$i==$border)?"hide-mobile ":"";
			if ($i==$page) { 
				$list_text	.="\r\n\t\t\t".'<li class="'.$hidden.$li_class.' active"><span class="page-link">'.$i."</span></li>"; 
			}
			elseif ($pages>20) {
				#pages too many
				if (($i<4||$i==($halfway-1)||$i==$halfway||$i==($halfway+1)||$i==($page-1)||$i==($page+1))&&$i!=$border||$i>($pages-3)) {
					#pages in the middle
					$list_text	.="\r\n\t\t\t".'<li class="'.$hidden.$li_class.'"><a href="'.url($page_link.$i).'" class="'.$link_class.'" title="Page '.$i.'">'.$i.'</a></li>';
				}
				else {
					# summarise the middle
					if (($i>=($halfway-2)&&$i<=($halfway+2))||$i==$border) $list_text.="\r\n\t\t\t".'<li class="'.$hidden.$li_class.'"><span class="page-link">&hellip;</span></li>';
				}
			}#end pages too many
			else { 
				$list_text	.="\r\n\t\t\t".'<li class="'.$hidden.$li_class.'"><a href="'.url($page_link.$i).'" class="'.$link_class.'">'.$i.'</a></li>';
			}
		} # end for each loop 	
		// Build Next Link 
		if ($page<$pages) {
			$list_text	.="\r\n\t\t\t".'<li class="next '.$li_class.'">'.$link_page["next"].'</li>';
			$list_text	.="\r\n\t\t\t".'<li class="last hide-mobile '.$li_class.'">'.$link_page["last"].'</li>';
		}
		// view all
		if ($count<=500 && $current<$count) {
			//$list_text	.="\r\n\t\t\t".'<li class="all hide-mobile '.$li_class.'">'.$link_page["all"].'</li>';
		}#-- end pagination links
		// close list
		$list_text	.='
		</ul>
	</div>'; # end pagination list 
	
		# ------- url var forms
		$fields	="";
		$form	=explode(",", $forms);
		foreach ($form as $key=>$value) {
			$url_value	=$this->session->get($value);
			if (is_array($url_value)) $value=$value.'[]';
			if (is_array($url_value)) $url_value=implode(",", $url_value);
			$fields.='
				<input type="hidden" name="'.$value.'" value="'.strip_tags($url_value).'" class="invisible" />';
		}
		# -------------- select
		$select_text	='
		<div class="page_select">
			<select class="'.$select_class.' jump-Menu input-md" name="max_'.$index.'" data-href="'.$link_path.'">';
		
		$opts	=[10, 20, 50, 100, 200, 500];
		if ($page_options) $opts=$page_options;
		foreach ($opts as $opt=>$val) {
			$state	=($max==$val)?' selected':"";
			$select_text	.='
				<option value="'.$val.'"'.$state.'>'.$val.'</option>';
		}
		#---------------- 
		$select_text	.='
			</select>	
		</div>';
		
		$page_goto	='
		<td class="text-right">Go to page: </td>
		<td class="text-left"><input type="text" class="'.$mini_class.' vb_input" name="page_'.$index.'" value="'.$page.'" '.($max<$count?"":"disabled").' /></td>    
		<td class="text-center"> of '.$pages.'</td>';
		$page_limit	='
		<td class="text-right">View: </td>
		<td class="text-left"><input type="text" class="'.$mini_class.' vb_input" name="max_'.$index.'" value="'.$max.'" /></td>
		<td class="text-center"> result(s) per page</td>';
		$page_submit	='
		<td class="text-center"><button type="submit" class="'.$btn_class.' vb_submit">Go</button>'.$fields.'</td>';
		# ---------- vb
		$vb_text	=$page_limit.'';
		if ($page>$first) {
			$vb_text	.='
		<td class="text-center">'.$link_page["first"].'</td> 
		<td class="text-center">'.$link_page["prev"].'</td>';		
		} 
		$vb_text	.=$page_goto;
		if ($page<$pages) {
			$vb_text	.='
		<td class="text-center">'.$link_page["next"].'</td>
		<td class="text-center">'.$link_page["last"].'</td>';
		}
		$vb_text	.='
		<td class="text-left">'.$page_count.'</td>';
		$vb_text	.=$page_submit;
		#----------- form
		$page_form	=$page_limit.$page_goto.$page_submit;
		# -------------- 
		if ($pages<=1) $list_text="";
		if ($pages<=1) $select_text="";
		$page_count	='<div class="page_count">'.$page_count.'</div>';
		if ($page_type=="hide") {
			$page_text	="";
		}
		elseif ($page_type=="dd") {
			$page_text	='';
			if ($select_text) $page_text.='<td>'.$select_text.'</td>';
			if ($list_text) $page_text.='<td>'.$list_text.'</td>';
			if ($page_text) $page_text=$table_start.$page_text.$table_end;
			$page_text	=$page_count.$page_text;
		}
		elseif ($page_type=="vb") {
			$page_text	=$table_start.$vb_text.$table_end;
		}
		elseif ($page_type=="info") {
			$page_text	=$page_count;
		}
		elseif ($page_type=="links") {
			$page_text	=$list_text;
		}
		elseif ($page_type=="small") {
			$page_text	=$page_count.$list_text;
		}
		elseif ($page_type=="form") {
			$page_text	=$table_start.$page_form.$table_end;
		}
		else {
			$page_text	=$page_count.$list_text;
			$page_text	.=$table_start.$page_form.$table_end;
		}
		# ---------- assemble pagination
		if (!$pages) $page_text=$this->msg(2, "No {$one} results found.");
		if ($page_type!="vb" && $page_text) $page_text='<div class="page_group '.$page_block.'" id="'.$page_id.'">'.$page_text.'</div>';
		#----------- return / output the pagination
		if ($return) {
			return $page_text;
		}
		else {
			echo $page_text;
		}
	}
	
	public function ajax($query, $page_array=[], $data="", $page=1) {
		$var_max	=$this->arrayKey("max", $page_array, 10);
		$var_item	=$this->arrayKey("item", $page_array, "item");
		$var_load	=$this->arrayKey("load", $page_array);
		$var_elem	=$this->arrayKey("elem", $page_array);
		$var_url	=$this->arrayKey("url", $page_array);
		if (!$var_elem) $var_elem=$this->textNorm($var_load, 1, 2);
		if (!$var_url) {
			$this_base	=$this->varKey("base_blank", "base/loader.php");
			$this_url	=$this->varKey("url", build_url($_GET));
			$var_url	="{$this_base}?{$this_url}";
		}
		
		$count	=$this->pageCount($query, $data);
		$array	=$this->vals($var_item, $var_max);
		$max	=$array["max"];
		$page	=$array["page"];
		$index	=$array["index"];
		#----------- elements
		$total	=@ceil($count / $max);

		$load_text	="";
		$link_base	=($var_url);
		if ($page<$total) {
			$page_next	=min(($page+1), $total);
			$link_next	=url($var_url.'&page_'.$index.'='.$page_next);
			$path_image =$this->varKey("base_assets");
				
			$load_text	='
			<a class="page-load-button pd-10 tx-center tx-14 d-block" title="Page '.$page.' in '.$count.' records" href="'.$link_next.'"> '.$this->lang("Load More").' ... </a>
			<img class="page-load-image d-none" src="'.$path_image.'images/ajax-loader.gif" alt="'.$this->lang("Loading").' ... " />';
		}
		
		$page_text	='<div class="page-load-trigger" id="trig_'.$var_elem.'" data-elem="'.$var_elem.'" data-load="'.$var_load.'" data-url="'.$link_base.'" data-item="'.$index.'" data-total="'.$total.'" data-page="'.$page.'">'.$load_text.'</div>';
		return $page_text;		
	}
	# end methods
}
