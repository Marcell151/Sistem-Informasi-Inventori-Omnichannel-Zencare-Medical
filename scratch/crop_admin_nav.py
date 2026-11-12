from PIL import Image

im = Image.open('scratch/admin_opname_ui_clean.png')
w, h = im.size
print("Image size:", w, h)

# Crop around the navigation area and top of table
# In a 1400x1000 window, the main content starts around y=250 to y=750
crop_area = im.crop((240, 260, 1360, 750))
crop_area.save('scratch/admin_opname_nav_crop.png')
print("Saved crop!")

