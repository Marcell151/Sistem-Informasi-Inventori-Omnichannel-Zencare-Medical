from pptx import Presentation
from pptx.util import Pt, Emu
from pptx.dml.color import RGBColor

prs = Presentation(r'C:\xampp\htdocs\inventory_zencare\lain\File PPT\Presentasi_Sempro_Marcell.pptx')
slide = prs.slides[0]

for shape in slide.shapes:
    if shape.shape_type == 1:  # Rectangle
        fill = shape.fill
        try:
            if fill.fore_color.rgb:
                print(f'Rect {shape.name}: fill={fill.fore_color.rgb} w={shape.width.inches:.2f} h={shape.height.inches:.2f} top={shape.top.inches:.2f} left={shape.left.inches:.2f}')
        except:
            pass

# Also get slide dimensions
print(f'Slide W={prs.slide_width.inches:.2f} H={prs.slide_height.inches:.2f}')

# Check all slides for layout patterns
for i, s in enumerate(prs.slides[:3]):
    print(f'\n--- Slide {i+1} ---')
    for shape in s.shapes:
        if shape.shape_type == 1:
            try:
                print(f'  Rect {shape.name} color={shape.fill.fore_color.rgb}')
            except:
                pass
