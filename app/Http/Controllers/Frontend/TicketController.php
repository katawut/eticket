<?php

namespace App\Http\Controllers\Frontend;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// Models
use App\Model\Stocks;
use App\Model\Ticket;

class TicketController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $tickets = Ticket::selectRaw('tickets.*, stocks.quantity AS stock_quantity')->onlyActive()->onSellPeriod()->haveStock()->get();

        if ($tickets->count() != 0) :
            foreach ($tickets as $key => $item) :
                $lists[$key]['count'] = $key + 1;
                $lists[$key]['id'] = $item->id;
                $lists[$key]['cover'] = $item->image;
                $lists[$key]['title_th'] = $item->title_th;
                $lists[$key]['title_en'] = $item->title_en;
                $lists[$key]['price'] = $item->price;
                $lists[$key]['discount'] = $item->discount_price;
                // $lists[$key][''] = $item->;
            endforeach;
        else :
            $lists = [];
        endif;

        // dd($lists);

        $compacts = ['lists'];
        return view('frontend.product.index', compact($compacts));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        $ticket = Ticket::findOrFail($id);
        return view('frontend.product.show', ['ticket' => $ticket]);
    }
}
