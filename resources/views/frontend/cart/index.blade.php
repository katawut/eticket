@extends('layouts.layout')

@section('pageStyle')
<!-- page style -->
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/cart.css') }}" />

<style>
    .alert-more5 {
        width: 300px;
        color: #d41f15;
        font-size: 13px;
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
        <div class="box-text">
            <h4 class="b-Title t-color">@lang('messages.basket')</h4>
            <div class="box-scroll">
                @if(empty($tickets))
                <div style="text-align: center">@lang('messages.basket_noproduct')</div>
                @else
                <table class="ta1">
                    <caption></caption>
                    <thead>
                        <tr>
                            <th>@lang('messages.products')</th>
                            <th>@lang('messages.price_unit')</th>
                            <th>@lang('messages.discount_unit')</th>
                            <th>@lang('messages.amount')</th>
                            <th>@lang('messages.price_total')</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                        @php
                            $total_price = 0;
                            $total_discount = 0;
                        @endphp

                        @foreach($tickets as $item)
                        <tr id="item-{{ $item->id }}">
                            <td>
                                <img src="{{ $item->image }}" class="img-ticket">
                                {{ (app()->isLocale('th') ? $item->title_th : $item->title_en) }}
                                @if ($item->id == 1)
                                    <span class="alert-more5">@lang('messages.advice_more5')</span>
                                @endif
                            </td>
                            <td class="unit-price" data-unit-price="{{ $item->price }}">@money($item->price)</td>
                            <td class="unit-discount" data-unit-discount="{{ $item->discount_price }}">
                                @money($item->discount_price)</td>
                            <td>
                                <div id="img-load"></div>
                                <div class="btn-group">
                                    @php
                                        foreach ($cart_items as $cart_item) :
                                            if ($cart_item->id == $item->id) :
                                                $item_total = $cart_item->total;
                                            endif;
                                        endforeach;
                                    @endphp
                                    <button type="button" class="btn btn-delete {{ $item_total > 1 ? '' : 'disabled' }}">-</button>
                                    <input data-item-id="{{ $item->id }}" type="text" class="btn selected-item" value="{{ $item_total }}" readonly>
                                    <button type="button" class="btn btn-plus {{ $item_total <= 20 ? '' : 'disabled' }}">+</button>
                                </div>
                                {{-- <span class="remind">@lang('messages.advice_more5')</span> --}}
                                {{-- <span class="remind2" style="display:none;">@lang('messages.not_enough_card')</span> --}}
                                <span style="font-size:13px;color:green;">
                                    @lang('messages.remain') : {{ $item->stocks->quantity }}
                                </span>
                            </td>

                            @php
                                $unit_amount = $item->price * $item_total;
                                $total_discount += $item->discount_price * $item_total;
                                $total_price += $unit_amount;
                            @endphp

                            <td class="unit-amount" data-unit-amount="@money($unit_amount)">@money($unit_amount)</td>
                            <td>
                                <button type="button" class="button-delete"
                                    onclick="event.preventDefault();document.getElementById('delete-item-{{ $item->id }}-form').submit();">
                                    <img src="{{ asset('frontend/images/bin.svg') }}">
                                </button>

                                <form id="delete-item-{{ $item->id }}-form" action="{{ route('frontend.cart.destroy', $item->id) }}" method="POST"
                                    style="display: none;">
                                    @csrf
                                    @method('delete')
                                    <input type="hidden" name="uid" value="{{ session()->get('cart_session') }}">
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
            <hr>

            <br>
            @if(empty($tickets))

            @else
            <h4 class="b-Title t-color" style="margin-bottom: 0px;">@lang('messages.total_products')</h4>
            <table>
                <tr style="border: 0px;">
                    <td colspan="2">
                        <table>
                            <tr>
                                @php
                                $total_items = 0;
                                foreach($cart_items as $cart_item) {
                                $total_items += $cart_item->total;
                                }
                                foreach($tickets as $ticket) {

                                }
                                @endphp
                                <td>@lang('messages.all_total_products') <b>({{ $total_items }}
                                        @lang('messages.unit'))</b></td>
                                <td>@money($total_price) @lang('messages.baht')</td>
                            </tr>
                            <tr>
                                <td>@lang('messages.total_discount')</td>
                                <td>-@money($total_discount) @lang('messages.baht')</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr class="b2">
                    <td style="text-align: right;">@lang('messages.total_net')</td>
                    <td>@money($total_price - $total_discount) @lang('messages.baht')</td>
                </tr>
            </table>

            <div class="box-button">
                <div class="box-R">
                    <button type="button" id="CPayment" class="Button"
                        onclick="event.preventDefault();document.getElementById('checkout-form').submit();">
                        <i></i> {{ __('messages.btn_process_checkout') }}
                    </button>

                    <form id="checkout-form" action="{{ route('frontend.checkout.index') }}" method="POST"
                        style="display: none;">
                        @csrf
                        @method('GET')
                    </form>

                    <button type="button" class="Button btn-back"
                        onClick="window.location.replace('{{ route('frontend.index') }}')">
                        <i></i> {{ __('messages.btn_continue_shopping') }}
                    </button>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

{{-- MODAL : TICKET STATUS --}}
<div class="box-pouup {{ session()->has('ticketStatus') ? 'open' : '' }}" id="pouup-noticket">
    <div class="b-head">@lang('messages.cannot_do_transaction')</div>
    <div class="b-text">
        @lang('messages.not_enough_card')
        <div class="b-button" style="justify-content: center">
            <a href="javascript:void(0)" onclick="location.reload()">@lang('messages.accept')</a>
        </div>
    </div>
</div>
<div class="box-shadow"></div>
@endsection


@section('pageScript')
<script>
    $(document).ready(function() {
        $(".btn-group input.btn").each(function() {
            if(parseInt($(this).val()) >= 5) {
                $(this).closest(".btn-group").find("+.remind").show();
            } else {
                $(this).closest(".btn-group").find("+.remind").hide();
            }

            if(parseInt($(this).val()) >= 20) {
                $(this).closest(".btn-group").find(".btn-plus").addClass('disabled');
            }
        });

        $('.btn-group .btn-plus').click(function() {
            let n1 = $(this).closest(".btn-group").find("input").val();
            $(this).closest(".btn-group").find(".btn-delete").removeClass('disabled');

            if(parseInt(n1) == 19) {
                $(this).closest(".btn-group").find(".btn-plus").addClass('disabled');
                $(this).closest(".btn-group").find("input").val(20);
            } else {
                $(this).closest(".btn-group").find(".btn-plus").removeClass('disabled');
                $(this).closest(".btn-group").find("input").val(parseInt(n1)+1);
            }

            $('#img-load').show();

            $.ajax('{{ url('api/cart') }}', {
            type: 'PUT',
            data: {
                uid: "{{ session()->get('cart_session') }}",
                item_id: $(this).closest(".btn-group").find("input").data('itemId'),
                total: parseInt($(this).closest(".btn-group").find("input").val())
            },
            success: function(data, status, xhr) {
                if(data.status === 'noticket') {
                $('#img-load').hide();
                $('#pouup-noticket').addClass('open');
                } else {
                location.reload();
                }
            },
            error: function (jqXhr, textStatus, errorMessage) {
                console.log('Error' + errorMessage);
            }
            })
        });

        $('.btn-group .btn-delete').click(function() {
            let n2 = $(this).closest(".btn-group").find("input").val();
            if(parseInt(n2) == 2){
                $(this).closest(".btn-group").find(".btn-delete").addClass('disabled');
            }

            if(parseInt(n2) > 1){
                $(this).closest(".btn-group").find("input").val(parseInt(n2)-1);
            }

            if(parseInt(n2) <= 20){
                $(this).closest(".btn-group").find(".btn-plus").removeClass('disabled');
            }

            $('#img-load').show();

            $.ajax('{{ url('api/cart') }}', {
                type: 'PUT',
                data: {
                    uid: "{{ session()->get('cart_session') }}",
                    item_id: $(this).closest(".btn-group").find("input").data('itemId'),
                    total: parseInt($(this).closest(".btn-group").find("input").val())
                },
                success: function(data, status, xhr) {
                    if(data.status === 'noticket') {
                        $('#img-load').hide();
                        $('#pouup-noticket').addClass('open');
                    } else {
                        location.reload();
                    }
                },
                error: function (jqXhr, textStatus, errorMessage) {
                    console.log('Error' + errorMessage);
                }
            })
        });

});


</script>
@endSection
