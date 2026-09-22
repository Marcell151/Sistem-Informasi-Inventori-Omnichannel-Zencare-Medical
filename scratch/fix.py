import xml.etree.ElementTree as ET

tree = ET.parse('scratch/usecase_rearranged.xml')
model = tree.getroot()
root_node = model.find('root')

for cell in list(model):
    if cell.tag == 'mxCell' and cell.get('id') and cell.get('id').startswith('swim_'):
        model.remove(cell)
        root_node.append(cell)

tree.write('scratch/usecase_fixed.xml', encoding='utf-8', xml_declaration=False)
