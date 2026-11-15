import pymysql

# Test DB Connection and verify cancellation logic
try:
    conn = pymysql.connect(
        host='127.0.0.1',
        user='root',
        password='',
        database='inventory_zencare',
        charset='utf8mb4',
        cursorclass=pymysql.cursors.DictCursor
    )
    print("Database connected successfully!")
    with conn.cursor() as cur:
        # Check an existing order or check cancelled orders
        cur.execute("SELECT id, no_invoice, status_pesanan, alasan_batal, dibatalkan_oleh, waktu_batal FROM penjualan WHERE status_pesanan = 'Dibatalkan' ORDER BY id DESC LIMIT 5")
        rows = cur.fetchall()
        print(f"Total cancelled orders found: {len(rows)}")
        for r in rows:
            print(" ", r)
        
        # Check enum of status_pesanan
        cur.execute("SHOW COLUMNS FROM penjualan LIKE 'status_pesanan'")
        col = cur.fetchone()
        print("Column definition:", col)

        # Check pesanan_batch_alokasi count
        cur.execute("SELECT COUNT(*) AS total_alloc FROM pesanan_batch_alokasi")
        alloc_cnt = cur.fetchone()
        print("Active allocated batches:", alloc_cnt['total_alloc'])

    conn.close()
except Exception as e:
    print("DB Error:", e)

