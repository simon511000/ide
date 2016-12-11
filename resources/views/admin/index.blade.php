@extends('webed-core::admin._master')

@section('css')
    <style>
        .content-header {
            display : none !important;
        }

        section.content {
            padding : 0;
            position : absolute;
            top : 0;
            left : 0;
            right : 0;
            bottom : 0;
        }

        .content-wrapper {
            background-color : #272822;
            position : relative;
        }

        .full-height {
            height : 100%;
        }
    </style>
@endsection

@section('js')

@endsection

@section('js-init')

@endsection

@section('content')
    <div class="layout-1columns full-height">
        <div class="column main full-height">
            <div class="embed-responsive embed-responsive-16by9 full-height">
                <iframe class="embed-responsive-item" src="{{ route('admin::webed-ide.editor.get') }}"></iframe>
            </div>
        </div>
    </div>
@endsection
