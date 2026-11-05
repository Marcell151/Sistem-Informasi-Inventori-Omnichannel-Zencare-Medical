with open('pos/pos.php', 'rb') as f:
    content = f.read()

# search for icon_toggle_drawer in bytes
idx = content.find(b'icon_toggle_drawer')
while idx != -1:
    print(content[idx-30:idx+80])
    idx = content.find(b'icon_toggle_drawer', idx + 1)

