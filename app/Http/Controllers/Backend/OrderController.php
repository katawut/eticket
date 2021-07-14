<?php

namespace App\Http\Controllers\Backend;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Controllers\MailController as OrderMail;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Exporting
use App\Exports\OrderExport;
use App\Exports\OrderExport2;
use Maatwebsite\Excel\Facades\Excel;

// Models
use App\Model\Order;
use App\Model\Stocks;
use App\Model\Ticket;

class OrderController extends Controller {
    const MODULE = 'order';

    public function index() {
        $this->authorize(mapPermission(self::MODULE));

        $total_unit_price = 0;
        $total_amount = 0;
        $total_discount = 0;
        $total_ticket = 0;

        $orders = Order::with(['user', 'orderItem', 'paymentType'])->orderBy('created_at', 'desc')->limit(50)->get();

        $result = $this->_order_list($orders);

        $options = $this->_filter_options();

        $compacts = ['result', 'options'];
        return view('backend.order.index', compact($compacts));
        // return view('backend.order.export2', compact('orders'));
    }

    public function search(Request $request) {
        $filters = [
            'status' => 'status',
            'payment_type_id' => 'payment_type_id',
            'receipt_requested' => 'receipt_requested'
        ];

        if (!is_null($request->start_date)) :
            $start_date = str_replace('/', '-', $request->start_date);
            $end_date = str_replace('/', '-', $request->end_date);
            $start = Carbon::parse($start_date)->toDateString();
            $end = Carbon::parse($end_date)->toDateString();

            $orders = Order::with(['user', 'orderItem', 'paymentType'])
                ->where(DB::raw('DATE(created_at)'), '>=', $start)
                ->where(DB::raw('DATE(created_at)'), '<=', $end)
                ->where(function ($query) use ($request, $filters) {
                    foreach ($filters as $column => $key) {
                        $value = Arr::get($request, $key);
                        if ($value != 99) {
                            $query->where($column, $value);
                        }
                    }
                })
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();
        else :
            $orders = Order::with(['user', 'orderItem', 'paymentType'])
                ->orderBy('created_at', 'desc')
                ->where(function ($query) use ($request, $filters) {
                    foreach ($filters as $column => $key) {
                        $value = Arr::get($request, $key);
                        if ($value != 99) {
                            $query->where($column, $value);
                        }
                    }
                })
                ->limit(50)
                ->get();
        endif;

        $result = $this->_order_list($orders);

        $options = $this->_filter_options();

        $filter = [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'payment_type_id' => $request->payment_type_id,
            'status' => $request->status,
            'receipt_requested' => $request->receipt_requested
        ];

        return view('backend.order.index', compact('result', 'filter', 'options'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        return ("Show");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order) {
        $this->authorize(mapPermission(self::MODULE));

        $receipt_data = [];

        if ($order->receipt_requested == 1) :
            $user_info = DB::table('user_infos')->where('user_id', $order->user_id)->first();
            $receipt_data['user_info'] = $user_info;

            $province_data = DB::table('provinces')->where('id', $user_info->receipt_province)->first();
            $amphure_data = DB::table('amphures')->where('id', $user_info->receipt_amphur)->first();
            $district_data = DB::table('districts')->where('id', $user_info->receipt_district)->first();
            $province_data2 = null;
            $amphure_data2 = null;
            $district_data2 = null;

            if (!is_null($user_info->deli_receipt_name)) :
                $province_data2 = DB::table('provinces')->where('id', $user_info->deli_receipt_province)->first();
                $amphure_data2 = DB::table('amphures')->where('id', $user_info->deli_receipt_amphur)->first();
                $district_data2 = DB::table('districts')->where('id', $user_info->deli_receipt_district)->first();
            endif;

            $receipt_data['addresses'] = [
                'province' => $province_data,
                'amphur' => $amphure_data,
                'district' => $district_data,
                'province2' => $province_data2,
                'amphur2' => $amphure_data2,
                'district2' => $district_data2
            ];
        endif;

        $options = $this->_filter_options();

        return view('backend.order.update', compact('order', 'receipt_data', 'options'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order) {
        $this->authorize(mapPermission(self::MODULE));

        $order->update($this->validateRequest());
        $order->storeImage();

        if ($order->status && $order->status == 2) :
            $mail = new OrderMail;
            $mail->sendMailOrder($order->id);
        endif;

        return redirect(route('backend.order.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $order = Order::find($id);

        if (!is_null($order)) :
            $total_ticket = $order->total;

            $ticket_id = $order->orderItem()->first()->ticket_id;

            // ลบ order item
            $order->delete();

            // คืนตั๋ว
            $stock = Stocks::where('tickets_id', $ticket_id)->first();
            $stock->quantity = $stock->quantity + $total_ticket;
            $stock->save();
        endif;

        return redirect(route('backend.order.index'));
    }

    // ------------------------------------------------------
    // Export
    public function export(Request $request) {
        $timestamp = date('dmY');
        return Excel::download(new OrderExport($request->filter), "{$timestamp}-all-tickets.xlsx");
    }

    public function export2(Request $request) {
        $timestamp = date('dmY');
        return Excel::download(new OrderExport2($request->filter), "{$timestamp}-separate-tickets.xlsx");
    }

    // ------------------------------------------------------
    // Private
    private function _order_list($orders) {
        $total_unit_price = 0;
        $total_amount = 0;
        $total_discount = 0;
        $total_ticket = 0;

        if ($orders->count() != 0) :
            foreach ($orders as $key => $item) :
                $updated_at = Carbon::parse(date('Y-m-d H:i:s', strtotime("$item->updated_at, + 543 years")))
                ->locale('th_TH')->isoFormat('D MMM g HH:mm:ss');

                $created_at = Carbon::parse(date('Y-m-d H:i:s', strtotime("$item->created_at, + 543 years")))
                ->locale('th_TH')->isoFormat('D MMM g HH:mm:ss');

                $paid_at = is_null($item->paid_at) ? '-' : Carbon::parse(date('Y-m-d H:i:s', strtotime("$item->paid_at, + 543 years")))
                ->locale('th_TH')->isoFormat('( dd ) D MMM g HH:mm:ss');

                $order_status = $this->_order_status($item->created_at, $item->status);

                $receipt_type = ($item->receipt_request == 1 ? ($item->receipt_type == 1 ? 'บุคคลธรรมดา' : 'นิติบุคคล') : '-');

                $lists[$key]['count'] = $key + 1;
                $lists[$key]['order_id'] = $item->id;
                $lists[$key]['updated_at'] = $updated_at;
                $lists[$key]['created_at'] = $created_at;
                $lists[$key]['paid_at'] = $paid_at;
                $lists[$key]['payment_type'] = $item->paymentType->name_th;
                $lists[$key]['order_status_class'] = $order_status['class'];
                $lists[$key]['order_status_text'] = $order_status['text'];
                $lists[$key]['price'] = $item->unit_price;
                $lists[$key]['discount'] = $item->discount_price;
                $lists[$key]['amount'] = $item->amount;
                $lists[$key]['buyer'] = $item->user->name;
                $lists[$key]['receipt_request'] = $item->receipt_request == 1 ? 'ต้องการ' : 'ไม่ต้องการ';
                $lists[$key]['receipt_type'] = $receipt_type;
                $lists[$key]['updater'] = $item->update_name == null ? '-' : $item->update_name->first_name;
                $lists[$key]['note'] = $item->note == null ? '-' : $item->note;
                // $lists[$key][''] = $item->;

                // Summaries
                foreach ($item->orderItem as $order_item) :
                    $total_ticket += $order_item->amount;
                endforeach;
                $total_unit_price += $item->unit_price;
                $total_amount += $item->amount;
                $total_discount += $item->discount_price;
            endforeach;
        else :
            $lists = [];
            $total_unit_price = 0;
            $total_amount = 0;
            $total_discount = 0;
            $total_ticket = 0;
        endif;

        $result = [
            'lists' => $lists,
            'total_unit_price' => $total_unit_price,
            'total_amount' => $total_amount,
            'total_discount' => $total_discount,
            'total_ticket' => $total_ticket
        ];
        return $result;
    }

    private function _order_status($created_at, $status) {
        $date_diff = Carbon::parse($created_at)->diffInDays(\Carbon\Carbon::now());

        if ($date_diff >= 3 && $status == 1) :
            $class = 'danger';
            $text = 'ยังไม่ได้ชำระ<br>( เกินกำหนดชำระ )';
        else :
            switch ($status) :
                case 1 :
                    $class = '';
                    $text = 'ยังไม่ได้ชำระ';
                break;

                case 2 :
                    $class = 'success';
                    $text = 'ชำระสำเร็จ';
                break;

                case 3 :
                    $class = 'danger';
                    $text = 'ชำระไม่สำเร็จ';
                break;

                case 4 :
                    $class = '';
                    $text = 'รอการตรวจสอบ';
                break;

                case 5 :
                    $class = 'danger';
                    $text = 'ยกเลิกคำสั่งซื้อ';
                break;
            endswitch;
        endif;

        $result = ['class' => $class, 'text' => $text];
        return $result;
    }

    private function validateRequest() {
        $validatedData = request()->validate([
            "status" => '',
            "note" => ''
        ]);
        $validatedData['updated_by'] = Auth::id();
        if (request()->route()->getActionMethod() == 'store') :
            $validatedData['created_by'] = Auth::id();
        endif;
        return $validatedData;
    }

    private function _filter_options() {
        $options = [
            'payment_type' => [
                1 => 'ยังไม่ได้เลือกการชำระเงิน',
                2 => 'บัตรเครดิต',
                3 => 'อินเทอเน็ตแบงค์กิ้ง',
                4 => 'PayPal',
                5 => 'ชำระเงินผ่านธนาคาร'
            ],
            'order_status' => [
                1 => 'ยังไม่ได้ชำระ',
                2 => 'ชำระสำเร็จ',
                3 => 'ชำระไม่สำเร็จ',
                4 => 'รอการตรวจสอบ',
                5 => 'ยกเลิกคำสั่งซื้อ'
            ],
            'receipt' => [
                0 => 'ไม่ต้องการ',
                1 => 'ต้องการ'
            ]
        ];
        return $options;
    }
}
