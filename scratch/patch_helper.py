with open('config/opname_helper.php', 'r', encoding='utf-8') as f:
    text = f.read()

old_fn = """    function cekSesiOpnameAktif($pdo) {
        static $cachedSesi = null;
        if ($cachedSesi !== null) {
            return $cachedSesi;
        }"""

new_fn = """    function cekSesiOpnameAktif($pdo, $forceRefresh = false) {
        static $cachedSesi = null;
        if ($forceRefresh) {
            $cachedSesi = null;
        }
        if ($cachedSesi !== null) {
            return $cachedSesi;
        }"""

assert old_fn in text, "old_fn not found!"
text = text.replace(old_fn, new_fn, 1)

with open('config/opname_helper.php', 'w', encoding='utf-8') as f:
    f.write(text)
print("config/opname_helper.php updated with forceRefresh support!")

