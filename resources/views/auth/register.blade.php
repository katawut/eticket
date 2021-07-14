@extends('layouts.layout')

@section('pageStyle')
<!-- page style -->
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/form.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/register.css') }}" />
@endSection

@section('content')
<div class="part-titlepage-main">
  <div class="container">
    <div class="text-titlepage">
        @if (app()->isLocale('th'))
            <h1 class="b-Title t-color t-align-c">ฟอร์มสมัครสมาชิก</h1>
            <h3 class="b-Title t-align-c">สมัครสมาชิก</h3>
        @else
            <h1 class="b-Title t-color t-align-c">Registration Form</h1>
            <h3 class="b-Title t-align-c">Registration</h3>
        @endif
    </div>
  </div>
</div>

<div class="part-content-main">
  <div class="container">
    <form name="myform" id="myform" method="POST" action="{{ route('register')}}">
      @csrf
      <input type="hidden" name="social"
      value="{{old('social', session()->has('fromSocial') ? session()->get('fromSocial')['provider'] : '')}}">

      <input type="hidden" name="social_id"
      value="{{old('social_id', session()->has('fromSocial') ? session()->get('fromSocial')['social_id'] : '')}}">

      <div class="box-content-main">
        {{-- Register By --}}
        @if (session()->has('fromSocial'))
        @lang('messages.registration_via') : {{ Str::ucfirst(session()->get('fromSocial')['provider']) }}
        @else
        @lang('messages.registration_via') : @lang('messages.via_website')
        @endif

        <div class="box-group-form cf">
          <div class="box-row cf">
            <div class="box-left">
              <div class="box-input-text">
                <p>@lang('messages.name') *</p>
                <div>
                  <input type="text" id="first_name" name="first_name" placeholder="@lang('messages.name')" autofocus
                  value="{{ old('first_name') }}"
                  {{ $errors->has('first_name') ? 'required' : ''}}>
                  @if ($errors->has('first_name'))
                  <div class="required">{{ $errors->first('first_name')  }}</div>
                  @endif
                </div>
              </div>
            </div>

            <div class="box-right">
              <div class="box-input-text">
                <p>@lang('messages.surname') *</p>
                <div>
                  <input type="text" id="last_name" name="last_name" placeholder="@lang('messages.surname')"
                  value="{{ old('last_name') }}" {{ $errors->has('last_name') ? 'required' : ''}}>
                  @if ($errors->has('last_name'))
                  <div class="required">{{ $errors->first('last_name')  }}</div>
                  @endif
                </div>
              </div>
            </div>
          </div>

          <div class="box-row cf">
            <div class="box-left">
              @if (session()->has('fromSocial'))
              <div class="box-input-text">
                <p>@lang('messages.email') *</p>
                <div>
                  <input type="text" id="email" name="email"
                  value="{{ session()->get('fromSocial')['email'] }}"
                  {{ (session()->get('fromSocial')['email'] == NULL ? 'required' : 'readonly') }}>
                </div>
              </div>
              @else
              <div class="box-input-text">
                <p>@lang('messages.email') *</p>
                <div>
                  <input type="email" id="email" name="email" placeholder="@lang('messages.email')"
                  value="{{ old('email') }}" {{ $errors->has('email') ? 'required' : '' }}>
                  @if ($errors->has('email'))
                  <div class="required">{{ $errors->first('email') }}</div>
                  @endif
                </div>
              </div>
              @endif
            </div>

            <div class="box-right">
              <div class="box-input-text">
                <p>@lang('messages.phone') *</p>
                <div>
                  <input type="tel" name="phone" pattern="[0-9]{10}" maxlength="10"
                  placeholder="0812345678" value="{{ old('phone') }}"
                  {{$errors->has('phone') ? 'required' : ''}}>
                  @if ($errors->has('phone'))
                  <div class="required">{{ $errors->first('phone')  }}</div>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- IF Register By Normal --}}
        @if (!session()->has('fromSocial'))
        <div class="box-group-form red cf">
          <div class="box-row cf">
            <div class="box-left">
              <div class="box-input-text">
                <p>@lang('messages.password') *</p>
                <div>
                  <input type="password" name="password" id="password" placeholder="@lang('messages.password')" required
                  {{ $errors->has('password') ? 'required' : '' }}>
                  @if ($errors->has('password'))
                  <div class="required">{{ $errors->first('password')  }}</div>
                  @endif
                </div>
              </div>
            </div>

            <div class="box-right">
              <div class="box-input-text">
                <p class="n"></p>
                <div>
                  @if (app()->isLocale('th'))
                  <span>
                    ** กรุณากรอกรหัสผ่านอย่างน้อย 8 ตัวอักษร และต้องเป็นตัวเลข<br>
                    หรือตัวอักษรภาษาอังกฤษเท่านั้น
                  </span>
                  @else
                  <span>
                    ** Please enter a password of at least 8 characters and must contain numbers or English characters only.
                  </span>
                  @endif
                </div>
              </div>
            </div>
          </div>

          <div class="box-row cf">
            <div class="box-left">
              <div class="box-input-text">
                <p>@lang('messages.confirm_password') *</p>
                <div>
                  <input type="password" name="password_confirmation" id="password_confirmation" required
                  placeholder="@lang('messages.confirm_password')" {{ $errors->has('password') ? 'required' : ''}}>
                </div>
              </div>
            </div>
          </div>
        </div>
        @else
        {{-- If Register By Social --}}
        <input type="hidden" name="password" id="password" value="{{ session()->get('fromSocial')['social_id'] }}">
        @endif

        <div class="box-btn submit">
          <a href="{{ route('frontend.index')}}"" class=" btnReset btn black">@lang('messages.cancel')</a>
          <button type="submit" class="btnSubmit btn black">@lang('messages.submit')</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- <x-relate-link-slide/> --}}
@endsection
