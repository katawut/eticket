<?php

namespace App\Exports;

use App\Model\Order;
use Illuminate\Support\Facades\DB;
// use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class OrderExport implements FromView
{
    /**
     * @return \Illuminate\Support\Collection
     */
    // public function collection()
    // {
    //     return Order::with(['user', 'orderItem', 'paymentType'])->get();
    // }
    public function __construct($filter)
    {
        $this->filter = $filter;
    }

    public function view(): View
    {
        if (is_null($this->filter)) {
            $orders = Order::with(['user', 'orderItem', 'paymentType'])->get();

            foreach ($orders as $order) {
                $receipt_data = [];

                if ($order->receipt_requested == 1) {
                    $user_info = DB::table('user_infos')->where('user_id', $order->user_id)->first();
                    $receipt_data['user_info'] = $user_info;

                    $province_data = DB::table('provinces')->where('id', $user_info->receipt_province)->first();
                    $amphure_data = DB::table('amphures')->where('id', $user_info->receipt_amphur)->first();
                    $district_data = DB::table('districts')->where('id', $user_info->receipt_district)->first();

                    $receipt_data['addresses'] = [
                        'province' => $province_data,
                        'amphur' => $amphure_data,
                        'district' => $district_data
                    ];
                }

                $order->receipt_data = $receipt_data;
            }
        } else {

            $request = $this->filter;

            $filters = [
                'status' => 'status',
                'payment_type_id' => 'payment_type_id',
                'receipt_requested' => 'receipt_requested'
            ];


            if (!empty($request['start_date'])) {
                $start_date = str_replace('/', '-', $request['start_date']);
                $end_date = str_replace('/', '-', $request['end_date']);
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
            } else {
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
            }

            foreach ($orders as $order) {
                $receipt_data = [];

                if ($order->receipt_requested == 1) {
                    $user_info = DB::table('user_infos')->where('user_id', $order->user_id)->first();
                    $receipt_data['user_info'] = $user_info;

                    $province_data = DB::table('provinces')->where('id', $user_info->receipt_province)->first();
                    $amphure_data = DB::table('amphures')->where('id', $user_info->receipt_amphur)->first();
                    $district_data = DB::table('districts')->where('id', $user_info->receipt_district)->first();

                    $receipt_data['addresses'] = [
                        'province' => $province_data,
                        'amphur' => $amphure_data,
                        'district' => $district_data
                    ];
                }

                $order->receipt_data = $receipt_data;
            }
        }


        // dd($orders);

        return view('backend.order.export', [
            'orders' => $orders
        ]);
    }
}
