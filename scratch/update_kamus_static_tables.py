with open('lain/kamus_data_zencare.html', 'r', encoding='utf-8') as f:
    c = f.read()

# 1. In sesi_opname static card
old_sesi_card_header = """                    <div class="meta-grid">
                        <div class="meta-item"><b>Primary Key:</b> <code class="code-pk">id_sesi_opname</code></div>
                        <div class="meta-item"><b>Foreign Key:</b> <code class="code-fk">dibekukan_oleh, dibuka_oleh</code></div>
                        <div class="meta-fungsi"><b>Fungsi:</b> Entitas baru yang mengelola jadwal dan siklus pelaksanaan pemeriksaan fisik stok opname berkala. Tabel ini mencatat status pembekuan transaksi sementara (freeze lock) pada kasir POS dan e-commerce guna menjamin keabsahan angka cacahan fisik.</div>
                    </div>
                    <div class="table-caption-bar">
                        <span>Tabel 3.17 Tabel sesi_opname</span>
                        <span style="font-size: 11px; font-weight: normal; color: #64748b;">Total: 8 Kolom</span>
                    </div>"""

new_sesi_card_header = """                    <div class="meta-grid">
                        <div class="meta-item"><b>Primary Key:</b> <code class="code-pk">id_sesi_opname</code></div>
                        <div class="meta-item"><b>Foreign Key:</b> <code class="code-fk">dibekukan_oleh, ditutup_hitung_oleh, dibuka_oleh</code></div>
                        <div class="meta-fungsi"><b>Fungsi:</b> Entitas yang mengelola jadwal dan siklus pelaksanaan pemeriksaan fisik stok opname berkala. Tabel ini mencatat status pembekuan transaksi sementara (freeze lock) pada kasir POS dan e-commerce, tahapan kerja 3-tier, serta otorisasi penutupan sesi.</div>
                    </div>
                    <div class="table-caption-bar">
                        <span>Tabel 3.17 Tabel sesi_opname</span>
                        <span style="font-size: 11px; font-weight: normal; color: #64748b;">Total: 11 Kolom</span>
                    </div>"""

c = c.replace(old_sesi_card_header, new_sesi_card_header)

old_sesi_rows = """                                <tr class="">
                                    <td style="text-align: center; font-weight: bold; color: #64748b;">3</td>
                                    <td><span class="field-name">status </span></td>
                                    <td><span class="col-type">Enum</span></td>
                                    <td style="text-align: center;"><span class="col-len">-</span></td>
                                    <td>Tahapan siklus sesi opname ('Aktif', 'Selesai', 'Dibatalkan').</td>
                                </tr>
                                <tr class="row-fk">
                                    <td style="text-align: center; font-weight: bold; color: #64748b;">4</td>
                                    <td><span class="field-name">dibekukan_oleh <span class="pill-fk">FK</span></span></td>
                                    <td><span class="col-type">Integer</span></td>
                                    <td style="text-align: center;"><span class="col-len">11</span></td>
                                    <td>Foreign Key ke users.id_user (Superadmin pembeku sistem operasional).</td>
                                </tr>
                                <tr class="">
                                    <td style="text-align: center; font-weight: bold; color: #64748b;">5</td>
                                    <td><span class="field-name">dibekukan_pada </span></td>
                                    <td><span class="col-type">Timestamp</span></td>
                                    <td style="text-align: center;"><span class="col-len">-</span></td>
                                    <td>Waktu dimulainya pembekuan transaksi operasional sementara.</td>
                                </tr>
                                <tr class="row-fk">
                                    <td style="text-align: center; font-weight: bold; color: #64748b;">6</td>
                                    <td><span class="field-name">dibuka_oleh <span class="pill-fk">FK</span></span></td>
                                    <td><span class="col-type">Integer</span></td>
                                    <td style="text-align: center;"><span class="col-len">11</span></td>
                                    <td>Foreign Key ke users.id_user (Superadmin pembuka kunci operasional).</td>
                                </tr>
                                <tr class="">
                                    <td style="text-align: center; font-weight: bold; color: #64748b;">7</td>
                                    <td><span class="field-name">dibuka_pada </span></td>
                                    <td><span class="col-type">Timestamp</span></td>
                                    <td style="text-align: center;"><span class="col-len">-</span></td>
                                    <td>Waktu pembukaan kembali operasional kasir dan e-commerce.</td>
                                </tr>
                                <tr class="">
                                    <td style="text-align: center; font-weight: bold; color: #64748b;">8</td>
                                    <td><span class="field-name">catatan </span></td>
                                    <td><span class="col-type">Text</span></td>
                                    <td style="text-align: center;"><span class="col-len">-</span></td>
                                    <td>Catatan manajerial mengenai ruang lingkup pelaksanaan opname.</td>
                                </tr>"""

