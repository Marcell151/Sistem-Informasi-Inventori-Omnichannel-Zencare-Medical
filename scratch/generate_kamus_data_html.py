import json
from build_subbab_3_5 import tables_data_3_5

# Create lain/kamus_data_zencare.html
html_content = '''<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kamus Data (Data Dictionary) - ZenCare Medical (Bab 3.5)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=JetBrains+Mono:wght@400;500;600&family=Times+New+Roman&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            padding: 24px;
        }

        /* Top Header Control Bar */
        .header-bar {
            max-width: 1360px;
            margin: 0 auto 20px auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            flex-wrap: wrap;
            gap: 12px;
        }

        .header-title h1 {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .header-title p {
            font-size: 12.5px;
            color: #64748b;
            margin-top: 2px;
        }

        .header-nav {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-nav {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .btn-outline {
            background: #f1f5f9;
            color: #334155;
            border-color: #cbd5e1;
        }

        .btn-outline:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .btn-primary {
            background: #2563eb;
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-success {
            background: #059669;
            color: #ffffff;
        }

        .btn-success:hover {
            background: #047857;
        }

        /* Filter Tab Bar */
        .filter-bar {
            max-width: 1360px;
            margin: 0 auto 20px auto;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
        }

        .filter-tabs {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 20px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-btn:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .filter-btn.active {
            background: #0f172a;
            color: #ffffff;
            border-color: #0f172a;
        }

        .search-box {
            position: relative;
            min-width: 260px;
        }

        .search-box input {
            width: 100%;
            padding: 7px 12px 7px 32px;
            font-size: 12.5px;
            border: 1px solid #cbd5e1;
            border-radius: 20px;
            outline: none;
            background: #ffffff;
            transition: all 0.2s;
        }

        .search-box input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .search-box svg {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 14px;
            height: 14px;
            color: #94a3b8;
        }

        /* Quick Jump Pills */
        .quick-jump-bar {
            max-width: 1360px;
            margin: 0 auto 24px auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 16px;
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            align-items: center;
        }

        .quick-jump-label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-right: 4px;
        }

        .jump-pill {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            padding: 3px 8px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.15s;
        }

        .jump-pill:hover {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
        }

        /* Main Container */
        .main-container {
            max-width: 1360px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Intro Card */
        .intro-card {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 20px 28px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            font-family: 'Times New Roman', Times, serif;
            font-size: 15px;
            line-height: 1.7;
        }

        .intro-card h3 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .intro-card p {
            text-align: justify;
            text-indent: 32px;
            color: #1e293b;
        }

        /* Table Item Card */
        .table-item-card {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: all 0.2s;
        }

        .table-item-card:hover {
            border-color: #94a3b8;
            box-shadow: 0 6px 12px -2px rgba(0, 0, 0, 0.08);
        }

        .table-item-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 14px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .table-title-group {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .table-num {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14.5px;
            font-weight: 800;
            color: #0f172a;
        }

        .table-name-tag {
            font-family: 'JetBrains Mono', monospace;
            font-size: 13.5px;
            font-weight: 700;
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            padding: 2px 8px;
            border-radius: 6px;
        }

        .domain-tag {
            font-size: 11.5px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 20px;
            border: 1px solid transparent;
        }

        .badge-master { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
        .badge-stock { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
        .badge-tx { background: #fefce8; color: #a16207; border-color: #fef08a; }
        .badge-audit { background: #faf5ff; color: #7e22ce; border-color: #e9d5ff; }
        .badge-new { background: #fee2e2; color: #b91c1c; border-color: #fecaca; font-weight: 700; }

        .table-item-body {
            padding: 16px 20px;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 14px;
            font-size: 12px;
        }

        .meta-item b {
            color: #475569;
            font-weight: 700;
        }

        .meta-item code {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
        }

        .code-pk {
            background: #fee2e2;
            color: #b91c1c;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .code-fk {
            background: #dbeafe;
            color: #1d4ed8;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .meta-fungsi {
            grid-column: 1 / -1;
            font-size: 12.5px;
            color: #334155;
            line-height: 1.6;
            text-align: justify;
        }

        .table-caption-bar {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 13px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-container {
            overflow-x: auto;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            text-align: left;
            background: #ffffff;
        }

        th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 700;
            padding: 9px 12px;
            border-bottom: 1px solid #cbd5e1;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        td {
            padding: 8px 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
            line-height: 1.45;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background-color: #f8fafc;
        }

        .row-pk {
            background-color: #fef2f2 !important;
        }

        .row-fk {
            background-color: #eff6ff !important;
        }

        .field-name {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
            color: #0f172a;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .pill-pk {
            background: #dc2626;
            color: #ffffff;
            font-size: 9px;
            font-weight: 800;
            padding: 1px 4px;
            border-radius: 4px;
        }

        .pill-fk {
            background: #2563eb;
            color: #ffffff;
            font-size: 9px;
            font-weight: 800;
            padding: 1px 4px;
            border-radius: 4px;
        }

        .col-type {
            font-family: 'JetBrains Mono', monospace;
            color: #475569;
        }

        .col-len {
            font-family: 'JetBrains Mono', monospace;
            color: #64748b;
            text-align: center;
        }

        /* Toast */
        .toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #0f172a;
            color: #ffffff;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.25s ease;
            pointer-events: none;
            z-index: 9999;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 768px) {
            body { padding: 12px; }
            .header-bar { padding: 12px; }
            .filter-bar { flex-direction: column; align-items: stretch; }
            .search-box { width: 100%; }
        }
    </style>
</head>

<body>

    <!-- Toast Notification -->
    <div id="toast" class="toast">Teks berhasil disalin!</div>

    <!-- Header Control Bar -->
    <div class="header-bar">
        <div class="header-title">
            <h1>Kamus Data (Data Dictionary) - ZenCare Medical</h1>
            <p>Spesifikasi Fisik 20 Entitas Tabel Basis Data Relasional MySQL (Subbab 3.5)</p>
        </div>
        <div class="header-nav">
            <a href="../laporan_ta/BAB_III_ZENCARE_MEDICAL.html#sec_3_5" class="btn-nav btn-outline" target="_blank">
                &larr; Buka Bab 3 Skripsi
            </a>
            <a href="logical_database_zencare.html" class="btn-nav btn-outline" target="_blank">
                Buka Logical Database (Bab 3.4)
            </a>
            <button onclick="copyAllMarkdown()" class="btn-nav btn-success">
                Salin Semua Kamus Data (MD)
            </button>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="filter-bar">
        <div class="filter-tabs">
            <button class="filter-btn active" onclick="filterTables('all', this)">Semua (20 Tabel)</button>
            <button class="filter-btn" onclick="filterTables('master', this)">Master &amp; Pengaturan (4)</button>
            <button class="filter-btn" onclick="filterTables('stock', this)">Poros Logistik &amp; Fisik (4)</button>
            <button class="filter-btn" onclick="filterTables('tx', this)">Transaksi &amp; Pasokan (5)</button>
            <button class="filter-btn" onclick="filterTables('audit', this)">Buku Besar &amp; Opname (7)</button>
            <button class="filter-btn" onclick="filterTables('new', this)">Tabel Baru (7)</button>
        </div>
        <div class="search-box">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input type="text" id="searchInput" placeholder="Cari tabel, field, tipe, atau deskripsi..." oninput="handleSearch()">
        </div>
    </div>

    <!-- Quick Jump Bar -->
    <div class="quick-jump-bar">
        <span class="quick-jump-label">Lompat Cepat:</span>
'''

