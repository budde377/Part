part of core;

class Color {
  final int r, g, b, a ;

  Color(this.r, this.g, this.b, [int this.a = 1]);

  Color.fromHexString(String hex): r = _calcRFromHex(hex), g = _calcGFromHex(hex), b = _calcBFromHex(hex), a = 1;

  Color.fromRGBString(String rgb): r = _calcRFromRGB(rgb), g = _calcGFromRGB(rgb), b = _calcBFromRGB(rgb), a = _calcAFromRGB(rgb);

  static _calcRFromRGB(String s) => _calcFromRGB(0, s);

  static _calcGFromRGB(String s) => _calcFromRGB(1, s);

  static _calcBFromRGB(String s) => _calcFromRGB(2, s);

  static _calcAFromRGB(String s) => _calcFromRGB(3, s, 1);

  static _calcFromRGB(int index, String rgb, [defaultValue = 0]) {
    var regexp = new RegExp(r"rgba?\(([^,]*),([^,]*),(([^,]*),?([^\)]*)\))?");
    var match = regexp.firstMatch(rgb);
    if (match == null) {
      return defaultValue;
    }
    var vars = [match.group(1), match.group(2), match.group(4), match.group(5).trim().length == 0 ? defaultValue.toString() : match.group(5)];
    return int.parse(vars[index]);
  }

  static _calcRFromHex(String s) => _calcFromHex(0, s);

  static _calcGFromHex(String s) => _calcFromHex(1, s);

  static _calcBFromHex(String s) => _calcFromHex(2, s);

  static _calcFromHex(int index, String hex) => hex.length == 6 ? int.parse("0x" + hex.substring(2 * index, 2 + 2 * index)) : (hex.length == 3 ? int.parse("0x" + hex.substring(index, 1 + index)) : 0);

  String get hex => _hexString(r) + _hexString(g) + _hexString(b);

  String _hexString(int i) {
    var r = i.toRadixString(16);
    return r.length == 0 ? "00" : (r.length == 1 ? "0${r}" : r.substring(0, 2));
  }

  bool operator ==(Color c) => c.r == r && c.g == g && c.b == b && c.a == a;

  get hashCode=>"${r}+${g}+${b}+${a}".hashCode;
}


class ColorPalette {
  final List<Color> greyScale = new List<Color>(), baseColors = new List<Color>();

  final List<List<Color>> variations = new List<List<Color>>();

  final Element element = new TableElement();

  Map<Color, Element> _colorElementsMap = new Map<Color, Element>();

  Color _selected;


  ColorPalette() {
    _initializeColors();
    _buildTable();
    element.classes.add("color_palette");
  }

  void _initializeColors() {
    greyScale.add(new Color(0, 0, 0));
    for (var i = 9; i >= 1; i--) {
      greyScale.add(new Color(255 ~/ i, 255 ~/ i, 255 ~/ i));
    }

    baseColors..add(new Color(152, 0, 0))..add(new Color(255, 0, 0))..add(new Color(255, 152, 0))..add(new Color(255, 255, 0))..add(new Color(0, 255, 0))..add(new Color(0, 255, 255))..add(new Color(74, 134, 255))..add(new Color(0, 0, 255))..add(new Color(152, 0, 255))..add(new Color(255, 0, 255));
    var l;
    variations.add(l = new List<Color>());
    l..add(new Color(230, 184, 175))..add(new Color(244, 204, 204))..add(new Color(252, 229, 205))..add(new Color(255, 242, 204))..add(new Color(217, 234, 211))..add(new Color(208, 224, 227))..add(new Color(201, 218, 248))..add(new Color(207, 226, 243))..add(new Color(217, 210, 233))..add(new Color(234, 209, 220));

    variations.add(l = new List<Color>());
    l..add(new Color(221, 126, 107))..add(new Color(234, 153, 153))..add(new Color(249, 203, 156))..add(new Color(255, 229, 153))..add(new Color(182, 215, 168)) ..add(new Color(162, 196, 201))..add(new Color(164, 194, 244))..add(new Color(159, 197, 232))..add(new Color(180, 167, 214))..add(new Color(213, 166, 189));

    variations.add(l = new List<Color>());
    l..add(new Color(204, 65, 37))..add(new Color(224, 102, 102))..add(new Color(246, 178, 107))..add(new Color(255, 217, 102))..add(new Color(147, 196, 125))..add(new Color(118, 165, 175))..add(new Color(109, 158, 235))..add(new Color(111, 168, 220))..add(new Color(142, 124, 195))..add(new Color(194, 123, 160));

    variations.add(l = new List<Color>());
    l..add(new Color(166, 28, 0))..add(new Color(204, 0, 0))..add(new Color(230, 146, 50))..add(new Color(241, 194, 50))..add(new Color(106, 168, 79))..add(new Color(69, 129, 142))..add(new Color(60, 120, 216))..add(new Color(61, 133, 198))..add(new Color(103, 78, 167))..add(new Color(166, 77, 121));


    variations.add(l = new List<Color>());
    l..add(new Color(133, 32, 12))..add(new Color(153, 0, 0))..add(new Color(180, 95, 6))..add(new Color(191, 144, 0))..add(new Color(56, 118, 29))..add(new Color(19, 79, 92))..add(new Color(17, 85, 204))..add(new Color(11, 83, 148))..add(new Color(53, 28, 117))..add(new Color(166, 27, 71));

    variations.add(l = new List<Color>());
    l..add(new Color(91, 15, 0))..add(new Color(102, 0, 0))..add(new Color(120, 63, 4))..add(new Color(127, 96, 0))..add(new Color(39, 78, 19))..add(new Color(12, 52, 61))..add(new Color(28, 69, 135))..add(new Color(7, 55, 99))..add(new Color(76, 17, 48))..add(new Color(76, 17, 48));

  }


  set selected(Color color) {
    if (color == _selected) {
      return;
    }
    var found = false;
    _colorElementsMap.forEach((Color c, Element e) {
      if (color == c) {
        found = true;
        if (_selected != null) {
          _colorElementsMap[_selected].classes.remove('selected');
        }
        _selected = c;
        e.classes.add("selected");
      }
    });
    if (found || _selected == null) {
      return;
    }

    _colorElementsMap[_selected].classes.remove('selected');
    _selected = null;
  }

  Color get selected => _selected;


  _buildTable() {
    var tr;
    element.append(tr = _buildRow(greyScale));
    tr.classes.add('greyscale');
    element.append(tr = _buildRow(baseColors));
    tr.classes.add('basecolors');
    variations.forEach((List<Color> l) => element.append(_buildRow(l)

    )

    );
  }

  _buildRow(List<Color> colors) {
    var tr = new TableRowElement();
    colors.forEach((Color c) {
      var td = new TableCellElement(), div = new DivElement();
      td.append(div);
      td.onClick.listen((_){
        selected = c;
        element.dispatchEvent(new Event("change", canBubble:false, cancelable:true));
      });
      div.style.backgroundColor = "#${c.hex}";
      tr.append(td);
      _colorElementsMap[c] = td;
    });
    return tr;
  }


}