<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingLog extends Model
{
    protected $fillable = [
        'surat_jalan_id','latitude','longitude','ip_address','scan_by','scanned_at'
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function suratJalan() { return $this->belongsTo(SuratJalan::class); }
}