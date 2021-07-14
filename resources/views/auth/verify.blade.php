@extends('layouts.layout')

@section('pageStyle')
<!-- Scripts -->
<script src="{{ asset('js/app.js') }}" defer></script>

<!-- Fonts -->
<link rel="dns-prefetch" href="//fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

<!-- Styles -->
<link href="{{ asset('css/app.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container pt-4 pb-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('ยืนยันอีเมล์ลงทะเบียน') }}</div>

                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('เราได้ส่งอีเมลเพื่อทำการยืนยันให้คุณแล้ว กรุณาตรวจสอบอีเมลที่คุณลงทะเบียนไว้') }}
                        </div>
                    @endif

                    {{ __('คุณต้องทำการยืนยันอีเมลก่อนจึงจะดำเนินการต่อได้') }}
                    {{ __('หากคุณยังไม่ได้รับอีเมลยืนยัน') }},
                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('คลิกที่นี่เพื่อรับอีเมลอีกครั้ง') }}</button> 
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
