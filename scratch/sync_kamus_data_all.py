import re

# Data definitions
sesi_opname_cols = [
    ("id_sesi_opname", "Integer", "11", "PK", "Primary Key, Auto Increment. ID unik sesi pelaksanaan stok opname."),
    ("kode_sesi", "Varchar", "50", "", "Kode dokumen resmi sesi stok opname (OPN-YYYYMMDD-XX)."),
    ("status", "Enum", "-", "", "Status sesi pemeriksaan persediaan ('Aktif', 'Selesai', 'Dibatalkan')."),
    ("tahap", "Enum", "-", "", "Tahapan operasional aktif ('Penghitungan', 'Verifikasi', 'Selesai')."),
    ("dibekukan_oleh", "Integer", "11", "FK", "Foreign Key ke users.id_user (Superadmin pengotorisasi freeze lock)."),
    ("dibekukan_pada", "Timestamp", "-", "", "Waktu pembekuan sistem transaksi kasir dan pesanan daring dieksekusi."),
    ("ditutup_hitung_oleh", "Integer", "11", "FK", "Foreign Key ke users.id_user (Kasir penutup sesi hitung fisik rak)."),
    ("ditutup_hitung_pada", "Timestamp", "-", "", "Waktu penguncian lembar hitung fisik kasir dan pembukaan tahap verifikasi selisih."),
    ("dibuka_oleh", "Integer", "11", "FK", "Foreign Key ke users.id_user (Superadmin penutup sesi opname dan pelepas freeze lock)."),
    ("dibuka_pada", "Timestamp", "-", "", "Waktu normalisasi sistem transaksi diaktifkan kembali oleh Superadmin."),
    ("catatan", "Text", "-", "", "Catatan manajerial mengenai ruang lingkup pelaksanaan stok opname.")
]

detail_opname_batch_cols = [
    ("id_detail_opname", "Integer", "11", "PK", "Primary Key, Auto Increment. ID lembar kerja pencacahan per batch."),
    ("id_sesi", "Integer", "11", "FK", "Foreign Key merujuk ke dokumen sesi_opname.id_sesi_opname."),
    ("id_batch", "Integer", "11", "FK", "Foreign Key merujuk ke stok_batch.id_stok_batch yang dicacah fisiknya."),
    ("id_variasi", "Integer", "11", "FK", "Foreign Key merujuk ke tabel produk_variasi.id_variasi."),
    ("jenis_komoditas", "Enum", "-", "", "Klasifikasi jenis komoditas ('Obat', 'Alkes')."),
    ("satuan_tipe", "Enum", "-", "", "Tipe satuan pemeriksaan persediaan ('besar', 'kecil')."),
    ("nama_satuan", "Varchar", "50", "", "Label satuan fisik barang yang dihitung (Pcs, Box, Strip, Tube, Unit)."),
    ("stok_tersedia_sistem", "Integer", "11", "", "Saldo persediaan aktif bebas menurut catatan buku sistem."),
    ("stok_reserved", "Integer", "11", "", "Kuantitas persediaan yang sedang terkunci pesanan daring."),
    ("stok_sistem_fisik", "Integer", "11", "", "Total saldo buku fisik yang harus ditemukan di rak toko (snapshot sistem)."),
    ("fisik_baik", "Integer", "11", "", "Kuantitas fisik riil di rak yang ditemukan dalam kondisi baik (layak jual)."),
    ("fisik_rusak", "Integer", "11", "", "Kuantitas fisik riil di rak yang ditemukan dalam kondisi rusak / cacat kemasan."),
    ("fisik_expired", "Integer", "11", "", "Kuantitas fisik riil di rak yang telah melewati tanggal kedaluwarsa."),
    ("status_hitung", "Enum", "-", "", "Status progres hitung fisik ('Belum', 'Sudah') untuk mendukung simpan bertahap."),
    ("hitung_fisik", "Integer", "11", "", "Total hitungan riil (kalkulasi otomatis: fisik_baik + fisik_rusak + fisik_expired)."),
    ("selisih", "Integer", "11", "", "Selisih kuantitas antara hitung fisik dengan stok sistem fisik."),
    ("status_mutasi", "Enum", "-", "", "Status eksekusi mutasi penyesuaian ('Belum Dimutasi', 'Sudah Dimutasi', 'Sesuai')."),
    ("alasan_mutasi", "Varchar", "255", "", "Alasan penetapan mutasi penyesuaian (Selisih Kurang/Lebih)."),
    ("keterangan_mutasi", "Text", "-", "", "Catatan penjelasan detail mengenai rekonsiliasi selisih fisik yang terjadi."),
    ("id_kartu_stok", "Integer", "11", "FK", "Foreign Key ke kartu_stok.id_kartu_stok yang terbentuk dari mutasi penyesuaian."),
    ("dihitung_oleh", "Integer", "11", "FK", "Foreign Key ke users.id_user (Kasir pelaksana pencacahan fisik rak toko)."),
    ("waktu_hitung", "Timestamp", "-", "", "Waktu staf kasir mengunggah dan menyimpan angka hitungan fisik ke sistem."),
    ("dimutasi_oleh", "Integer", "11", "FK", "Foreign Key ke users.id_user (Admin Toko / Superadmin verifikator mutasi selisih)."),
    ("waktu_mutasi", "Timestamp", "-", "", "Waktu pembukuan penyesuaian saldo persediaan dieksekusi.")
]

