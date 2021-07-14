@extends('layouts.layout')

@section('pageStyle')
<!-- page style -->
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/detail.css') }}" />
<style>
    .box-left ul {
        list-style-type: disc;
    }
</style>
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
    <div class="container">
        <div class="box-left">
            {{-- <img src="{{$ticket->image}}" alt="" class="img-ticket" id="showTicket"> --}}
            <img src="/storage/{{ $ticket->image_detail[0]->id }}/{{ $ticket->image_detail[0]->file_name }}" class="img-ticket" id="showTicket">
            <p style="color:red; margin-top:5px; font-size:14px;">
                {{ app()->isLocale('th') ? '* ลายของบัตรเข้าชมจะไม่สามารถเลือกได้' : '* The pattern of the ticket cannot be changed.' }}
            </p>
            @if ($ticket->image_detail->count() != 0)
            <div class="box-List">
                @foreach($ticket->image_detail as $thumbnail)
                <img src="/storage/{{ $thumbnail->id }}/{{ $thumbnail->file_name }}" onclick="showTicket({{ $thumbnail->id }}, '{{ $thumbnail->file_name }}');" @if ($loop->first)
                class="active"
                @endif
                >
                @endforeach
            </div>
            @endif
        </div>

        <div class="box-right">
            <h4 class="b-Title t-color">{{ app()->isLocale('en') ? $ticket->title_en : $ticket->title_th }}</h4>

            <div class="box-Itop">
                <span class="I-price">
                    @lang('messages.price') @lang('messages.ticket_type_adult') @money($ticket->price)
                    @lang('messages.baht')
                </span>

                @if($ticket->discount_price != 0)
                <span class="I-price" style="color: red;">
                    @lang('messages.discount') @money($ticket->discount_price) @lang('messages.baht')
                </span>
                @endif

                <span class="I-ticket">
                    @if ($ticket->stocks->quantity < 1) <strong style="color: red;">@lang('messages.soldout')</strong>
                        @elseif ($ticket->active == 'Inactive')
                        <strong style="color: red;">@lang('messages.unavailable')</strong>
                        @else
                        @lang('messages.available') (@lang('messages.remain') {{ $ticket->stocks->quantity }}
                        @lang('messages.unit'))
                        @endif
                </span>
            </div>

            <div class="box-Ticket">
                @lang('messages.price') {{ $ticket->price - $ticket->discount_price }} @lang('messages.baht')
                {{-- @method('GET'); --}}
                {{-- @lang('messages.amount') <input id="TicketNumber" name="TicketNumber"
                  type="number" min="1" max="{{ $ticket->quantity }}"
                value="{{ $ticket->quantity ? 1 : 0 }}"
                {{ $ticket->quantity < 1 || $ticket->active == 'Inactive' ? 'disabled' : '' }} >
                @lang('messages.unit')<br> --}}

                {{-- @lang('messages.price') <input id="b-Total" name="b-Total" readonly> @lang('messages.baht')<br>

                  @lang('messages.remain') <input id="remain" readonly> @lang('messages.unit')

                  <input type="hidden" id="b-price" name="b-price">
                  <input type="hidden" id="totalAmount" name="totalAmount">
                  <input type="hidden" id="price" name="price" value="{{$ticket->price}}">
                <input type="hidden" id="discount_price" name="discount_price" value="{{$ticket->discount_price}}"> --}}

                @if ($ticket->quantity < 1 || $ticket->active == 'Inactive')

                    @else
                    <button type="button" class="Icon-ticket" onclick="buyTicket()">
                        <i></i> {{ __('messages.buy_ticket') }}
                    </button>
                    <form id="add-item-{{ $ticket->id }}" action="{{ route('frontend.cart.update', $ticket->id) }}" method="POST" style="display: none;">
                        @csrf
                        @method('PUT')
                    </form>
                    @endif
            </div>

            <br>

            <h4 class="b-Title t-color">@lang('messages.description')</h4>
            @if (app()->isLocale('th') )
            {!! $ticket->description_th !!}
            @else
            {!! $ticket->description_en !!}
            @endif
        </div>

        <div class="box-left m-width100" style="clear: both;">
            <h4 class="b-Title t-color">@lang('messages.ticket_condition')</h4>
            @if (app()->isLocale('th') )
            {!! $ticket->condition_th !!}
            @else
            {!! $ticket->condition_en !!}
            @endif
            <br>
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
            <a href="javascript:void(0)" onclick="$(this).closest('.box-pouup').removeClass('open')">
                @lang('messages.cancel')
            </a>
            <a href="{{ route('login') }}">@lang('messages.sign_in')</a>
        </div>
    </div>