# Add quick jump pills
pills = []
for t in tables_data_3_5:
    is_new = 'Baru' in t['domain']
    badge_star = '★ ' if is_new else ''
    pills.append(f'        <a href="#{t["name"]}" class="jump-pill">{badge_star}{t["name"]}</a>')
html_content += '\n'.join(pills)

html_content += '''
    </div>

    <!-- Main Container -->
    <div class="main-container">

        <!-- Intro Card -->
        <div class="intro-card">
            <h3>
                <span>Uraian Pengantar Subbab 3.5 Kamus Data</span>
                <span style="font-size: 11px; font-weight: normal; color: #64748b; font-family: sans-serif;">Gaya Bahasa Akademik Baku EYD V</span>
            </h3>
            <p>
                Kamus data disusun untuk mendokumentasikan secara rinci struktur fisik setiap entitas tabel, penetapan kunci utama (*Primary Key*), keterkaitan relasi kunci tamu (*Foreign Key*), serta spesifikasi teknis atribut kolom yang digunakan dalam basis data Sistem Informasi Inventori Omnichannel ZenCare Medical. Perancangan basis data terintegrasi ini mencakup <b>20 entitas tabel</b> yang merefleksikan seluruh kebutuhan alur operasional inventori medis, transaksi penjualan omnichannel, dan modul audit persediaan secara presisi sebagai berikut:
            </p>
        </div>

        <!-- 20 Tables Container -->
        <div id="tablesContainer" style="display: flex; flex-direction: column; gap: 20px;">
'''

