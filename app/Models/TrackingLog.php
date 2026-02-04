<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingLog extends Model
{
    protected $fillable = [
        'surat_jalan_id',
        'latitude','longitude',
        'ip_address',
        'scanned_at',
        'ip_latitude','ip_longitude','distance_ip_km',
        'is_suspicious','suspicious_reason',
        'user_agent',
        'scan_by',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'is_suspicious' => 'boolean',
    ];


    public function suratJalan() { return $this->belongsTo(SuratJalan::class); }
}