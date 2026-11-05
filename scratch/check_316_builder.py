# -*- coding: utf-8 -*-
with open('scratch/final_builder_part3.py', 'r', encoding='utf-8') as f:
    text = f.read()

idx = text.find('def get_diagram_316')
print(text[idx:idx+2500])

