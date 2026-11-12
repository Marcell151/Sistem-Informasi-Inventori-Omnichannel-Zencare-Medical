with open('lain/wireframe_zencare.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

import re
print("Length of wireframe_zencare.html:", len(text))
pos_data = text.find('const wireframeData = [')
if pos_data != -1:
    pos_data_end = text.find('];', pos_data)
    data_str = text[pos_data:pos_data_end+2]
    print(f"wireframeData found! Length: {len(data_str)}")
    # Find all items
    items = re.findall(r'\{\s*"id":\s*"([^"]+)"', data_str)
    print("Items in wireframeData:", items)

