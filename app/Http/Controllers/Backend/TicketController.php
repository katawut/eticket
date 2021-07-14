<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Models
use App\Model\Stocks;
use App\Model\Ticket;

class TicketController extends Controller {
    const MODULE = 'ticket';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $this->authorize(mapPermission(self::MODULE));

        if ($request->has('_token')) :
            $keyword = $request->keyword;
            $tickets = Ticket::onlyActive()->getTicketByKeyword($keyword)->get();
        else :
            $tickets = Ticket::onlyActive()->get();
        endif;

        // Preparing
        if ($tickets->count() != 0) :
            foreach ($tickets as $key => $item) :
                $updated_at = Carbon::parse(date('Y-m-d H:i:s', strtotime("$item->updated_at, + 543 years")))
                ->locale('th_TH')->isoFormat('D MMM g HH:mm:ss');

                $created_at = Carbon::parse(date('Y-m-d H:i:s', strtotime("$item->created_at, + 543 years")))
                ->locale('th_TH')->isoFormat('D MMM g HH:mm:ss');

                $start = Carbon::parse(date('Y-m-d', strtotime("$item->start_at, + 543 years")))->locale('th_TH')->isoFormat('D MMM g');
                $end = Carbon::parse(date('Y-m-d', strtotime("$item->end_at, + 543 years")))->locale('th_TH')->isoFormat('D MMM g');

                $lists[$key]['count'] = $key + 1;
                $lists[$key]['id'] = $item->id;
                $lists[$key]['updated_at'] = $updated_at;
                $lists[$key]['created_at'] = $created_at;
                $lists[$key]['duration_sell'] = "{$start} - {$end}";
                $lists[$key]['name'] = $item->title_th;
                $lists[$key]['price'] = number_format($item->price, 2);
                $lists[$key]['discount'] = number_format($item->discount_price, 2);
                $lists[$key]['quantity'] = $item->stocks->quantity;
                $lists[$key]['updated_by'] = $item->update_name->name;
            endforeach;
        else :
            $lists = [];
        endif;

        $compacts = ['lists'];
        return view('backend.ticket.index', compact($compacts));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        $this->authorize(mapPermission(self::MODULE));

        $ticket = new TICKET;

        return view('backend.ticket.create', compact('ticket'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $this->authorize(mapPermission(self::MODULE));

        DB::beginTransaction();
        try {
            $ticket = Ticket::create($this->_validate_request());
            $ticket->storeImage();

            Stocks::create($this->_stock_validate_request($ticket->id));

            foreach ($request->input('image_detail', []) as $file) :
              $ticket->addMedia(storage_path('tmp/uploads/' . $file))->toMediaCollection('image_detail');
            endforeach;
            deleteImageTmp();
            DB::commit();
            $flash = ['class' => 'success', 'message' => "บัตรเข้าชม [ {$request->title_th} ] ได้ถูกเพิ่มแล้ว"];
        } catch(Exception $ex) {
            DB::rollback();
            $flash = ['class' => 'danger', 'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล กรุณาลองใหม่อีกครั้ง'];
        }

        return redirect(route('backend.ticket.index'))->with($flash);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        $this->authorize(mapPermission(self::MODULE));

        $ticket = Ticket::findOrFail($id);

        return view('backend.ticket.update', compact('ticket'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Ticket $ticket) {
        $this->authorize(mapPermission(self::MODULE));

        DB::beginTransaction();
        try {
            $ticket->update($this->_validate_request());
            $ticket->storeImage();

            if (collect($ticket->image_detail)->count() > 0) :
                foreach ($ticket->image_detail as $media) :
                    if (!in_array($media->file_name, $request->input('image_detail', []))) :
                        $media->delete();
                    endif;
                endforeach;
            endif;

            $media = $ticket->image_detail->pluck('file_name')->toArray();

            foreach ($request->input('image_detail', []) as $file) :
                if (count($media) === 0 || !in_array($file, $media)) :
                    $ticket->addMedia(storage_path('tmp/uploads/' . $file))->toMediaCollection('image_detail');
                endif;
            endforeach;
            deleteImageTmp();

            DB::commit();
            $flash = ['class' => 'success', 'message' => "บัตรเข้าชม [ {$request->title_th} ] ถูกแก้ไขแล้ว"];
        } catch(Exception $ex) {
            DB::rollback();
            $flash = ['class' => 'danger', 'message' => 'เกิดข้อผิดพลาดขึ้น ในการบันทึกการแก้ไขข้อมูล กรุณาลองอีกครั้ง'];
        }

        return redirect(route('backend.ticket.index'))->with($flash);
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

    // ------------------------------------------------------
    // Private
    private function _validate_request() {
        $validatedData = request()->validate([
            'title_th' => ['required', 'string'],
            'title_en' => ['required', 'string'],
            'description_th' => ['required', 'string'],
            'description_en' => ['required', 'string'],
            'condition_th' => ['required', 'string'],
            'condition_en' => ['required', 'string'],
            'active' => ['required', 'boolean'],
            'price' => ['required', 'numeric'],
            'discount_price' => ['numeric'],
            'start_at' => ['required'],
            'end_at' => ['required'],
        ]);

        $start_date = str_replace('/', '-', request()->start_at);
        $end_date = str_replace('/', '-', request()->end_at);
        $start = Carbon::parse($start_date)->toDateString();
        $end = Carbon::parse($end_date)->toDateString();

        $validatedData['start_at'] = $start;
        $validatedData['end_at'] = $end;
        // $validatedData['quantity'] = 0;
        $validatedData['updated_by'] = Auth::id();

        if (request()->route()->getActionMethod() == 'store') :
            $validatedData['created_by'] = Auth::id();
        endif;

        return $validatedData;
    }

    private function _stock_validate_request($ticket_id) {
        $validatedData = request()->validate([
            'quantity' => ['required', 'numeric']
        ]);

        $validatedData['updated_by'] = Auth::id();

        if (request()->route()->getActionMethod() == 'store') :
            $validatedData['tickets_id'] = $ticket_id;
            $validatedData['created_by'] = Auth::id();
        endif;

        return $validatedData;
    }
}
