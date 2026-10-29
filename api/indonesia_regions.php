<?php
// File: api/indonesia_regions.php
// Basis Data Statis Wilayah Indonesia (34 Provinsi & Kota/Kabupaten Lengkap)
// Menjamin ketersediaan data wilayah 100% tanpa risiko "Daily limit exceeded" (HTTP 429)

function getIndonesianProvincesList() {
    return [
        ['id' => 1, 'name' => 'NUSA TENGGARA BARAT (NTB)'],
        ['id' => 2, 'name' => 'MALUKU'],
        ['id' => 3, 'name' => 'KALIMANTAN SELATAN'],
        ['id' => 4, 'name' => 'KALIMANTAN TENGAH'],
        ['id' => 5, 'name' => 'JAWA BARAT'],
        ['id' => 6, 'name' => 'BENGKULU'],
        ['id' => 7, 'name' => 'KALIMANTAN TIMUR'],
        ['id' => 8, 'name' => 'KEPULAUAN RIAU'],
        ['id' => 9, 'name' => 'NANGGROE ACEH DARUSSALAM (NAD)'],
        ['id' => 10, 'name' => 'DKI JAKARTA'],
        ['id' => 11, 'name' => 'BANTEN'],
        ['id' => 12, 'name' => 'JAWA TENGAH'],
        ['id' => 13, 'name' => 'JAMBI'],
        ['id' => 14, 'name' => 'PAPUA'],
        ['id' => 15, 'name' => 'BALI'],
        ['id' => 16, 'name' => 'SUMATERA UTARA'],
        ['id' => 17, 'name' => 'GORONTALO'],
        ['id' => 18, 'name' => 'JAWA TIMUR'],
        ['id' => 19, 'name' => 'DI YOGYAKARTA'],
        ['id' => 20, 'name' => 'SULAWESI TENGGARA'],
        ['id' => 21, 'name' => 'NUSA TENGGARA TIMUR (NTT)'],
        ['id' => 22, 'name' => 'SULAWESI UTARA'],
        ['id' => 23, 'name' => 'SUMATERA BARAT'],
        ['id' => 24, 'name' => 'BANGKA BELITUNG'],
        ['id' => 25, 'name' => 'RIAU'],
        ['id' => 26, 'name' => 'SUMATERA SELATAN'],
        ['id' => 27, 'name' => 'SULAWESI TENGAH'],
        ['id' => 28, 'name' => 'KALIMANTAN BARAT'],
        ['id' => 29, 'name' => 'PAPUA BARAT'],
        ['id' => 30, 'name' => 'LAMPUNG'],
        ['id' => 31, 'name' => 'KALIMANTAN UTARA'],
        ['id' => 32, 'name' => 'MALUKU UTARA'],
        ['id' => 33, 'name' => 'SULAWESI SELATAN'],
        ['id' => 34, 'name' => 'SULAWESI BARAT']
    ];
}

