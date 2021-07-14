<?php


namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\PaymentComplete;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Log;
use Throwable;

// Models
use App\User;
use App\Model\Cart;
use App\Model\Order;
use App\Model\OrderItem;
use App\Model\PaymentType;
use App\Model\Stocks;
use App\Model\Ticket;
use App\Model\UserInfo;

class CheckoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only('index', 'store', 'update', 'destroy', 'confirm', 'complete', 'thankyou');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $cart_items = null;

        if (session()->has('cart_session')) :
            $session_id = session()->get('cart_session');

            $cart = Cart::where('session_id', $session_id)->first();
            $cart_items = json_decode($cart->details);

            foreach ($cart_items as $item) :
                $item->ticket = Ticket::find($item->id);
            endforeach;
        else :
            return redirect()->route('frontend.index');
        endif;

        return view('frontend.checkout', ['cart_items' => $cart_items]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Auth::check()) :
            if (session()->has('cart_session')) :
                $cart_id = session()->get('cart_session');

                $cart = Cart::where('session_id', $cart_id)->first();
                $cart_items = json_decode($cart->details);

                foreach ($cart_items as $item) :
                    $ticket = Ticket::find($item->id);

                    if ($ticket->stocks->quantity < $item->total) :
                        $request->session()->flash('ticketStatus', 'noTicket');
                        return redirect()->route('frontend.index');
                    endif;

                    if ($item->total > 20) :
                        $request->session()->flash('ticketStatus', 'ticketLimitExceed');
                        return redirect()->route('frontend.index');
                    endif;
                endforeach;
            else :
                return redirect()->route('frontend.index');
            endif;

            $payment_getway_token = '';
            if ($request->payment_type == 'creditcard') :
                $payment_id = 2;
                $payment_getway_token = $request->payment_getway_token;
            elseif ($request->payment_type == 'ibanking') :
                $payment_id = 3;
                $payment_getway_token = $request->ibankingSource;
            elseif ($request->payment_type == 'paypal') :
                $payment_id = 4;
            elseif ($request->payment_type == 'bank') :
                $payment_id = 5;
            endif;

            // ถ้ามีการขอใบเสร็จ
            if ($request->receipt_request == 1) :
                $userId = Auth::user()->id;

                // CHECK : user_id from table : user_infos
                $userInfo = UserInfo::whereUserId($userId)->first();

                $receipt_data = [
                    'receipt_name' => $request->receipt_name,
                    'receipt_phone' => $request->receipt_phone,
                    'receipt_address' => $request->receipt_address,
                    'receipt_postal_code' => $request->receipt_postal_code,
                    'receipt_province' => $request->receipt_province,
                    'receipt_amphur' => $request->receipt_amphur,
                    'receipt_district' => $request->receipt_district,
                    'receipt_request' => $request->receipt_request,
                    'receipt_type' => $request->receipt_type,

                    'deli_receipt_name' => $request->deli_receipt_name,
                    'deli_receipt_phone' => $request->deli_receipt_phone,
                    'deli_receipt_address' => $request->deli_receipt_address,
                    'deli_receipt_zipcode' => $request->deli_receipt_zipcode,
                    'deli_receipt_province' => $request->deli_receipt_province,
                    'deli_receipt_district' => $request->deli_receipt_district,
                    'deli_receipt_subdistrict' => $request->deli_receipt_subdistrict
                ];
                $request->session()->flash('receipt_data', $receipt_data);
            endif;

            $request->session()->put('payment_info', ['id' => $payment_id, 'payment_getway_token' => $payment_getway_token]);

            // ไปยังหน้ายืนยันการชำระเงิน
            return redirect()->route('frontend.checkout.confirm');
        else :
            return redirect()->route('login');
        endif;
    }

    //backup store method
    public function storebeforecart(Request $request)
    {
        if (Auth::check()) {
            // เช็คว่ามี session order หรือไม่ ถ้ามีให้ลบ order เดิมทิ้งก่อน
            if ($request->session()->has('order')) {
                $order_id = $request->session()->get('order')['orderId'];
                $order = Order::find($order_id);

                if (!is_null($order)) {
                    $total_ticket = $order->total;
                    $ticket_id = $order->orderItem()->first()->ticket_id;
                    // $order->forceDelete();
                    $order->update(['status' => 5, 'note' => 'ยกเลิกคำสั่งซื้อโดยผู้ใช้']);

                    // คืนตั๋ว
                    $ticket = Ticket::find($ticket_id);
                    $ticket->stocks->quantity = $ticket->stocks->quantity + $total_ticket;
                    $ticket->save();
                }

                $request->session()->forget('order');
            }

            // เช็คสถานะบัตร
            $ticket = Ticket::find(1)->whereActive(1)->whereDate('start_at', '<=', Carbon::now())->whereDate('end_at', '>=', Carbon::now())->first();

            if (!$ticket) {
                $request->session()->flash('ticketStatus', 'ticketNotAvailable');
                return redirect()->route('frontend.index');
            }

            //เช็คจำนวนบัตรคงเหลือ
            if ($ticket->stocks->quantity < $request['TicketNumber']) {
                $request->session()->flash('ticketStatus', 'noTicket');
                return redirect()->route('frontend.index');
            }

            //ถ้าสั่งซื้อบัตรเกิน 100 ใบ จะไม่สามารถทำการสั่งซื้อได้
            if ($request['TicketNumber'] > 20) {
                $request->session()->flash('ticketStatus', 'ticketLimitExceed');
                return redirect()->route('frontend.index');
            }

            // ทำการสร้าง order
            $purchaseDate = new Carbon();
            $userId = User::find(Auth::id());
            $paymentType = PaymentType::find(1);

            $order = new Order();
            $order->total = $request['TicketNumber'];
            $order->amount = ($request['TicketNumber'] * $request['b-price']);
            $order->transaction_id = '';
            $order->unit_price = $request['price'];
            $order->discount_price = $request['discount_price'];
            $order->purchased_at = $purchaseDate->now();
            $order->status = 1;
            $order->created_by = Auth::id();
            $order->updated_by = Auth::id();

            $order->paymentType()->associate($paymentType);
            $order->user()->associate($userId);
            $order->save();

            // ทำการสร้าง order item
            for ($i = 0; $i < $request['TicketNumber']; $i++) {
                $orderItem = new OrderItem;
                $orderItem->ticket_id = $ticket->id;
                $orderItem->order()->associate($order);
                $orderItem->save();
            }

            // ตัดจำนวนบัตรคงเหลือ
            $ticket->stocks->quantity = $ticket->stocks->quantity - $request['TicketNumber'];
            $ticket->save();

            // สร้าง session order
            $request->session()->put('order', [
                'orderId' => $order->id,
                'unitPrice' => $request['b-price'],
                'discountPrice' => $request['discount_price'],
                'price' => $request['price'],
                'totalTicket' => $request['TicketNumber'],
                'amount' => ($request['TicketNumber'] * $request['b-price'])
            ]);

            // ไปยังหน้าชำระเงิน
            return redirect()->route('frontend.checkout.index');

            // dd($request->session()->all());


        } else {
            return redirect()->route('login');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (session()->has('cart_session')) :
            $total_discount_price = 0;
            $total_price = 0;
            $total_unit_price = 0;
            $cart_id = session()->get('cart_session');

            $order_items = 0;

            $cart = Cart::where('session_id', $cart_id)->first();
            $cart_items = json_decode($cart->details);

            foreach ($cart_items as $item) :
                $ticket = Ticket::find($item->id);

                if ($ticket->stocks->quantity < $item->total) :
                    $request->session()->flash('ticketStatus', 'noTicket');
                    return redirect()->route('frontend.index');
                endif;

                if ($item->total > 20) :
                    $request->session()->flash('ticketStatus', 'ticketLimitExceed');
                    return redirect()->route('frontend.index');
                endif;

                $item_total_price = $ticket->price * $item->total;
                $item_total_discount = $ticket->discount_price * $item->total;
                $item_price_with_discount = $item_total_price - $item_total_discount;
                $total_unit_price += $item_total_price;
                $total_discount_price += $item_total_discount;
                $total_price += $item_price_with_discount;

                $order_items += 1;
            endforeach;
        else :
            return redirect()->route('frontend.index');
        endif;

        //ทำการสร้าง order
        $purchaseDate = new Carbon();
        $userId = User::find(Auth::id());
        $paymentType = PaymentType::find($request->payment_type);

        if (is_null($cart->order_id)) :
            $order = new Order();
            $order->payment_type_id = $request->payment_type;
            $order->total = $order_items;
            $order->amount = $total_price;
            $order->transaction_id = '';
            $order->unit_price = $total_unit_price;
            $order->discount_price = $total_discount_price;
            $order->purchased_at = $purchaseDate->now();
            $order->status = 1;
            $order->created_by = Auth::id();
            $order->updated_by = Auth::id();

            $order->paymentType()->associate($paymentType);
            $order->user()->associate($userId);
            $order->save();

            // ทำการสร้าง order item
            foreach ($cart_items as $item) :
                $orderItem = new OrderItem;
                $orderItem->ticket_id = $item->id;
                $orderItem->amount = $item->total;
                $orderItem->order()->associate($order);
                $orderItem->save();

                // ตัดจำนวนบัตรคงเหลือ
                // $ticket = Ticket::find($item->id);
                // $ticket->stocks->quantity = $ticket->stocks->quantity - $item->total;
                // $ticket->save();

                $stock = Stocks::where('tickets_id', $item->id)->first();
                $stock->quantity = $stock->quantity - $item->total;
                $stock->save();
            endforeach;

            $cart->order_id = $order->id;
            $cart->save();
        endif;

        // ต้องการใบเสร็จตัวจริงหรือไม่
        if ($request->receipt_request == 1) :
            $order->receipt_requested = 1;
            $order->receipt_type = $request->receipt_type;
            $order->save();
        endif;

        // ตรวจสอบประเภทการชำระเงิน
        if ($request->payment_type == 2) : // Credit Card
            try {
                $charge = payment_getwayClient::charge([
                    'amount' => ($order->amount * 100),
                    'currency' => 'thb',
                    'description' => 'ETICKET-' . $order->id,
                    'card' => $request->token,
                    'return_uri' => route('frontend.checkout.complete', $order->id)
                ]);
            } catch (Throwable $e) {
                Log::error('payment_getway API error: ' . $e->getMessage() . ', ORder ID: ' . $order->id);
                $request->session()->flash('ticketStatus', 'ไม่สามารถทำการชำระเงินได้ กรุณาติดต่อเจ้าหน้าที่');
                return redirect()->route('frontend.index');
            }

            // ถ้า charge บัตรสำเร็จ
            if ($charge['status'] == 'successful') :
                $transaction_id = $charge['transaction'];

                $order->transaction_id = $transaction_id;
                $order->status = 2;
                $order->paid_at = tzstringToMysqlFormat($charge['paid_at']);
                $order->save();

                // ทำลาย session
                $request->session()->forget('cart_session');
                $request->session()->forget('payment_info');
                $cart->delete();

                // redirect ไปหน้า complete
                return redirect()->route('frontend.checkout.complete', $order->id);
            endif;
        elseif ($request->payment_type == 3) : // Internet Banking
            try {
                $charge = payment_getwayClient::charge([
                    'amount' => ($order->amount * 100),
                    'currency' => 'thb',
                    'description' => 'ETICKET-' . $order->id,
                    'source' => $request->token,
                    'return_uri' => route('frontend.checkout.complete', $order->id)
                ]);
            } catch (Throwable $e) {
                Log::error('payment_getway API error: ' . $e->getMessage() . ', ORder ID: ' . $order->id);
                $request->session()->flash('ticketStatus', 'ไม่สามารถทำการชำระเงินได้ กรุณาติดต่อเจ้าหน้าที่');
                return redirect()->route('frontend.index');
            }

            if ($charge['status'] == 'pending') :
                // ทำลาย session order
                $request->session()->forget('cart_session');
                $request->session()->forget('payment_info');
                $cart->delete();

                $authorize_uri = $charge['authorize_uri'];

                // redirect ไปหน้าของธนาคาร
                return redirect()->away($authorize_uri);
            endif;
        elseif ($request->payment_type == 4) : // PayPal
            // ทำลาย session
            $request->session()->forget('cart_session');
            $request->session()->forget('payment_info');
            $cart->delete();

            // redirect ไปหน้า complete
            return redirect()->route('frontend.checkout.paypal', $order->id);
        elseif ($request->payment_type == 5) : // Bank
            // ทำลาย session
            $request->session()->forget('cart_session');
            $request->session()->forget('payment_info');
            $cart->delete();

            // redirect ไปหน้า complete
            return redirect()->route('frontend.checkout.complete', $order->id);
        else :
            if (is_null($request->token) || is_null($request->payment_type)) :
                Log::error('Checkout Error: token or payment type data is null., ORder ID: ' . $order->id);
                $request->session()->flash('ticketStatus', 'ไม่สามารถทำการชำระเงินได้ กรุณาติดต่อเจ้าหน้าที่');
                return redirect()->route('frontend.index');
            else :
                Log::error('Checkout Error: , ORder ID: ' . $order->id);
                $request->session()->flash('ticketStatus', 'ไม่สามารถทำการชำระเงินได้ กรุณาติดต่อเจ้าหน้าที่');
                return redirect()->route('frontend.index');
            endif;
        endif;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if ($request->session()->has('cart_session')) {
            $session_id = $request->session()->get('cart_session');
            $cart = Cart::where('session_id', $session_id)->first();
            $cart->delete();
            $request->session()->forget('cart_session');
        }

        return redirect()->route('frontend.index');
    }

    public function complete(Request $request, $id)
    {
        // check order id ว่ามีหรือไม่

        // ไม่มี ให้ redirect ไปหน้าแรก

        // ถ้ามี เช็คว่า order complete หรือยัง

        // ถ้ายัง redirect ไปหน้าแรก

        // ถ้า complete ให้ส่งเมลยืนยันอีกครั้ง

        $order = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->join('payment_types', 'orders.payment_type_id', '=', 'payment_types.id')
            ->where('orders.id', $id)
            ->select('orders.*', 'users.first_name', 'users.last_name', 'users.email', 'payment_types.name_th AS payment_type_name', 'payment_types.name_en AS payment_type_name_en', 'payment_types.id AS payment_type_id')
            ->get();

        $order_data = $order[0];
        $email = $order_data->email;
        $user_id = $order_data->user_id;
        $payment_type_id = $order_data->payment_type_id;
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

            if (!is_null($user_info->deli_receipt_name)) {
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
        Mail::to($email)->bcc($admin_email)->send(new PaymentComplete($order_data, $receipt_data, $order_items,  'การสั่งซื้อสำเร็จ'));
        $request->session()->flash('payment_type', $payment_type_id);
        return redirect()->route('frontend.thankyou');

        // return view('frontend.mail.paymentComplete', ['order' => $order_data, 'receipt_data' => $receipt_data, 'order_items' => $order_items]);
        // return view('frontend.user.orderDetail', ['order' => $order_data, 'receipt_data' => $receipt_data]);

        // dd(resource_path('/frontend/css/style.css'));
    }

    public function confirm(Request $request)
    {
        if (Auth::check()) {
            if (session()->has('cart_session')) {

                $cart_id = session()->get('cart_session');
                $cart = Cart::where('session_id', $cart_id)->first();
                $cart_items = json_decode($cart->details);

                foreach ($cart_items as $item) {
                    $ticket = Ticket::find($item->id);

                    if ($ticket->stocks->quantity < $item->total) {
                        $request->session()->flash('ticketStatus', 'noTicket');
                        return redirect()->route('frontend.index');
                    }

                    if ($item->total > 20) {
                        $request->session()->flash('ticketStatus', 'ticketLimitExceed');
                        return redirect()->route('frontend.index');
                    }

                    $item->ticket = $ticket;
                }


                // เช็ตว่ามีการขอใบเสร็จไหม
                $address_data = [];

                if ($request->session()->has('receipt_data')) {
                    $receipt_data = $request->session()->get('receipt_data');
                    $userId = Auth::user()->id;

                    // CHECK : user_id from table : user_infos
                    $userInfo = UserInfo::whereUserId($userId)->first();

                    if (is_null($userInfo)) {
                        UserInfo::create([
                            'user_id' => $userId,
                            'receipt_name' => $receipt_data['receipt_name'],
                            'receipt_phone' => $receipt_data['receipt_phone'],
                            'receipt_address' => $receipt_data['receipt_address'],
                            'receipt_postal_code' => $receipt_data['receipt_postal_code'],
                            'receipt_province' => $receipt_data['receipt_province'],
                            'receipt_amphur' => $receipt_data['receipt_amphur'],
                            'receipt_district' => $receipt_data['receipt_district'],
                            'deli_receipt_name' => $receipt_data['deli_receipt_name'],
                            'deli_receipt_phone' => $receipt_data['deli_receipt_phone'],
                            'deli_receipt_address' => $receipt_data['deli_receipt_address'],
                            'deli_receipt_postal_code' => $receipt_data['deli_receipt_zipcode'],
                            'deli_receipt_province' => $receipt_data['deli_receipt_province'],
                            'deli_receipt_amphur' => $receipt_data['deli_receipt_district'],
                            'deli_receipt_district' => $receipt_data['deli_receipt_subdistrict']
                        ]);
                    } else {
                        $userInfo->receipt_name = $receipt_data['receipt_name'];
                        $userInfo->receipt_phone = $receipt_data['receipt_phone'];
                        $userInfo->receipt_address = $receipt_data['receipt_address'];
                        $userInfo->receipt_postal_code = $receipt_data['receipt_postal_code'];
                        $userInfo->receipt_province = $receipt_data['receipt_province'];
                        $userInfo->receipt_amphur = $receipt_data['receipt_amphur'];
                        $userInfo->receipt_district = $receipt_data['receipt_district'];
                        $userInfo->deli_receipt_name = $receipt_data['deli_receipt_name'];
                        $userInfo->deli_receipt_phone = $receipt_data['deli_receipt_phone'];
                        $userInfo->deli_receipt_address = $receipt_data['deli_receipt_address'];
                        $userInfo->deli_receipt_postal_code = $receipt_data['deli_receipt_zipcode'];
                        $userInfo->deli_receipt_province = $receipt_data['deli_receipt_province'];
                        $userInfo->deli_receipt_amphur = $receipt_data['deli_receipt_district'];
                        $userInfo->deli_receipt_district = $receipt_data['deli_receipt_subdistrict'];

                        // $userInfo->deli_receipt_name = $receipt_data['']
                        $userInfo->save();
                    }

                    $province_data = DB::table('provinces')->where('id', $receipt_data['receipt_province'])->first();
                    $amphure_data = DB::table('amphures')->where('id', $receipt_data['receipt_amphur'])->first();
                    $district_data = DB::table('districts')->where('id', $receipt_data['receipt_district'])->first();
                    $province_data2 = null;
                    $amphure_data2 = null;
                    $district_data2 = null;

                    if (!is_null($receipt_data['deli_receipt_name'])) {
                        $province_data2 = DB::table('provinces')->where('id', $receipt_data['deli_receipt_province'])->first();
                        $amphure_data2 = DB::table('amphures')->where('id', $receipt_data['deli_receipt_district'])->first();
                        $district_data2 = DB::table('districts')->where('id', $receipt_data['deli_receipt_subdistrict'])->first();
                    }

                    $address_data = [
                        'province' => $province_data,
                        'amphur' => $amphure_data,
                        'district' => $district_data,
                        'province2' => $province_data2,
                        'amphur2' => $amphure_data2,
                        'district2' => $district_data2,
                    ];
                }

                $payment_type = PaymentType::find($request->session()->get('payment_info')['id']);

                return view('frontend.confirm', ['cart_items' => $cart_items, 'created_date' => $cart->created_at, 'payment_type' => $payment_type, 'addresses' => $address_data]);
            } else {
                return redirect()->route('frontend.index');
            }
        } else {
            return redirect()->route('login');
        }
    }

    public function paypal(Request $request, $id)
    {
        $order = Order::find($id);

        return view('frontend.paypal', ['order' => $order]);
    }

    public function thankyou()
    {
        if (!session()->exists('payment_type')) {
            return redirect('/');
        } else {
            $data['payment_type'] = session()->get('payment_type');
            return view('frontend.thankyou', $data);
        }
    }
}