# Builders for kamus_data_zencare.html style
def build_kd_tbody(cols):
    lines = []
    lines.append("                            <tbody>")
    for idx, (fn, ft, pj, key, ket) in enumerate(cols, 1):
        tr_class = ' class="row-pk"' if key == 'PK' else (' class="row-fk"' if key == 'FK' else ' class=""')
        pill = f' <span class="pill-pk">PK</span>' if key == 'PK' else (f' <span class="pill-fk">FK</span>' if key == 'FK' else '')
        lines.append(f"                                <tr{tr_class}>")
        lines.append(f'                                    <td style="text-align: center; font-weight: bold; color: #64748b;">{idx}</td>')
        lines.append(f'                                    <td><span class="field-name">{fn}{pill}</span></td>')
        lines.append(f'                                    <td><span class="col-type">{ft}</span></td>')
        lines.append(f'                                    <td style="text-align: center;"><span class="col-len">{pj}</span></td>')
        lines.append(f'                                    <td>{ket}</td>')
        lines.append("                                </tr>")
    lines.append("                            </tbody>")
    return "\n".join(lines)

# Builders for Bab 3 style
def build_bab3_tbody(cols):
    lines = []
    lines.append("                        <tbody>")
    for idx, (fn, ft, pj, key, ket) in enumerate(cols, 1):
        pill = ' <span class="text-red-700 font-bold font-sans text-[10px]">(PK)</span>' if key == 'PK' else (' <span class="text-blue-700 font-bold font-sans text-[10px]">(FK)</span>' if key == 'FK' else '')
        lines.append("                            <tr>")
        lines.append(f'                                <td class="border border-black px-2 py-1 text-center font-bold">{idx}</td>')
        lines.append(f'                                <td class="border border-black px-2 py-1 font-mono font-semibold">{fn}{pill}</td>')
        lines.append(f'                                <td class="border border-black px-2 py-1">{ft}</td>')
        lines.append(f'                                <td class="border border-black px-2 py-1 text-center">{pj}</td>')
        lines.append(f'                                <td class="border border-black px-2 py-1">{ket}</td>')
        lines.append("                            </tr>")
    lines.append("                        </tbody>")
    return "\n".join(lines)

# 1. Update lain/kamus_data_zencare.html
with open('lain/kamus_data_zencare.html', 'r', encoding='utf-8') as f:
    kd_html = f.read()

# Replace sesi_opname card in kamus_data_zencare.html
pos_sesi_start = kd_html.find('id="sesi_opname"')
pos_sesi_body = kd_html.find('<div class="table-item-body">', pos_sesi_start)
pos_sesi_tbl = kd_html.find('<tbody>', pos_sesi_body)
pos_sesi_tbl_end = kd_html.find('</tbody>', pos_sesi_tbl) + len('</tbody>')

