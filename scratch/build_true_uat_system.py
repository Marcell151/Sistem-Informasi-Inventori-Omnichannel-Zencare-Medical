import re
import os

indicators = [
    # Bagian I
    {
        "part_idx": 0, "no": 1,
        "statement": "Sistem mampu memisahkan wewenang kerja staf secara tegas (Kasir hanya ke terminal kasir, Admin ke persediaan, Pemilik ke manajerial) guna mencegah penyalahgunaan hak akses.",
        "p": 5, "a": 5, "k": 5, "c": 4, "total": 19, "pct": "95.0%", "kat": "Sangat Layak"
    },
    {
        "part_idx": 0, "no": 2,
        "statement": "Sistem secara andal melindungi kerahasiaan data usaha dari upaya staf membuka menu manajerial tanpa izin melalui manipulasi alamat peramban.",
        "p": 5, "a": 5, "k": 4, "c": 5, "total": 19, "pct": "95.0%", "kat": "Sangat Layak"
    },
    # Bagian II
    {
        "part_idx": 1, "no": 3,
        "statement": "Struktur master produk memudahkan pengelompokan produk induk dengan variasi kekuatan dosis, bentuk sediaan, serta berbagai pilihan jenis kemasan.",
        "p": 5, "a": 5, "k": 4, "c": 5, "total": 19, "pct": "95.0%", "kat": "Sangat Layak"
    },
    {
        "part_idx": 1, "no": 4,
        "statement": "Otomasi konversi satuan kemasan (pemecahan box menjadi strip/tablet) berjalan akurat tanpa kesalahan perhitungan rasio serta memberikan peringatan saat stok mendekati batas minimum.",
        "p": 5, "a": 5, "k": 4, "c": 4, "total": 18, "pct": "90.0%", "kat": "Sangat Layak"
    },
    # Bagian III
    {
        "part_idx": 2, "no": 5,
        "statement": "Terminal kasir POS mempercepat pelayanan penjualan melalui pemindaian barcode yang responsif serta kalkulasi subtotal dan uang kembalian yang presisi.",
        "p": 5, "a": 5, "k": 5, "c": 4, "total": 19, "pct": "95.0%", "kat": "Sangat Layak"
    },
    {
        "part_idx": 2, "no": 6,
        "statement": "Sistem secara otomatis mengalokasikan stok dengan tanggal kedaluwarsa terdekat (FEFO) dan memunculkan dialog peringatan wajib isi alasan jika kasir mengeluarkan stok lain.",
        "p": 5, "a": 5, "k": 4, "c": 5, "total": 19, "pct": "95.0%", "kat": "Sangat Layak"
    },
    # Bagian IV
    {
        "part_idx": 3, "no": 7,
        "statement": "Konsumen dapat menjelajahi katalog produk kesehatan secara mandiri, memilih variasi kemasan, memilih opsi pengiriman, dan mengunggah bukti pembayaran dengan mudah.",
        "p": 5, "a": 4, "k": 4, "c": 5, "total": 18, "pct": "90.0%", "kat": "Sangat Layak"
    },
    {
        "part_idx": 3, "no": 8,
        "statement": "Mekanisme reservasi stok daring (reserved stock) berhasil mencegah penjualan ganda persediaan (race condition) dengan transaksi kasir gerai fisik secara seketika (real-time).",
        "p": 5, "a": 5, "k": 5, "c": 4, "total": 19, "pct": "95.0%", "kat": "Sangat Layak"
    },
    # Bagian V
    {
        "part_idx": 4, "no": 9,
        "statement": "Formulir penerimaan barang memudahkan staf mencatat faktur distributor lengkap dengan nomor batch, tanggal kedaluwarsa, dan nomor seri unik alat kesehatan.",
        "p": 5, "a": 5, "k": 4, "c": 5, "total": 19, "pct": "95.0%", "kat": "Sangat Layak"
    },
    {
        "part_idx": 4, "no": 10,
        "statement": "Fitur cetak stiker label barcode kemasan menghasilkan cetakan tajam dan dapat langsung dipindai dengan akurat oleh alat pemindai kasir.",
        "p": 5, "a": 5, "k": 4, "c": 5, "total": 19, "pct": "95.0%", "kat": "Sangat Layak"
    },
    # Bagian VI
    {
        "part_idx": 5, "no": 11,
        "statement": "Pencatatan pengurangan stok rusak, botol pecah, atau barang kedaluwarsa di luar penjualan berjalan tertib dengan kewajiban mencantumkan alasan operasional tertulis.",
        "p": 5, "a": 5, "k": 4, "c": 4, "total": 18, "pct": "90.0%", "kat": "Sangat Layak"
    },
    {
        "part_idx": 5, "no": 12,
        "statement": "Buku besar kartu stok persediaan (inventory ledger) menyajikan riwayat mutasi masuk, keluar, saldo berjalan, dan nomor batch secara kronologis dan transparan.",
        "p": 5, "a": 5, "k": 5, "c": 4, "total": 19, "pct": "95.0%", "kat": "Sangat Layak"
    },
    # Bagian VII
    {
        "part_idx": 6, "no": 13,
        "statement": "Fitur pembekuan transaksi server (freeze lock) efektif mengunci pergerakan stok selama penghitungan fisik, dan formulir kasir (blind count) menjamin objektivitas pencacahan tanpa bias angka sistem.",
        "p": 5, "a": 5, "k": 5, "c": 4, "total": 19, "pct": "95.0%", "kat": "Sangat Layak"
    },
    {
        "part_idx": 6, "no": 14,
        "statement": "Layar verifikasi admin memudahkan identifikasi barang berselisih (surplus/defisit), memfasilitasi mutasi koreksi penyeimbang, dan mencetak Berita Acara resmi format A4.",
        "p": 5, "a": 5, "k": 4, "c": 5, "total": 19, "pct": "95.0%", "kat": "Sangat Layak"
    },
    # Bagian VIII
    {
        "part_idx": 7, "no": 15,
        "statement": "Dasbor eksekutif menyajikan informasi strategis yang jelas mencakup ringkasan omzet penjualan, status stok kritis, dan peringatan dini barang mendekati kedaluwarsa (30, 60, 90 hari).",
        "p": 5, "a": 5, "k": 4, "c": 4, "total": 18, "pct": "90.0%", "kat": "Sangat Layak"
    },
    {
        "part_idx": 7, "no": 16,
        "statement": "Seluruh laporan manajerial (penjualan, kartu stok, penerimaan, dan analisis anomali FEFO) dapat difilter sesuai periode dan dicetak rapi format A4 siap arsip.",
        "p": 5, "a": 5, "k": 4, "c": 5, "total": 19, "pct": "95.0%", "kat": "Sangat Layak"
    }
]

# Verify math
total_score = sum(i['total'] for i in indicators)
max_score = len(indicators) * 20
pct = (total_score / max_score) * 100
print(f"Total Indicators: {len(indicators)}")
print(f"Total Score: {total_score} / {max_score}")
print(f"Percentage: {pct:.2f}%")
assert total_score == 300, f"Expected 300, got {total_score}"
assert max_score == 320, f"Expected 320, got {max_score}"
assert pct == 93.75, f"Expected 93.75%, got {pct}%"
print("[MATH VERIFIED] 100% Correct!")

