from PIL import Image

im = Image.open(r"C:\Users\Marcell\.gemini\antigravity\brain\13d0fe16-dde0-4c4c-837c-637a5d24e784\.user_uploaded\media_1794199411157_40ef304e.jpg")
w, h = im.size

# Let's crop into 4 quadrants or horizontal slices
# The middle slice where the content is:
box = (0, int(h * 0.1), w, int(h * 0.9))
cropped = im.crop(box)
cropped.save(r"scratch/cropped_content.png")
print("Cropped saved")

