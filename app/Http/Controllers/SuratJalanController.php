<?php

namespace App\Http\Controllers;

use App\Models\SuratJalan;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;

class SuratJalanController extends Controller
{
    public function index()
    {
        // DataTables server-side: data diambil via AJAX dari datatable()
        return view('pages.surat_jalan.index');
    }

    public function datatable(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $search = trim((string) $request->input('search.value', ''));

        // optional filter (kalau kamu pasang dropdown status di UI)
        $status = $request->input('status'); // created|on_delivery|delivered|null

        // mapping kolom DataTables -> kolom DB (whitelist biar aman)
        $columnsMap = [
            1 => 'surat_jalan.kode_surat_jalan',
            2 => 'surat_jalan.status',
            3 => 'surat_jalan.created_at',
            4 => 'users.name',
        ];

        $orderColumnIndex = (int) $request->input('order.0.column', 2);
        $orderDir = strtolower((string) $request->input('order.0.dir', 'desc'));
        $orderDir = in_array($orderDir, ['asc', 'desc'], true) ? $orderDir : 'desc';
        $orderBy  = $columnsMap[$orderColumnIndex] ?? 'surat_jalan.created_at';

        // base query
        $baseQuery = SuratJalan::query()
            ->leftJoin('users', 'users.id', '=', 'surat_jalan.created_by')
            ->select([
                'surat_jalan.id',
                'surat_jalan.kode_surat_jalan',
                'surat_jalan.status',
                'surat_jalan.created_at',
                DB::raw('COALESCE(users.name, "-") as creator_name'),
            ]);

        // total semua data (sebelum filter/search)
        $recordsTotal = (clone $baseQuery)->count(DB::raw('distinct surat_jalan.id'));

        // filter status (optional)
        if ($status && in_array($status, ['created', 'on_delivery', 'delivered'], true)) {
            $baseQuery->where('surat_jalan.status', $status);
        }

        // search
        if ($search !== '') {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('surat_jalan.kode_surat_jalan', 'like', "%{$search}%")
                  ->orWhere('surat_jalan.status', 'like', "%{$search}%")
                  ->orWhere('users.name', 'like', "%{$search}%");
            });
        }

        // total setelah filter/search
        $recordsFiltered = (clone $baseQuery)->count(DB::raw('distinct surat_jalan.id'));

        // paging + ordering
        $rows = $baseQuery
            ->orderBy($orderBy, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        // format output DataTables
        

        $data = $rows->map(function ($r) {
            $badge = match ($r->status) {
                'delivered' => 'success',
                'on_delivery' => 'warning',
                default => 'secondary',
            };

            return [
                'kode' => $r->kode_surat_jalan,
                'status' => '<span class="badge text-bg-'.$badge.'">'.$r->status.'</span>',
                'created_at' => optional($r->created_at)->format('H:i d-m-Y'),
                'creator' => $r->creator_name,
                'action' => "
                            <button type=\"button\" class=\"btn btn-sm btn-outline-primary btn-detail\" data-id=\"{$r->id}\">Detail</button>
                            <button type=\"button\" class=\"btn btn-sm btn-outline-warning btn-edit\" data-id=\"{$r->id}\" data-status=\"{$r->status}\">Edit</button>
                        ",
            ];
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function show($id)
    {
        $sj = SuratJalan::with([
            'trackingLogs' => fn($q) => $q->latest(),
            'proof'
        ])->findOrFail($id);

        return view('surat_jalan.show', compact('sj'));
    }

    public function detail($id)
    {
        $sj = SuratJalan::with([
            'trackingLogs' => fn($q) => $q->latest()->limit(20), // biar modal gak berat
            'proof'
        ])->findOrFail($id);

        return response()->json([
            'ok' => true,
            'data' => [
                'id' => $sj->id,
                'kode' => $sj->kode_surat_jalan,
                'status' => $sj->status,
                'created_at' => optional($sj->created_at)->format('H:i d-m-Y'),
                'qr_url' => $sj->qr_code_path ? asset('storage/'.$sj->qr_code_path) : null,
                
                'proof' => $sj->proof ? [
                    'receiver_name' => $sj->proof->receiver_name,
                    'photo_url' => $sj->proof->photo_path ? asset('storage/'.$sj->proof->photo_path) : null,
                    'received_at' => optional($sj->proof->received_at)->format('H:i d-m-Y'),
                ] : null,

                'tracking_logs' => $sj->trackingLogs->map(fn($log) => [
                    'scanned_at' => optional($log->scanned_at)->format('H:i d-m-Y'),
                    'latitude' => (string) $log->latitude,
                    'longitude' => (string) $log->longitude,
                    'ip' => $log->ip_address,
                ])->values()
            ]
        ]);
    }

    public function store(Request $request)
    {
        do {
            $kode = 'SJ-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            $exists = SuratJalan::where('kode_surat_jalan', $kode)->exists();
        } while ($exists);

        $sj = SuratJalan::create([
            'kode_surat_jalan' => $kode,
            'status' => 'created',
            'created_by' => $request->user()->id,
        ]);

        // ✅ Generate QR (SVG) + simpan ke storage publik
        try {
            $qr = QrCode::create($sj->kode_surat_jalan)->setSize(250);
            $writer = new SvgWriter();
            $result = $writer->write($qr);

            $fileName = 'qrcodes/' . $sj->kode_surat_jalan . '.svg'; // aman karena alnum + dash
            Storage::disk('public')->put($fileName, $result->getString());

            $sj->update(['qr_code_path' => $fileName]);
        } catch (\Throwable $e) {
            // kalau gagal, biarin null (nanti bisa fallback generate client)
            $sj->update(['qr_code_path' => null]);
        }

        AuditTrail::create([
            'surat_jalan_id' => $sj->id,
            'user_id' => $request->user()->id,
            'action' => 'CREATE',
            'description' => 'Membuat surat jalan baru'
        ]);

        return response()->json([
            'ok' => true,
            'id' => $sj->id,
            'kode' => $sj->kode_surat_jalan,
            'qr_url' => $sj->qr_code_path ? asset('storage/'.$sj->qr_code_path) : null,
        ]);
    }

    public function update(Request $request, $id)
    {
        $sj = SuratJalan::findOrFail($id);

        $request->validate([
            'status' => 'required|in:created,on_delivery,delivered'
        ]);

        $sj->update(['status' => $request->status]);

        AuditTrail::create([
            'surat_jalan_id' => $sj->id,
            'user_id' => $request->user()->id,
            'action' => 'EDIT',
            'description' => 'Update status surat jalan'
        ]);

        return response()->json(['ok' => true]);
    }
}
