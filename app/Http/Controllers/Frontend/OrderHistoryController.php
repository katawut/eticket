<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Model\Order;
use App\Model\OrderItem;
use Carbon\Carbon;

class OrderHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    const MODULE = 'orderHistory';
    public function __construct()
    {
        $this->middleware('auth')->only('index', 'create', 'store', 'show', 'update', 'destroy');
    }

    public function index()
    {
        $orders = Order::with(['user', 'orderItem', 'paymentType'])
            ->orderBy('created_at', 'desc')
            ->where('user_id', Auth::user()->id)
            ->paginate(5);
        return view('frontend.user.orderHistory', compact('orders'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $userId = Auth::user()->id;

        $order_detail = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->join('payment_types', 'orders.payment_type_id', '=', 'payment_types.id')
            ->where('orders.id', $id)
            ->where('orders.user_id', $userId)
            ->select('orders.*', 'users.first_name', 'users.last_name', 'users.email', 'payment_types.name_th AS payment_type_name')
            ->first();

        if (is_null($order_detail)) {
            return redirect()->route('frontend.orders.index');
        }

        if ($order_detail->payment_type_id == 5) {
            $order_slip  = Order::find($id)->image;
            $order_detail->slip = $order_slip;
        }

        $order_items = OrderItem::whereOrderId($order_detail->id)->get();

        return view('frontend.user.orderDetail', ['order' => $order_detail, 'order_items' => $order_items]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        //$this->authorize(mapPermission(self::MODULE));
        $order->storeImage();
        $order->status = 4;
        $order->paid_at = Carbon::now();
        $order->save();

        $request->session()->flash('notice', 'ส่งข้อมูลเรียบร้อย เราจะส่งอีเมลให้ท่านอีกครั้ง เมื่อข้อมูลได้รับการตรวจสอบเรียบร้อยแล้ว');

        return redirect()->route('frontend.orders.show', $order->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
