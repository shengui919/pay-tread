<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class PaymentIntent extends Model {
    use HasFactory;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    protected static function booted(){ static::creating(fn($m)=>$m->id ??= (string) Str::uuid()); }
    public function load(){ return $this->belongsTo(Load::class); }
}
