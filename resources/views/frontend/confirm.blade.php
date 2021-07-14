@extends('layouts.layout')

@section('pageStyle')
<!-- page style -->
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/successful.css') }}" />
<style>
  .box-paper .box-btn .btn2.paypalButton {
    background-color: transparent;
    box-shadow: none;
  }

  .box-pouup.open {
    opacity: 0;
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
    <div class="box-paper">
      <div class="box-body">
        <br>
        <br>
        <h3 class="text-color" style="margin-left: 15px;">@lang('confirm.order_summary')</h3>
        <br>
        <table>
          <tr>
            <td><b>@lang('confirm.transaction_date') :</b></td>
            <td>
              @php
              $date = \Carbon\Carbon::parse( $created_date );
              @endphp
              @if (app()->isLocale('th'))
              {{ $date->locale('th')->isoFormat('D MMMM GGGG') }}
              @else
              {{ $date->locale('en')->isoFormat('MMMM Do, GGGG') }}
              @endif
            </td>
          </tr>

          <tr>
            <td><b>@lang('messages.payment') :</b></td>
            <td>{{ app()->isLocale('th') ? $payment_type->name_th : $payment_type->name_en }}</td>
          </tr>

          @if ($payment_type->id == 5)
          <tr>
            <td>
              @if (app()->isLocale('th'))
              <b style="color:red;">หลังจากทำการสั่งซื้อแล้ว กรุณาทำการโอนหลักฐานการชำระเงินภายใน 3 วัน</b>
              @else
              <b style="color:red;">After placing an order Please transfer proof of payment within 3 days.</b>
              @endif
            </td>
          </tr>
          @endif
        </table>

        <br>
        <hr>

        @php
        $total_discount_price = 0;
        $total_price = 0;
        $total_price_with_discount = 0;
        $total_unit_price = 0;
        @endphp
        @foreach($cart_items as $item)
        @php
        $item_total_price = $item->ticket->price * $item->total;
        $item_total_discount = $item->ticket->discount_price * $item->total;
        $item_price_with_discount = $item_total_price - $item_total_discount;
        $total_unit_price += $item_total_price;
        $total_discount_price += $item_total_discount;
        $total_price += $item_price_with_discount;
        @endphp
        <div class="box-t2">
          <div class="img-ticket">
            <img src="{{ $item->ticket->image }}">
          </div>
          <table>
            <tr>
              <td><b>{{ $item->ticket->name_th }} @lang('messages.amount') {{ $item->total }} @lang('messages.unit') </b></td>
              {{-- <td><b> @money(session()->get('order')['totalTicket'] * session()->get('order')['price']) @lang('messages.baht')</b></td> --}}
              <td><b> @money( $item_total_price ) @lang('messages.baht')</b></td>
            </tr>
            <tr>
              <td>(@money($item->ticket->price) x {{ $item->total }})<br><br></td>
              <td></td>
            </tr>
            <tr>
              <td><b>@lang('messages.discount')</b></td>
              <td>-@money($item_total_discount) @lang('messages.baht')</td>
            </tr>
            <tr>
              <td><b>@lang('messages.total_price')</b></td>
              <td>@money($item_price_with_discount) @lang('messages.baht')</td>
            </tr>
          </table>

        </div>
        <br>
        <hr>
        @endforeach
        <br>


        <table class="table-color">
          <tr>
            <td style="width: 58%;"></td>
            <td><b>@lang('messages.total_net')</b></td>
            <td><b>@money($total_price)</b> @lang('messages.baht')</td>
          </tr>
        </table>

        @if (session()->has('receipt_data'))
        @if(session()->get('receipt_data')['receipt_request'] == 1)
        <div class="box-colorbox">
          <table class="table2">
            <tr>
              <td><b class="text-color">@lang('messages.info_issuing_receipt') </b></td>
            </tr>
            <tr>
              <td><b>{{session()->get('receipt_data')['receipt_name']}}</b></td>
              <td><b>@lang('messages.phone') :</b></td>
              <td>{{session()->get('receipt_data')['receipt_phone']}}</td>
            </tr>
            <tr>
              <td style="max-width: 450px;display: inline-block;">
                {{session()->get('receipt_data')['receipt_address']}}
                {{$addresses['district']->name_th}}
                {{$addresses['amphur']->name_th}}
                {{$addresses['province']->name_th}}

                {{ session()->get('receipt_data')['receipt_postal_code']}}
              </td>
              <td></td>
              <td></td>
            </tr>
          </table>
        </div>
        @if(!is_null(session()->get('receipt_data')['deli_receipt_name']))
        <div class="box-colorbox">
          <table class="table2">
            <tr>
              <td><b class="text-color">@lang('messages.info_delivery_receipt')</b></td>
            </tr>
            <tr>
              <td><b>{{session()->get('receipt_data')['deli_receipt_name']}}</b></td>
              <td><b>@lang('messages.phone') :</b></td>
              <td>{{session()->get('receipt_data')['deli_receipt_phone']}}</td>
            </tr>
            <tr>
              <td style="max-width: 450px;display: inline-block;">
                {{session()->get('receipt_data')['deli_receipt_address']}}
                {{$addresses['district2']->name_th}}
                {{$addresses['amphur2']->name_th}}
                {{$addresses['province2']->name_th}}

                {{ session()->get('receipt_data')['deli_receipt_zipcode']}}
              </td>
              <td></td>
              <td></td>
            </tr>
          </table>
        </div>
        @endif
        @endif
        @endif
      </div>

      <div class="box-btn">
        <a href="{{ route('frontend.checkout.index') }}" class="btn2 btn-back" style="width: 200px;">
          <i></i> {{ __('messages.sm_back') }}
        </a>

        @php
        if ($payment_type->id == 5) :
        $message = "messages.btn_confirm_order";
        else :
        $message = "messages.btn_confirm";
        endif;
        @endphp
        <a href="#" class="btn2" style="width: 200px;" onclick="event.preventDefault();document.getElementById('confirm-form').submit();">
          <i></i> {{ __("$message") }}
        </a>

        <form id="confirm-form" method="POST" action="{{ route('frontend.checkout.update', session()->get('cart_session')) }}">
          @method('PUT')
          @csrf
          <input type="hidden" name="confirm" value="1">
          @if(session()->has('receipt_data'))
          <input type="hidden" name="receipt_request" value="{{ session()->get('receipt_data')['receipt_request'] }}">
          <input type="hidden" name="receipt_type" value="{{ session()->get('receipt_data')['receipt_type'] }}">
          @endif

          @if($payment_type->id != 5 && $payment_type->id != 4)
          <input type="hidden" name="token" value="{{session()->get('payment_info')['payment_getway_token']}}">
          @endif
          <input type="hidden" name="payment_type" value="{{ $payment_type->id }}">
        </form>
      </div>
    </div>
  </div>
</div>

<div class="box-pouup" id="purchasing"></div>
<div class="box-shadow"></div>

@endsection

@section('pageScript')
<script src="https://www.paypal.com/sdk/js?client-id=ATu-OGVjlQehW9CD0WgoVPHDj4Znltdgi2sgDRqcSEpVy830VDrz1RBVjhY0_D5CFcAVvgwXW6z8sylf&currency=THB&disable-funding=card">
  // Replace YOUR_SB_CLIENT_ID with your sandbox client ID
</script>

<script src="{{ asset('frontend/js/total.js') }}" type="text/javascript"></script>
{{-- @if($payment_type->id == 4)
<script>
    $('#paypal-button-container').html('');
              paypal.Buttons({
                createOrder: function(data, actions) {
                  return actions.order.create({
                    purchase_units: [{
                      invoice_id: {{$order->id}},
amount: {
currency_code: "THB",
value: {{ $order->amount }}
},
description: 'ETICKET-{{$order->id}}',
return_url: '{{ route('frontend.thankyou') }}'
}]
})
},
onApprove: function (data, actions) {
return actions.order.capture().then(function (details) {
// console.log('Transaction completed by ' + details.payer.name.given_name);
// console.log(details);
$('.btn2').hide();
$('#purchasing').addClass('open');
document.getElementById('confirm-form').submit();
})
}
}).render('#paypal-button-container');
$('#paypal-selected').addClass('open');
</script>
@endif --}}
<script>
  $('.btn2').click(function() {
    $('.btn2').hide();
    $('#purchasing').addClass('open');
  })
</script>
@endsection