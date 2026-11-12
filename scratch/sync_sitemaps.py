with open('lain/semua_sitemap_zencare.html', 'r', encoding='utf-8') as f:
    content = f.read()

# Let's save a clean standalone copy in scratch/sitemap_lr.html
with open('scratch/sitemap_lr.html', 'w', encoding='utf-8') as f:
    f.write(content)

print("Synchronized scratch/sitemap_lr.html with lain/semua_sitemap_zencare.html")

