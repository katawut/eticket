@extends('layouts.layout')

@section('pageStyle')
<!-- page style -->
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/form.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/login.css') }}" />
<style>
    input:valid+.required {
        display: block;
    }
</style>
@endSection

@section('content')
<div class="part-titlepage-main">
    <div class="container">
        <div class="box-titlepage">
            <h1 class="b-Title t-color t-align-c">@lang('messages.sign_in')</h1>
        </div>
    </div>
</div>

<div class="part-content-main bb">
    <form action="{{ route('login') }}" method="POST" id="formLogin">
        @csrf
        <div class="container">
            <div class="box-content-main">
                <div class="box-login">
                    <div class="box-top">
                        <input type="email" id="email" name="email" placeholder="@lang('messages.email')"
                            value="{{ old('email') }}" {{ $errors->has('email') ? 'required' : ''}}>
                        @if ($errors->has('email'))
                        <div class="required">{{ $errors->first('email')  }}</div>
                        @endif

                        <input type="password" name="password" id="password" placeholder="@lang('messages.password')"
                            {{ $errors->has('password') ? 'required' : ''}}>
                        @if ($errors->has('password'))
                        <div class="required">{{ $errors->first('password')  }}</div>
                        @endif
                    </div>

                    <div class="box-bottom cf">
                        <div class="box-btn">
                            <a id="btnLogin" name="btnLogin" class="btn black">@lang('messages.sign_in')</a>
                        </div>
                    </div>
                </div>
                <br>

                {{-- Register By Facebook --}}
                <div class="box-bottom cf" style="display: flex; justify-content: center;">
                    <a href="{{ url('/') }}/login/facebook">
                        <img class="btn" src="{{ asset('images/btn-login-fb.png')}}" alt="Login via Facebook">
                    </a>
                </div>

                {{-- Forgot Password --}}
                <div class="box-bottom cf">
                    <div style="text-align: center; padding: 20px 0;">
                        <a style="color:#fff; font-size: 16px; text-decoration: underline;"
                            href="{{ route('password.request') }}">{{ __('messages.forgot_password') }}
                        </a>
                    </div>
                </div>

                <div class="box-createacc cf">
                    <p>@lang('messages.need_register')</p>
                    <div class="box-btn">
                        <a href="{{ route('frontend.reg_terms') }}" class="btn black">@lang('messages.register')</a>
                    </div>

                    <div style="text-align: center; padding-top: 10px;">
                        <a style="color:#fff; font-size: 12px; text-decoration: underline;"
                            href="{{ route('frontend.policy') }}" target="_blank">@lang('messages.menu_policy')
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('pageScript')
<script src="{{ asset('frontend/js/login.js') }}" type="text/javascript"></script>
@endSection
