<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\Order;
use App\Model\Ticket;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DeleteNoPaymentOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cronjob:delete-nopayment-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete the transaction was not made within the 3 days';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $notrans_orders = Order::where(['status' => 1])->whereDate('created_at', '<=', Carbon::now()->subDay(3));
        if (!$notrans_orders->get()->isEmpty()) {
            $restock_ticket_id = Order::where(['status' => 1])->whereDate('created_at', '<=', Carbon::now()->subDay(8))->first()->orderItem()->first()->ticket_id;
            $restock_items = $notrans_orders->sum('total');

            $notrans_orders->update(['status' => 5, 'note' => 'ยกเลิกโดยระบบ - ไม่ได้ทำการชำระเงินภายในเวลาที่กำหนด']);

            // คืน stock
            $ticket = Ticket::find($restock_ticket_id);
            $ticket->quantity = $ticket->quantity + $restock_items;
            $ticket->save();
            return Log::notice('cronjob: cancel no transaction payment orders already.');
        }
        return 0;
    }
}
