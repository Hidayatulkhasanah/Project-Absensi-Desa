@extends('layouts.app')

@section('title', 'Kelola SPPD – SAPD Mekarsari')

@section('content')
    <div class="page-title">Kelola SPPD</div>
    <div class="page-sub">Daftar pengajuan surat perjalanan dinas. Untuk menambah, mengubah, atau memverifikasi pengajuan, gunakan Admin Panel.</div>

    <div class="card">
        <div id="sppd-list" class="empty">Memuat data...</div>
    </div>

    <script>
    (function () {
        const token = localStorage.getItem('auth_token');
        if (!token) { window.location.href = '/'; return; }

        const badge = (status) => {
            if (status === 'menunggu') return '<span class="badge bp">Menunggu</span>';
            if (status === 'disetujui') return '<span class="badge bap">Disetujui</span>';
            return '<span class="badge brj">Ditolak</span>';
        };

        fetch('/api/sppd', { headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' } })
            .then(res => {
                if (res.status === 401) { localStorage.clear(); window.location.href = '/'; return null; }
                return res.json();
            })
            .then(json => {
                if (!json) return;
                const list = json.data ?? [];
                const el = document.getElementById('sppd-list');
                if (list.length === 0) {
                    el.innerHTML = '<div class="empty">Belum ada data SPPD.</div>';
                    return;
                }
                el.outerHTML = `
                    <table>
                        <thead>
                            <tr><th>Pegawai</th><th>Nomor Surat</th><th>Tujuan</th><th>Periode</th><th>Status</th><th></th></tr>
                        </thead>
                        <tbody>
                            ${list.map(item => `
                                <tr>
                                    <td>
                                        <div style="font-weight:600">${item.nama ?? '-'}</div>
                                        <div style="font-size:11px;color:var(--hi)">${item.jabatan ?? ''}</div>
                                    </td>
                                    <td>${item.nomor_sppd}</td>
                                    <td>${item.tujuan}</td>
                                    <td style="white-space:nowrap">${item.tanggal_berangkat} &ndash; ${item.tanggal_kembali}</td>
                                    <td>${badge(item.status)}</td>
                                    <td><a href="/sppd/${item.id}" class="btn btn-s">Detail</a></td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>`;
            })
            .catch(() => {
                document.getElementById('sppd-list').textContent = 'Gagal memuat data SPPD.';
            });
    })();
    </script>
@endsection
