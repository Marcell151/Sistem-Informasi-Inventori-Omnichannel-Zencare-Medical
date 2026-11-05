import json

with open(r'scratch\update_wireframe_tool_27.py', 'r', encoding='utf-8') as f:
    text = f.read()

# Extract pages_data definition from update_wireframe_tool_27.py
start_marker = "pages_data = ["
end_marker = "print(f\"Total pages: {len(pages_data)}\")"
p_start = text.find(start_marker)
p_end = text.find(end_marker)
pages_data_code = text[p_start:p_end].strip()

# Now read build_complete_wireframe_tool.py
with open(r'scratch\build_complete_wireframe_tool.py', 'r', encoding='utf-8') as f:
    tool_text = f.read()

# In build_complete_wireframe_tool.py, replace from "pages_data = [" to "html_template = f'''"
t_start = tool_text.find("pages_data = [")
t_end = tool_text.find("html_template = f'''")

new_tool_text = tool_text[:t_start] + pages_data_code + "\n\n" + tool_text[t_end:]

with open(r'scratch\build_complete_wireframe_tool.py', 'w', encoding='utf-8') as f:
    f.write(new_tool_text)

print("build_complete_wireframe_tool.py successfully updated!")

