<?php

namespace App\Http\Controllers;

use App\Models\SuratJalan;
use App\Models\TrackingLog;
use App\Models\AuditTrail;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function index()
    {
        return view('pages.scan.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $sj = SuratJalan::where('kode_surat_jalan', $request->kode)->firstOrFail();

        TrackingLog::create([
            'surat_jalan_id' => $sj->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'ip_address' => $request->ip(),
            'scan_by' => $request->user()->id,
            'scanned_at' => now(),
        ]);

        if ($sj->status !== 'delivered') {
            $sj->update(['status' => 'on_delivery']);
        }

        AuditTrail::create([
            'surat_jalan_id' => $sj->id,
            'user_id' => $request->user()->id,
            'action' => 'SCAN',
            'description' => 'Scan QR & update lokasi'
        ]);

        return response()->json([
            'ok' => true,
            'id' => $sj->id,
            'kode' => $sj->kode_surat_jalan,
            'status' => $sj->status,
        ]);
    }
}
