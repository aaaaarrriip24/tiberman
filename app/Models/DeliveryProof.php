<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryProof extends Model
{
    protected $fillable = ['surat_jalan_id','receiver_name','photo_path','received_at'];
    protected $casts = [
        'received_at' => 'datetime',
    ];
    public function suratJalan() { return $this->belongsTo(SuratJalan::class); }
}