@extends('layouts.layout')

@section('pageStyle')
<!-- page style -->
    <link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/form.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/login.css') }}" />

    <style>
        input:valid+.required { display: block; }
        .control-label-title { color: #FFF; font-size: 16px; font-weight: bold; padding: 10px 0; }
        .control-label-success { color: lightgreen; font-size: 18px; font-weight: bold; padding: 10px 0; }
        .text-center { text-align: center; }
    </style>
@endSection

@section('content')
<div class="part-titlepage-main">
    <div class="container">
        <div class="box-titlepage">
            <h1 class="b-Title t-color t-align-c">{{ __('messages.reset_password') }}</h1>
        </div>
    </div>
</div>

<div class="part-content-main bb">
    <form action="{{ route('password.email') }}" method="POST">
        @csrf
        <div class="container">
            <div class="box-content-main">
                <div class="box-login">
                    {{-- Alert Send Success --}}
                    @if (session('status'))
                        <div class="box-top">
                            <label class="control-label-success text-center">
                                {{ __('messages.reset_pass_send_mail_success') }}
                            </label>
                        </div>
                    @endif

                    <div class="box-top">
                        <label class="control-label-title">{{ __('messages.email') }}</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                        @error('email')
                            <div class="required">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="box-bottom cf">
                        <div class="box-btn">
                            <button type="submit" name="btnLogin" class="btn black">
                                {{ __('messages.send_request') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('pageScript')
@endSection
