<?php

namespace App\Http\Controllers\Frontend;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Model\Cart;
use App\Model\Ticket;
use Carbon\Carbon;

class CartController extends Controller {
    const MODULE = 'cart';
    public function __construct() {
        $this->middleware('auth')->only('index', 'store', 'update', 'destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $tickets = [];
        $cart_items = null;

        if(session()->has('cart_session')) :
            $session_id = session()->get('cart_session');

            $cart = Cart::where('session_id', $session_id)->first();
            $cart_items = json_decode($cart->details);

            foreach($cart_items as $item) :
                array_push($tickets, Ticket::find($item->id));
            endforeach;

        endif;

        return view('frontend.cart.index', ['tickets' => $tickets, 'cart_items' => $cart_items]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        // $request->session()->forget('cart_session');
        // return;

        $user_id = Auth::user()->id;
        $ticket_id = $id;
        $detail = [];
        $request_item = [
            'id' => $ticket_id,
            'total' => 1
        ];

        // ถ้ามี session แสดงว่ายังไม่เคยแอดสินค้าลงตะกร้าหรือไม่มีตะกร้าแล้ว ให้ทำการสร้างใหม่
        if(!request()->session()->has('cart_session')) {
            $timestamp = Carbon::now()->timestamp;
            $session_id = $timestamp + $user_id;
            array_push($detail, $request_item);

            Cart::create([
                'session_id' => $session_id,
                'user_id' => $user_id,
                'details' => json_encode($detail)
            ]);

            $request->session()->put('cart_session', $session_id);

        } else {
            $already_has_items = false;

            $session_id = $request->session()->get('cart_session');

            $cart = Cart::where('session_id', $session_id)->first();
            $cart_items = json_decode($cart->details);

            // เช็คว่ามีสินค้านี้ในตะกร้าหรือยัง
            foreach($cart_items as $item) {
                if($item->id == $ticket_id) {
                    $already_has_items = true;
                    break;
                }
            }

            // ถ้ายังไม่เคยมีสินค้านี้ให้เพิ่มสินค้าเข้าไปในตะกร้า
            if(!$already_has_items) {
                array_push($cart_items, $request_item);
                $cart->update([
                    'details' => json_encode($cart_items)
                ]);
            }
        }

        return redirect()->route('frontend.cart.index');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id) {
        $session_id = $request->uid;
        $new_cart_items = [];

        $cart = Cart::where('session_id', $session_id)->first();
        $cart_items = json_decode($cart->details);

        foreach ($cart_items as $item) :
            if ($item->id != $id) :
               array_push($new_cart_items, $item);
            endif;
        endforeach;

        if(empty($new_cart_items)) :
            $request->session()->forget('cart_session');
            $cart->delete();
        else :
            $cart->update(['details' => json_encode($new_cart_items)]);
        endif;

        return redirect()->back();
    }

    // ------------------------------------------------------
    // APIs
    public function getCartItems(Request $request) {
        $total_items = 0;

        $session_id = $request->uid;
        $cart = Cart::where('session_id', $session_id)->first();
        $cart_items = json_decode($cart->details);

        foreach($cart_items as $item) :
            $total_items += $item->total;
        endforeach;

        return response()->json(['items'=> $total_items], 200);
    }

    public function UpdateCart(Request $request) {
        $session_id = $request->uid;
        $item_id = $request->item_id;
        $total = $request->total;
        $new_cart_items = [];

        $cart = Cart::where('session_id', $session_id)->first();
        $cart_items = json_decode($cart->details);

        // เช็คว่าสินค้าพอไหม
        $ticket = Ticket::where('id', $item_id)->first();

        if ($ticket->stocks->quantity == 0) :
            foreach ($cart_items as $item) :
                if ($item->id != $item_id) :
                    array_push($new_cart_items, $item);
                endif;
            endforeach;

            $cart->update(['details' => json_encode($new_cart_items)]);

            return response()->json(['id' => $item_id, 'remain' => $ticket->stocks->quantity, 'status' => 'noticket'], 200);
        endif;

        if ($total > $ticket->stocks->quantity) :
            foreach ($cart_items as $item) :
                if ($item->id == $item_id) :
                    $item->total = $ticket->stocks->quantity;
                endif;
            endforeach;

            $cart->update(['details' => json_encode($cart_items)]);

            return response()->json(['id' => $item_id, 'remain' => $ticket->quantity, 'status' => 'noticket'], 200);
        endif;

        foreach ($cart_items as $item) :
            if ($item->id == $item_id) :
                $item->total = $total;
            endif;
        endforeach;

        $cart->update(['details' => json_encode($cart_items)]);

        return response()->json(['status'=>'OK'], 200);
    }
}
