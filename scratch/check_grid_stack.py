from html.parser import HTMLParser

class TagStackPrinter(HTMLParser):
    def __init__(self):
        super().__init__()
        self.stack = []

    def handle_starttag(self, tag, attrs):
        attr_dict = dict(attrs)
        cls = attr_dict.get('class', '')
        self.stack.append((tag, cls))
        if 'grid grid-cols-1' in cls:
            print("Found grid! Current stack:")
            for s_tag, s_cls in self.stack:
                print(f"  <{s_tag} class='{s_cls[:50]}'>")

    def handle_endtag(self, tag):
        if self.stack:
            # find matching tag from top
            for i in range(len(self.stack) - 1, -1, -1):
                if self.stack[i][0] == tag:
                    self.stack = self.stack[:i]
                    break

with open("scratch/rendered_stok_opname.html", "r", encoding="utf-8") as f:
    content = f.read()

pos = content.find("</aside>")
parser = TagStackPrinter()
parser.feed(content[pos+8:])

