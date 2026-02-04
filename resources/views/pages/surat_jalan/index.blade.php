@extends('layouts.app')

@push('styles')
    <style>
        .dt-search {
            margin-bottom: .75rem;
        }
    </style>
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Surat Jalan</h4>
        <button class="btn btn-primary" id="btnCreate">Buat Surat Jalan</button>
    </div>

    <div class="card">
        <div class="card-body">

            {{-- Optional filter status (kalau controller support status filter) --}}
            <div class="row g-2 mb-3">
                <div class="col-md-3">
                    <select class="form-select" id="filterStatus">
                        <option value="">Semua Status</option>
                        <option value="created">created</option>
                        <option value="on_delivery">on_delivery</option>
                        <option value="delivered">delivered</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table id="tblSJ" class="table table-bordered table-striped nowrap w-100">
                    <thead class="table-light">
                        <tr>
                            <th width="60">No</th>
                            <th>Kode</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th>Creator</th>
                            <th width="160">Aksi</th>
                        </tr>
                    </thead>
                    {{-- tbody dikosongkan karena server-side --}}
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal QR -->
    <div class="modal fade" id="modalQR" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Surat Jalan Berhasil Dibuat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-2">
                        <div class="text-muted small">Kode Surat Jalan:</div>
                        <div class="fs-5 fw-bold" id="kodeText">-</div>
                    </div>

                    <div class="border rounded p-3 d-flex justify-content-center">
                        <div id="qrcode"></div>
                    </div>

                    <div class="small text-muted mt-2">
                        QR ini bisa di-scan untuk update lokasi.
                    </div>
                </div>

                <div class="modal-footer">
                    <button id="btnDetail" class="btn btn-outline-secondary" type="button">Lihat Detail</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Oke</button>
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

    <!-- Modal Edit -->
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Surat Jalan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="editId">

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="editStatus">
                            <option value="created">created</option>
                            <option value="on_delivery">on_delivery</option>
                            <option value="delivered">delivered</option>
                        </select>
                    </div>

                    <div id="editMsg"></div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-warning" id="btnSaveEdit">Simpan</button>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- QR generator -->
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

    <script>
        $(function() {
            const csrf = $('meta[name="csrf-token"]').attr('content');

            const table = $('#tblSJ').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                pageLength: 10,
                order: [
                    [3, 'desc']
                ],
                ajax: {
                    url: "{{ route('sj.datatable') }}",
                    type: "GET",
                    data: function(d) {
                        d.status = $('#filterStatus').val();
                    }
                },
                columns: [{
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'kode',
                        name: 'kode'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    }, // boleh HTML badge dari server
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'creator',
                        name: 'creator'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    } // HTML button
                ],
            });

            // filter reload
            $('#filterStatus').on('change', function() {
                table.ajax.reload();
            });

            // Create surat jalan (AJAX)
            $('#btnCreate').on('click', function() {
                $.ajax({
                    url: "{{ route('sj.store') }}",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': csrf
                    },
                    success: function(res) {
                        if (!res.ok) return alert('Gagal membuat surat jalan');

                        $('#kodeText').text(res.kode);
                        $('#btnDetail').data('id', res.id);

                        if (res.qr_url) {
                            $('#qrcode').html(
                                `<img class="img-fluid" style="max-width:220px" src="${res.qr_url}" alt="QR">`
                            );
                        } else {
                            $('#qrcode').empty();
                            new QRCode(document.getElementById("qrcode"), {
                                text: res.kode,
                                width: 220,
                                height: 220
                            });
                        }

                        table.ajax.reload(null, false);
                        new bootstrap.Modal('#modalQR').show();
                    },
                    error: function(xhr) {
                        alert('Error: ' + (xhr.responseJSON?.message ?? 'Unknown'));
                    }
                });
            });

            // klik tombol Edit dari table (event delegation)
            $(document).on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                const status = $(this).data('status');

                $('#editId').val(id);
                $('#editStatus').val(status);
                $('#editMsg').html('');

                new bootstrap.Modal('#modalEdit').show();
            });

            // submit edit
            $('#btnSaveEdit').on('click', function() {
                const id = $('#editId').val();
                const status = $('#editStatus').val();

                $.ajax({
                    url: "{{ url('/surat-jalan') }}/" + id,
                    method: "PUT",
                    headers: {
                        'X-CSRF-TOKEN': csrf
                    },
                    data: {
                        status
                    },

                    success: function(res) {
                        if (!res.ok) return $('#editMsg').html(
                            '<div class="alert alert-danger py-2 mb-0">Gagal update.</div>'
                        );

                        $('#editMsg').html(
                            '<div class="alert alert-success py-2 mb-0">Berhasil update ✅</div>'
                        );

                        // refresh datatable tanpa reset page
                        table.ajax.reload(null, false);

                        // auto close setelah sebentar (optional)
                        setTimeout(() => {
                            const editModalEl = document.getElementById('modalEdit');
                            const editModal = bootstrap.Modal.getInstance(editModalEl);
                            if (editModal) editModal.hide();
                        }, 700);
                    },

                    error: function(xhr) {
                        $('#editMsg').html('<div class="alert alert-danger py-2 mb-0">Error: ' +
                            (xhr.responseJSON?.message ?? 'Unknown') + '</div>');
                    }
                });
            });

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

            let map = null;
            let marker = null;

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

            document.getElementById('modalDetail').addEventListener('shown.bs.modal', function() {
                if (map) setTimeout(() => map.invalidateSize(), 150);
            });

            // Detail modal (server-side table => pakai event delegation)
            $('#btnDetail').on('click', function() {
                const id = $(this).data('id');
                if (!id) return alert('ID belum ada. Buat surat jalan dulu ya.');

                const qrModalEl = document.getElementById('modalQR');
                const qrModal = bootstrap.Modal.getInstance(qrModalEl);
                if (qrModal) qrModal.hide();

                $(document).trigger('open-detail', [id]);
            });

            $(document).on('click', '.btn-detail', function() {
                const id = $(this).data('id');
                $(document).trigger('open-detail', [id]);
            });

            $(document).on('open-detail', function(e, id) {
                $('#detailLoading').removeClass('d-none');
                $('#detailContent').addClass('d-none');
                $('#dLogs').html('');
                $('#dProofEmpty').removeClass('d-none');
                $('#dProofBox').addClass('d-none');
                $('#dPhoto').addClass('d-none').attr('src', '');

                $('#dQrImg').addClass('d-none').attr('src', '');
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
                        $('#dStatus').html(
                            `<span class="badge text-bg-${badge}">${d.status}</span>`);

                        if (d.tracking_logs && d.tracking_logs.length) {
                            const rows = d.tracking_logs.map(l => `
                        <tr>
                            <td>${l.scanned_at ?? '-'}</td>
                            <td>${l.latitude ?? '-'}</td>
                            <td>${l.longitude ?? '-'}</td>
                            <td>${l.ip ?? '-'}</td>
                        </tr>
                        `).join('');
                            $('#dLogs').html(rows);
                        } else {
                            $('#dLogs').html(
                                '<tr><td colspan="4" class="text-muted text-center">Belum ada log.</td></tr>'
                            );
                        }

                        if (d.proof) {
                            $('#dProofEmpty').addClass('d-none');
                            $('#dProofBox').removeClass('d-none');
                            $('#dReceiver').text(d.proof.receiver_name ?? '-');
                            $('#dReceivedAt').text(d.proof.received_at ?? '-');
                            if (d.proof.photo_url) $('#dPhoto').removeClass('d-none').attr(
                                'src', d.proof.photo_url);
                        }

                        if (d.qr_url) {
                            $('#dQrFallback').addClass('d-none');
                            $('#dQr').empty();
                            $('#dQrImg').attr('src', d.qr_url).removeClass('d-none');
                        } else {
                            $('#dQrImg').addClass('d-none').attr('src', '');
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
            });

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
        });
    </script>
@endpush
