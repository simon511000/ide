<!DOCTYPE html>
<html lang="en" class="full-height">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ Config::get('laraedit::laraedit.title') }}</title>

    <link rel="stylesheet" href="{{asset('admin/modules/ide/css/bootstrap/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('admin/modules/ide/css/font-awesome/font-awesome.min.css')}}">
    <link rel="stylesheet" href="{{asset('admin/modules/ide/js/jquery-ui/jquery-ui.min.css')}}">
    <link rel="stylesheet" href="{{asset('admin/modules/ide/js/js-tree/themes/default-dark/style.min.css')}}">

    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Source+Code+Pro:200,400">

    <link rel="stylesheet" href="{{asset('admin/modules/ide/css/ide.css')}}">

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <style>
        .phpdebugbar {
            display : none !important;
        }
    </style>
</head>
<body class="full-height">

<div class="code-editor-wrapper full-height">
    <div class="sidebar full-height overflow-auto">
        <div id="tree"></div>
    </div>
    <div class="main full-height">
        <pre id="editor" class="full-height"></pre>
    </div>
</div>

<script src="{{asset('admin/modules/ide/js/jquery/jquery.min.js')}}"></script>
<script src="{{asset('admin/modules/ide/js/bootstrap/bootstrap.min.js')}}"></script>
<script src="{{asset('admin/modules/ide/js/jquery-ui/jquery-ui.min.js')}}"></script>
<script src="{{asset('admin/modules/ide/js/ace/ace.js')}}"></script>
<script src="{{asset('admin/modules/ide/js/js-tree/jstree.min.js')}}"></script>
<script src="{{asset('admin/modules/ide/js/ide.js')}}"></script>

<script type="text/javascript">
    $(document).ready(function(){
        WebEdIDE.init({
            getFileUrl: '{{ route('admin::webed-ide.file-tree.get') }}',
            saveUrl: '{{ route('admin::webed-ide.save.post', [
                '_token' => csrf_token()
            ]) }}',
        });
    });
</script>
</body>
</html>
