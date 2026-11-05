# -*- coding: utf-8 -*-
"""
Master Builder for ZenCare Medical Activity Diagrams (Gambar 3.10 to 3.17)
- 100% Orthogonal paths (90-degree angles only, M/L horizontal & vertical)
- Strictly valid XML (no &ge;, proper &amp;)
- Zero 'farmasi' (standardized to 'obat dan alat kesehatan', 'logistik medis', etc.)
- Perfectly matches SRS, codebase, and thesis draft
- Professional draw.io visual aesthetic
"""

import os
import re
import xml.etree.ElementTree as ET

target_dir = r'c:\xampp\htdocs\inventory_zencare\lain'
os.makedirs(target_dir, exist_ok=True)

DEFS = '''  <defs>
    <marker id="arrow" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
      <path d="M 0 1 L 10 5 L 0 9 z" fill="#0f172a" />
    </marker>
    <marker id="arrow-red" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
      <path d="M 0 1 L 10 5 L 0 9 z" fill="#dc2626" />
    </marker>
    <marker id="arrow-green" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
      <path d="M 0 1 L 10 5 L 0 9 z" fill="#16a34a" />
    </marker>
    <marker id="arrow-blue" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
      <path d="M 0 1 L 10 5 L 0 9 z" fill="#2563eb" />
    </marker>
    <filter id="shadow" x="-5%" y="-5%" width="110%" height="110%">
      <feDropShadow dx="1" dy="2" stdDeviation="1.5" flood-opacity="0.06"/>
    </filter>
  </defs>'''

