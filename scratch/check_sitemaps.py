import os

sitemap_dir = 'lain/File PPT/Sitemap'
for f in os.listdir(sitemap_dir):
    p = os.path.join(sitemap_dir, f)
    print(f, os.path.getsize(p), 'bytes')