# Domain category mapping for filter
def get_domain_cat(t):
    name = t['name']
    if name in ['users', 'pengaturan_web', 'supplier', 'produk_induk']:
        return 'master'
    elif name in ['produk_variasi', 'stok_toko', 'stok_batch', 'unit_serial']:
        return 'stock'
    elif name in ['penerimaan_stok', 'penerimaan_detail', 'penjualan', 'detail_penjualan', 'pesanan_batch_alokasi']:
        return 'tx'
    else:
        return 'audit'

def get_badge_class(t):
    cat = get_domain_cat(t)
    if 'Baru' in t['domain']:
        return 'badge-new'
    elif cat == 'master':
        return 'badge-master'
    elif cat == 'stock':
        return 'badge-stock'
    elif cat == 'tx':
        return 'badge-tx'
    else:
        return 'badge-audit'

for t in tables_data_3_5:
    cat = get_domain_cat(t)
    badge_cls = get_badge_class(t)
    is_new = 'Baru' in t['domain']
    new_badge_html = '<span class="domain-tag badge-new" style="margin-left: 4px;">Tabel Baru</span>' if is_new else ''

    html_content += f'''
            <!-- Item: {t['name']} -->
            <div class="table-item-card" id="{t['name']}" data-cat="{cat}" data-isnew="{'true' if is_new else 'false'}" data-name="{t['name']}">
                <div class="table-item-header">
                    <div class="table-title-group">
                        <span class="table-num">{t['sub_no']}</span>
                        <span class="table-name-tag">{t['name']}</span>
                        <span class="domain-tag {badge_cls}">{t['domain']}</span>
                        {new_badge_html}
                    </div>
                    <button onclick="copyTableMarkdown('{t['name']}')" class="btn-nav btn-outline" style="padding: 4px 10px; font-size: 11px;">
                        Salin Tabel (MD)
                    </button>
                </div>
                <div class="table-item-body">
                    <div class="meta-grid">
                        <div class="meta-item"><b>Primary Key:</b> <code class="code-pk">{t['pk']}</code></div>
                        <div class="meta-item"><b>Foreign Key:</b> <code class="code-fk">{t['fk']}</code></div>
                        <div class="meta-fungsi"><b>Fungsi:</b> {t['fungsi']}</div>
                    </div>
                    <div class="table-caption-bar">
                        <span>Tabel {t['table_no']} Tabel {t['name']}</span>
                        <span style="font-size: 11px; font-weight: normal; color: #64748b;">Total: {len(t['cols'])} Kolom</span>
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
                            <tbody>
'''
    for idx, (col_name, col_type, col_len, col_desc) in enumerate(t['cols'], 1):
        is_pk = (col_name == t['pk'])
        is_fk = (col_name in t['fk'])
        row_cls = 'row-pk' if is_pk else ('row-fk' if is_fk else '')
        badge = '<span class="pill-pk">PK</span>' if is_pk else ('<span class="pill-fk">FK</span>' if is_fk else '')
        html_content += f'''                                <tr class="{row_cls}">
                                    <td style="text-align: center; font-weight: bold; color: #64748b;">{idx}</td>
                                    <td><span class="field-name">{col_name} {badge}</span></td>
                                    <td><span class="col-type">{col_type}</span></td>
                                    <td style="text-align: center;"><span class="col-len">{col_len}</span></td>
                                    <td>{col_desc}</td>
                                </tr>
'''
    html_content += '''                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
'''

