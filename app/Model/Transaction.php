<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['order_id', 'transaction_id', 'transaction', 'payment_type', 'status', 'detail'];
}
