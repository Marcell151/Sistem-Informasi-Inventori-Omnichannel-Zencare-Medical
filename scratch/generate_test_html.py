import re
import json

with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    c = f.read()

divs = re.findall(r'<div class="mermaid[^"]*">([\s\S]*?)</div>', c)

blocks_json = json.dumps(divs)

html_content = f"""<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
</head>
<body>
    <h1>Mermaid Parsing Test</h1>
    <div id="results"></div>
    <script>
        const blocks = {blocks_json};
        mermaid.initialize({{ startOnLoad: false }});
        async function run() {{
            const summary = [];
            for (let i = 0; i < blocks.length; i++) {{
                const code = blocks[i];
                try {{
                    await mermaid.parse(code);
                    summary.push(`BLOCK ${{i+1}}: SUCCESS`);
                }} catch (e) {{
                    summary.push(`BLOCK ${{i+1}}: SYNTAX ERROR - ${{e.message || e}}`);
                }}
            }}
            await fetch('http://localhost/inventory_zencare/scratch/save_result.php', {{
                method: 'POST',
                headers: {{ 'Content-Type': 'application/x-www-form-urlencoded' }},
                body: 'data=' + encodeURIComponent(summary.join('\\n'))
            }});
        }}
        run();
    </script>
</body>
</html>
"""

with open('scratch/test_mermaid_browser.html', 'w', encoding='utf-8') as f:
    f.write(html_content)

print("Generated test_mermaid_browser.html")
