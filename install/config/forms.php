<?php

$alert_success	="alert alert-success";
$alert_info		="alert alert-secondary";#info, primary, secondary
$alert_warning	="alert alert-warning";
$alert_error	="alert alert-danger";
$alert_others	="alert-dismissible shadow fade show alert-label-icon rounded-label alert-border-left";

$multi_class	='form-control multi-input';
$mini_class	    ='form-control input-mini';
$tag_class      ="form-control tag-input"; 
$select_class   ="form-control select-input";

return [
    "colon"=>'[c]', 
    "comma"=>'[cm]', 
    "form"=>[
        "tags"=>"yes", 
        "path"=>"", 
        "class"=>"", 
        "method"=>"post"
    ], 
       
    "btn"=>[
        "mini"	=>"btn-sm ", 
        "value"=>"Submit", 
        "prefix"	=>"btn btn-soft-", 
        "colors"	=>"primary,success,info,warning,danger,secondary,dark,light", 
        "class"	=>"btn btn-dark waves-effect waves-light bg-gradient btn-animation btn-border active", 
        "active"	=>"waves-effect waves-light bg-gradient btn-label btn btn-outline-primary btn-border mg-b-5", 
        "other"	=>"waves-effect waves-light bg-gradient btn-label btn btn-secondary mg-b-5"
    ], 
    "tab"=>[
        "class"	=>"search_btn btn btn-outline-info btn-border rounded-pill", #
        "inner1"	=>'<i class="fad fa-@ label-icon mg-r-10-f"></i> ',
        "inner2"	=>'',
        "active"	=>"active", 
        "other"	=>"default", 
        "start"	=>'',
        "end"	=>''
    ],

    'table' => [
        'id'=>'', #dt
        'attr'=>"", 
        'header'=>"", 
        'footer'=>"", 
        'body'=>"", 
        'foot'=>"", 
        'class'=>'table table-bordered',
        'class1'=>'table table-bordered striped-columns'
    ], 
    'print_pages'=>[
        'pages'=>"", 
        'path'=>""
    ], 
    
    'this'=>[
        'url'=>"", 
        'role'=>"", 
        'auth'=>"", 
        'user'=>""
    ], 
      'current'=>[  
        'role'=>"", 
        'user'=>"", 
        'id'=>"", 
        'names'=>""
    ], 
   
    "alert"=>[
        "success"	=>$alert_success, 
        "info"		=>$alert_info, 
        "warning"	=>$alert_warning, 
        "error"	    =>$alert_error, 
        "others"	=>$alert_others, 
        "class"	=>[1=>$alert_success, 2=>$alert_info, 3=>$alert_warning, 4=>$alert_error]
    ], 

    'c'=>[], 
    'style'=>["", ""], 
    'colors'=>"primary,success,info,warning,danger", 
    'limit' => 12, 
    'library'=>"", 
    'includes'=>"", 
    'language'=>"", 
    'action'=>"", 
    'icon'=>"", 
    'date'=>"", 
    'tables'=>"", 
    'item'=>"", 
    'control'=>"", 
    'root'=>"", 
    'exclude'=>["view,update,delete,ie"], 
    'siteid'=>"", 
    'sitename'=>env("APP_NAME"), 
    'site'=>[
        'id'=>"", 
        'name'=>"", 
        'root'=>"", 
        'base'=>""
    ], 
    'db_sites'=>"", 
    'array_sites'=>"", 
    'session'=>[
        'code'=>"", 
        'data'=>"", 
        'language'=>""
    ], 
    'row'=>[
		"cols"=>"", 
		"blocks"=>"", 
		"class"=>"row no-gutters"
    ], 
     
    "readonly_class"	=>"form-control-plaintext", 
    "block_class"	=>"bd-b pd-b-5", 
    "text_class"	=>"form-control bs5", 
    "select_class1"	=>$select_class, 
    "select_class2"	=>"form-control select-input", 
    "select_class"	=>$select_class,
    "file_class"	=>"form-control cropper-input", 
    "textarea_class"	=>'form-control elastic-input bs5', 
    "radio_class"	=>"material-radio with-gap", 
    "checkbox_class"	=>"material-checkbox", #filled-in
    "number_class"	=>"form-control number-input", 
    "richtext"	=>'class"=>"form-control richtext"', 
    "richtext2"	=>'class="form-control richtext3"', 
    "spin_class"		=>"form-control number-input", 
    "password_class"	=>"form-control", 

    "multi_class"	=>$multi_class, 
    "multi_input"	=>'class="'.$multi_class.'" multiple', 

    "mini_class"	=>$mini_class, 
    "mini_input"	=>'class="'.$mini_class.'"', 

    "tag_class"	=>$tag_class, 
    "tag_input"	=>'class="'.$tag_class.'"', 

    "row_class"	=>'row g-2', 
    "span_class"	=>'col-md-', 

    "closeable"		=>'<button type=>"button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',

    "icon_list"		=>["insert"=>"plus-circle","select"=>"list","view"=>"file","search"=>"search","update"=>"edit","ie"=>"th-list","print"=>"print","print_all"=>"print","vb"=>"list","grid"=>"th","export"=>"upload","import"=>"download"], 

    "no_option"	=>0,
    "page"	=>[
        'id'=>"",
        "class"	=>'justify-content-center', 
        "ul"	=>'pages pagination pagination-separated', 
        "li"	=>'page-item', 
        "a"	=>'page_a page-link'
    ], 

    'thead_class'=>"", 
    'ts_extra_row'=>"", 
    'ts_extra_vrow'=>"", 
    'th_class'=>"th_head", 
    'tip_class'=>"tip-top", 
    'get_order'=>"", 
    'get_col'=>"", 
    'app_extension'=>"", 
    'mobile_device'=>"", 
    'screen_size'=>"", 
    'cms_connect'=>"", 
    'base_path'=>"", 
    'cms_path'=>"", 
    'int_action'=>"", 
    'action_name'=>"", 
    'get_id'=>"", 
    'icon_name'=>"", 
    'get_mod'=>"", 
    'module_name'=>"", 
    'get_title'=>"", 
    'mod_array'=>"", 
    'path_icons'=>"", 
    'module_sql'=>"", 
    'lang_array'=>"", 
    'is_lang'=>""
];