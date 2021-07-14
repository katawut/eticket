<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\Models\Media;

class Ticket extends Model implements HasMedia
{
    use LogsActivity, HasMediaTrait;
    protected $table = 'tickets';
    protected $guarded = [];

    protected static $logName = 'ticket';
    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;
    protected $attributes = ['active' => 1];

    // ------------------------------------------------------
    // Relations
    public function orderItem() {
        return $this->hasMany('App\Model\OrderItem');
    }

    public function update_name() {
        return $this->belongsTo('App\User', 'updated_by');
    }

    public function stocks() {
        return $this->hasOne('App\Model\Stocks', 'tickets_id');
    }

    // ------------------------------------------------------
    // Media
    public function registerMediaCollections() {
        $this->addMediaCollection('image')->singleFile();
    }

    public function storeImage() {
        if (request()->has('image')) :
            $this->addMediaFromRequest('image')->sanitizingFileName(function ($fileName) {
                return sanitizeFileName($fileName);
            })->toMediaCollection('image');
        endif;
    }

    // ------------------------------------------------------
    // Attributes
    public function getImageAttribute() {
        return $this->getFirstMediaUrl('image');
    }

    public function getImageDataAttribute() {
        return $this->getMedia('image');
    }

    public function getImageDetailAttribute() {
        return $this->getMedia('image_detail');
    }

    public function getActiveAttribute($attributes) {
        return  [
            1 => 'Active',
            0 => 'Inactive'
        ][$attributes];
    }

    // ------------------------------------------------------
    // Scopes
    public function scopeOnSellPeriod($query) {
        $now = date('Y-m-d');
        return $query->whereDate('start_at', '<=', "{$now}")
        ->whereDate('end_at', '>=', "{$now}");
    }

    public function scopeHaveStock($query) {
        return $query->leftJoin('stocks', function($join) {
            $join->on('tickets.id', '=', 'stocks.tickets_id')
            ->where('stocks.quantity', '<>', 0);
        });
    }

    public function scopeGetTicketByKeyword($query, $keyword) {
        return $query->where('title_th', 'like', "%{$keyword}%")
            ->orWhere('title_en', 'like', "%{$keyword}%");
    }

    public function scopeOnlyActive($query) {
        return $query->where('active', 1);
    }
}