# Update metadata header for sesi_opname
new_sesi_meta = """<div class="meta-grid">
                        <div class="meta-item"><b>Primary Key:</b> <code class="code-pk">id_sesi_opname</code></div>
                        <div class="meta-item"><b>Foreign Key:</b> <code class="code-fk">dibekukan_oleh, ditutup_hitung_oleh, dibuka_oleh</code></div>
                        <div class="meta-fungsi"><b>Fungsi:</b> Entitas yang mengelola jadwal dan siklus pelaksanaan pemeriksaan fisik stok opname berkala. Tabel ini mencatat status pembekuan transaksi sementara (freeze lock) pada kasir POS dan penjualan daring, pembagian wewenang tahapan hitung dan verifikasi, serta otorisasi penutupan sesi.</div>
                    </div>
                    <div class="table-caption-bar">
                        <span>Tabel 3.17 Tabel sesi_opname</span>
                        <span style="font-size: 11px; font-weight: normal; color: #64748b;">Total: 11 Kolom</span>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 48px; text-align: center;">No</th>
                                    <th style="width: 220px;">Nama Field</th>
                                    <th style="width: 130px;">Tipe Data</th>
                                    <th style="width: 80px; text-align: center;">Panjang</th>
                                    <th>Keterangan / Deskripsi</th>
                                </tr>
                            </thead>
""" + build_kd_tbody(sesi_opname_cols)

# Replace the table part of sesi_opname in kd_html
pos_sesi_table_container = kd_html.find('<div class="meta-grid">', pos_sesi_start)
kd_html = kd_html[:pos_sesi_table_container] + new_sesi_meta + kd_html[pos_sesi_tbl_end:]

# Now replace detail_opname_batch in kd_html
pos_dob_start = kd_html.find('id="detail_opname_batch"')
pos_dob_meta = kd_html.find('<div class="meta-grid">', pos_dob_start)
pos_dob_tbl = kd_html.find('<tbody>', pos_dob_meta)
pos_dob_tbl_end = kd_html.find('</tbody>', pos_dob_tbl) + len('</tbody>')

new_dob_meta = """<div class="meta-grid">
                        <div class="meta-item"><b>Primary Key:</b> <code class="code-pk">id_detail_opname</code></div>
                        <div class="meta-item"><b>Foreign Key:</b> <code class="code-fk">id_sesi, id_batch, id_variasi, id_kartu_stok, dihitung_oleh, dimutasi_oleh</code></div>
                        <div class="meta-fungsi"><b>Fungsi:</b> Lembar kerja pencatatan fisik persediaan per nomor batch/produk. Tabel ini menampung hasil hitungan fisik 3 kondisi (Baik, Rusak, Expired) oleh kasir secara objektif (blind count), memfasilitasi simpan bertahap, menghitung total selisih, dan mencatat mutasi penyesuaian oleh admin.</div>
                    </div>
                    <div class="table-caption-bar">
                        <span>Tabel 3.18 Tabel detail_opname_batch</span>
                        <span style="font-size: 11px; font-weight: normal; color: #64748b;">Total: 24 Kolom</span>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 48px; text-align: center;">No</th>
                                    <th style="width: 220px;">Nama Field</th>
                                    <th style="width: 130px;">Tipe Data</th>
                                    <th style="width: 80px; text-align: center;">Panjang</th>
                                    <th>Keterangan / Deskripsi</th>
                                </tr>
                            </thead>
""" + build_kd_tbody(detail_opname_batch_cols)

kd_html = kd_html[:pos_dob_meta] + new_dob_meta + kd_html[pos_dob_tbl_end:]

# Clean data-cat="audit"
kd_html = kd_html.replace('data-cat="audit"', 'data-cat="opname"')

with open('lain/kamus_data_zencare.html', 'w', encoding='utf-8') as f:
    f.write(kd_html)

print("Updated lain/kamus_data_zencare.html successfully!")

