for ext in ['html', 'md']:
    p = f'laporan_ta/DOKUMENTASI_SRS_LENGKAP_ZENCARE_MEDICAL.{ext}'
    with open(p, 'r', encoding='utf-8') as f:
        c = f.read()
    c = c.replace('FARMASI & ALKES', 'LOGISTIK OBAT & ALKES')
    c = c.replace('farmasi dan alkes', 'obat dan alat kesehatan')
    c = c.replace('Farmasi & FEFO', 'Obat & FEFO')
    c = c.replace('distribusi farmasi', 'distribusi obat dan alkes')
    c = c.replace('farmasi', 'obat dan alkes')
    c = c.replace('Farmasi', 'Obat dan Alkes')
    with open(p, 'w', encoding='utf-8') as f:
        f.write(c)
    rem = c.lower().count('farmasi')
    print(f'{p} sanitized. Remaining farmasi: {rem}')

