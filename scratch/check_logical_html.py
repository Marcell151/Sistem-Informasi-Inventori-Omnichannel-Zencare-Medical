with open('lain/logical_database_zencare.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

print("HTML length:", len(text))
import re
print("Has svg?:", "<svg" in text)
print("Has dbml?:", "dbml" in text.lower())
# Let's see title and main elements
print("Title:", re.findall(r'<title>(.*?)</title>', text))
m_img = re.findall(r'<img[^>]+>', text)
print("Images in html:", m_img)

