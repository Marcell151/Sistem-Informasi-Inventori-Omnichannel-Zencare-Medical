import xml.etree.ElementTree as ET

def rearrange_use_case():
    tree = ET.parse('scratch/usecase_original.xml')
    root = tree.getroot()
    
    left_actor_x = 100
    right_actor_x = 1100
    
    uc_layout = {
        'Autentikasi &amp; Kelola Akun Profil': (330, 180),
        'Eksplorasi Katalog &amp; Keranjang': (330, 270),
        'Checkout Pesanan': (330, 360),
        'Kelola Riwayat Pesanan': (470, 270),
        'Pembayaran Gateway': (470, 360),
        
        ' Kelola Pesanan Daring': (700, 180),
        'Batalkan Pesanan': (850, 180),
        'Kelola Transaksi Kasir (POS)': (700, 270),
        'Kelola Penerimaan Barang': (700, 360),
        'Validasi Identitas Logistik <i data-path-to-node="9" data-index-in-node="356">(Batch/SN)</i>': (850, 360),
        'Lakukan Mutasi Stok': (700, 450),
        'Akses Kartu Stok': (700, 540),
        
        'Kelola Master Data': (350, 680),
        'Master&nbsp;Produk': (350, 770),
        'Import Data Barang (Excel)': (350, 860),
        'Master&nbsp;Satuan': (470, 770),
        'Master&nbsp;Supplier': (590, 770),
        
        'Dashboard Analitik': (750, 680),
        'Akses Laporan Kuantitas &amp; Logistik': (750, 770),
        'Kelola User Admin': (750, 860),
        'Pengaturan Sistem (API &amp; Web)': (900, 770)
    }

    parent_id = "hU2W0jvd7m7iPvwlQaHw-1"
    
    def add_swimlane(id_str, value, x, y, w, h):
        cell = ET.Element('mxCell', id=id_str, parent=parent_id, value=value, style="swimlane;whiteSpace=wrap;html=1;")
        ET.SubElement(cell, 'mxGeometry', x=str(x), y=str(y), width=str(w), height=str(h), **{"as": "geometry"})
        root.find('.').append(cell)

    add_swimlane("swim_ecommerce", "Portal E-Commerce", 280, 120, 320, 350)
    add_swimlane("swim_transaksi", "Transaksi & Logistik", 630, 120, 380, 510)
    add_swimlane("swim_admin", "Master Data & Laporan", 280, 630, 760, 320)
    
    for cell in root.iter('mxCell'):
        val = cell.get('value', '')
        style = cell.get('style', '')
        
        if 'shape=umlActor' in style:
            geo = cell.find('mxGeometry')
            if geo is not None:
                if 'Pelanggan' in val:
                    geo.set('x', str(left_actor_x))
                    geo.set('y', '250')
                elif 'Admin (Staff)' in val:
                    geo.set('x', str(right_actor_x))
                    geo.set('y', '300')
                elif 'Superadmin' in val:
                    geo.set('x', str(left_actor_x))
                    geo.set('y', '700')
                    
        if 'ellipse' in style:
            geo = cell.find('mxGeometry')
            if geo is not None:
                for k, v in uc_layout.items():
                    if k in val or val in k:
                        geo.set('x', str(v[0]))
                        geo.set('y', str(v[1]))
                        break
                        
        if cell.get('edge') == '1' or cell.get('source'):
            geo = cell.find('mxGeometry')
            if geo is not None:
                array = geo.find('Array')
                if array is not None:
                    geo.remove(array)
                    
    tree.write('scratch/usecase_rearranged.xml', encoding='utf-8', xml_declaration=False)

if __name__ == '__main__':
    rearrange_use_case()
