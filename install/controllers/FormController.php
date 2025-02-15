<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Teescripts\RptForms\layout;

class FormController extends Controller
{
    function __construct() {
    }

    public function index(): View
    {
        $path_files="";
        $row_blocks	=[[3,1,7], [3,[1,1,1,2,1],7], [3,1,2], [3,1,4]];
        config(['forms.break'=>' ']);
        config(['forms.row.cols'=>$row_blocks]);
        $form   =new layout;
            
        $result	=["text"=>"Some <b>HTML</b> text", "image"=>"male.png", "photo"=>"appoint-120-1-.jpg", "html"=>"Separator below", "number"=>"1200", "false"=>2, "true"=>1, "color"=>"#A22C44", "dual"=>"en,sw"];
        $fields	='head=heading_section_with_separator:h3,html=text,text=text_input_with_validate:input:text:remail,number=number_input:wrap:vtype="number" prefix="fad fa-sort-numeric-down" suffix=".00" decimal="0" class="* text-right",|,true=select:input:select::list_true,photo:photo:'.$path_files.':class="tx-center img-polaroid shadow-lg",true=radio:input:radio::list_true,false=checkbox:input:checkbox::list_true,color:input:text:color,date:input:text:date,time:input:text:time,text=textarea:input:textarea:::class="* richtext3" height="100",grid:input:grid::list_conttype,dual=dual_list:input:select::list_conttype:class="dual-input" multiple,image=file_upload:input:file,image2=photo_editor:input:file';

        $results	=[$result];
        $html   =$form->table($fields, "div", "vertical", $results);

        $data   =["title"=>"Example forms", "form_html"=>$html];

        return view('rpt-forms.forms', $data);
    }

    public function insert(): View
    {
        $row_cols   =[[6,1,1], [3,[2,1],5], [3,1,8]];
        config(['forms.break'=>' ']);
        config(['forms.row.cols'=>$row_cols]);
        $form   =new layout;
        $fields ='details:input:textarea:yes::class="* richtext" height="250",fname:input:text,lname:input:text,email:input:text:remail,dob:input:text:rdate,languages:input:radio::eng;fr,skills:input:select::php;css;html,level:input:checkbox::1;2;3;4;5,input_date';

        $html   =$form->table($fields, "div", "vertical");
        $data   =["title"=>"Add a new record", "form_html"=>$html];

        return view('rpt-forms.insert', $data);
    }

    public function update(): View
    {
        $row_cols   =[[6,1,1], [3,[2,1],5], [3,1,8]];
        config(['forms.break'=>' ']);
        config(['forms.row.cols'=>$row_cols]);
        $form   =new layout;
        $fields ='details:input:textarea:yes::class="* richtext" height="250",fname:input:text,lname:input:text,email:input:text:remail,dob:input:text:rdate,languages:input:radio::eng;fr,skills:input:select::php;css;html,level:input:checkbox::1;2;3;4;5,input_date';

        $html   =$form->table($fields, "div", "vertical");
        $data   =["title"=>"Update record", "form_html"=>$html];

        return view('rpt-forms.update', $data);
    }

    
    public function view(): View
    {
        $row_cols   =[[6,1,1], [3,[2,1],5], [3,1,8]];
        config(['forms.break'=>' ']);
        config(['forms.row.cols'=>$row_cols]);
        $form   =new layout;
        $fields ='details:input:textarea:yes::class="* richtext" height="250",fname:input:text,lname:input:text,email:input:text:remail,dob:input:text:rdate,languages:input:radio::eng;fr,skills:input:select::php;css;html,level:input:checkbox::1;2;3;4;5,input_date';

        $html   =$form->table($fields, "div", "vertical");
        $data   =["title"=>"Record Details", "form_html"=>$html];

        return view('rpt-forms.view', $data);
    }

    public function select(): View
    {
        $results   =[];
        
        $form   =new layout;
        $fields ='fname,lname,email:view_email,dob:view_age,languages:view_languages,skills:view_skills,level:view_level,input_date:view_date';

        $html   =$form->table($fields, "div", "vertical");
        $data   =["title"=>"List all records", "form_html"=>$html];

        return view('rpt-forms.list', $data);
    }

}
