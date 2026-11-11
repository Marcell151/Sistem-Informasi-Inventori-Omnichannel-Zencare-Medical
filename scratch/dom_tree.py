from html.parser import HTMLParser

class TagHierarchy(HTMLParser):
    def __init__(self):
        super().__init__()
        self.stack = []

    def handle_starttag(self, tag, attrs):
        if tag in ['img', 'br', 'hr', 'input', 'meta', 'link']:
            return
        attr_dict = dict(attrs)
        cls = attr_dict.get('class', '')
        self.stack.append((tag, cls))
        if tag in ['div', 'table', 'h2', 'h3', 'section', 'main']:
            indent = "  " * len(self.stack)
            if 'flex' in cls or 'grid' in cls or 'bg-white' in cls or tag in ['table', 'main']:
                print(f"{indent}<{tag} class='{cls[:50]}'>")

    def handle_endtag(self, tag):
        for i in range(len(self.stack) - 1, -1, -1):
            if self.stack[i][0] == tag:
                self.stack = self.stack[:i]
                break

with open("scratch/rendered_stok_opname.html", "r", encoding="utf-8") as f:
    content = f.read()

pos = content.find("</aside>")
parser = TagHierarchy()
parser.feed(content[pos+8:])
