part of elements;

class Calendar {
  DateTime _showDate, _now = new DateTime.now();

  final DivElement element, nav, leftNav, rightNav;

  final SpanElement navText;

  final TableElement table;

  Map<int, TableCellElement> _cellMap = new Map<int, TableCellElement>();

  Calendar() : element = new DivElement(), nav = new DivElement(), leftNav = new DivElement(), rightNav = new DivElement(), table = new TableElement(), navText = new SpanElement() {
    date = _now;
    leftNav.classes
      ..add('nav')
      ..add('left_nav');
    leftNav.append(new DivElement());
    rightNav.classes
      ..add('nav')
      ..add('right_nav');
    rightNav.append(new DivElement());

    rightNav.onClick.listen((_) => showNextMonth());
    leftNav.onClick.listen((_) => showPrevMonth());

    nav
      ..append(leftNav)
      ..append(rightNav)
      ..append(navText)
      ..classes.add('calendar_nav');

    element
      ..append(nav)
      ..append(table)
      ..classes.add('calendar');
  }

  Calendar.fromElements(DivElement element, DivElement navDiv, TableElement calendarTable, DateTime showing, DateTime timeMapper(TableCellElement), bool timeMarker(DateTime)) :
  leftNav = navDiv.querySelector('div.left_nav.nav'),
  rightNav = navDiv.querySelector('div.right_nav.nav'),
  table = calendarTable,
  navText = navDiv.querySelector('span'),
  this.element = element,
  nav = navDiv
  {
    _cellMap = new Map.fromIterable(table.querySelectorAll('td'),
    key:(TableCellElement td) => _dateTimeToInt(timeMapper(td)),
    value:(TableCellElement td) => td);
    _cellMap.forEach((_, TableCellElement td){
      var dt = timeMapper(td);
      if(timeMarker(dt)){
        markDate(dt);
      }
    });

    date = showing;
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
    table.children.clear();
    var d = dt.subtract(new Duration(days:dt.day + ((dt.weekday - dt.day) % 7) - 1));
    while (table.children.length < 6) {
      var row = table.addRow();
      for (var i = 0;i < 7;i++) {
        row.append(_createCell(d));
        d = new DateTime(d.year, d.month, d.day + 1);
      }
      table.append(row);
    }

  }

  int _dateTimeToInt(DateTime dt) => dt.year * 10000 + dt.month * 100 + dt.day;

  TableCellElement _createCell(DateTime dt) {
    var cell = _cellMap.putIfAbsent(_dateTimeToInt(dt), () => new TableCellElement());
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