new_sesi_rows = """                                <tr class="">
                                    <td style="text-align: center; font-weight: bold; color: #64748b;">3</td>
                                    <td><span class="field-name">status </span></td>
                                    <td><span class="col-type">Enum</span></td>
                                    <td style="text-align: center;"><span class="col-len">-</span></td>
                                    <td>Status sesi pemeriksaan ('Aktif', 'Selesai', 'Dibatalkan').</td>
                                </tr>
                                <tr class="">
                                    <td style="text-align: center; font-weight: bold; color: #64748b;">4</td>
                                    <td><span class="field-name">tahap </span></td>
                                    <td><span class="col-type">Enum</span></td>
                                    <td style="text-align: center;"><span class="col-len">-</span></td>
                                    <td>Tahapan operasional aktif ('Penghitungan', 'Verifikasi', 'Selesai').</td>
                                </tr>
                                <tr class="row-fk">
                                    <td style="text-align: center; font-weight: bold; color: #64748b;">5</td>
                                    <td><span class="field-name">dibekukan_oleh <span class="pill-fk">FK</span></span></td>
                                    <td><span class="col-type">Integer</span></td>
                                    <td style="text-align: center;"><span class="col-len">11</span></td>
                                    <td>Foreign Key ke users.id_user (Superadmin pengotorisasi freeze lock).</td>
                                </tr>
                                <tr class="">
                                    <td style="text-align: center; font-weight: bold; color: #64748b;">6</td>
                                    <td><span class="field-name">dibekukan_pada </span></td>
                                    <td><span class="col-type">Timestamp</span></td>
                                    <td style="text-align: center;"><span class="col-len">-</span></td>
                                    <td>Waktu pembekuan sistem transaksi kasir dan e-commerce dieksekusi.</td>
                                </tr>
                                <tr class="row-fk">
                                    <td style="text-align: center; font-weight: bold; color: #64748b;">7</td>
                                    <td><span class="field-name">ditutup_hitung_oleh <span class="pill-fk">FK</span></span></td>
                                    <td><span class="col-type">Integer</span></td>
                                    <td style="text-align: center;"><span class="col-len">11</span></td>
                                    <td>Foreign Key ke users.id_user (Admin/Superadmin pemverifikasi peralihan fase).</td>
                                </tr>
                                <tr class="">
                                    <td style="text-align: center; font-weight: bold; color: #64748b;">8</td>
                                    <td><span class="field-name">ditutup_hitung_pada </span></td>
                                    <td><span class="col-type">Timestamp</span></td>
                                    <td style="text-align: center;"><span class="col-len">-</span></td>
                                    <td>Waktu penguncian lembar hitung fisik kasir dan pembukaan verifikasi selisih.</td>
                                </tr>
                                <tr class="row-fk">
                                    <td style="text-align: center; font-weight: bold; color: #64748b;">9</td>
                                    <td><span class="field-name">dibuka_oleh <span class="pill-fk">FK</span></span></td>
                                    <td><span class="col-type">Integer</span></td>
                                    <td style="text-align: center;"><span class="col-len">11</span></td>
                                    <td>Foreign Key ke users.id_user (Superadmin penutup sesi dan pembuka transaksi).</td>
                                </tr>
                                <tr class="">
                                    <td style="text-align: center; font-weight: bold; color: #64748b;">10</td>
                                    <td><span class="field-name">dibuka_pada </span></td>
                                    <td><span class="col-type">Timestamp</span></td>
                                    <td style="text-align: center;"><span class="col-len">-</span></td>
                                    <td>Waktu normalisasi sistem transaksi diaktifkan kembali oleh Superadmin.</td>
                                </tr>
                                <tr class="">
                                    <td style="text-align: center; font-weight: bold; color: #64748b;">11</td>
                                    <td><span class="field-name">catatan </span></td>
                                    <td><span class="col-type">Text</span></td>
                                    <td style="text-align: center;"><span class="col-len">-</span></td>
                                    <td>Catatan manajerial dan alasan evaluasi hasil stok opname.</td>
                                </tr>"""

