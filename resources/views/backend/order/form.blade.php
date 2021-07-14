<div class="row mb-3">
    <div class="col-md-6">
        <label class='control-label mb-2' for="user_id">หมายเลขออเดอร์ : </label>
        <input type="text" class="form-control" id="user_id" name="user_id" value="{{ $order->id}}" disabled>
    </div>

    <div class="col-md-6">
        <label class='control-label mb-2' for="purchase_name">ผู้สั่งซื้อ : </label>
        <input type="text" class="form-control" id="purchase_name" name="purchase_name"
        value="{{ $order->user->first_name}} {{$order->user->last_name}}" disabled>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="control-label mb-2" for="quantity">จำนวนที่สั่งซื้อ : </label>
        <input type="text" class="form-control" id="quantity" name="quantity"
            value="{{ $order->orderItem->sum('amount') }} ใบ" disabled>
    </div>

    <div class="col-md-6">
        <label class="control-label mb-2" for="price">จำนวนเงิน ( บาท ): </label>
        <input type="text" class="form-control" id="price" name="price"
            value="@money( $order->unit_price )" disabled>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="control-label mb-2" for="discount">ส่วนลด ( บาท ): </label>
        <input type="text" class="form-control" id="discount" name="discount" value="{{  $order->discount_price }}" disabled>
    </div>

    <div class="col-md-6">
        <label class="control-label mb-2" for="amount">จำนวนเงินรวมทั้งหมด ( บาท ): </label>
        <input type="text" class="form-control" id="amount" name="amount" value="@money( $order->amount )" disabled>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="control-label mb-2" for="purchased_date">วันที่สั่งซื้อ : </label>
        <input type="text" class="form-control" id="purchased_date" name="purchased_date" value="{{ \Carbon\Carbon::parse( $order->created_at )->locale('th_TH')->day }} {{ \Carbon\Carbon::parse( $order->created_at )->locale('th_TH')->monthName }} {{ (\Carbon\Carbon::parse( $order->created_at )->locale('th_TH')->year + 543) }}" disabled>
    </div>

    <div class="col-md-6">
        <label class="control-label mb-2" for="payment_type">ประเภทการชำระเงิน : </label>
        <input type="text" class="form-control" id="payment_type" name="payment_type" value="{{  $order->paymentType->name_th }}" disabled>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="control-label mb-2" for="status">สถานะการชำระเงิน : </label>
        <select id="status" name="status" class="form-control">
            @foreach($options['order_status'] as $key => $item)
                <option value="{{ $loop->iteration}}" {{ $loop->iteration == $order->status ? 'selected' : ''}}>
                    {{ $item }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="control-label mb-2" for="amount">จำนวนเงินรวม : </label>
        <input type="text" class="form-control" id="amount" name="amount" value="@money( $order->amount )" disabled>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        @if($order->payment_type_id == 5)
            <label class="control-label mb-2">
                <span class='text-danger'>*</span>หลักฐานการโอนชำระ :
            </label>

            <div class="icon">
                <div class="uploaded_image">
                    <a href="{{$order->image}}" target="_blank">
                        <img id="preview_image" src="{{ $order->image ?? '' }}" class="img-icon" data-toggle='popover' data-html='true'>
                    </a>
                </div>
            </div>

            <p>คลิกที่ภาพเพื่อดูรูป</p>

            <div class="custom-file">
                <input type="file" class="image" id="image" name="image"
                    {{request()->route()->getActionMethod() == 'create' ? 'required' : ''}}>
                <label class="custom-file-label" for="image">อัพโหลดภาพหลักฐานการโอนชำระ</label>
            </div>

            {{ $errors->first('image') }}
        @endif
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="control-label mb-2" for="note">บันทึก: </label>
        <textarea type="text" class="form-control" id="note" name="note" rows="4">{{ $order->note }}</textarea>
    </div>
</div>

@if (!empty($receipt_data))
    <div class="row">
        <div class="col-md-6">
            <label class="control-label mb-2" for="discount_price">ที่อยู่สำหรับออกใบเสร็จ : </label>
            <p>ชื่อ: {{ $receipt_data['user_info']->receipt_name }}</p>
            <p>
                ที่อยู่:
                {{ $receipt_data['user_info']->receipt_address }}
                {{ $receipt_data['addresses']['amphur']->name_th }}
                {{ $receipt_data['addresses']['district']->name_th }}
                {{ $receipt_data['addresses']['province']->name_th }}
                {{ $receipt_data['user_info']->receipt_postal_code }}
            </p>
            <p>เบอร์โทร: {{ $receipt_data['user_info']->receipt_phone }}</p>
        </div>

        <div class="col-md-6">
            <label class="control-label mb-2" for="discount_price">ที่อยู่สำหรับจัดส่งใบเสร็จ : </label>
            @if (!is_null($receipt_data['user_info']->deli_receipt_name))
                <p>ชื่อ: {{ $receipt_data['user_info']->deli_receipt_name }}</p>
                <p>
                    ที่อยู่:
                    {{ $receipt_data['user_info']->deli_receipt_address }}
                    {{ $receipt_data['addresses']['amphur2']->name_th }}
                    {{ $receipt_data['addresses']['district2']->name_th }}
                    {{ $receipt_data['addresses']['province2']->name_th }}
                    {{ $receipt_data['user_info']->deli_receipt_postal_code }}
                </p>
                <p>เบอร์โทร: {{ $receipt_data['user_info']->deli_receipt_phone }}</p>
            @else
                <p>ชื่อ: {{ $receipt_data['user_info']->receipt_name }}</p>
                <p>
                    ที่อยู่:
                    {{ $receipt_data['user_info']->receipt_address }}
                    {{ $receipt_data['addresses']['amphur']->name_th }}
                    {{ $receipt_data['addresses']['district']->name_th }}
                    {{ $receipt_data['addresses']['province']->name_th }}
                    {{ $receipt_data['user_info']->receipt_postal_code }}
                </p>
                <p>เบอร์โทร: {{ $receipt_data['user_info']->receipt_phone }}</p>
            @endif
        </div>
    </div>
@endif

<hr>

<div class="form-group row mt-2">
    <div class="col-12 text-left">
        <button type="submit" class="btn btn-white">
            <i class="fa fa-save text-success"></i> บันทึกข้อมูล
        </button>

        <button type="button" class="btn btn-white back" value="{{  route('backend.order.index') }}">
            <i class="fas fa-reply text-danger"></i> ย้อนกลับ
        </button>
    </div>
</div>


@push('after-scripts')
<script>
    // Dropzone.autoDiscover = false;
    $(function() {
        $('#form-validate').validate({
            errorPlacement: function(error, element) {
                if ($(element).attr('id') == 'image') {
                    error.insertAfter($(element).parent());
                    $(element).siblings('.custom-file-label').toggleClass('error-border');
                } else {
                    error.insertAfter(element);
                }
            }
        });

        $('#image').on('change', function() {
            readURL(this, "preview_image");
        });
    });
</script>
@endpush