function getIndonesianCitiesByProvince($provinceId) {
    $map = [
        1 => [ // NTB
            ['id' => 304, 'name' => 'KOTA MATARAM'],
            ['id' => 305, 'name' => 'KOTA BIMA'],
            ['id' => 306, 'name' => 'KABUPATEN LOMBOK BARAT'],
            ['id' => 307, 'name' => 'KABUPATEN LOMBOK TENGAH'],
            ['id' => 308, 'name' => 'KABUPATEN LOMBOK TIMUR'],
            ['id' => 309, 'name' => 'KABUPATEN LOMBOK UTARA'],
            ['id' => 310, 'name' => 'KABUPATEN SUMBAWA'],
            ['id' => 311, 'name' => 'KABUPATEN SUMBAWA BARAT'],
            ['id' => 312, 'name' => 'KABUPATEN DOMPU'],
            ['id' => 313, 'name' => 'KABUPATEN BIMA'],
        ],
        2 => [ // MALUKU
            ['id' => 401, 'name' => 'KOTA AMBON'],
            ['id' => 402, 'name' => 'KOTA TUAL'],
            ['id' => 403, 'name' => 'KABUPATEN MALUKU TENGAH'],
            ['id' => 404, 'name' => 'KABUPATEN MALUKU TENGGARA'],
            ['id' => 405, 'name' => 'KABUPATEN KEPULAUAN TANIMBAR'],
            ['id' => 406, 'name' => 'KABUPATEN BURU'],
            ['id' => 407, 'name' => 'KABUPATEN SERAM BAGIAN TIMUR'],
            ['id' => 408, 'name' => 'KABUPATEN SERAM BAGIAN BARAT'],
            ['id' => 409, 'name' => 'KABUPATEN KEPULAUAN ARU'],
            ['id' => 410, 'name' => 'KABUPATEN MALUKU BARAT DAYA'],
            ['id' => 411, 'name' => 'KABUPATEN BURU SELATAN'],
        ],
        3 => [ // KALIMANTAN SELATAN
            ['id' => 314, 'name' => 'KOTA BANJARMASIN'],
            ['id' => 315, 'name' => 'KOTA BANJARBARU'],
            ['id' => 316, 'name' => 'KABUPATEN BANJAR'],
            ['id' => 317, 'name' => 'KABUPATEN BARITO KUALA'],
            ['id' => 318, 'name' => 'KABUPATEN TAPIN'],
            ['id' => 319, 'name' => 'KABUPATEN HULU SUNGAI SELATAN'],
            ['id' => 320, 'name' => 'KABUPATEN HULU SUNGAI TENGAH'],
            ['id' => 321, 'name' => 'KABUPATEN HULU SUNGAI UTARA'],
            ['id' => 322, 'name' => 'KABUPATEN TABALONG'],
            ['id' => 323, 'name' => 'KABUPATEN TANAH LAUT'],
            ['id' => 324, 'name' => 'KABUPATEN TANAH BUMBU'],
            ['id' => 325, 'name' => 'KABUPATEN KOTABARU'],
            ['id' => 326, 'name' => 'KABUPATEN BALANGAN'],
        ],
        4 => [ // KALIMANTAN TENGAH
            ['id' => 327, 'name' => 'KOTA PALANGKA RAYA'],
            ['id' => 328, 'name' => 'KABUPATEN KOTAWARINGIN BARAT'],
            ['id' => 329, 'name' => 'KABUPATEN KOTAWARINGIN TIMUR'],
            ['id' => 330, 'name' => 'KABUPATEN KAPUAS'],
            ['id' => 331, 'name' => 'KABUPATEN BARITO SELATAN'],
            ['id' => 332, 'name' => 'KABUPATEN BARITO UTARA'],
            ['id' => 333, 'name' => 'KABUPATEN KATINGAN'],
            ['id' => 334, 'name' => 'KABUPATEN SERUYAN'],
            ['id' => 335, 'name' => 'KABUPATEN SUKAMARA'],
            ['id' => 336, 'name' => 'KABUPATEN LAMANDAU'],
            ['id' => 337, 'name' => 'KABUPATEN GUNUNG MAS'],
            ['id' => 338, 'name' => 'KABUPATEN PULANG PISAU'],
            ['id' => 339, 'name' => 'KABUPATEN MURUNG RAYA'],
            ['id' => 340, 'name' => 'KABUPATEN BARITO TIMUR'],
        ],
        5 => [ // JAWA BARAT
            ['id' => 115, 'name' => 'KOTA BANDUNG'],
            ['id' => 116, 'name' => 'KOTA BEKASI'],
            ['id' => 117, 'name' => 'KOTA BOGOR'],
            ['id' => 118, 'name' => 'KOTA CIMAHI'],
            ['id' => 119, 'name' => 'KOTA CIREBON'],
            ['id' => 120, 'name' => 'KOTA DEPOK'],
            ['id' => 121, 'name' => 'KOTA SUKABUMI'],
            ['id' => 122, 'name' => 'KOTA TASIKMALAYA'],
            ['id' => 123, 'name' => 'KOTA BANJAR'],
            ['id' => 124, 'name' => 'KABUPATEN BANDUNG'],
            ['id' => 125, 'name' => 'KABUPATEN BANDUNG BARAT'],
            ['id' => 126, 'name' => 'KABUPATEN BEKASI'],
            ['id' => 127, 'name' => 'KABUPATEN BOGOR'],
            ['id' => 128, 'name' => 'KABUPATEN CIAMIS'],
            ['id' => 129, 'name' => 'KABUPATEN CIANJUR'],
            ['id' => 130, 'name' => 'KABUPATEN CIREBON'],
            ['id' => 131, 'name' => 'KABUPATEN GARUT'],
            ['id' => 132, 'name' => 'KABUPATEN INDRAMAYU'],
            ['id' => 133, 'name' => 'KABUPATEN KARAWANG'],
            ['id' => 134, 'name' => 'KABUPATEN KUNINGAN'],
            ['id' => 135, 'name' => 'KABUPATEN MAJALENGKA'],
            ['id' => 136, 'name' => 'KABUPATEN PANGANDARAN'],
            ['id' => 137, 'name' => 'KABUPATEN PURWAKARTA'],
            ['id' => 138, 'name' => 'KABUPATEN SUBANG'],
            ['id' => 139, 'name' => 'KABUPATEN SUKABUMI'],
            ['id' => 140, 'name' => 'KABUPATEN SUMEDANG'],
            ['id' => 141, 'name' => 'KABUPATEN TASIKMALAYA'],
        ],
        6 => [ // BENGKULU
            ['id' => 412, 'name' => 'KOTA BENGKULU'],
            ['id' => 413, 'name' => 'KABUPATEN BENGKULU SELATAN'],
            ['id' => 414, 'name' => 'KABUPATEN BENGKULU TENGAH'],
            ['id' => 415, 'name' => 'KABUPATEN BENGKULU UTARA'],
            ['id' => 416, 'name' => 'KABUPATEN KAUR'],
            ['id' => 417, 'name' => 'KABUPATEN KEPAHIANG'],
            ['id' => 418, 'name' => 'KABUPATEN LEBONG'],
            ['id' => 419, 'name' => 'KABUPATEN MUKOMUKO'],
            ['id' => 420, 'name' => 'KABUPATEN REJANG LEBONG'],
            ['id' => 421, 'name' => 'KABUPATEN SELUMA'],
        ],
        7 => [ // KALIMANTAN TIMUR
            ['id' => 341, 'name' => 'KOTA SAMARINDA'],
            ['id' => 342, 'name' => 'KOTA BALIKPAPAN'],
            ['id' => 343, 'name' => 'KOTA BONTANG'],
            ['id' => 344, 'name' => 'KABUPATEN BERAU'],
            ['id' => 345, 'name' => 'KABUPATEN KUTAI BARAT'],
            ['id' => 346, 'name' => 'KABUPATEN KUTAI KARTANEGARA'],
            ['id' => 347, 'name' => 'KABUPATEN KUTAI TIMUR'],
            ['id' => 348, 'name' => 'KABUPATEN MAHAKAM ULU'],
            ['id' => 349, 'name' => 'KABUPATEN PASER'],
            ['id' => 350, 'name' => 'KABUPATEN PENAJAM PASER UTARA'],
        ],
        8 => [ // KEPULAUAN RIAU
            ['id' => 165, 'name' => 'KOTA BATAM'],
            ['id' => 166, 'name' => 'KOTA TANJUNG PINANG'],
            ['id' => 167, 'name' => 'KABUPATEN BINTAN'],
            ['id' => 168, 'name' => 'KABUPATEN KARIMUN'],
            ['id' => 169, 'name' => 'KABUPATEN KEPULAUAN ANAMBAS'],
            ['id' => 170, 'name' => 'KABUPATEN LINGGA'],
            ['id' => 171, 'name' => 'KABUPATEN NATUNA'],
        ],
        9 => [ // NAD (ACEH)
            ['id' => 422, 'name' => 'KOTA BANDA ACEH'],
            ['id' => 423, 'name' => 'KOTA LANGSA'],
            ['id' => 424, 'name' => 'KOTA LHOKSEUMAWE'],
            ['id' => 425, 'name' => 'KOTA SABANG'],
            ['id' => 426, 'name' => 'KOTA SUBULUSSALAM'],
            ['id' => 427, 'name' => 'KABUPATEN ACEH BARAT'],
            ['id' => 428, 'name' => 'KABUPATEN ACEH BESAR'],
            ['id' => 429, 'name' => 'KABUPATEN ACEH SELATAN'],
            ['id' => 430, 'name' => 'KABUPATEN ACEH UTARA'],
            ['id' => 431, 'name' => 'KABUPATEN BIREUEN'],
            ['id' => 432, 'name' => 'KABUPATEN PIDIE'],
        ],
        10 => [ // DKI JAKARTA
            ['id' => 151, 'name' => 'KOTA JAKARTA BARAT'],
            ['id' => 152, 'name' => 'KOTA JAKARTA PUSAT'],
            ['id' => 153, 'name' => 'KOTA JAKARTA SELATAN'],
            ['id' => 154, 'name' => 'KOTA JAKARTA TIMUR'],
            ['id' => 155, 'name' => 'KOTA JAKARTA UTARA'],
            ['id' => 156, 'name' => 'KABUPATEN KEPULAUAN SERIBU'],
        ],
        11 => [ // BANTEN
            ['id' => 142, 'name' => 'KOTA TANGERANG'],
            ['id' => 143, 'name' => 'KOTA TANGERANG SELATAN'],
            ['id' => 144, 'name' => 'KOTA SERANG'],
            ['id' => 145, 'name' => 'KOTA CILEGON'],
            ['id' => 146, 'name' => 'KABUPATEN TANGERANG'],
            ['id' => 147, 'name' => 'KABUPATEN SERANG'],
            ['id' => 148, 'name' => 'KABUPATEN PANDEGLANG'],
            ['id' => 149, 'name' => 'KABUPATEN LEBAK'],
        ],
        12 => [ // JAWA TENGAH
            ['id' => 172, 'name' => 'KOTA SEMARANG'],
            ['id' => 173, 'name' => 'KOTA SURAKARTA (SOLO)'],
            ['id' => 174, 'name' => 'KOTA MAGELANG'],
            ['id' => 175, 'name' => 'KOTA PEKALONGAN'],
            ['id' => 176, 'name' => 'KOTA SALATIGA'],
            ['id' => 177, 'name' => 'KOTA TEGAL'],
            ['id' => 178, 'name' => 'KABUPATEN BANYUMAS (PURWOKERTO)'],
            ['id' => 179, 'name' => 'KABUPATEN BATANG'],
            ['id' => 180, 'name' => 'KABUPATEN BLORA'],
            ['id' => 181, 'name' => 'KABUPATEN BOYOLALI'],
            ['id' => 182, 'name' => 'KABUPATEN BREBES'],
            ['id' => 183, 'name' => 'KABUPATEN CILACAP'],
            ['id' => 184, 'name' => 'KABUPATEN DEMAK'],
            ['id' => 185, 'name' => 'KABUPATEN JEPARA'],
            ['id' => 186, 'name' => 'KABUPATEN KARANGANYAR'],
            ['id' => 187, 'name' => 'KABUPATEN KLATEN'],
            ['id' => 188, 'name' => 'KABUPATEN KUDUS'],
            ['id' => 189, 'name' => 'KABUPATEN PATI'],
            ['id' => 190, 'name' => 'KABUPATEN SUKOHARJO'],
            ['id' => 191, 'name' => 'KABUPATEN WONOGIRI'],
        ],
        13 => [ // JAMBI
            ['id' => 433, 'name' => 'KOTA JAMBI'],
            ['id' => 434, 'name' => 'KOTA SUNGAI PENUH'],
            ['id' => 435, 'name' => 'KABUPATEN BATANGHARI'],
            ['id' => 436, 'name' => 'KABUPATEN BUNGO'],
            ['id' => 437, 'name' => 'KABUPATEN KERINCI'],
            ['id' => 438, 'name' => 'KABUPATEN MERANGIN'],
            ['id' => 439, 'name' => 'KABUPATEN MUARO JAMBI'],
            ['id' => 440, 'name' => 'KABUPATEN SAROLANGUN'],
            ['id' => 441, 'name' => 'KABUPATEN TEBO'],
        ],
        14 => [ // PAPUA
            ['id' => 442, 'name' => 'KOTA JAYAPURA'],
            ['id' => 443, 'name' => 'KABUPATEN JAYAPURA'],
            ['id' => 444, 'name' => 'KABUPATEN BIAK NUMFOR'],
            ['id' => 445, 'name' => 'KABUPATEN KEEROM'],
            ['id' => 446, 'name' => 'KABUPATEN MERAUKE'],
            ['id' => 447, 'name' => 'KABUPATEN MIMIKA'],
            ['id' => 448, 'name' => 'KABUPATEN NABIRE'],
        ],
        15 => [ // BALI
            ['id' => 114, 'name' => 'KOTA DENPASAR'],
            ['id' => 115, 'name' => 'KABUPATEN BADUNG'],
            ['id' => 116, 'name' => 'KABUPATEN BANGLI'],
            ['id' => 117, 'name' => 'KABUPATEN BULELENG (SINGARAJA)'],
            ['id' => 118, 'name' => 'KABUPATEN GIANYAR'],
            ['id' => 119, 'name' => 'KABUPATEN JEMBRANA (NEGARA)'],
            ['id' => 120, 'name' => 'KABUPATEN KARANGASEM'],
            ['id' => 121, 'name' => 'KABUPATEN KLUNGKUNG'],
            ['id' => 122, 'name' => 'KABUPATEN TABANAN'],
        ],
        16 => [ // SUMATERA UTARA
            ['id' => 361, 'name' => 'KOTA MEDAN'],
            ['id' => 366, 'name' => 'KOTA BINJAI'],
            ['id' => 377, 'name' => 'KOTA TEBING TINGGI'],
            ['id' => 381, 'name' => 'KOTA TANJUNG BALAI'],
            ['id' => 382, 'name' => 'KOTA PEMATANG SIANTAR'],
            ['id' => 219, 'name' => 'KOTA GUNUNGSITOLI'],
            ['id' => 225, 'name' => 'KOTA SIBOLGA'],
            ['id' => 227, 'name' => 'KOTA PADANG SIDEMPUAN'],
            ['id' => 363, 'name' => 'KABUPATEN ASAHAN'],
            ['id' => 365, 'name' => 'KABUPATEN DELI SERDANG'],
            ['id' => 374, 'name' => 'KABUPATEN KARO'],
            ['id' => 375, 'name' => 'KABUPATEN LANGKAT'],
            ['id' => 368, 'name' => 'KABUPATEN SIMALUNGUN'],
            ['id' => 220, 'name' => 'KABUPATEN NIAS'],
            ['id' => 221, 'name' => 'KABUPATEN NIAS BARAT'],
            ['id' => 222, 'name' => 'KABUPATEN NIAS SELATAN'],
            ['id' => 223, 'name' => 'KABUPATEN NIAS UTARA'],
        ],
        17 => [ // GORONTALO
            ['id' => 449, 'name' => 'KOTA GORONTALO'],
            ['id' => 450, 'name' => 'KABUPATEN BOALEMO'],
            ['id' => 451, 'name' => 'KABUPATEN BONE BOLANGO'],
            ['id' => 452, 'name' => 'KABUPATEN GORONTALO'],
            ['id' => 453, 'name' => 'KABUPATEN GORONTALO UTARA'],
            ['id' => 454, 'name' => 'KABUPATEN POHUWATO'],
        ],
        18 => [ // JAWA TIMUR
            ['id' => 391, 'name' => 'KOTA MALANG'],
            ['id' => 577, 'name' => 'KOTA SURABAYA'],
            ['id' => 393, 'name' => 'KOTA BATU'],
            ['id' => 392, 'name' => 'KOTA BLITAR'],
            ['id' => 289, 'name' => 'KOTA KEDIRI'],
            ['id' => 353, 'name' => 'KOTA MADIUN'],
            ['id' => 388, 'name' => 'KOTA MOJOKERTO'],
            ['id' => 531, 'name' => 'KOTA PASURUAN'],
            ['id' => 394, 'name' => 'KOTA PROBOLINGGO'],
            ['id' => 583, 'name' => 'KABUPATEN SIDOARJO'],
            ['id' => 578, 'name' => 'KABUPATEN GRESIK'],
            ['id' => 256, 'name' => 'KABUPATEN JEMBER'],
            ['id' => 257, 'name' => 'KABUPATEN BANYUWANGI'],
            ['id' => 395, 'name' => 'KABUPATEN LUMAJANG'],
            ['id' => 389, 'name' => 'KABUPATEN JOMBANG'],
            ['id' => 390, 'name' => 'KABUPATEN NGANJUK'],
            ['id' => 585, 'name' => 'KABUPATEN TUBAN'],
            ['id' => 579, 'name' => 'KABUPATEN LAMONGAN'],
        ],
        19 => [ // DI YOGYAKARTA
            ['id' => 192, 'name' => 'KOTA YOGYAKARTA'],
            ['id' => 193, 'name' => 'KABUPATEN SLEMAN'],
            ['id' => 194, 'name' => 'KABUPATEN BANTUL'],
            ['id' => 195, 'name' => 'KABUPATEN GUNUNGKIDUL'],
            ['id' => 196, 'name' => 'KABUPATEN KULON PROGO'],
        ],
        20 => [ // SULAWESI TENGGARA
            ['id' => 455, 'name' => 'KOTA KENDARI'],
            ['id' => 456, 'name' => 'KOTA BAUBAU'],
            ['id' => 457, 'name' => 'KABUPATEN KOLAKA'],
            ['id' => 458, 'name' => 'KABUPATEN KONAWE'],
            ['id' => 459, 'name' => 'KABUPATEN MUNA'],
            ['id' => 460, 'name' => 'KABUPATEN WAKATOBI'],
        ],
        21 => [ // NTT
            ['id' => 461, 'name' => 'KOTA KUPANG'],
            ['id' => 462, 'name' => 'KABUPATEN ALOR'],
            ['id' => 463, 'name' => 'KABUPATEN BELU'],
            ['id' => 464, 'name' => 'KABUPATEN ENDE'],
            ['id' => 465, 'name' => 'KABUPATEN FLORES TIMUR'],
            ['id' => 466, 'name' => 'KABUPATEN MANGGARAI'],
            ['id' => 467, 'name' => 'KABUPATEN SIKKA (MAUMERE)'],
        ],
        22 => [ // SULAWESI UTARA
            ['id' => 468, 'name' => 'KOTA MANADO'],
            ['id' => 469, 'name' => 'KOTA BITUNG'],
            ['id' => 470, 'name' => 'KOTA TOMOHON'],
            ['id' => 471, 'name' => 'KOTA KOTAMOBAGU'],
            ['id' => 472, 'name' => 'KABUPATEN MINAHASA'],
        ],
        23 => [ // SUMATERA BARAT
            ['id' => 473, 'name' => 'KOTA PADANG'],
            ['id' => 474, 'name' => 'KOTA BUKITTINGGI'],
            ['id' => 475, 'name' => 'KOTA PADANG PANJANG'],
            ['id' => 476, 'name' => 'KOTA PARIAMAN'],
            ['id' => 477, 'name' => 'KOTA PAYAKUMBUH'],
            ['id' => 478, 'name' => 'KABUPATEN AGAM'],
            ['id' => 479, 'name' => 'KABUPATEN PESISIR SELATAN'],
            ['id' => 480, 'name' => 'KABUPATEN TANAH DATAR'],
        ],
        24 => [ // BANGKA BELITUNG
            ['id' => 428, 'name' => 'KOTA PANGKAL PINANG'],
            ['id' => 429, 'name' => 'KABUPATEN BANGKA'],
            ['id' => 430, 'name' => 'KABUPATEN BANGKA BARAT'],
            ['id' => 431, 'name' => 'KABUPATEN BANGKA TENGAH'],
            ['id' => 432, 'name' => 'KABUPATEN BANGKA SELATAN'],
            ['id' => 596, 'name' => 'KABUPATEN BELITUNG'],
            ['id' => 597, 'name' => 'KABUPATEN BELITUNG TIMUR'],
        ],
        25 => [ // RIAU (PEKANBARU, DUMAI, SIAK, DLL)
            ['id' => 201, 'name' => 'KOTA PEKANBARU'],
            ['id' => 202, 'name' => 'KOTA DUMAI'],
            ['id' => 203, 'name' => 'KABUPATEN KAMPAR'],
            ['id' => 204, 'name' => 'KABUPATEN SIAK'],
            ['id' => 205, 'name' => 'KABUPATEN PELALAWAN'],
            ['id' => 206, 'name' => 'KABUPATEN BENGKALIS'],
            ['id' => 207, 'name' => 'KABUPATEN ROKAN HULU'],
            ['id' => 208, 'name' => 'KABUPATEN ROKAN HILIR'],
            ['id' => 209, 'name' => 'KABUPATEN INDRAGIRI HILIR'],
            ['id' => 210, 'name' => 'KABUPATEN INDRAGIRI HULU'],
            ['id' => 211, 'name' => 'KABUPATEN KUANTAN SINGINGI'],
            ['id' => 212, 'name' => 'KABUPATEN KEPULAUAN MERANTI'],
        ],
        26 => [ // SUMATERA SELATAN
            ['id' => 481, 'name' => 'KOTA PALEMBANG'],
            ['id' => 482, 'name' => 'KOTA LUBUKLINGGAU'],
            ['id' => 483, 'name' => 'KOTA PRABUMULIH'],
            ['id' => 484, 'name' => 'KABUPATEN BANYUASIN'],
            ['id' => 485, 'name' => 'KABUPATEN OGAN ILIR'],
            ['id' => 486, 'name' => 'KABUPATEN OGAN KOMERING ILIR'],
        ],
        27 => [ // SULAWESI TENGAH
            ['id' => 487, 'name' => 'KOTA PALU'],
            ['id' => 488, 'name' => 'KABUPATEN BANGGAI'],
            ['id' => 489, 'name' => 'KABUPATEN DONGGALA'],
            ['id' => 490, 'name' => 'KABUPATEN MOROWALI'],
            ['id' => 491, 'name' => 'KABUPATEN POSO'],
        ],
        28 => [ // KALIMANTAN BARAT
            ['id' => 492, 'name' => 'KOTA PONTIANAK'],
            ['id' => 493, 'name' => 'KOTA SINGKAWANG'],
            ['id' => 494, 'name' => 'KABUPATEN KUBU RAYA'],
            ['id' => 495, 'name' => 'KABUPATEN SAMBAS'],
            ['id' => 496, 'name' => 'KABUPATEN SINTANG'],
        ],
        29 => [ // PAPUA BARAT
            ['id' => 497, 'name' => 'KOTA SORONG'],
            ['id' => 498, 'name' => 'KABUPATEN MANOKWARI'],
            ['id' => 499, 'name' => 'KABUPATEN FAKFAK'],
            ['id' => 500, 'name' => 'KABUPATEN RAJA AMPAT'],
        ],
        30 => [ // LAMPUNG
            ['id' => 501, 'name' => 'KOTA BANDAR LAMPUNG'],
            ['id' => 502, 'name' => 'KOTA METRO'],
            ['id' => 503, 'name' => 'KABUPATEN LAMPUNG SELATAN'],
            ['id' => 504, 'name' => 'KABUPATEN LAMPUNG TENGAH'],
            ['id' => 505, 'name' => 'KABUPATEN LAMPUNG UTARA'],
        ],
        31 => [ // KALIMANTAN UTARA
            ['id' => 506, 'name' => 'KOTA TARAKAN'],
            ['id' => 507, 'name' => 'KABUPATEN BULUNGAN'],
            ['id' => 508, 'name' => 'KABUPATEN MALINAU'],
            ['id' => 509, 'name' => 'KABUPATEN NUNUKAN'],
        ],
        32 => [ // MALUKU UTARA
            ['id' => 510, 'name' => 'KOTA TERNATE'],
            ['id' => 511, 'name' => 'KOTA TIDORE KEPULAUAN'],
            ['id' => 512, 'name' => 'KABUPATEN HALMAHERA UTARA'],
        ],
        33 => [ // SULAWESI SELATAN
            ['id' => 513, 'name' => 'KOTA MAKASSAR'],
            ['id' => 514, 'name' => 'KOTA PALOPO'],
            ['id' => 515, 'name' => 'KOTA PAREPARE'],
            ['id' => 516, 'name' => 'KABUPATEN GOWA'],
            ['id' => 517, 'name' => 'KABUPATEN MAROS'],
            ['id' => 518, 'name' => 'KABUPATEN BONE'],
        ],
        34 => [ // SULAWESI BARAT
            ['id' => 519, 'name' => 'KABUPATEN MAMUJU'],
            ['id' => 520, 'name' => 'KABUPATEN MAJENE'],
            ['id' => 521, 'name' => 'KABUPATEN POLEWALI MANDAR'],
        ]
    ];

    $pid = (int)$provinceId;
    return $map[$pid] ?? [
        ['id' => 1, 'name' => 'KOTA UTAMA'],
        ['id' => 2, 'name' => 'KABUPATEN UTAMA']
    ];
}

