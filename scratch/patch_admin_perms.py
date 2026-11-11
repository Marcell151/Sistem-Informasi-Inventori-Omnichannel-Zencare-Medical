# Script to update permissions in stok_opname.php and dokumentasi_testing.php
import re

with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

# 1. Reset database in stok_opname.php: allow Admin as well
text = text.replace(
    'if (!$isSuperadmin) {\n            $msg = "Akses ditolak! Hanya Superadmin yang berwenang mereset database inventori.";',
    'if (!$canVerify) {\n            $msg = "Akses ditolak! Utilitas reset database pengujian hanya dapat diakses oleh Admin atau Superadmin.";'
)

# 2. Mulai sesi: allow Admin as well
text = text.replace(
    'if (!$isSuperadmin) {\n            $msg = "Akses ditolak! Hanya Superadmin yang berwenang memulai Sesi Stok Opname.";',
    'if (!$canVerify) {\n            $msg = "Akses ditolak! Hanya Admin atau Superadmin yang berwenang memulai Sesi Stok Opname.";'
)

# 3. isi_cepat_demo: allow Admin as well
text = text.replace(
    'if (!$isSuperadmin) {\n            $msg = "Akses ditolak! Fitur auto-generate skenario uji coba hanya diperuntukkan bagi Superadmin.";',
    'if (!$canVerify) {\n            $msg = "Akses ditolak! Fitur auto-generate skenario uji coba hanya diperuntukkan bagi Admin dan Superadmin.";'
)

# 4. Selesaikan sesi: allow Admin as well
text = text.replace(
    'if (!$isSuperadmin) {\n            $msg = "Akses ditolak! Hanya Superadmin yang berwenang menyelesaikan Sesi Stok Opname.";',
    'if (!$canVerify) {\n            $msg = "Akses ditolak! Hanya Admin atau Superadmin yang berwenang menyelesaikan Sesi Stok Opname.";'
)

# 5. Batal sesi: allow Admin as well
text = text.replace(
    'if (!$isSuperadmin) {\n            $msg = "Akses ditolak! Hanya Superadmin yang berwenang membatalkan Sesi Stok Opname.";',
    'if (!$canVerify) {\n            $msg = "Akses ditolak! Hanya Admin atau Superadmin yang berwenang membatalkan Sesi Stok Opname.";'
)

# 6. In UI: Replace <?php if ($isSuperadmin): ?> with <?php if ($canVerify): ?> for action buttons
text = text.replace('<?php if ($isSuperadmin): ?>\n                        <!-- Tombol Reset Database Demo (Utilitas Khusus Penguji / Demo Sidang) -->', '<?php if ($canVerify): ?>\n                        <!-- Tombol Reset Database Demo (Utilitas Khusus Penguji / Demo Sidang) -->')

# In landing page buttons:
text = text.replace('<?php else:\n                        // Hanya Superadmin', '<?php else:\n                        // Hanya Admin & Superadmin')

# In active session header buttons:
text = text.replace('<?php if ($isSuperadmin && $stats[\'belum_hitung\'] > 0): ?>\n                            <!-- Tombol Auto-Generate Skenario Uji Coba', '<?php if ($canVerify && $stats[\'belum_hitung\'] > 0): ?>\n                            <!-- Tombol Auto-Generate Skenario Uji Coba')

text = text.replace('<?php if ($isSuperadmin): ?>\n                            <?php \n                            $bisaSelesai =', '<?php if ($canVerify): ?>\n                            <?php \n                            $bisaSelesai =')

text = text.replace('<?php if ($isSuperadmin): ?>\n                        <button onclick="document.getElementById(\'modal_batal_sesi\')', '<?php if ($canVerify): ?>\n                        <button onclick="document.getElementById(\'modal_batal_sesi\')')

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(text)

print("Updated stok_opname.php permissions for Admin & Superadmin.")