</div>

<div class="box-shadow"></div>

{{-- MODAL : TICKET STATUS ( Not Used ) --}}
<div class="box-pouup {{ session()->has('ticketStatus') ? 'open' : '' }}" id="pouup-noticket">
    <div class="b-head">ไม่สามารถทำรายการได้</div>
    <div class="b-text">
        @php
        $message = session()->get('ticketStatus');
        @endphp

        @if ($message == 'ticketLimitExceed')
        ท่านสามารถซื้อบัตรได้ครั้งละไม่เกิน 20 ใบเท่านั้น
        @else
        {{ $message }}
        @endif

        <div class="b-button" style="justify-content: center">
            <a href="javascript:void(0)" onclick="$(this).closest('.box-pouup').removeClass('open')">ตกลง</a>
        </div>
    </div>
</div>
<div class="box-shadow"></div>

{{-- MODAL : NOTIFICATION ( Not Used ) --}}
<div class="box-pouup" id="pouup-noti" data-noti="0" data-submit="0">
    <div class="b-head">
        ข้อแนะนำในการซื้อตั๋วเข้าชม
    </div>
    <div class="b-text">
        หากท่านต้องการซื้อตั๋วเข้าชม ตั้งแต่ 5 ใบขึ้นไป
        <div class="b-button" style="justify-content: center">
            <a href="javascript:void(0)" onclick="closeNotiBox($(this))">ตกลง</a>
        </div>
    </div>
</div>
<div class="box-shadow"></div>
@endsection

@section('pageScript')

<script>
    $(document).ready(function() {

        // ราคาตั๋ว
        @php
        if ($ticket - > discount_price != 0) {
            $ticketPrice = ($ticket - > price - $ticket - > discount_price);
        } else {
            $ticketPrice = $ticket - > price;
        }
        @endphp

        var ticketRemain = {
            {
                $ticket - > quantity
            }
        };
        var BPdefault = {
            {
                $ticketPrice
            }
        };
        var totalAmount = numeral(BPdefault * $('#TicketNumber').val()).format('0.00');
        var formatTotalAmount = numeral(totalAmount).format('0,0.00');

        setTimeout(function() {
            $('#b-Total').val(formatTotalAmount);
            $('#totalAmount').val(totalAmount);
        }, 50);

        $('#b-price').val(BPdefault);

        $('#remain').val(ticketRemain);

        $("#TicketNumber").change(function() {
            if ($(this).val() > 4 && $('#pouup-noti').data('noti') === 0) {
                $('#pouup-noti').addClass('open');
                $('#pouup-noti').data('noti', 1);
            }

            var TN = $('#TicketNumber').val();
            var BP = $('#b-Total').val();
            totalAmount = numeral(BPdefault * TN).format('0.00');
            formatTotalAmount = numeral(totalAmount).format('0,0.00');
            $('#b-Total').val(formatTotalAmount);
            $('#totalAmount').val(totalAmount);
        });

        $(".part-content-main .box-left .box-List img").click(function() {
            $(this).addClass('active');
            $(this).siblings().removeClass('active');
        });

    });

    function buyTicket() {
        // if($("#TicketNumber").val() > 4 && $('#pouup-noti').data('submit') === 0) {
        //   $('#pouup-noti').data('submit',1);
        //   $('#pouup-noti').addClass('open');
        //   return;
        // }
        // กดซื้อทุกครั้ง สั่งให้เริ่มจับเวลาใหม่ทุกครั้ง
        localStorage.clear('TRemaining');
        $.ajax({
            url: "{{ route('frontend.checklogin') }}"
        }).done(function(status) {
            if (!status.login) {
                $('#pouup-login').addClass('open');
            } else {
                $('#add-item-{{ $ticket->id }}').submit();
            }
        })

        // ถ้ายังไม่ login
        //$('#pouup-login').addClass('open');

        //$('#form-Ticket').submit();
    }

    function closeNotiBox(el) {
        el.closest('.box-pouup').removeClass('open');
        if ($('#pouup-noti').data('noti') === 1 && $('#pouup-noti').data('submit') === 0) {
            return;
        }
        if ($('#pouup-noti').data('submit') === 1) {
            return buyTicket();
        }
    }

    function showTicket(id, filename) {
        $('#showTicket').attr('src', `/storage/${id}/${filename}`);
    }
</script>
@endSection