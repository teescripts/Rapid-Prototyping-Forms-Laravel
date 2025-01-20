<?php
namespace Tee\RptForms;

class template {
	function template($type="") {
		
		$label	="label-control";
		$row_class	=varKey("block_class");#readonly_class
		if (!$row_class) $row_class="bd-b pd-b-5 is-div not-form";#

		# form-blocks
		$layouts	=[

	# bootstrap material
	"M1"=>[
		"label"=>"bmd-label-floating", 
		"form"=>'
<div class="form-group bmd-form-group m-10 [tc]">
	[fl]
	[ff]
</div>'], 

	# bootstrap material alternative
	"M1-ALT"=>[
		"label"=>"bmd-label", 
		"form"=>'
<div class="form-group bmd-form-group m-10 is-filled [tc]">
	[fl]
	<div class="controls '.$row_class.' [rc]">[ff]</div>
</div>'], 

	# materialise
	"M2"=>[
		"label"=>$label, 
		"form"=>'
<div class="input-field label-floating [tc]">
	[ff]
	[fl]
</div>'], 

	"M2-ALT"=>[
	"form"=>'
<div class="input-field [tc]">
	[fl]
	<div class="controls '.$row_class.' [rc]">[ff]</div>
</div>'], 

	# google material
	"M3"=>[
	"label"=>"form-label", 
	"form"=>'
<div class="form-group form-float [tc] pd-r-10-f">
	<div class="form-line">
		[fl]
		[ff]
	</div>
</div>'], 

	# google material alternative
	"M3-ALT"=>[
	"label"=>"form-label", 
	"form"=>'
<div class="form-group form-float [tc] pd-r-10-f">
	<div class="form-line nbb elevate">
		[fl]
		<div class="controls '.$row_class.' [rc]">[ff]</div>
	</div>
</div>'], 

	# material dashboard
	"M4"=>[
		"label"=>$label, 
		"form"=>'
<div class="md-input-container md-float-label [tc]">
	[ff]
	[fl]
</div>'], 

	# material dashboard alternative
	"M4-ALT"=>[
	"form"=>'
<div class="md-input-container md-float-label is-filled [tc] [rc]">
	[ff]
	[fl]
</div>'], 

	# material bootstrap
	"MB"=>[
	"label"=>"form-label", 
	"form"=>'
<div class="input-group input-group-outline m-2 wd-95p [tc]">
	[fl]
	[ff]
</div>'], 

	# material bootstrap alternative
	"MB-ALT"=>[
		"form"=>'
<div class="input-group input-group-outline m-2 wd-95p focused is-focused [tc]">
	[fl]
	<div class="'.$row_class.' [rc]">[ff]</div>
</div>'], 

	"MB-BTN"=>[
		"form"=>'
<div class="input-group m-2 wd-95p [tc]">
	<div class="'.$row_class.' [rc]">[ff]</div>
</div>'], 

	# material dashboard custom (materia)
	"MDC"=>[
		"label"=>"mdc-floating-label", 
		"form"=>'
<div class="mdc-text-field mdc-text-field--outlined is-form [tc]">
	[ff]
	<div class="mdc-notched-outline">
		<div class="mdc-notched-outline__leading"></div>
		<div class="mdc-notched-outline__notch">
			[fl]
		</div>
		<div class="mdc-notched-outline__trailing"></div>
	</div>
</div>'], 

	# material alt
	"MDC-ALT"=>[
	"label"=>"mdc-floating-label mdc-floating-label--float-above bg-white pd-x-5 z-index-100", 
	"form"=>'
	<div class="mdc-text-field mdc-text-field--outlined ht-100p-f [tc]">
		<div class="mdc-notched-outline mdc-notched-outline--upgraded mdc-notched-outline--notched">
			<div class="mdc-notched-outline__leading"></div>
			<div class="mdc-notched-outline__notch">
				[fl]
			</div>
			<div class="mdc-notched-outline__trailing"></div>
		</div>
		<div class="controls d-block wd-100p is-div">
			[ff]
		</div>
	</div>'], 
	
	# material alt
	"MDC-BTN"=>[
		"form"=>'
		<div class="is-form ht-100p-f [tc] [rc]">
			<div class="controls d-block wd-100p">
				[ff]
			</div>
		</div>'], 
		
	# material custom (reactor)
	"MC"=>[
		"label"=>"input__label", 
		"form"=>'
<div class="input m-b-md [tc]">
	[ff]
	<label class="input__label" [la]>
		<span class="input__label-content">[lt]</span>
	</label>
</div>'], 

	"MC-ALT"=>[
	"form"=>'
<div class="input m-b-md [tc]">
	<label class="input__label" [la]>
		<span class="input__label-content">[lt]</span>
	</label>
	<div class="controls '.$row_class.' [rc]">[ff]</div>
</div>'], 

	# block wrap (mooli)
	"MW"=>[
	"label"=>"input__label", 
	"form"=>'
<div class="form-group c_form_group mg-r-5 [tc]">
	[fl]
	[ff]
</div>'], 

	# css float label display in border
	"FLB"=>[
		"label"=>$label." mg-l-5-f pd-l-5-f bg-white", 
		"form"=>'
<div class="form-label-group in-border [tc] mg-r-5-f">
	[ff]
	<span class="legend">[fl]</span>
</div>'], 

	"FLB-ALT"=>[
		"label"=>$label." mg-l-5-f pd-l-5-f bg-white", 
		"form"=>'
<div class="form-label-group in-border [tc] [rc] mg-r-5-f">
	<div class="is-div form-control">[ff]</div>
	<span class="legend">[fl]</span>
</div>'], 

	"FLB-BTN"=>[
		"form"=>'
<div class="form-label [tc] [rc]">
	[ff]
</div>'], 

	# css float label display outside
	"FLO"=>[
		"label"=>$label, 
		"form"=>'
<div class="form-label-group outline [tc] mg-r-5-f">
	[ff]
	<span class="legend">[fl]</span>
</div>'], 

	"FLO-ALT"=>[
		"form"=>'
<div class="form-label-group outline [tc] [rc] mg-r-5-f">
	[ff]
	<span class="legend">[fl]</span>
</div>'],

	"FLO-BTN"=>[
		"form"=>'
<div class="form-label [tc] [rc]">
	[ff]
</div>'], 

	# bootstrap 4 structure
	"B4"=>[
		"label"=>"col-form-label", 
		"form"=>'
<div class="form-group [tc] pd-x-5-f mg-b-5">
	[fl]
	[ff]
</div>'], 

	# bootstrap 4 structure
	"B4-ALT"=>[
		"form"=>'
<div class="form-group [tc] [rc] pd-x-5-f mg-b-5">
	[fl]
	<div class="controls '.$row_class.' [rc]">[ff]</div>
</div>'], 

	# bootstrap 5 structure
	"B5"=>[
		"label"=>"form-label", 
		"form"=>'
<div class="form-group [tc] mb-3">
	[fl]
	[ff]
</div>'], 

	"B5-ALT"=>[
		"form"=>'
<div class="form-group [tc] [rc] mb-3">
	[fl]
	<div class="controls '.$row_class.' [rc]">[ff]</div>
</div>'], 

	# bootstrap 5 structure with floating label
	"B5F"=>[
		"label"=>"form-label", 
		"form"=>'
<div class="form-floating mg-b-10 [tc]">
	[ff]
	[fl]
</div>'], 

	"B5F-ALT"=>[
		"form"=>'
<div class="form-group bd pd-5 b-rad5 mg-b-10 [tc] [rc]">
	[fl]
	<div class="controls '.$row_class.' [rc]">[ff]</div>
</div>'], 

	# bootstrap 5 custom floating label structure
	"B5X"=>[
		"label"=>$label, 
		"form"=>'
<div class="form-group [tc]">
	<label class="floating-label"[la]>
		[ff]
		<span>[lt]</span>
	</label>
</div>'], 

"B5X-ALT"=>[
	"label"=>$label, 
	"form"=>'
<div class="form-group [tc] [rc]">
	[fl]
	<div class="controls '.$row_class.'">[ff]</div>
</div>'], 

	# bootstrap 4 structure in reverse
	"BR"=>[
		"label"=>$label, 
		"form"=>'
<div class="form-group [tc]">
	[ff]
	[fl]
</div>'], 

"BR-ALT"=>[
	"label"=>$label, 
	"form"=>'
<div class="form-group [tc] [rc]">
	[fl]
	<div class="controls '.$row_class.'">[ff]</div>
</div>'], 

	# unspecified falls to # bootstrap 2/3 structure
	"NA"=>[
		"label"=>"control-label", 
		"form"=>'
<div class="control-group [tc] [rc] pd-5-f">
	[fl]
	<div class="controls">[ff]</div>
</div>'], 

	# unspecified falls to # bootstrap 2/3 structure
	"ALT"=>[
	"label"=>"col-form-label", 
	"form"=>'
<div class="form-group [tc] [rc] pd-y-10 pd-x-5 mg-b-0-f">
	[fl]
	<div class="controls '.$row_class.' [rc]">[ff]</div>
</div>']

		];

		if ($type) {
			$result	=arrayKey($type, $layouts, $layouts["NA"]);
			$form	=arrayKey("form", $result);
			$label	=arrayKey("label", $result);
			$result	=["form"=>$form, "label"=>$label];
		}
		else {
			$result	=$layouts;
		}
		return $result;
	}
}
