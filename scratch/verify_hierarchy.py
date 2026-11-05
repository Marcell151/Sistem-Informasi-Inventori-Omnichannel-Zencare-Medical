with open(r'laporan_ta\BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

p_start = text.find('id="full_bab3_content"')
s_34 = text.find('id="sec_3_4"')
s_35 = text.find('id="sec_3_5"')
s_36 = text.find('id="sec_3_6"')
p_end = text.find('Closes full_bab3_content paper')
notif = text.find('id="bottom_notification"')

print(f'Paper Start: {p_start}')
print(f'Subbab 3.4 : {s_34}')
print(f'Subbab 3.5 : {s_35}')
print(f'Subbab 3.6 : {s_36}')
print(f'Paper End  : {p_end}')
print(f'BottomNotif: {notif}')

assert p_start < s_34 < s_35 < s_36 < p_end < notif, 'Hierarchy check failed!'
print('ALL CHECKS PASSED PERFECTLY!')