# Append JavaScript logic
html_content += '''
        </div>
    </div>

    <!-- Client-side Search and Filter Script -->
    <script>
        const rawTablesData = ''' + json.dumps(tables_data_3_5) + ''';

        function showToast(msg) {
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 2500);
        }

        function filterTables(cat, btn) {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            if (btn) btn.classList.add('active');

            const searchVal = document.getElementById('searchInput').value.toLowerCase().trim();
            const cards = document.querySelectorAll('.table-item-card');

            cards.forEach(card => {
                const cardCat = card.getAttribute('data-cat');
                const isNew = card.getAttribute('data-isnew') === 'true';
                const cardText = card.innerText.toLowerCase();

                let matchesCat = (cat === 'all') || (cat === 'new' && isNew) || (cardCat === cat);
                let matchesSearch = !searchVal || cardText.includes(searchVal);

                if (matchesCat && matchesSearch) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function handleSearch() {
            const activeBtn = document.querySelector('.filter-btn.active');
            const cat = activeBtn ? (activeBtn.getAttribute('onclick').match(/'(.*?)'/)[1]) : 'all';
            filterTables(cat, activeBtn);
        }

        function copyTableMarkdown(tableName) {
            const t = rawTablesData.find(item => item.name === tableName);
            if (!t) return;

            let md = `#### ${t.sub_no} Tabel ${t.name}\\n`;
            md += `* **Primary Key:** \`${t.pk}\`\\n`;
            md += `* **Foreign Key:** \`${t.fk}\`\\n`;
            md += `* **Fungsi:** ${t.fungsi}\\n\\n`;
            md += `**Tabel ${t.table_no}** Tabel ${t.name}\\n\\n`;
            md += `| No | Nama Field | Tipe Data | Panjang | Keterangan / Deskripsi |\\n`;
            md += `| :---: | :--- | :--- | :---: | :--- |\\n`;
            t.cols.forEach((col, idx) => {
                md += `| ${idx + 1} | \`${col[0]}\` | ${col[1]} | ${col[2]} | ${col[3]} |\\n`;
            });

            navigator.clipboard.writeText(md).then(() => {
                showToast(`Kamus data tabel '${tableName}' berhasil disalin!`);
            });
        }

        function copyAllMarkdown() {
            let allMd = `### 3.5 Kamus Data\\n\\n`;
            allMd += `Kamus data disusun untuk mendokumentasikan secara rinci struktur fisik setiap entitas tabel, penetapan kunci utama (*Primary Key*), keterkaitan relasi kunci tamu (*Foreign Key*), serta spesifikasi teknis atribut kolom yang digunakan dalam basis data Sistem Informasi Inventori Omnichannel ZenCare Medical. Perancangan basis data terintegrasi ini mencakup **20 entitas tabel** yang merefleksikan seluruh kebutuhan alur operasional inventori medis, transaksi penjualan omnichannel, dan modul audit persediaan secara presisi sebagai berikut:\\n\\n`;

            rawTablesData.forEach(t => {
                allMd += `#### ${t.sub_no} Tabel ${t.name}\\n`;
                allMd += `* **Primary Key:** \`${t.pk}\`\\n`;
                allMd += `* **Foreign Key:** \`${t.fk}\`\\n`;
                allMd += `* **Fungsi:** ${t.fungsi}\\n\\n`;
                allMd += `**Tabel ${t.table_no}** Tabel ${t.name}\\n\\n`;
                allMd += `| No | Nama Field | Tipe Data | Panjang | Keterangan / Deskripsi |\\n`;
                allMd += `| :---: | :--- | :--- | :---: | :--- |\\n`;
                t.cols.forEach((col, idx) => {
                    allMd += `| ${idx + 1} | \`${col[0]}\` | ${col[1]} | ${col[2]} | ${col[3]} |\\n`;
                });
                allMd += `\\n---\\n\\n`;
            });

            navigator.clipboard.writeText(allMd).then(() => {
                showToast("Seluruh Kamus Data (20 Tabel) berhasil disalin ke clipboard!");
            });
        }
    </script>
</body>

</html>
'''

with open('lain/kamus_data_zencare.html', 'w', encoding='utf-8') as f:
    f.write(html_content)

print('Successfully generated lain/kamus_data_zencare.html!')

