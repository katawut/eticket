@extends('backend.layouts.header')

@push('custom-style')
    <style>
        /* thead tr th { font-size: 14px; font-weight: bold; color: steelblue !important; letter-spacing: 2px; }
        tbody tr td { font-size: 14px; color: black !important; letter-spacing: 1px; } */

        .control-label { color: darkblue; font-size: 14px; font-weight: bold; letter-spacing: 1px; }
        .control-label-answer { color: black; font-size: 14px; font-weight: bold; letter-spacing: 1px; }
        .btn { font-size: 14px; font-weight: bold; letter-spacing: 2px; }
    </style>
@endpush

@section('title')
<h3>
    <i class="fa fa-lg fa-shopping-cart"></i> รายงานการสั่งซื้อ
</h3>
@endsection

@section('content')
{{-- Form --}}
<div class="row">
    <div class="col-12 col-xl-12">
        <div class="panel panel-inverse gray">
            <div class="panel-body mgbt">
                <form action="{{ route('backend.order.search') }}" method='post' data-parsley-validate="true">
                    @method('get')
                    @csrf
                    <div class="row">
                        <div class="form-group col-md-2">
                            <label class="control-label">วันที่สร้าง : จาก </label>
                            <input type="text" class="form-control datepicker-startdate" name="start_date"
                                value="<?php echo !empty($start_date) ? $start_date : '' ?>"
                                data-parsley-required-message="กรุณาระบุวันที่เริ่มต้น">
                        </div>

                        <div class="form-group col-md-2">
                            <label class="control-label">วันที่สร้าง : ถึง </label>
                            <input type="text" class="form-control datepicker-enddate" name="end_date"
                                value="<?php echo !empty($end_date) ? $end_date : '' ?>"
                                data-parsley-required-message="กรุณาระบุวันที่สิ้นสุด">
                        </div>

                        <div class="form-group col-md-2">
                            <label class="control-label" for="payment_type_id">ประเภทการชำระเงิน</label>
                            <select id="payment_type_id" name="payment_type_id" class="form-control">
                                <option value="99">กรุณาเลือก</option>
                                @foreach($options['payment_type'] as $key => $item)
                                    <option value="{{ $loop->iteration }}" {{ request()->payment_type_id == $loop->iteration ? 'selected' : ''}}>
                                        {{ $item }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-2">
                            <label class="control-label" for="status">สถานะการสั่งซื้อ </label>
                            <select id="status" name="status" class="form-control">
                                <option value="99">กรุณาเลือก</option>
                                @foreach($options['order_status'] as $key => $item)
                                    <option value="{{ $loop->iteration}}" {{ request()->status == $loop->iteration ? 'selected' : '' }}>
                                        {{ $item }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-2">
                            <label class="control-label" for="receipt_requested">ขอใบเสร็จ </label>
                            <select id="receipt_requested" name="receipt_requested" class="form-control">
                                <option value="99">กรุณาเลือก</option>
																@foreach ($options['receipt'] as $key => $item)
                                    <option value="{{ $key }}">
                                        {{ $item }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-12 mt-2">
                            <button type="submit" class="btn btn-white btn-search" id="search">
                                <i class='fas fa-search text-info'></i> ค้นหา
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Table --}}
<div class='return-list'>
    <div class="row">
        <div class="col-lg-12">
            <div class="panel ">
                <div class="panel-body">
                    {{-- Export Excel --}}
                    @if($result['lists'] != [])
                    <div class="export-container mb-3">
                        <div class="row py-2 mt-3 border-bottom">
                            <div class="col-md-12">
                                <label class="control-label-answer">Export Excel</label>
                            </div>

                            <div class="col-md-12">
                                <a href="{{ route('backend.order.export', ['filter'=> empty($filter) ? '' : $filter]) }}"
                                    class="btn btn-outline-success mr-2" target="_blank">แบบรวม
                                </a>

                                <a href="{{ route('backend.order.export2', ['filter' => empty($filter) ? '' : $filter]) }}"
                                    class="btn btn-outline-danger" targer="_blank">แบบแยกประเภทบัตร
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif

                    <table id="order-table-list" class="table table-striped table-bordered w-100 nowrap ">
                        <thead>
                            <tr class="text-center">
                                <th>จัดการ</th>
                                <th>วันที่อัพเดต</th>
                                <th>วันที่สั่งซื้อ</th>
                                <th>วันที่ชำระ</th>
                                <th>หมายเลขการสั่งซื้อ</th>
                                <th>ประเภทการชำระ</th>
                                <th>สถานะการสั่งซื้อ</th>
                                <th>ราคารวม</th>
                                <th>ส่วนลด</th>
                                <th>ราคาสุทธิ</th>
                                <th>ผู้สั่งซื้อ</th>
                                <th>ต้องการใบเสร็จ</th>
                                <th>ประเภทใบเสร็จ</th>
                                <th>ผู้แก้ไขล่าสุด</th>
                                <th>บันทึก</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($result['lists'] as $item)
                                <tr class='del text-center'>
                                    {{-- Action --}}
                                    <td>
                                        <div class=" dropright">
                                            <button class="btn btn-white dropdown-toggle" type="button"
                                                id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-bars "></i>
                                                <span class="sr-only">Toggle Dropdown</span>
                                            </button>

                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                @can('edit order')
                                                {{-- <div class="dropdown-divider"></div> --}}

                                                <a href="{{ route('backend.order.edit', ['order' => $item['order_id']]) }}" class="edit dropdown-item">
                                                    <i class="fa fa-pencil-alt text-warning"></i> แก้ไข
                                                </a>
                                                @endcan
                                            </div>
                                        </div>
                                    </td>

                                    <td>{{ $item['updated_at'] }}</td>
                                    <td>{{ $item['created_at'] }}</td>
                                    <td>{{ $item['paid_at'] }}</td>
                                    <td>{{ $item['order_id'] }}</td>
                                    <td>{{ $item['payment_type'] }}</td>
                                    <td class="font-weight-bold text-{{ $item['order_status_class'] }}">
                                        {{ $item['order_status_text'] }}
                                    </td>
                                    <td>{{ $item['price'] }}</td>
                                    <td>{{ $item['discount'] }}</td>
                                    <td>{{ $item['amount'] }}</td>
                                    <td>{{ $item['buyer'] }}</td>
                                    <td>{{ $item['receipt_request'] }}</td>
                                    <td>{{ $item['receipt_type'] }}</td>
                                    <td>{{ $item['updater'] }}</td>
                                    <td>{{ $item['note'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Summary --}}
                    <div class="summary-container">
                        <div id="orders-summary" class="d-flex align-items-end flex-column py-2 mt-2 border-top border-bottom">
                            <div class="w-25 d-flex justify-content-between">
                                <label class="control-label">จำนวนบัตรรวมทั้งหมด :</label>
                                <label class="control-label-answer">{{ $result['total_ticket'] }} ใบ</label>
                            </div>

                            <div class="w-25 d-flex justify-content-between">
                                <label class="control-label">ราคาสั่งซื้อรวมทั้งหมด :</label>
                                <label class="control-label-answer">@money($result['total_unit_price']) บาท</label>
                            </div>

                            <div class="w-25 d-flex justify-content-between">
                                <label class="control-label">ราคาส่วนลดทั้งหมด :</label>
                                <label class="control-label-answer">@money($result['total_discount']) บาท</label>
                            </div>

                            <div class="w-25 d-flex justify-content-between">
                                <label class="control-label">ราคาสุทธิรวมทั้งหมด :</label>
                                <label class="control-label-answer">@money($result['total_amount']) บาท</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- end panel-body -->
            </div>

            <!-- end panel -->
        </div>
        <!-- end col-12 -->
    </div>
    <!-- end row -->
</div>
@endsection

@push('after-scripts')
<script src="//cdnjs.cloudflare.com/ajax/libs/numeral.js/2.0.6/numeral.min.js"></script>

<script>
    // $('#data-table-list').after($('#orders-summary').html())
    $('.delete').on('click', function(){
      var orderId = $(this).data('orderid');
      console.log($('#delete-order-'+orderId));
      swal({
        title: 'คุณต้องการลบออเดอร์?',
        text: 'กดยืนยันเพื่อทำการลบ',
        icon: 'warning',
        buttons: {
          cancel: {
            text: 'ยกเลิก',
            value: null,
            visible: true,
            className: 'btn btn-default',
            closeModal: true,
          },
          confirm: {
            text: 'ยืนยัน',
            value: true,
            visible: true,
            className: 'btn btn-danger',
            closeModal: true
          }

        }
      }).then(function (value) {
        switch (value) {
          case true:
          $('#delete-order-'+orderId).submit();
          break;
        }

      });

    });
    data_table_list.columns.adjust().draw();
    // onclick="event.preventDefault();document.getElementById('delete-order').submit();"
</script>
@endpush