# 2. Build complete blocks for BAB III
bab3_sesi_block = f"""<!-- 3.5.17 Tabel sesi_opname -->
                <div class="mb-8" id="sec_sesi_opname">
                    <h4 class="text-sm font-bold font-serif mb-2">3.5.17 Tabel sesi_opname</h4>
                    <div class="text-xs mb-3 space-y-1 font-serif leading-relaxed">
                        <p><b>Primary Key:</b> id_sesi_opname</p>
                        <p><b>Foreign Key:</b> dibekukan_oleh, ditutup_hitung_oleh, dibuka_oleh</p>
                        <p class="text-justify"><b>Fungsi:</b> Entitas yang mengelola jadwal dan siklus pelaksanaan pemeriksaan fisik stok opname berkala. Tabel ini mencatat status pembekuan transaksi sementara (freeze lock) pada kasir POS dan penjualan daring, pembagian wewenang tahapan hitung dan verifikasi, serta otorisasi penutupan sesi.</p>
                    </div>
                    <p class="text-xs font-bold font-serif mb-1">Tabel 3.17 Tabel sesi_opname</p>
                    <table class="w-full text-xs border-collapse border border-black mb-6 font-serif">
                        <thead>
                            <tr class="bg-slate-100 text-black">
                                <th class="border border-black px-2 py-1.5 text-center font-bold" style="width: 6%;">No</th>
                                <th class="border border-black px-2 py-1.5 text-left font-bold" style="width: 25%;">Nama Field</th>
                                <th class="border border-black px-2 py-1.5 text-left font-bold" style="width: 17%;">Tipe Data</th>
                                <th class="border border-black px-2 py-1.5 text-center font-bold" style="width: 12%;">Panjang</th>
                                <th class="border border-black px-2 py-1.5 text-left font-bold" style="width: 40%;">Keterangan / Deskripsi</th>
                            </tr>
                        </thead>
{build_bab3_tbody(sesi_opname_cols)}
                    </table>
                </div>"""

bab3_dob_block = f"""<!-- 3.5.18 Tabel detail_opname_batch -->
                <div class="mb-8" id="sec_detail_opname_batch">
                    <h4 class="text-sm font-bold font-serif mb-2">3.5.18 Tabel detail_opname_batch</h4>
                    <div class="text-xs mb-3 space-y-1 font-serif leading-relaxed">
                        <p><b>Primary Key:</b> id_detail_opname</p>
                        <p><b>Foreign Key:</b> id_sesi, id_batch, id_variasi, id_kartu_stok, dihitung_oleh, dimutasi_oleh</p>
                        <p class="text-justify"><b>Fungsi:</b> Lembar kerja pencatatan fisik persediaan per nomor batch/produk. Tabel ini menampung hasil hitungan fisik 3 kondisi (Baik, Rusak, Expired) oleh kasir secara objektif (blind count), memfasilitasi simpan bertahap, menghitung total selisih, dan mencatat mutasi penyesuaian oleh admin.</p>
                    </div>
                    <p class="text-xs font-bold font-serif mb-1">Tabel 3.18 Tabel detail_opname_batch</p>
                    <table class="w-full text-xs border-collapse border border-black mb-6 font-serif">
                        <thead>
                            <tr class="bg-slate-100 text-black">
                                <th class="border border-black px-2 py-1.5 text-center font-bold" style="width: 6%;">No</th>
                                <th class="border border-black px-2 py-1.5 text-left font-bold" style="width: 25%;">Nama Field</th>
                                <th class="border border-black px-2 py-1.5 text-left font-bold" style="width: 17%;">Tipe Data</th>
                                <th class="border border-black px-2 py-1.5 text-center font-bold" style="width: 12%;">Panjang</th>
                                <th class="border border-black px-2 py-1.5 text-left font-bold" style="width: 40%;">Keterangan / Deskripsi</th>
                            </tr>
                        </thead>
{build_bab3_tbody(detail_opname_batch_cols)}
                    </table>
                </div>"""

for target_file in ['laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html']:
    with open(target_file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Replace sesi_opname block
    pattern_sesi = r'<!-- 3\.5\.17 Tabel sesi_opname -->.*?</div>\s*(?=<!-- 3\.5\.18)'
    m_s = re.search(pattern_sesi, content, re.DOTALL)
    assert m_s, f"sesi_opname block not found in {target_file}"
    content = content[:m_s.start()] + bab3_sesi_block + "\n\n                " + content[m_s.end():]
    
    # Replace detail_opname_batch block
    pattern_dob = r'<!-- 3\.5\.18 Tabel detail_opname_batch -->.*?</div>\s*(?=<!-- 3\.5\.19)'
    m_d = re.search(pattern_dob, content, re.DOTALL)
    assert m_d, f"detail_opname_batch block not found in {target_file}"
    content = content[:m_d.start()] + bab3_dob_block + "\n\n                " + content[m_d.end():]
    
    with open(target_file, 'w', encoding='utf-8') as f:
        f.write(content)
    print(f"Updated {target_file} successfully!")

print("All 3 files updated with complete 11 and 24 columns!")

