<?php

namespace App\Http\Controllers;

use App\Models\SuratJalan;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class ProofController extends Controller
{
    public function store(Request $request, $id)
    {
        $request->validate([
            'receiver_name' => 'required|string|max:255',
            'received_at'   => 'nullable|date',
            'photo'         => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $sj = SuratJalan::with('proof')->findOrFail($id);

        // simpan foto
        $path = $request->file('photo')->store('proofs', 'public');

        // kalau ada bukti lama, hapus file lamanya (optional)
        if ($sj->proof && $sj->proof->photo_path) {
            Storage::disk('public')->delete($sj->proof->photo_path);
        }

        // simpan/update proof (hasOne)
        $proof = $sj->proof()->updateOrCreate(
            ['surat_jalan_id' => $sj->id],
            [
                'receiver_name' => $request->receiver_name,
                'received_at'   => $request->received_at
                    ? Carbon::parse($request->received_at)
                    : now(),
                'photo_path'    => $path,
                'uploaded_by'   => $request->user()->id,
            ]
        );

        // update status jadi delivered
        $sj->update(['status' => 'delivered']);

        AuditTrail::create([
            'surat_jalan_id' => $sj->id,
            'user_id' => $request->user()->id,
            'action' => 'PROOF',
            'description' => 'Upload bukti serah terima & set delivered',
        ]);

        return response()->json([
            'ok' => true,
            'status' => $sj->status,
            'proof' => [
                'receiver_name' => $proof->receiver_name,
                'received_at' => optional($proof->received_at)->format('Y-m-d H:i:s'),
                'photo_url' => $proof->photo_path ? asset('storage/'.$proof->photo_path) : null,
            ],
        ]);
    }
}
