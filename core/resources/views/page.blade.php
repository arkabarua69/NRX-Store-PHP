@extends('layouts.app')

@section('title')
    {{ $page->title }} {{ __('-') }} {{ $settings->site_title }}
@endsection

@section('content')
    <div class="container-fluid">
        <div class="col-sm-9 mx-auto m-tb-50">
            <div class="panel simple_form shadow">
                <div class="panel_head">
                    <div class="title">
                        {{ $page->title }}
                    </div>
                </div>
                <div class="body">
                    {!! $page->content !!}
                </div>
            </div>
        </div>
    </div>
@endsection
