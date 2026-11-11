from PIL import Image

im = Image.open(r"C:\Users\Marcell\.gemini\antigravity\brain\13d0fe16-dde0-4c4c-837c-637a5d24e784\.user_uploaded\media_1794199411157_40ef304e.jpg")
w, h = im.size

# Let's crop into 4 horizontal sections across X
# Sidebar is roughly x: 0 to 220
# Content is roughly x: 220 to 1024
box1 = (220, 0, 450, h)
box2 = (450, 0, 750, h)
box3 = (750, 0, 1024, h)
im.crop(box1).save("scratch/crop_col1.png")
im.crop(box2).save("scratch/crop_col2.png")
im.crop(box3).save("scratch/crop_col3.png")
print("Col slices saved")

