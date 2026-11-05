with open(r'scratch\build_subbab_3_6_27.py', 'r', encoding='utf-8') as f:
    code = f.read()

target = """    end_marker = '</div>\\n        <!-- Bottom Notification Banner'
    end_marker_crlf = '</div>\\r\\n        <!-- Bottom Notification Banner'
    end_idx = content.find(end_marker, start_idx)
    if end_idx == -1:
        end_idx = content.find(end_marker_crlf, start_idx)
    if end_idx == -1:
        # Search for closing tag before bottom notification
        notif_idx = content.find('id=\"bottom_notification\"')
        if notif_idx != -1:
            # find last </div> before notif_idx
            end_idx = content.rfind('</div>', start_idx, notif_idx)
            # and another </div> if needed
            end_idx = content.rfind('</div>', start_idx, end_idx)"""

replacement = """    end_marker = '</div> <!-- Closes full_bab3_content paper -->'
    end_idx = content.find(end_marker, start_idx)
    if end_idx == -1:
        end_marker = 'Closes full_bab3_content paper'
        idx2 = content.find(end_marker, start_idx)
        if idx2 != -1:
            end_idx = content.rfind('</div>', start_idx, idx2)"""

code = code.replace(target, replacement)
with open(r'scratch\build_subbab_3_6_27.py', 'w', encoding='utf-8') as f:
    f.write(code)

print("Updated build_subbab_3_6_27.py")

