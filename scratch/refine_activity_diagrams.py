# -*- coding: utf-8 -*-
"""
Script to refine and rebuild all 8 Activity Diagrams:
1. Fix XML entities (replace &ge; with &#8805; or >=)
2. Ensure 100% ORTHOGONAL line routing (only 0, 90, 180, 270 degree segments, zero slanted lines)
3. Fix overlapping lines and margins
4. Remove the word 'farmasi' everywhere and replace with academic health/medical phrasing
5. Re-generate all 8 SVGs and update HTML viewer and Bab III document
"""

import os
import re

print("Starting complete refinement of Activity Diagrams...")

