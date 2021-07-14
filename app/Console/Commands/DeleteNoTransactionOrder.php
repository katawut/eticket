<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\Order;
use App\Model\Ticket;
use Illuminate\Support\Facades\Log;

class DeleteNoTransactionOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cronjob:delete-notrans-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete the transaction was not made within the specified time';

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
        $notrans_orders = Order::where(['payment_type_id' => 1, 'status' => 1]);
        if (!$notrans_orders->get()->isEmpty()) {
            $restock_ticket_id = Order::where(['payment_type_id' => 1, 'status' => 1])->first()->orderItem()->first()->ticket_id;
            $restock_items = $notrans_orders->sum('total');

            $notrans_orders->update(['status' => 5, 'note' => 'ยกเลิกโดยระบบ - ไม่ได้ทำการสั่งซื้อภายในเวลาที่กำหนด']);

            // คืน stock
            $ticket = Ticket::find($restock_ticket_id);
            $ticket->quantity = $ticket->quantity + $restock_items;
            $ticket->save();
            return Log::notice('cronjob: cancel no transaction orders already.');
        }

        return 0;
    }
}
