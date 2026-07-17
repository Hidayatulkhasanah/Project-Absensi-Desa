@extends('layouts.app')

@section('title', 'Detail SPPD – SAPD Mekarsari')

@section('content')
    <div class="page-title">Detail SPPD</div>
    <div class="page-sub">&nbsp;</div>

    <div class="card card-p">
        <div id="sppd-detail" class="empty">Memuat data...</div>
    </div>

    <div style="margin-top:16px">
        <a href="{{ route('sppd.index') }}" class="btn btn-s">&larr; Kembali ke Daftar SPPD</a>
    </div>

    <script>
    (function () {
        const token = localStorage.getItem('auth_token');
        if (!token) { window.location.href = '/'; return; }

        const sppdId = {{ (int) $id }};
        const badge = (status) => {
            if (status === 'menunggu') return '<span class="badge bp">Menunggu</span>';
            if (status === 'disetujui') return '<span class="badge bap">Disetujui</span>';
            return '<span class="badge brj">Ditolak</span>';
        };
        const row = (label, val) => `
            <div class="info-row">
                <div class="info-label">${label}</div>
                <div class="info-val">${val}</div>
            </div>`;

        fetch('/api/sppd', { headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' } })
            .then(res => {
                if (res.status === 401) { localStorage.clear(); window.location.href = '/'; return null; }
                return res.json();
            })
            .then(json => {
                if (!json) return;
                const item = (json.data ?? []).find(s => s.id === sppdId);
                const el = document.getElementById('sppd-detail');
                if (!item) {
                    el.textContent = 'Data SPPD tidak ditemukan.';
                    return;
                }
                document.querySelector('.page-sub').textContent = item.nomor_sppd;
                el.className = '';
                el.innerHTML =
                    row('Pegawai', `${item.nama ?? '-'} &middot; ${item.jabatan ?? ''}`) +
                    row('Nomor Surat', item.nomor_sppd) +
                    row('Tujuan', item.tujuan) +
                    row('Keperluan', item.keperluan) +
                    row('Tanggal Berangkat', item.tanggal_berangkat) +
                    row('Tanggal Kembali', item.tanggal_kembali) +
                    row('Status', badge(item.status)) +
                    (item.keterangan ? row('Keterangan', item.keterangan) : '') +
                    (item.file_sppd ? row('File SPPD', `<a href="/storage/${item.file_sppd}" target="_blank" class="btn btn-p">Lihat File</a>`) : '');
            })
            .catch(() => {
                document.getElementById('sppd-detail').textContent = 'Gagal memuat data SPPD.';
            });
    })();
    </script>
@endsection
