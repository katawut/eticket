<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class Order extends Model implements HasMedia
{
    use SoftDeletes, LogsActivity, HasMediaTrait;
    protected $table = 'orders';
    protected $guarded = [];
    protected static $logName = 'orders';
    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;
    protected $attributes = [
        'active' => 1,
    ];

    protected $fillable = ['user_id', 'total', 'amount', 'unit_price', 'discount_price', 'status', 'created_by', 'updated_by', 'active', 'note', 'paid_at', 'used', 'used_at'];

    public static function boot() {
        parent::boot();

        static::deleted(function (Order $order) {
            $order->orderItem()->delete();
        });
    }

    // ------------------------------------------------------
    // Relations
    public function user() {
        return $this->belongsTo('App\User');
    }

    public function paymentType() {
        return $this->belongsTo('App\Model\PaymentType');
    }

    public function orderItem() {
        return $this->hasMany('App\Model\OrderItem');
    }

    public function update_name() {
        return $this->hasOne('App\User', 'id', 'updated_by');
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

    public function getImageAttribute() {
        return $this->getFirstMediaUrl('image');
    }

    public function getImageDetailAttribute() {
        return $this->getMedia('image_detail');
    }

    // ------------------------------------------------------
    // Attributes
    public function getActiveAttribute($attributes) {
        return  [
            1 => 'Active',
            0 => 'Inactive'
        ][$attributes];
    }

    // ------------------------------------------------------
    // Scopes
    public function scopeGetNewsByKeyword($query, $keyword) {
        return $query->where('title_th', 'like', "%{$keyword}%")
        ->orWhere('title_en', 'like', "%{$keyword}%");
    }

    public function scopeOnlyActive($query) {
        return $query->where('active', 1);
    }
}
