with open('lain/semua_activity_diagram_zencare.html', 'r', encoding='utf-8') as f:
    c = f.read()

pos = c.find('id="tab_h"')
if pos != -1:
    end_pos = c.find('<!-- FOOTER', pos)
    if end_pos == -1:
        end_pos = c.find('</div>\n    </div>\n</body>', pos)
    print(c[pos:end_pos])

