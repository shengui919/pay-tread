<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Load extends Model {
    use HasFactory;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id','ref','amount_cents','status','pod_method','delivery_lat','delivery_lng','geofence_radius_m','bol_path'];
    protected static function booted(){ static::creating(fn($m)=>$m->id ??= (string) Str::uuid()); }
    public function pod(){ return $this->hasOne(Pod::class); }
    public function paymentIntent(){ return $this->hasOne(PaymentIntent::class); }
}
