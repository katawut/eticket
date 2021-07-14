@extends('layouts.layout')

@section('pageStyle')
<!-- page style -->
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/index.css') }}" />
@endSection

@section('content')
<div class="part-head">
    <div class="container">
        <div class="box-title">
            <h1 class="b-Title t-color-w">E-TICKET</h1>
            <h4 class="b-Title" style="color: #d6ae66;">@lang('messages.menu_buy_ticket')</h4>
        </div>
    </div>
</div>

<div class="part-content-main">
    <h3 class="box-head">@lang('messages.museum__pass')</h3>
    <div class="container">
        <div class="box-list">
            @foreach ($lists as $item)
            <div class="List">
                <a href="{{ route('frontend.products.show', $item['id']) }}">
                    <div class="img">
                        <img src="{{ $item['cover'] }}">
                    </div>
                    <h4>{{ (app()->isLocale('th') ? $item['title_th'] : $item['title_en']) }}</h4>
                    {{-- บัตรเข้าชมพิพิธภัณฑ์แบบรายวัน --}}
                    <b>@lang('messages.price') : @money($item['price']) @lang('messages.baht')</b>
                    @if ($item['discount'] != 0)
                    <b style="color:green">@lang('messages.discount_price') : @money($item['discount'])
                        @lang('messages.baht')</b>
                    @endif
                </a>

                <button type="button" class="Icon-ticket">
                    <i></i> {{ __('messages.buy_ticket') }}
                </button>

                <form id="add-item-{{ $item['id'] }}" action="{{ route('frontend.cart.update', $item['id']) }}" method="POST" style="display: none;">
                    @csrf
                    @method('PUT')
                </form>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- MODAL : SIGNIN --}}
<div class="box-pouup" id="pouup-login">
    <div class="b-head">@lang('messages.sign_in')</div>
    <div class="b-text">
        @if (app()->isLocale('th'))
        คุณยังไม่ได้เข้าสู่ระบบ ?<br>กรุณา Log in เพื่อเข้าสู่ระบบ
        @else
        You are not logged in yet ?<br>Please Login
        @endif
        <div class="b-button">
            <a href="javascript:void(0)" onclick="$(this).closest('.box-pouup').removeClass('open')">@lang('messages.cancel')</a>
            <a href="{{ route('login') }}">@lang('messages.sign_in')</a>
        </div>
    </div>
</div>

<div class="box-shadow"></div>
@endsection

@section('pageScript')
<script>
    $(document).ready(function() {
        $('.Icon-ticket').click(function() {
            var itemToCart = $(this).next().attr('id');
            localStorage.clear('TRemaining');
            $.ajax({
                url: "{{ route('frontend.checklogin') }}"
            }).done(function(status) {
                if (!status.login) {
                    $('#pouup-login').addClass('open');
                } else {
                    $('#' + itemToCart).submit();
                }
            })
        })
    });
</script>
@endSection