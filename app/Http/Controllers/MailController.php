<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\OrderItem;
use App\Model\Ticket;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentComplete;
use Illuminate\Support\Facades\DB;
use App\Model\Order;

class MailController extends Controller {
    public function sendMailOrder($id) {
        $order = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->join('payment_types', 'orders.payment_type_id', '=', 'payment_types.id')
            ->where('orders.id', $id)
            ->select('orders.*', 'users.first_name', 'users.last_name', 'users.email', 'payment_types.name_th AS payment_type_name', 'payment_types.name_en AS payment_type_name_en', 'payment_types.id AS payment_type_id')
            ->get();

        $order_data = $order[0];
        $email = $order_data->email;
        $user_id = $order_data->user_id;
        $receipt_data = [];

        if ($order_data->receipt_requested == 1) {
            $user_info = DB::table('user_infos')->where('user_id', $user_id)->first();
            $receipt_data['user_info'] = $user_info;

            $province_data = DB::table('provinces')->where('id', $user_info->receipt_province)->first();
            $amphure_data = DB::table('amphures')->where('id', $user_info->receipt_amphur)->first();
            $district_data = DB::table('districts')->where('id', $user_info->receipt_district)->first();
            $province_data2 = null;
            $amphure_data2 = null;
            $district_data2 = null;

            if(!is_null($user_info->deli_receipt_name)) {
                $province_data2 = DB::table('provinces')->where('id', $user_info->deli_receipt_province)->first();
                $amphure_data2 = DB::table('amphures')->where('id', $user_info->deli_receipt_amphur)->first();
                $district_data2 = DB::table('districts')->where('id', $user_info->deli_receipt_district)->first();
            }

            $receipt_data['addresses'] = [
                'province' => $province_data,
                'amphur' => $amphure_data,
                'district' => $district_data,
                'province2' => $province_data2,
                'amphur2' => $amphure_data2,
                'district2' => $district_data2
            ];
        }

        $order_items = Order::find($id)->orderItem()->get();

        // ส่งเมลยืนยันแล้ว redirect ไปหน้า thankyou
        $admin_email = ($order_data->status == 2 ? ['ticketms@ndmi.or.th'] : []);
        Mail::to($email)->bcc($admin_email)->send(new PaymentComplete($order_data, $receipt_data, $order_items,  'การชำระเงินสำเร็จ'));
        return;

        //return view('frontend.mail.paymentComplete', ['order' => $order_data, 'receipt_data' => $receipt_data, 'ticket' => $ticket]);
        //return view('frontend.user.orderDetail', ['order' => $order_data, 'receipt_data' => $receipt_data]);

        // dd(resource_path('/frontend/css/style.css'));
    }
}
