@extends('backend.layouts.header', ['script' => ['editor'   => true, 'dropzone' => true]],[ 'css' => ['dropzone'   => true]])

@push('custom-style')
    <style>
        h5 { letter-spacing: 2px; }
        input[type="text"]:disabled { background-color: #fff; color: #090910; }

        .control-label { color: darkblue; font-size: 14px; font-weight: bold; letter-spacing: 1px; }
        .btn { font-size: 14px; font-weight: bold; letter-spacing: 2px; }
    </style>
@endpush

@section('title')
	<i class="fa fa-lg fa-shopping-cart"></i> แก้ไขรายการคำสั่งซื้อ
@endsection

@section('content')
<div class="row">
  <div class="col-lg-12">
    <div class="panel " data-sortable-id="form-validation-1">
      <div class="panel-heading panel-black">
        <h5 class="text-white">ข้อมูลรายการคำสั่งซื้อ</h5>
      </div>

      <div class="panel-body">
        <form class="form-horizontal" id="form-validate" name="demo-form" enctype="multipart/form-data"
        action="{{ route('backend.order.update', ['order' => $order->id] ) }}"  method='post'>
            @method('patch')
            @csrf
            @include('backend.order.form')
          </form>
        </div>
        <!-- end panel-body -->
      </div>
      <!-- end panel -->
    </div>
    <!-- end col-6 -->
  </div>
  <!-- end row -->

@endsection





