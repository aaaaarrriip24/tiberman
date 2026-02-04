<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratJalan extends Model
{
    protected $table = 'surat_jalan';
    protected $fillable = ['kode_surat_jalan','qr_code_path','status','created_by'];

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function trackingLogs() { return $this->hasMany(TrackingLog::class); }
    public function proof() { return $this->hasOne(DeliveryProof::class); }
    public function audits() { return $this->hasMany(AuditTrail::class); }
}