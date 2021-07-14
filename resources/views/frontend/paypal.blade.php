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
      <h4 class="b-Title" style="color: #d6ae66;">ซื้อบัตรเข้าชมพิพิธภัณฑ์</h4>
    </div>
  </div>
</div>

<div class="part-content-main">
  <div class="container" style="text-align: center;">
    <h3>คลิกเพื่อชำระเงินด้วย PayPal</h3>
    <div class="btn2 paypalButton" id="paypal-button-container"></div>
  </div>
</div>
<div class="box-pouup" id="purchasing">
</div>
<div class="box-shadow"></div>

@endsection

@section('pageScript')
<script src="https://www.paypal.com/sdk/js?client-id=AdSlnrn4vVBxadhCHn0oiJqzUS0ZjrstfIRxIl_2d97Axc4tENXMmDKmP-tdblb7_kXlCUe7paz0wCze&currency=THB&disable-funding=card">
  // Replace YOUR_SB_CLIENT_ID with your sandbox client ID
</script>
<script src="{{ asset('frontend/js/total.js') }}" type="text/javascript"></script>
<script>
  $('#paypal-button-container').html('');
  paypal.Buttons({
    locale: 'en_TH',
    style: {
      label: 'pay',
      size: 'large',
      shape: 'rect'
    },
    createOrder: function(data, actions) {
      return actions.order.create({
        purchase_units: [{
          invoice_id: {
            {
              $order - > id
            }
          },
          amount: {
            currency_code: "THB",
            value: {
              {
                $order - > amount
              }
            }
          },
          description: 'ETICKET-{{ $order->id }}',
          return_url: '{{ route('
          frontend.checkout.complete ', $order->id) }}'
        }]
      })
    },
    onApprove: function(data, actions) {
      return actions.order.capture().then(function(details) {
        // console.log('Transaction completed by ' + details.payer.name.given_name);
        // console.log(details);
        $('.btn2').hide();
        $('#purchasing').addClass('open');
        $(location).attr('href', '{{ url("/checkout/$order->id/complete") }}');

      })
    }
  }).render('#paypal-button-container');
  $('#paypal-selected').addClass('open');
</script>
<script>
  $('.btn2').click(function() {
    $('.btn2').hide();
    $('#purchasing').addClass('open');
  })
</script>
@endsection