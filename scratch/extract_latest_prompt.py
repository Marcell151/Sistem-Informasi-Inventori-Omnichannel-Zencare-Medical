import json

last_user_content = ""
with open(r'C:\Users\Marcell\.gemini\antigravity\brain\13d0fe16-dde0-4c4c-837c-637a5d24e784\.system_generated\logs\transcript_full.jsonl', 'r', encoding='utf-8') as f:
    for line in f:
        data = json.loads(line)
        if data.get('source') == 'USER_EXPLICIT' and data.get('type') == 'USER_INPUT':
            last_user_content = data.get('content', '')

with open('scratch/latest_user_prompt.txt', 'w', encoding='utf-8') as out:
    out.write(last_user_content)

print('Wrote latest_user_prompt.txt, length:', len(last_user_content))
print('Preview:', last_user_content[:200])

