@extends('layouts.app')

@push('styles')
    <style>
        #reader {
            width: 100%;
        }

        .mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        }
    </style>
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Scan QR Surat Jalan</h4>
        <a class="btn btn-outline-secondary" href="{{ route('sj.index') }}">Kembali</a>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header fw-semibold">Kamera</div>
                <div class="card-body">
                    <div id="reader" class="border rounded p-2"></div>
                    {{-- <div class="small text-muted mt-2">
                        Kalau kamera nggak muncul: cek permission browser + pastikan pakai HTTPS / localhost.
                    </div> --}}

                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-outline-primary" id="btnStart">Start</button>
                        <button class="btn btn-outline-danger" id="btnStop" disabled>Stop</button>
                        <select id="cameraSelect" class="form-select" style="max-width: 260px;"></select>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header fw-semibold">Hasil Scan</div>
                <div class="card-body">
                    <div class="mb-2">
                        <div class="text-muted small">Kode Surat Jalan</div>
                        <div class="fs-5 fw-bold mono" id="kode">-</div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <div class="text-muted small">Latitude</div>
                            <div class="mono" id="lat">-</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Longitude</div>
                            <div class="mono" id="lng">-</div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mb-3">
                        <button class="btn btn-outline-secondary" id="btnGetLoc">Ambil Lokasi</button>
                        <button class="btn btn-primary" id="btnSubmit" disabled>Kirim Lokasi</button>
                    </div>

                    <div id="scanResultBox" class="d-none">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-muted small">Kode</div>
                                <div class="fs-5 fw-bold" id="rKode">-</div>

                                <div class="mt-2" id="rStatus">-</div>

                                <div class="mt-3">
                                    <button class="btn btn-outline-primary" id="btnOpenDetail" type="button" disabled>
                                        Buka Detail
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="msg" class="mt-3"></div>

                    <hr>

                    <div class="small text-muted">
                        Tips: setelah QR terbaca, klik <b>Ambil Lokasi</b> (atau otomatis), lalu <b>Kirim Lokasi</b>.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Detail Surat Jalan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="detailLoading" class="text-muted">Loading...</div>

                    <div id="detailContent" class="d-none">
                        <div class="row g-3 align-items-start">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <div class="text-muted small">Kode</div>
                                    <div class="fs-5 fw-bold" id="dKode">-</div>
                                </div>

                                <div class="mb-3">
                                    <span id="dStatus"></span>
                                    <span class="text-muted small ms-2" id="dCreatedAt">-</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small mb-1">QR Code</div>
                                <div class="border rounded p-3 d-flex justify-content-center">
                                    <img id="dQrImg" class="img-fluid" style="max-width:220px" alt="QR Code">
                                </div>
                                <div id="dQrFallback" class="d-none mt-2">
                                    <div class="text-muted small">QR belum tersedia (fallback generate client).</div>
                                    <div id="dQr"></div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div id="mapSection" class="d-none">
                            <h6 class="mb-2">Lokasi Terakhir (Map)</h6>
                            <div class="border rounded overflow-hidden">
                                <div id="mapLast"></div>
                            </div>
                            <div class="small text-muted mt-2" id="mapHint">Belum ada lokasi.</div>
                            <hr>
                        </div>

                        <h6 class="mb-2">Tracking Logs (terbaru)</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Lat</th>
                                        <th>Lng</th>
                                        <th>IP</th>
                                    </tr>
                                </thead>
                                <tbody id="dLogs"></tbody>
                            </table>
                        </div>

                        <hr>

                        <h6 class="mb-2">Bukti Serah Terima</h6>
                        @if (in_array(auth()->user()->role ?? '', ['admin', 'superuser']))
                            <div class="mt-2" id="proofUploadWrap">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <div class="fw-semibold mb-2">Upload Bukti</div>

                                        <form id="formProof" enctype="multipart/form-data">
                                            <input type="hidden" id="proofSjId" name="sj_id">

                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <label class="form-label">Nama Penerima</label>
                                                    <input type="text" class="form-control" name="receiver_name"
                                                        required>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">Tanggal Terima</label>
                                                    <input type="datetime-local" class="form-control" name="received_at">
                                                    <div class="small text-muted">Boleh kosong (auto now).</div>
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label">Foto Bukti</label>
                                                    <input type="file" class="form-control" name="photo"
                                                        accept="image/*" required>
                                                    <div class="small text-muted">jpg/png/webp max 2MB.</div>
                                                </div>
                                            </div>

                                            <div id="proofMsg" class="mt-2"></div>

                                            <div class="mt-3 d-flex gap-2">
                                                <button type="submit" class="btn btn-success" id="btnUploadProof">
                                                    Upload & Set Delivered
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div id="dProofEmpty" class="text-muted small">Belum ada bukti.</div>

                        <div id="dProofBox" class="d-none">
                            <div class="mb-1"><b>Penerima:</b> <span id="dReceiver">-</span></div>
                            <div class="mb-2"><b>Diterima:</b> <span id="dReceivedAt">-</span></div>
                            <img id="dPhoto" class="img-fluid rounded border d-none" alt="Bukti Foto">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- QR Scanner -->
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

    <script>
        $(function() {
            const csrf = $('meta[name="csrf-token"]').attr('content');

            let html5QrCode = null;
            let currentCamId = null;

            let currentKode = null;
            let currentLat = null;
            let currentLng = null;

            let map = null;
            let marker = null;

            function resetMap() {
                if (!map) return;

                // close popup kalau ada
                if (marker) {
                    marker.closePopup?.();
                    map.removeLayer(marker);
                    marker = null;
                }

                // balik ke default
                map.setView([-2.5, 118.0], 4);
                setTimeout(() => map.invalidateSize(), 150);
            }

            function initMapIfNeeded() {
                if (map) return;

                map = L.map('mapLast', {
                    zoomControl: true
                });
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(map);

                // default view (Indonesia)
                map.setView([-2.5, 118.0], 4);
            }

            function setMapLocation(lat, lng, label) {
                initMapIfNeeded();

                const ll = [lat, lng];
                map.setView(ll, 16);

                if (!marker) {
                    marker = L.marker(ll).addTo(map);
                } else {
                    marker.setLatLng(ll);
                }

                if (label) marker.bindPopup(label);

                // ⚠️ Penting: map di dalam modal butuh invalidateSize
                setTimeout(() => map.invalidateSize(), 150);
            }

            function setMsg(type, text) {
                $('#msg').html(`<div class="alert alert-${type} py-2 mb-0">${text}</div>`);
            }

            function setKode(kode) {
                currentKode = kode;
                $('#kode').text(kode || '-');
                $('#btnSubmit').prop('disabled', !(currentKode && currentLat && currentLng));
            }

            function setLocation(lat, lng) {
                currentLat = lat;
                currentLng = lng;
                $('#lat').text(lat ?? '-');
                $('#lng').text(lng ?? '-');
                $('#btnSubmit').prop('disabled', !(currentKode && currentLat && currentLng));
            }

            function getLocation() {
                if (!navigator.geolocation) {
                    setMsg('danger', 'Browser kamu nggak support Geolocation.');
                    return;
                }

                setMsg('info', 'Mengambil lokasi...');
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        setLocation(pos.coords.latitude, pos.coords.longitude);
                        setMsg('success', 'Lokasi didapat ✅');
                    },
                    (err) => {
                        setMsg('warning', 'Gagal ambil lokasi. Aktifkan izin lokasi ya.');
                    }, {
                        enableHighAccuracy: true,
                        timeout: 15000,
                        maximumAge: 0
                    }
                );
            }

            async function loadCameras() {
                try {
                    const devices = await Html5Qrcode.getCameras();
                    if (!devices || devices.length === 0) {
                        setMsg('danger', 'Kamera tidak ditemukan.');
                        return;
                    }

                    $('#cameraSelect').empty();
                    devices.forEach((d, i) => {
                        $('#cameraSelect').append(
                            `<option value="${d.id}">${d.label || ('Camera ' + (i+1))}</option>`);
                    });

                    currentCamId = devices[0].id;
                    $('#cameraSelect').val(currentCamId);
                } catch (e) {
                    setMsg('danger', 'Tidak bisa load kamera. Cek permission.');
                }
            }

            async function startScanner() {
                if (!currentCamId) return;

                if (!html5QrCode) html5QrCode = new Html5Qrcode("reader");

                try {
                    await html5QrCode.start(
                        currentCamId, {
                            fps: 10,
                            qrbox: {
                                width: 250,
                                height: 250
                            }
                        },
                        (decodedText) => {
                            // QR berisi kode surat jalan
                            if (decodedText && decodedText !== currentKode) {
                                setKode(decodedText);
                                setMsg('success', 'QR terbaca ✅ Sekarang ambil lokasi lalu kirim.');
                                // auto ambil lokasi sekali
                                getLocation();
                            }
                        },
                        (err) => {
                            /* ignore */
                        }
                    );

                    $('#btnStart').prop('disabled', true);
                    $('#btnStop').prop('disabled', false);
                    setMsg('info', 'Scanner aktif. Arahkan kamera ke QR.');
                } catch (e) {
                    setMsg('danger', 'Gagal start scanner. Cek izin kamera / HTTPS.');
                }
            }

            async function stopScanner() {
                if (!html5QrCode) return;
                try {
                    await html5QrCode.stop();
                    $('#btnStart').prop('disabled', false);
                    $('#btnStop').prop('disabled', true);
                    setMsg('secondary', 'Scanner berhenti.');
                } catch (e) {
                    // noop
                }
            }

            // events
            $('#btnStart').on('click', startScanner);
            $('#btnStop').on('click', stopScanner);

            $('#cameraSelect').on('change', async function() {
                const newId = $(this).val();
                currentCamId = newId;

                // restart jika sedang running
                if (html5QrCode && html5QrCode.isScanning) {
                    await stopScanner();
                    await startScanner();
                }
            });

            $('#btnGetLoc').on('click', getLocation);

            $('#btnSubmit').on('click', function() {
                if (!currentKode || currentLat == null || currentLng == null) {
                    return setMsg('warning', 'Kode / lokasi belum lengkap.');
                }

                setMsg('info', 'Mengirim lokasi...');

                $.ajax({
                    url: "{{ route('scan.store') }}",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': csrf
                    },
                    data: {
                        kode: currentKode,
                        latitude: currentLat,
                        longitude: currentLng
                    },
                    success: function(res) {
                        if (!res.ok) return setMsg('danger', 'Gagal update lokasi.');

                        $('#scanResultBox').removeClass('d-none');
                        $('#rKode').text(res.kode);

                        let badge = 'secondary';
                        if (res.status === 'delivered') badge = 'success';
                        else if (res.status === 'on_delivery') badge = 'warning';
                        $('#rStatus').html(
                            `<span class="badge text-bg-${badge}">${res.status}</span>`);

                        $('#btnOpenDetail').data('id', res.id).prop('disabled', false);
                        setMsg('success', 'Lokasi berhasil diupdate ✅');
                    },
                    error: function(xhr) {
                        setMsg('danger', 'Error: ' + (xhr.responseJSON?.message ?? 'Unknown'));
                    }
                });
            });

            document.getElementById('modalDetail').addEventListener('shown.bs.modal', function() {
                if (map) setTimeout(() => map.invalidateSize(), 150);
            });

            $('#btnOpenDetail').on('click', function() {
                const id = $(this).data('id');
                if (!id) return;
                openDetail(id);
            });

            function openDetail(id) {
                $('#detailLoading').removeClass('d-none');
                $('#detailContent').addClass('d-none');
                $('#dLogs').html('');
                $('#dProofEmpty').removeClass('d-none');
                $('#dProofBox').addClass('d-none');
                $('#dPhoto').addClass('d-none').attr('src', '');

                $('#dQrImgBox').addClass('d-none');
                $('#dQrImg').attr('src', '');
                $('#dQr').empty();
                $('#dQrFallback').addClass('d-none');

                $('#mapSection').addClass('d-none');
                $('#mapHint').text('Loading lokasi...');
                resetMap();
                new bootstrap.Modal('#modalDetail').show();

                $.ajax({
                    url: "{{ url('/surat-jalan') }}/" + id + "/detail",
                    method: "GET",
                    success: function(res) {
                        if (!res.ok) return alert('Gagal ambil detail');
                        const d = res.data;
                        $('#proofSjId').val(d.id);
                        $('#dKode').text(d.kode);
                        $('#dCreatedAt').text(d.created_at);

                        let badge = 'secondary';
                        if (d.status === 'delivered') badge = 'success';
                        else if (d.status === 'on_delivery') badge = 'warning';
                        $('#dStatus').html(`<span class="badge text-bg-${badge}">${d.status}</span>`);

                        // logs
                        if (d.tracking_logs && d.tracking_logs.length) {
                            $('#dLogs').html(d.tracking_logs.map(l => `
                                <tr>
                                    <td>${l.scanned_at ?? '-'}</td>
                                    <td>${l.latitude ?? '-'}</td>
                                    <td>${l.longitude ?? '-'}</td>
                                    <td>${l.ip ?? '-'}</td>
                                </tr>
                            `).join(''));
                        } else {
                            $('#dLogs').html(
                                '<tr><td colspan="4" class="text-muted text-center">Belum ada log.</td></tr>'
                            );
                        }

                        // proof
                        if (d.proof) {
                            $('#dProofEmpty').addClass('d-none');
                            $('#dProofBox').removeClass('d-none');
                            $('#dReceiver').text(d.proof.receiver_name ?? '-');
                            $('#dReceivedAt').text(d.proof.received_at ?? '-');
                            if (d.proof.photo_url) $('#dPhoto').removeClass('d-none').attr('src', d
                                .proof.photo_url);
                        }

                        // QR
                        if (d.qr_url) {
                            $('#dQr').empty();
                            $('#dQrFallback').addClass('d-none');
                            $('#dQrImg').attr('src', d.qr_url);
                            $('#dQrImgBox').removeClass('d-none');
                        } else {
                            $('#dQrImgBox').addClass('d-none');
                            $('#dQrImg').attr('src', '');
                            $('#dQr').empty();
                            new QRCode(document.getElementById("dQr"), {
                                text: d.kode,
                                width: 200,
                                height: 200
                            });
                            $('#dQrFallback').removeClass('d-none');
                        }

                        // ====== MAP: lokasi terakhir ======
                        if (d.tracking_logs && d.tracking_logs.length) {
                            const last = d.tracking_logs[0];
                            const lat = parseFloat(last.latitude);
                            const lng = parseFloat(last.longitude);

                            if (!isNaN(lat) && !isNaN(lng)) {
                                $('#mapSection').removeClass('d-none'); // tampilkan map

                                initMapIfNeeded();

                                const label = `
                                    <div class="p-title">${d.kode}</div>
                                    <div class="p-sub">${last.scanned_at ?? '-'}</div>
                                `;

                                $('#mapHint').text(`Lat: ${lat}, Lng: ${lng}`);
                                setMapLocation(lat, lng, label);
                            } else {
                                // koordinat invalid => tetep hide map
                                $('#mapSection').addClass('d-none');
                            }
                        } else {
                            // no logs => hide map
                            $('#mapSection').addClass('d-none');
                        }

                        $('#detailLoading').addClass('d-none');
                        $('#detailContent').removeClass('d-none');
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                        alert('Error detail: ' + (xhr.responseJSON?.message ?? 'Unknown'));
                    }
                });
            }

            $(document).on('submit', '#formProof', function(e) {
                e.preventDefault();

                const id = $('#proofSjId').val();
                if (!id) return alert('ID Surat Jalan tidak ditemukan');

                const fd = new FormData(this);

                $('#btnUploadProof').prop('disabled', true);
                $('#proofMsg').html('<div class="text-muted small">Uploading...</div>');

                $.ajax({
                    url: "{{ url('/surat-jalan') }}/" + id + "/proof",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': csrf
                    },
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (!res.ok) {
                            $('#proofMsg').html(
                                '<div class="alert alert-danger py-2 mb-0">Gagal upload.</div>'
                            );
                            return;
                        }

                        // update status badge di modal jadi delivered
                        $('#dStatus').html(
                            '<span class="badge text-bg-success">delivered</span>');

                        // update tampilan proof di modal
                        $('#dProofEmpty').addClass('d-none');
                        $('#dProofBox').removeClass('d-none');
                        $('#dReceiver').text(res.proof.receiver_name ?? '-');
                        $('#dReceivedAt').text(res.proof.received_at ?? '-');

                        if (res.proof.photo_url) {
                            $('#dPhoto').removeClass('d-none').attr('src', res.proof.photo_url);
                        }

                        $('#proofMsg').html(
                            '<div class="alert alert-success py-2 mb-0">Upload berhasil ✅</div>'
                        );

                        // reload datatable kalau ada (di halaman index)
                        if (window.sjTable) window.sjTable.ajax.reload(null, false);
                        // kalau kamu belum set, bisa juga pakai variabel table kamu langsung:
                        // table.ajax.reload(null, false);

                        // reset form
                        $('#formProof')[0].reset();
                    },
                    error: function(xhr) {
                        $('#proofMsg').html(
                            '<div class="alert alert-danger py-2 mb-0">Error: ' + (xhr
                                .responseJSON?.message ?? 'Unknown') + '</div>');
                    },
                    complete: function() {
                        $('#btnUploadProof').prop('disabled', false);
                    }
                });
            });

            // init
            loadCameras();
        });
    </script>
@endpush
{{-- x --}}
