
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="format-detection" content="telephone=no" />
    <meta http-equiv="x-ua-compatible" content="ie=edge,chrome=1" />
    
    <base name="base" id="app_url" href="https://kibatu.local/" data-cdn="https://clic.local//" />
    <meta name="csrf-token" content="site_form_120d405bbd64cf1557b42e8a6a8a15531736">

    <title>RPT Forms</title>
	<meta name="keywords" content="RPT Forms" />
	<meta name="description" content="RPT Forms" />
	<meta name="author" content="teescripts" />
 
    @include('rpt-forms.styles')
</head>
<body class="theme-retail">

<div class="mg-10 pd-30 shadow">
    @yield('forms')
</div>
 
@include('rpt-forms.scripts')
</body>
</html>