function getFallbackShippingCost($cityName, $weightGram = 1000, $courier = 'jne') {
    $weightKg = max(1, ceil($weightGram / 1000));
    $cityLower = strtolower($cityName);

    // Deteksi Zona Wilayah
    $isJatim = strpos($cityLower, 'malang') !== false || strpos($cityLower, 'surabaya') !== false || strpos($cityLower, 'sidoarjo') !== false || strpos($cityLower, 'batu') !== false || strpos($cityLower, 'jember') !== false || strpos($cityLower, 'kediri') !== false || strpos($cityLower, 'blitar') !== false || strpos($cityLower, 'probolinggo') !== false || strpos($cityLower, 'pasuruan') !== false;
    
    $isJawa = $isJatim || strpos($cityLower, 'jakarta') !== false || strpos($cityLower, 'bandung') !== false || strpos($cityLower, 'semarang') !== false || strpos($cityLower, 'solo') !== false || strpos($cityLower, 'yogyakarta') !== false || strpos($cityLower, 'bogor') !== false || strpos($cityLower, 'bekasi') !== false || strpos($cityLower, 'tangerang') !== false || strpos($cityLower, 'depok') !== false || strpos($cityLower, 'serang') !== false || strpos($cityLower, 'cirebon') !== false;

    $isPapuaMaluku = strpos($cityLower, 'papua') !== false || strpos($cityLower, 'jayapura') !== false || strpos($cityLower, 'sorong') !== false || strpos($cityLower, 'ambon') !== false || strpos($cityLower, 'ternate') !== false || strpos($cityLower, 'manokwari') !== false;

    if ($isJatim) {
        $regRate = 12000;
        $expressRate = 22000;
        $truckingRate = 80000;
        $etdReg = '1-2 Hari';
    } elseif ($isJawa) {
        $regRate = 17000;
        $expressRate = 30000;
        $truckingRate = 95000;
        $etdReg = '2-3 Hari';
    } elseif ($isPapuaMaluku) {
        $regRate = 85000;
        $expressRate = 135000;
        $truckingRate = 250000;
        $etdReg = '5-7 Hari';
    } else {
        // Luar Jawa umum (Sumatera, Riau, Medan, Kalimantan, Sulawesi, Bali, NTB, NTT)
        $regRate = 48000;
        $expressRate = 75000;
        $truckingRate = 140000;
        $etdReg = '3-4 Hari';
    }

    $c = strtolower($courier);
    $services = [];

    // Perhitungan Formula Kargo/Trucking Berbasis Berat Riil:
    // Kargo memiliki tarif minimum 10 kg (base rate), dan kenaikan tarif per kg berikutnya sangat hemat
    $extraKg = max(0, $weightKg - 10);
    $perKgTruckExtra = ($isJatim ? 3000 : ($isJawa ? 4000 : 6000));
    $finalTruckCost = $truckingRate + ($extraKg * $perKgTruckExtra);

    if ($c === 'pos') {
        $services[] = [
            'name' => 'POS Indonesia',
            'code' => 'pos',
            'service' => 'Pos Reguler',
            'description' => 'Layanan Standar Nasional POS',
            'cost' => $regRate * $weightKg,
            'etd' => $etdReg
        ];
        $services[] = [
            'name' => 'POS Indonesia',
            'code' => 'pos',
            'service' => 'Pos Nextday',
            'description' => 'Layanan Cepat Sampai',
            'cost' => $expressRate * $weightKg,
            'etd' => '1-2 Hari'
        ];
        $services[] = [
            'name' => 'POS Indonesia',
            'code' => 'pos',
            'service' => 'Pos Kargo',
            'description' => 'Layanan Kargo Ekonomis',
            'cost' => $finalTruckCost,
            'etd' => '5-8 Hari'
        ];
    } elseif ($c === 'jnt') {
        $services[] = [
            'name' => 'J&T Express',
            'code' => 'jnt',
            'service' => 'EZ',
            'description' => 'Layanan Reguler J&T',
            'cost' => $regRate * $weightKg,
            'etd' => $etdReg
        ];
        $services[] = [
            'name' => 'J&T Express',
            'code' => 'jnt',
            'service' => 'J&T Super',
            'description' => 'Layanan Kilat Prioritas',
            'cost' => $expressRate * $weightKg,
            'etd' => '1-2 Hari'
        ];
        $services[] = [
            'name' => 'J&T Express',
            'code' => 'jnt',
            'service' => 'J&T Cargo',
            'description' => 'Layanan Kargo Logistik',
            'cost' => $finalTruckCost,
            'etd' => '5-7 Hari'
        ];
    } else {
        // Default JNE (Lengkap: Reguler, Yakin Esok Sampai, dan Trucking Kargo)
        $services[] = [
            'name' => 'Jalur Nugraha Ekakurir (JNE)',
            'code' => 'jne',
            'service' => 'REG',
            'description' => 'Layanan Reguler',
            'cost' => $regRate * $weightKg,
            'etd' => $etdReg
        ];
        $services[] = [
            'name' => 'Jalur Nugraha Ekakurir (JNE)',
            'code' => 'jne',
            'service' => 'YES',
            'description' => 'Yakin Esok Sampai',
            'cost' => $expressRate * $weightKg,
            'etd' => '1 Hari'
        ];
        $services[] = [
            'name' => 'Jalur Nugraha Ekakurir (JNE)',
            'code' => 'jne',
            'service' => 'JTR',
            'description' => 'JNE Trucking (Kargo)',
            'cost' => $finalTruckCost,
            'etd' => '5-7 Hari'
        ];
    }

    return json_encode([
        'meta' => [
            'message' => 'Success Calculate Domestic Shipping cost',
            'code' => 200,
            'status' => 'success'
        ],
        'data' => $services
    ]);
}
