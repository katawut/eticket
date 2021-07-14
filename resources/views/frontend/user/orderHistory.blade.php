@extends('layouts.layout')
    <link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/history.css') }}" />
    <style>
        .box-pagination li a {
            width: auto;
            color: #af1003;
        }
    </style>
@section('pageStyle')

@endSection

@section('content')

<div class="part-head">
  <div class="container">
    <div class="box-title">
      <h1 class="b-Title t-color-w">E-TICKET</h1>
      <h4 class="b-Title" style="color: #d6ae66;">{{ __('messages.menu_buy_ticket') }}</h4>
    </div>
  </div>
</div>

<div class="part-content-main">
    <div class="container">
        <h3 style="color: #8b0307;">{{ __('messages.order_history') }}</h3>

        <div class="box-paper">
            @foreach($orders as $order)
            <div class="List">
                <div class="b-List">
                    <div class="bTop">{{ __('messages.order_number') }} : {{ $order->id }}</div>
                    <div class="btext">
                        <div>{{ __('messages.order_date') }} : <br>
                            @php
                                $date = \Carbon\Carbon::parse($order->created_at);
                                if (app()->isLocale('th')) :
                                    echo $date->locale('th')->isoFormat('D MMMM GGGG');
                                else :
                                    echo $date->locale('en')->isoFormat('MMMM Do, GGGG');
                                endif;
                            @endphp
                         </div>
                        <div>{{ __('messages.amount') }} : <br>{{$order->total}}</div>
                        <div>{{ __('messages.total_price') }} : <br>{{$order->amount}} {{ __('messages.baht') }}</div>
                        <div>{{ __('messages.status') }} : <br>
                            <div class="Checkmark2" style="font-weight: bold; letter-spacing: 1px;">
                                @switch($order->status)
                                    @case(1)
                                        <span style="color: grey;">
                                            {{ app()->isLocale('th') ? 'รอการชำระ' : 'Waiting Payment' }}
                                        </span>
                                    @break

                                    @case(2)
                                        {{ app()->isLocale('th') ? 'ชำระเงินเรียบร้อยแล้ว' : 'Payment Completed' }}
                                    @break

                                    @case(3)
                                        <span style="color: red;">
                                            {{ app()->isLocale('th') ? 'ชำระไม่สำเร็จ' : 'Failed' }}
                                        </span>
                                    @break

                                    @case(4)
                                        <span style="color: dodgerblue;">
                                            {{ app()->isLocale('th') ? 'รอการตรวจสอบ' : 'Awaiting Approval' }}
                                        </span>
                                    @break

                                    @case(5)
                                        <span style="color: black;">
                                            {{ app()->isLocale('th') ? 'ถูกยกเลิก' : 'Cancelled' }}
                                        </span>
                                    @break

                                    @default
                                        {{ $order->status }}
                                @endswitch
                            </div>
                        </div>
                    </div>
                </div>

                <div class="b-btn">
                    @php
                        // Transfer Payment
                        if ($order->paymentType->id == 5) :
                            // Transfer Patment & Not Pay
                            if ($order->status == 1) :
                                $message = 'messages.btn_payment_proof';
                            else : // Transfer Patment & Pay
                                $message = "messages.btn_view_detail";
                            endif;
                        else : // Other Payment
                            $message = 'messages.btn_view_detail';
                        endif;
                    @endphp
                    <a href="{{route('frontend.orders.show', $order->id)}}" class="btn2">
                        <i></i> {{ __("$message") }}
                    </a>
                </div>
            </div>
            @endforeach

            <div class="box-pagination">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>

@endsection