c = c.replace(old_sesi_rows, new_sesi_rows)

# 2. In detail_opname_batch static card
old_batch_header = """                    <div class="meta-grid">
                        <div class="meta-item"><b>Primary Key:</b> <code class="code-pk">id_detail_opname</code></div>
                        <div class="meta-item"><b>Foreign Key:</b> <code class="code-fk">id_sesi, id_batch, id_variasi, dihitung_oleh, dimutasi_oleh</code></div>
                        <div class="meta-fungsi"><b>Fungsi:</b> Entitas baru yang bertindak sebagai lembar kerja pencacahan fisik obat per nomor batch. Tabel ini menampung hasil hitung mandiri staf tanpa melihat angka buku sistem (blind count), mengkalkulasi selisih secara otomatis, dan mengeksekusi penyesuaian saldo.</div>
                    </div>
                    <div class="table-caption-bar">
                        <span>Tabel 3.18 Tabel detail_opname_batch</span>
                        <span style="font-size: 11px; font-weight: normal; color: #64748b;">Total: 20 Kolom</span>
                    </div>"""

new_batch_header = """                    <div class="meta-grid">
                        <div class="meta-item"><b>Primary Key:</b> <code class="code-pk">id_detail_opname</code></div>
                        <div class="meta-item"><b>Foreign Key:</b> <code class="code-fk">id_sesi, id_batch, id_variasi, dihitung_oleh, dimutasi_oleh</code></div>
                        <div class="meta-fungsi"><b>Fungsi:</b> Lembar kerja pencatatan fisik persediaan per nomor batch/produk. Tabel ini menampung hasil hitungan fisik 3 kondisi (Baik, Rusak, Expired) oleh kasir secara objektif (blind count), memfasilitasi simpan parsial, menghitung total selisih, dan mencatat mutasi penyesuaian oleh admin.</div>
                    </div>
                    <div class="table-caption-bar">
                        <span>Tabel 3.18 Tabel detail_opname_batch</span>
                        <span style="font-size: 11px; font-weight: normal; color: #64748b;">Total: 24 Kolom</span>
                    </div>"""

c = c.replace(old_batch_header, new_batch_header)

old_batch_rows_center = """                                <tr class="">
                                    <td style="text-align: center; font-weight: bold; color: #64748b;">10</td>
                                    <td><span class="field-name">stok_sistem_fisik </span></td>
                                    <td><span class="col-type">Integer</span></td>
                                    <td style="text-align: center;"><span class="col-len">11</span></td>
                                    <td>Total saldo buku fisik yang harus ditemukan di rak toko.</td>
                                </tr>
                                <tr class="">
                                    <td style="text-align: center; font-weight: bold; color: #64748b;">11</td>
                                    <td><span class="field-name">hitung_fisik </span></td>
                                    <td><span class="col-type">Integer</span></td>
                                    <td style="text-align: center;"><span class="col-len">11</span></td>
                                    <td>Angka kuantitas nyata hasil pencacahan staf di rak toko.</td>
                                </tr>"""

