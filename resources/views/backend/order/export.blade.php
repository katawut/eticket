<table id="data-table-list" class="table table-striped table-bordered w-100 nowrap ">
    <thead>
      <tr>
        <th class="text-center">วันที่อัพเดต</th>
        <th class="text-center">วันที่สั่งซื้อ</th>
        <th class="text-center">วันที่ชำระ</th>
        <th class="text-center">ผู้สั่งซื้อ</th>
        <th class="text-center">หมายเลขการสั่งซื้อ</th>
        <th class="text-center sum">ราคาจำหน่าย</th>
        <th class="text-center">ส่วนลด</th>
        <th class="text-center">ราคาสุทธิ</th>
        <th class="text-center">ประเภทการชำระ</th>
        <th class="text-center">สถานะการสั่งซื้อ</th>
        <th class="text-center">ข้อมูลใบเสร็จ</th>
        <th class="text-center">ผู้แก้ไขล่าสุด</th>
        <th class="text-center">บันทึก</th>
      </tr>
    </thead>

    <tbody>
      @if(!empty($orders))
        @php
            $total_unit_price = 0;
            $total_amount = 0;
            $total_discount = 0;
            $total_ticket = 0;
        @endphp

        @foreach($orders as $order)
          <tr class='del'>
            <td class='text-center'>{{ date('d-m-Y h:i:s', strtotime($order->updated_at)) }}</td>
            <td class='text-center'>{{ date('d-m-Y h:i:s', strtotime($order->created_at)) }}</td>
            <td class='text-center'>{{ is_null($order->paid_at) ? '-' : date('d-m-Y H:i:s', strtotime($order->paid_at)) }}</td>
            <td class='text-center'>{{ $order->user->first_name }}</td>
            <td class='text-center'>{{ $order->id }}</td>
            <td class='text-center'>{{ $order->unit_price }}</td>
            <td class='text-center'>{{ $order->discount_price }}</td>
            <td class='text-center'>{{ $order->amount }}</td>
            <td class='text-center'>{{ $order->paymentType->name_th }}</td>
            <td class='text-center'>
              @switch($order->status)
                @case(1)
                  ยังไม่ได้ชำระ
                  @break
                @case(2)
                  <span style="color:green">ชำระสำเร็จ</span>
                  @break
                @case(3)
                  ชำระไม่สำเร็จ
                  @break
                @case(4)
                  รอการตรวจสอบ
                  @break
                @case(5)
                  ยกเลิก
                  @break
              @endswitch
            </td>
            <td class='text-center'>
              @if($order->receipt_requested == 1)
                {{ $order->receipt_data['user_info']->receipt_name }}, {{ $order->receipt_data['user_info']->receipt_address }}
                {{ $order->receipt_data['addresses']['amphur']->name_th }}
                {{ $order->receipt_data['addresses']['district']->name_th }}
                {{ $order->receipt_data['addresses']['province']->name_th }}
                {{ $order->receipt_data['user_info']->receipt_postal_code }},
                {{ $order->receipt_data['user_info']->receipt_phone }}
              @else
                -
              @endif
            </td>
            <td class='text-center'>{{ ($order->update_name == null ? '-' : $order->update_name->first_name ) }}</td>
            <td class='text-center'>{{ ($order->note == null ? '-' : $order->note ) }}</td>
          </tr>

            @php
                foreach ($order->orderItem as $ordItem) {
                    $total_ticket += $ordItem->amount;
                }

                $total_unit_price += $order->unit_price;
                $total_amount += $order->amount;
                $total_discount += $order->discount_price;
            @endphp
        @endforeach
        @endif
    </tbody>

    <tfoot>
        <tr style="font-weight: bold;">
        <th>รวมทั้งสิ้น</th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th>{{ $total_ticket }} ใบ</th>
        <th>{{ $total_unit_price }}</th>
        <th>{{ $total_discount }}</th>
        <th>{{ $total_amount }}</th>
        <th></th>
        <th></th>
        <th></th>
      </tr>
    </tfoot>

  </table>
