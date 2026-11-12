from PIL import Image

im = Image.open('scratch/ba_screenshot_fixed.png')
# Crop the table header area (x from 0 to 1400, y from 180 to 350)
crop_header = im.crop((0, 180, 1400, 350))
crop_header.save('scratch/header_crop.png')
print("Cropped header area, size:", crop_header.size)

