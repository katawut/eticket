<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use SoftDeletes;
    protected $fillable = ['used'];

    public function order()
    {
        return $this->belongsTo('App\Model\Order');
    }

    public function ticket()
    {
        return $this->belongsTo('App\Model\Ticket');
    }
}
