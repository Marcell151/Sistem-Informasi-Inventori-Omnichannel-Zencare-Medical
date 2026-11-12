import re

def update_bab3_tables(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        c = f.read()

    # 1. Update Table 3.17 sesi_opname
    old_sesi_tbody = """                            <tr>

                                <td class="border border-black px-2 py-1 text-center font-bold">3</td>

                                <td class="border border-black px-2 py-1 font-mono font-semibold">status</td>

                                <td class="border border-black px-2 py-1">Enum</td>

                                <td class="border border-black px-2 py-1 text-center">-</td>

                                <td class="border border-black px-2 py-1">Tahapan siklus sesi audit ('Aktif', 'Selesai', 'Dibatalkan').</td>

                            </tr>

                            <tr>

                                <td class="border border-black px-2 py-1 text-center font-bold">4</td>

                                <td class="border border-black px-2 py-1 font-mono font-semibold">dibekukan_oleh <span class="text-blue-700 font-bold font-sans text-[10px]">(FK)</span></td>

                                <td class="border border-black px-2 py-1">Integer</td>

                                <td class="border border-black px-2 py-1 text-center">11</td>

                                <td class="border border-black px-2 py-1">Foreign Key ke users.id_user (Superadmin pembeku sistem operasional).</td>

                            </tr>"""

    new_sesi_tbody = """                            <tr>

                                <td class="border border-black px-2 py-1 text-center font-bold">3</td>

                                <td class="border border-black px-2 py-1 font-mono font-semibold">status</td>

                                <td class="border border-black px-2 py-1">Enum</td>

                                <td class="border border-black px-2 py-1 text-center">-</td>

                                <td class="border border-black px-2 py-1">Status ketersediaan sesi pemeriksaan ('Aktif', 'Selesai', 'Dibatalkan').</td>

                            </tr>

                            <tr>

                                <td class="border border-black px-2 py-1 text-center font-bold">4</td>

                                <td class="border border-black px-2 py-1 font-mono font-semibold">tahap</td>

                                <td class="border border-black px-2 py-1">Enum</td>

                                <td class="border border-black px-2 py-1 text-center">-</td>

                                <td class="border border-black px-2 py-1">Tahapan transisi alur kerja operasional ('Penghitungan', 'Verifikasi', 'Selesai').</td>

                            </tr>

                            <tr>

                                <td class="border border-black px-2 py-1 text-center font-bold">5</td>

                                <td class="border border-black px-2 py-1 font-mono font-semibold">dibekukan_oleh <span class="text-blue-700 font-bold font-sans text-[10px]">(FK)</span></td>

                                <td class="border border-black px-2 py-1">Integer</td>

                                <td class="border border-black px-2 py-1 text-center">11</td>

                                <td class="border border-black px-2 py-1">Foreign Key ke users.id_user (Superadmin pengotorisasi freeze lock).</td>

                            </tr>"""

    # Also check the rest of sesi_opname to re-number and add ditutup_hitung_oleh
    old_sesi_dibekukan_pada = """                            <tr>

                                <td class="border border-black px-2 py-1 text-center font-bold">5</td>

                                <td class="border border-black px-2 py-1 font-mono font-semibold">dibekukan_pada</td>

                                <td class="border border-black px-2 py-1">Timestamp</td>

                                <td class="border border-black px-2 py-1 text-center">-</td>

                                <td class="border border-black px-2 py-1">Waktu pembekuan sistem transaksi kasir dan e-commerce dieksekusi.</td>

                            </tr>

                            <tr>

                                <td class="border border-black px-2 py-1 text-center font-bold">6</td>

                                <td class="border border-black px-2 py-1 font-mono font-semibold">dibuka_oleh <span class="text-blue-700 font-bold font-sans text-[10px]">(FK)</span></td>

                                <td class="border border-black px-2 py-1">Integer</td>

                                <td class="border border-black px-2 py-1 text-center">11</td>

                                <td class="border border-black px-2 py-1">Foreign Key ke users.id_user (Superadmin pembuka kembali transaksi).</td>

                            </tr>

                            <tr>

                                <td class="border border-black px-2 py-1 text-center font-bold">7</td>

                                <td class="border border-black px-2 py-1 font-mono font-semibold">dibuka_pada</td>

                                <td class="border border-black px-2 py-1">Timestamp</td>

                                <td class="border border-black px-2 py-1 text-center">-</td>

                                <td class="border border-black px-2 py-1">Waktu normalisasi sistem transaksi diaktifkan kembali oleh Superadmin.</td>

                            </tr>

                            <tr>

                                <td class="border border-black px-2 py-1 text-center font-bold">8</td>

                                <td class="border border-black px-2 py-1 font-mono font-semibold">catatan</td>

                                <td class="border border-black px-2 py-1">Text</td>

                                <td class="border border-black px-2 py-1 text-center">-</td>

                                <td class="border border-black px-2 py-1">Catatan manajerial hasil pelaksanaan audit stok opname.</td>

                            </tr>"""

    new_sesi_dibekukan_pada = """                            <tr>

                                <td class="border border-black px-2 py-1 text-center font-bold">6</td>

                                <td class="border border-black px-2 py-1 font-mono font-semibold">dibekukan_pada</td>

                                <td class="border border-black px-2 py-1">Timestamp</td>

                                <td class="border border-black px-2 py-1 text-center">-</td>

                                <td class="border border-black px-2 py-1">Waktu pembekuan sistem transaksi kasir dan e-commerce dieksekusi.</td>

                            </tr>

                            <tr>

                                <td class="border border-black px-2 py-1 text-center font-bold">7</td>

                                <td class="border border-black px-2 py-1 font-mono font-semibold">ditutup_hitung_oleh <span class="text-blue-700 font-bold font-sans text-[10px]">(FK)</span></td>

                                <td class="border border-black px-2 py-1">Integer</td>

                                <td class="border border-black px-2 py-1 text-center">11</td>

                                <td class="border border-black px-2 py-1">Foreign Key ke users.id_user (Admin/Superadmin pemverifikasi peralihan fase).</td>

                            </tr>

                            <tr>

                                <td class="border border-black px-2 py-1 text-center font-bold">8</td>

                                <td class="border border-black px-2 py-1 font-mono font-semibold">ditutup_hitung_pada</td>

                                <td class="border border-black px-2 py-1">Timestamp</td>

                                <td class="border border-black px-2 py-1 text-center">-</td>

                                <td class="border border-black px-2 py-1">Waktu penguncian lembar hitung fisik kasir dan pembukaan verifikasi selisih.</td>

                            </tr>

                            <tr>

                                <td class="border border-black px-2 py-1 text-center font-bold">9</td>

                                <td class="border border-black px-2 py-1 font-mono font-semibold">dibuka_oleh <span class="text-blue-700 font-bold font-sans text-[10px]">(FK)</span></td>

                                <td class="border border-black px-2 py-1">Integer</td>

                                <td class="border border-black px-2 py-1 text-center">11</td>

                                <td class="border border-black px-2 py-1">Foreign Key ke users.id_user (Superadmin penutup sesi dan pembuka transaksi).</td>

                            </tr>

                            <tr>

                                <td class="border border-black px-2 py-1 text-center font-bold">10</td>

                                <td class="border border-black px-2 py-1 font-mono font-semibold">dibuka_pada</td>

                                <td class="border border-black px-2 py-1">Timestamp</td>

                                <td class="border border-black px-2 py-1 text-center">-</td>

                                <td class="border border-black px-2 py-1">Waktu normalisasi sistem transaksi diaktifkan kembali oleh Superadmin.</td>

                            </tr>

                            <tr>

                                <td class="border border-black px-2 py-1 text-center font-bold">11</td>

                                <td class="border border-black px-2 py-1 font-mono font-semibold">catatan</td>

                                <td class="border border-black px-2 py-1">Text</td>

                                <td class="border border-black px-2 py-1 text-center">-</td>

                                <td class="border border-black px-2 py-1">Catatan manajerial dan alasan evaluasi hasil stok opname.</td>

                            </tr>"""

    c = c.replace(old_sesi_tbody, new_sesi_tbody)
    c = c.replace(old_sesi_dibekukan_pada, new_sesi_dibukakan_pada if 'new_sesi_dibukakan_pada' in locals() else new_sesi_dibekukan_pada)

    # 2. Update Table 3.18 detail_opname_batch
    old_batch_stok = """                            <tr>

                                <td class="border border-black px-2 py-1 text-center font-bold">10</td>

                                <td class="border border-black px-2 py-1 font-mono font-semibold">stok_sistem_fisik</td>

                                <td class="border border-black px-2 py-1">Integer</td>

                                <td class="border border-black px-2 py-1 text-center">11</td>

                                <td class="border border-black px-2 py-1">Total stok buku fisik sistem saat pembekuan freeze lock (snapshot).</td>

                            </tr>

                            <tr>

                                <td class="border border-black px-2 py-1 text-center font-bold">11</td>

                                <td class="border border-black px-2 py-1 font-mono font-semibold">hitung_fisik</td>

                                <td class="border border-black px-2 py-1">Integer</td>

                                <td class="border border-black px-2 py-1 text-center">11</td>

                                <td class="border border-black px-2 py-1">Jumlah kuantitas riil yang ditemukan petugas kasir/gudang di rak toko.</td>

                            </tr>"""

    new_batch_stok = """                            <tr>

                                <td class="border border-black px-2 py-1 text-center font-bold">10</td>

                                <td class="border border-black px-2 py-1 font-mono font-semibold">stok_sistem_fisik</td>

                                <td class="border border-black px-2 py-1">Integer</td>

                                <td class="border border-black px-2 py-1 text-center">11</td>

                                <td class="border border-black px-2 py-1">Total stok buku fisik sistem saat pembekuan freeze lock (snapshot).</td>

                            </tr>

                            <tr>

                                <td class="border border-black px-2 py-1 text-center font-bold">11</td>

                                <td class="border border-black px-2 py-1 font-mono font-semibold">fisik_baik</td>

                                <td class="border border-black px-2 py-1">Integer</td>

                                <td class="border border-black px-2 py-1 text-center">11</td>

                                <td class="border border-black px-2 py-1">Kuantitas fisik riil di rak yang ditemukan dalam kondisi baik (layak jual).</td>

                            </tr>

                            <tr>

                                <td class="border border-black px-2 py-1 text-center font-bold">12</td>

                                <td class="border border-black px-2 py-1 font-mono font-semibold">fisik_rusak</td>

                                <td class="border border-black px-2 py-1">Integer</td>

                                <td class="border border-black px-2 py-1 text-center">11</td>

                                <td class="border border-black px-2 py-1">Kuantitas fisik riil di rak yang ditemukan dalam kondisi rusak / kemasan cacat.</td>

                            </tr>

                            <tr>

                                <td class="border border-black px-2 py-1 text-center font-bold">13</td>

                                <td class="border border-black px-2 py-1 font-mono font-semibold">fisik_expired</td>

                                <td class="border border-black px-2 py-1">Integer</td>

                                <td class="border border-black px-2 py-1 text-center">11</td>

                                <td class="border border-black px-2 py-1">Kuantitas fisik riil di rak yang telah melewati tanggal kedaluwarsa.</td>

                            </tr>

                            <tr>

                                <td class="border border-black px-2 py-1 text-center font-bold">14</td>

                                <td class="border border-black px-2 py-1 font-mono font-semibold">status_hitung</td>

                                <td class="border border-black px-2 py-1">Enum</td>

                                <td class="border border-black px-2 py-1 text-center">-</td>

                                <td class="border border-black px-2 py-1">Status progres hitung kasir ('Belum', 'Sudah') untuk simpan bertahap.</td>

                            </tr>

                            <tr>

                                <td class="border border-black px-2 py-1 text-center font-bold">15</td>

                                <td class="border border-black px-2 py-1 font-mono font-semibold">hitung_fisik</td>

                                <td class="border border-black px-2 py-1">Integer</td>

                                <td class="border border-black px-2 py-1 text-center">11</td>

                                <td class="border border-black px-2 py-1">Total hitungan riil kasir (akumulasi otomatis: fisik_baik + fisik_rusak + fisik_expired).</td>

                            </tr>"""

    c = c.replace(old_batch_stok, new_batch_stok)

    # Re-number rows 12..20 to 16..24 in detail_opname_batch
    old_rest = """                            <tr>

                                <td class="border border-black px-2 py-1 text-center font-bold">12</td>

                                <td class="border border-black px-2 py-1 font-mono font-semibold">selisih</td>"""

    new_rest = """                            <tr>

                                <td class="border border-black px-2 py-1 text-center font-bold">16</td>

                                <td class="border border-black px-2 py-1 font-mono font-semibold">selisih</td>"""
    c = c.replace(old_rest, new_rest)

    for i in range(13, 21):
        old_row = f'<td class="border border-black px-2 py-1 text-center font-bold">{i}</td>'
        new_row = f'<td class="border border-black px-2 py-1 text-center font-bold">{i+4}</td>'
        # replace only the first occurrence in detail_opname_batch
        pos_table = c.find('Tabel 3.18 Tabel detail_opname_batch')
        if pos_table != -1:
            part1 = c[:pos_table]
            part2 = c[pos_table:]
            part2 = part2.replace(old_row, new_row, 1)
            c = part1 + part2

    # Clean audit term in description
    c = c.replace(
        'Foreign Key ke users.id_user (staf pelaksana pencacahan fisik).',
        'Foreign Key ke users.id_user (Kasir pelaksana pencacahan fisik rak).'
    )
    c = c.replace(
        'Foreign Key ke users.id_user (pejabat penyetuju mutasi saldo).',
        'Foreign Key ke users.id_user (Admin Toko / Superadmin verifikator mutasi selisih).'
    )
    c = c.replace(
        'demi kebutuhan audit kepatuhan.',
        'demi kebutuhan pemantauan kepatuhan logistik.'
    )

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(c)
    print(f"Updated Kamus Data Tables in {filepath}")

update_bab3_tables('laporan_ta/BAB_III_ZENCARE_MEDICAL.html')
update_bab3_tables('laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html')

