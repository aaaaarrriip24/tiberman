<?php

namespace App\Http\Controllers;

use App\Models\SuratJalan;
use App\Models\TrackingLog;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ScanController extends Controller
{
    private function haversineKm($lat1,$lon1,$lat2,$lon2): float
    {
        $earth = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2)**2 + cos(deg2rad($lat1))*cos(deg2rad($lat2))*sin($dLon/2)**2;
        return 2 * $earth * asin(min(1, sqrt($a)));
    }

    private function geoFromIp(?string $ip): ?array
    {
        if (!$ip) return null;

        // skip private / local IP (127.x, 10.x, 192.168.x, dll)
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return null;
        }

        // ✅ HTTPS, no-key (cukup buat demo nilai plus)
        $res = Http::timeout(3)->get("https://ipwho.is/{$ip}");
        if (!$res->ok() || !$res->json('success')) return null;

        return [
            'lat' => (float) $res->json('latitude'),
            'lng' => (float) $res->json('longitude'),
        ];
    }

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

        $sj = $request->filled('surat_jalan_id')
            ? SuratJalan::find($request->surat_jalan_id)
            : SuratJalan::where('kode_surat_jalan', $request->kode)->first();

        if (!$sj) {
            return response()->json(['ok' => false, 'message' => 'Surat Jalan tidak ditemukan.'], 404);
        }


        $MODE = 'flag'; // 'block' atau 'flag'
        $THRESHOLD_IP_KM = 80;     // toleransi IP (karena IP geo ngaco kadang)
        $THRESHOLD_SPEED = 220;    // km/h

        $gpsLat = (float) $request->latitude;
        $gpsLng = (float) $request->longitude;

        $ip = $request->ip();
        $ua = substr((string) $request->userAgent(), 0, 1000);

        $ipLoc = $this->geoFromIp($ip);
        $distanceIp = null;

        $reasons = [];

        // 1) GPS vs IP distance
        if ($ipLoc) {
            $distanceIp = $this->haversineKm($gpsLat, $gpsLng, $ipLoc['lat'], $ipLoc['lng']);
            if ($distanceIp > $THRESHOLD_IP_KM) {
                $reasons[] = "GPS vs IP terlalu jauh ({$distanceIp} km)";
            }
        }

        // 2) Speed check dibanding log terakhir
        $lastLog = TrackingLog::where('surat_jalan_id', $sj->id)
            ->orderByDesc('scanned_at')
            ->first();

        if ($lastLog && $lastLog->latitude && $lastLog->longitude && $lastLog->scanned_at) {
            $dist = $this->haversineKm((float)$lastLog->latitude, (float)$lastLog->longitude, $gpsLat, $gpsLng);
            $hours = max(0.0001, $lastLog->scanned_at->diffInSeconds(now()) / 3600);
            $speed = $dist / $hours;

            // biar gak false positive kalau jaraknya kecil
            if ($dist > 10 && $speed > $THRESHOLD_SPEED) {
                $reasons[] = "Perpindahan tidak wajar (≈" . round($speed) . " km/jam)";
            }
        }

        $isSuspicious = count($reasons) > 0;
        $reasonText = $isSuspicious ? implode(' | ', $reasons) : null;

        if ($isSuspicious && $MODE === 'block') {
            // ditolak total
            return response()->json([
                'ok' => false,
                'suspect' => true,
                'message' => 'Lokasi terdeteksi tidak valid (indikasi Fake GPS).',
                'reasons' => $reasons,
            ], 422);
        }

        // kalau mode flag: simpan log tapi ditandai
        $log = TrackingLog::create([
            'surat_jalan_id' => $sj->id,
            'latitude' => $gpsLat,
            'longitude' => $gpsLng,
            'ip_address' => $ip,
            'scanned_at' => now(),
            'scan_by' => $request->user()->id ?? null,

            'ip_latitude' => $ipLoc['lat'] ?? null,
            'ip_longitude' => $ipLoc['lng'] ?? null,
            'distance_ip_km' => $distanceIp,

            'is_suspicious' => $isSuspicious,
            'suspicious_reason' => $reasonText,
            'user_agent' => $ua,
        ]);

        // “nonaktifkan” efek scan kalau suspicious: jangan ubah status/flow
        if (!$isSuspicious) {
            // contoh: kalau scan valid, status jadi on_delivery
            // $sj->update(['status' => 'on_delivery']);
        }

        AuditTrail::create([
            'surat_jalan_id' => $sj->id,
            'user_id' => $request->user()->id,
            'action' => 'SCAN',
            'description' => $isSuspicious
                ? 'SCAN (SUSPECT): '.$reasonText
                : 'SCAN lokasi',
        ]);

        return response()->json([
            'ok' => true,
            'suspect' => $isSuspicious,
            'message' => $isSuspicious ? 'Lokasi masuk tapi ditandai SUSPECT.' : 'Lokasi berhasil direkam.',
            'reasons' => $reasons,
        ]);
    }
}
