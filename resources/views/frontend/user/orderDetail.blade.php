@extends('layouts.layout')

@section('pageStyle')
<!-- page style -->
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/successful.css') }}" />
<style>
    .part-content-main .Button.btn-back {
        background-image: url({{asset('frontend/images/back.svg')
    }
    }

    );
    background-size: 16px;
    }

    .part-content-main .Button {
        background: #af1003 url(../images/Icon-Payment.png) no-repeat 30px 7px;
        display: inline-block;
        float: right;
        padding: 3px 34px 3px 62px;
        color: #fff;
        transition: .3s;
        box-shadow: 2px 3px 0px 0px rgb(80 7 1);
        font-family: sans-serif, Tahoma;
        font-size: 17px;
        border: 0px;
        outline: none;
        margin: 20px 8px;
        cursor: pointer;
        height: 40px;
    }
</style>
@endSection

@section('content')
<div class="part-content-main">
    <div class="box-paper" style="max-width: none;">
        <div class="box-body" style="max-width: 1000px; margin: auto;">
            <div class="box-top">
                <h3>{{ __('messages.order_detail') }}</h3>
            </div>
            <hr>
            <br>

            <h3 class="text-color" style="margin-left: 15px;">{{ __('messages.order_information') }}</h3>
            <table>
                <tr>
                    <td><b>{{ __('messages.order_number') }} : </b></td>
                    <td>{{ $order->id }}</td>
                </tr>
                <tr>
                    <td><b>{{ __('messages.order_date') }} :</b></td>
                    <td>
                        @php
                        $date = \Carbon\Carbon::parse($order->created_at);
                        if (app()->isLocale('th')) :
                        echo $date->locale('th')->isoFormat('D MMMM GGGG');
                        else :
                        echo $date->locale('en')->isoFormat('MMMM Do, GGGG');
                        endif;
                        @endphp
                    </td>
                </tr>
                <tr>
                    <td><b>{{ __('messages.status') }} :</b></td>
                    <td>
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
                    </td>
                </tr>
                <tr>
                    <td><b>{{ __('messages.payment') }} :</b></td>
                    <td>{{ $order->payment_type_name}}</td>
                </tr>
            </table>

            @if($order->payment_type_id == 5)
            @if($order->slip == '')
            <table class="table-color">
                <tr>
                    <td><b>{{ __('messages.send_proof_payment') }}</b></td>
                    <td>
                        <form action="{{route('frontend.orders.update', $order->id)}}" enctype="multipart/form-data" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="file" class="image" id="image" name="image" required>
                            <button type="submit">{{ __('messages.btn_send') }}</button>
                        </form>
                    </td>
                </tr>
            </table>
            @endif
            @endif
            <br>
            <hr>
            @php
            $total_discount_price = 0;
            $total_price = 0;
            $total_price_with_discount = 0;
            $total_unit_price = 0;
            @endphp

            @foreach($order_items as $item)
            <div class="box-t2">
                @php
                $item_total_price = $item->ticket->price * $item->amount;
                $item_total_discount = $item->ticket->discount_price * $item->amount;
                $item_price_with_discount = $item_total_price - $item_total_discount;

                $total_unit_price += $item_total_price;
                $total_discount_price += $item_total_discount;
                $total_price += $item_price_with_discount;
                @endphp
                <div class="img-ticket">
                    <img src="{{ $item->ticket->image }}">
                    {{-- <img src="https://eticket.commarketing.co.th/frontend/images/ticket_Mouckup.png"> --}}
                </div>
                <table>
                    <tr>
                        <td><b>{{ app()->isLocale('th') ? $item->ticket->title_th : $item->ticket->title_en }}</b></td>
                        <td><b>@money($item_total_price) {{ __('messages.baht') }}</b></td>
                    </tr>
                    <tr>
                        <td>({{$item->ticket->price}} x {{$item->amount}} = @money($item_total_price) {{ __('messages.baht') }})<br><br></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><b>{{ __('messages.discount') }}</b></td>
                        <td>
                            -@money($item_total_discount)
                            {{ __('messages.baht') }}
                        </td>
                    </tr>
                    <tr>
                        <td><b>{{ __('messages.total_price') }}</b></td>
                        <td>@money($item_price_with_discount) {{ __('messages.baht') }}</td>
                    </tr>
                </table>
            </div>
            <hr>
            @endforeach
            <table class="table-color">
                <tr>
                    <td><b></b></td>
                    <td><b>{{ __('messages.net_price') }}</b></td>
                    <td><b>{{$order->amount}}</b> {{ __('messages.baht') }}</td>
                </tr>
            </table>

        </div>
        <div class="box-t2" style="justify-content: center;">
            <button type="button" class="Button btn-back" onClick="window.location.replace('{{ route('frontend.orders.index') }}')">
                <i></i> {{ __('messages.back') }}
            </button>
        </div>
    </div>

</div>


@endsection

@section('pageScript')
<script>
    @if(session() - > has('notice'))
    alert('{{ session()->get("notice") }}')
    @endif
</script>
@endsection