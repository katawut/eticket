<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Transaction;
use App\Model\Order;
use Throwable;
use Log;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\MailController as OrderMail;
use Carbon\Carbon;

use function GuzzleHttp\json_decode;

class TransactionController extends Controller
{
    public function store(Request $request)
    {

        $orderId = explode('-', $request->input('data.description'))[1];
        $detail = [
            'id' => $request->input('data.id'),
            'transaction' => $request->input('data.transaction'),
            'order_id' => $request->input('data.description'),
            'event_id' => $request->input('id'),
            'key' => $request->input('key'),
            'amount' => $request->input('data.amount'),
            'net' => $request->input('data.net'),
            'fee' => $request->input('data.fee'),
            'fee_vat' => $request->input('data.fee_vat'),
            'interest' => $request->input('data.interest'),
            'interest_vat' => $request->input('data.interest_vat'),
            'funding_amount' => $request->input('data.funding_amount'),
            'refunded_amount' => $request->input('data.refunded_amount'),
            'currency' => $request->input('data.currency'),
            'created_at' => $request->input('data.created_at'),
            'paid_at' => $request->input('data.paid_at'),
            'status' => $request->input('data.status'),
            'failure_code' => $request->input('data.failure_code'),
            'failure_message' => $request->input('data.failure_message')
        ];

        $payment_type = null;

        if (!is_null($request->input('data.card'))) {
            $payment_type = 'creditcard';
        }
        if (!is_null($request->input('data.source'))) {
            $payment_type = 'ibanking';
            $detail['source'] = $request->input('data.source.type');

            if ($request->input('data.status') == 'successful') {
                try {
                    $order = Order::find($orderId);
                    // $order->payment_type_id = 3;
                    $order->transaction_id = $request->input('data.transaction');
                    $order->status = 2;
                    $order->paid_at = tzstringToMysqlFormat($request->input('data.paid_at'));
                    $order->save();
                } catch (Throwable $e) {
                    Log::error('Transaction Error: ' . $e);
                }
            } else if ($request->input('data.status') == 'failed') {
                try {
                    $order = Order::find($orderId);
                    // $order->payment_type_id = 3;
                    $order->status = 3;
                    $order->save();
                } catch (Throwable $e) {
                    Log::error('Transaction Error: ' . $e);
                }
            }
        }

        $t = Transaction::create([
            'order_id' => $orderId,
            'transaction_id' => $request->input('data.id'),
            'transaction' => $request->input('data.transaction'),
            'status' => $request->input('data.status'),
            'payment_type' => $payment_type,
            'detail' => json_encode($detail)
        ]);

        if ($request->input('data.status') == 'successful') {
            $mail = new OrderMail;
            $mail->sendMailOrder($orderId);
        }

        return 'done';

        // return 'test';
    }

    public function payPal(Request $request)
    {
        $order_id = 0;
        $transaction = '';
        $order_info = '';

        $resource = $request->input('resource');
        $order_id = $resource['invoice_id'];
        $order_info = $resource;

        if ($request->input('event_type') == 'PAYMENT.CAPTURE.COMPLETED') {
            $order_id = $resource['invoice_id'];
            try {
                $order = Order::find($order_id);
                $order->transaction_id = $resource['id'];
                $order->status = 2;
                $order->paid_at = tzstringToMysqlFormat($resource['create_time']);
                $order->save();
            } catch (Throwable $e) {
                Log::error('Transaction Error PayPal: ' . $e);
            }
        }

        $detail = [
            'event_id' => $request->input('id'),
            'event_type' => $request->input('event_type'),
            'status' => $request->input('status'),
            'summary' => $request->input('summary'),
            'order_links' => $resource['links'],
            'order_info' => $order_info,
        ];

        $t = Transaction::create([
            'order_id' => $order_id,
            'transaction_id' => $request->input('id'),
            'transaction' => $resource['id'],
            'status' => $request->input('event_type'),
            'payment_type' => 'paypal',
            'detail' => json_encode($detail)
        ]);

        if ($request->input('event_type') == 'PAYMENT.CAPTURE.COMPLETED') {
            $mail = new OrderMail;
            $mail->sendMailOrder($order_id);
        }

        return 'done';
    }
}