new_batch_rows_center = """                                <tr class="">
                                    <td style="text-align: center; font-weight: bold; color: #64748b;">10</td>
                                    <td><span class="field-name">stok_sistem_fisik </span></td>
                                    <td><span class="col-type">Integer</span></td>
                                    <td style="text-align: center;"><span class="col-len">11</span></td>
                                    <td>Total saldo buku fisik yang harus ditemukan di rak toko (snapshot).</td>
                                </tr>
                                <tr class="">
                                    <td style="text-align: center; font-weight: bold; color: #64748b;">11</td>
                                    <td><span class="field-name">fisik_baik </span></td>
                                    <td><span class="col-type">Integer</span></td>
                                    <td style="text-align: center;"><span class="col-len">11</span></td>
                                    <td>Kuantitas fisik riil di rak yang ditemukan dalam kondisi baik (layak jual).</td>
                                </tr>
                                <tr class="">
                                    <td style="text-align: center; font-weight: bold; color: #64748b;">12</td>
                                    <td><span class="field-name">fisik_rusak </span></td>
                                    <td><span class="col-type">Integer</span></td>
                                    <td style="text-align: center;"><span class="col-len">11</span></td>
                                    <td>Kuantitas fisik riil di rak yang ditemukan dalam kondisi rusak / cacat fisik kemasan.</td>
                                </tr>
                                <tr class="">
                                    <td style="text-align: center; font-weight: bold; color: #64748b;">13</td>
                                    <td><span class="field-name">fisik_expired </span></td>
                                    <td><span class="col-type">Integer</span></td>
                                    <td style="text-align: center;"><span class="col-len">11</span></td>
                                    <td>Kuantitas fisik riil di rak yang telah melewati tanggal kedaluwarsa.</td>
                                </tr>
                                <tr class="">
                                    <td style="text-align: center; font-weight: bold; color: #64748b;">14</td>
                                    <td><span class="field-name">status_hitung </span></td>
                                    <td><span class="col-type">Enum</span></td>
                                    <td style="text-align: center;"><span class="col-len">-</span></td>
                                    <td>Status progres hitung kasir ('Belum', 'Sudah') untuk simpan bertahap.</td>
                                </tr>
                                <tr class="">
                                    <td style="text-align: center; font-weight: bold; color: #64748b;">15</td>
                                    <td><span class="field-name">hitung_fisik </span></td>
                                    <td><span class="col-type">Integer</span></td>
                                    <td style="text-align: center;"><span class="col-len">11</span></td>
                                    <td>Total hitungan riil kasir (akumulasi otomatis: fisik_baik + fisik_rusak + fisik_expired).</td>
                                </tr>"""

c = c.replace(old_batch_rows_center, new_batch_rows_center)

# Re-number 12..20 to 16..24 in detail_opname_batch static card
old_selisih = """                                <tr class="">
                                    <td style="text-align: center; font-weight: bold; color: #64748b;">12</td>
                                    <td><span class="field-name">selisih </span></td>"""

new_selisih = """                                <tr class="">
                                    <td style="text-align: center; font-weight: bold; color: #64748b;">16</td>
                                    <td><span class="field-name">selisih </span></td>"""
c = c.replace(old_selisih, new_selisih)

pos_card = c.find('id="detail_opname_batch"')
if pos_card != -1:
    pos_end_card = c.find('id="detail_opname_sn"', pos_card)
    card_text = c[pos_card:pos_end_card]
    for i in range(13, 21):
        old_no = f'<td style="text-align: center; font-weight: bold; color: #64748b;">{i}</td>'
        new_no = f'<td style="text-align: center; font-weight: bold; color: #64748b;">{i+4}</td>'
        card_text = card_text.replace(old_no, new_no, 1)
    
    # Update descriptions
    card_text = card_text.replace('Foreign Key ke users.id_user (staf pelaksana pencacahan fisik).', 'Foreign Key ke users.id_user (Kasir pelaksana pencacahan fisik rak).')
    card_text = card_text.replace('Foreign Key ke users.id_user (pejabat penyetuju mutasi saldo).', 'Foreign Key ke users.id_user (Admin Toko / Superadmin verifikator mutasi selisih).')
    
    c = c[:pos_card] + card_text + c[pos_end_card:]

with open('lain/kamus_data_zencare.html', 'w', encoding='utf-8') as f:
    f.write(c)

print("Updated static HTML tables in lain/kamus_data_zencare.html successfully!")

