part of elements;

class Calendar {
  DateTime _showDate, _now = new DateTime.now();

  final DivElement element = new DivElement(), nav = new DivElement(), leftNav = new DivElement(), rightNav = new DivElement();

  final SpanElement navText = new SpanElement();

  TableElement _table = new TableElement();

  Map<int, TableCellElement> _cellMap = new Map<int, TableCellElement>();

  Calendar() {
    date = _now;
    leftNav.classes..add('nav')..add('left_nav');
    leftNav.append(new DivElement());
    rightNav.classes..add('nav')..add('right_nav');
    rightNav.append(new DivElement());

    rightNav.onClick.listen((_) => showNextMonth());
    leftNav.onClick.listen((_) => showPrevMonth());

    nav..append(leftNav)..append(rightNav)..append(navText)..classes.add('calendar_nav');

    element..append(nav)..append(_table)..classes.add('calendar');
  }

  bool get showNav => nav.hidden;

  set showNav(bool b) => nav.hidden = b;

  Element markDate(DateTime date) {
    var cell = _createCell(date);
    cell.classes.add('marked');
    return cell;
  }

  String _dateToString(DateTime dt) {
    var m = ["", "Jan", "Feb", "Mar", "Apr", "Maj", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dec"];
    return "${m[dt.month]} ${dt.year.toString()}";
  }

  void showNextMonth() {
    date = new DateTime(_showDate.year, _showDate.month + 1);
  }

  void showPrevMonth() {
    date = new DateTime(_showDate.year, _showDate.month - 1);
  }

  DateTime get date => _showDate;

  set date(DateTime dt) {
    _showDate = dt;
    navText.text = _dateToString(_showDate);
    _table.children.clear();
    var d = dt.subtract(new Duration(days:dt.day + ((dt.weekday - dt.day) % 7) - 1));
    while (_table.children.length < 6) {
      var row = new TableRowElement();
      for (var i = 0;i < 7;i++) {
        row.append(_createCell(d));
        d = d.add(new Duration(days:1));
      }

      _table.append(row);
    }

  }

  TableCellElement _createCell(DateTime dt) {
    var cell = _cellMap.putIfAbsent(dt.year * 10000 + dt.month * 100 + dt.day, () => new TableCellElement());
    if (cell.text.length == 0) {
      cell.text = "${dt.day}";
      if (dt.day == _now.day && dt.month == _now.month && dt.year == _now.year) {
        cell.classes.add('today');
      }

    }
    if (dt.month != _showDate.month) {
      cell.classes.add('another_month');
    } else {
      cell.classes.remove('another_month');
    }
    return cell;
  }
}

