part of user_settings;


class UserSettingsLoggerInitializer extends core.Initializer {

  TableElement _logTable = querySelector("#UserSettingsLogTable");

  AnchorElement _logLink = querySelector("#ClearLogLink");

  ParagraphElement _pElm = querySelector("#LogInfoParagraph");

  DivElement _numDiv = new DivElement();


  UserSettingsLoggerInitializer() {
    _numDiv.classes.add("num");
  }

  bool get canBeSetUp => _logTable != null && _logLink != null && _pElm != null;

  void _updateNum() {
    if (_logTable.classes.contains("empty")) {
      _numDiv.remove();
      return;
    }
    _numDiv.text = _logTable.querySelectorAll("tr:not(.empty_row)").length.toString();
    var p = querySelector("#UserSettingsMenu .log");
    p.append(_numDiv);

  }

  void setUp() {
    _updateNum();

    var addDumpListener = (AnchorElement a) {
      a.onClick.listen((MouseEvent evt) {
        var loader = dialogContainer.loading("Henter log filen");
        logger.contextAt(new DateTime.fromMillisecondsSinceEpoch(int.parse(a.dataset["id"]) * 1000)).then((core.Response<Map> resp) {
          if (resp.type != core.Response.RESPONSE_TYPE_SUCCESS) {
            loader.close();
            return;
          }

          var button = new ButtonElement(), pre = new PreElement();
          button.text = "Luk";
          button.onClick.listen((_) => loader.close());
          pre.classes.add("code");
          pre.text = resp.payload.toString();
          loader.element
            ..children.clear()
            ..append(pre)
            ..append(button);
          loader.stopLoading();
          core.escQueue.add(() {
            loader.close();
            return true;
          });
        });
      });
    };

    logger.onLog.listen((LogEntry entry) {
      _logTable.classes.remove('empty');
      var row = _logTable.insertRow(0);
      var levelStrings = entry.levelStrings;
      row.classes.addAll(levelStrings);

      var c1 = row.addCell();
      c1
        ..title = levelStrings.map(core.upperCaseWords).join(" ")
        ..classes.add("level");
      var c2 = row.addCell();
      c2.text = entry.message;
      var c3 = row.addCell();
      c3.classes.add("dumpfile");
      if (entry.context != null) {
        var a = new AnchorElement();
        a
          ..dataset['id'] = entry.id.toString()
          ..href = "#";
        addDumpListener(a);
        c3.append(a);
      }
      var c4 = row.addCell();
      c4
        ..classes.add('date')
        ..text = core.dateString(entry.time);
      _updateNum();
    });

    _logTable.querySelectorAll(".dumpfile a").forEach(addDumpListener);


    _logLink.onClick.listen((MouseEvent evt) {
      _logTable.classes.add('blur');
      logger.clearLog().then((core.Response<Logger> response) {
        _logTable.classes.remove('blur');
        if (response.type == core.Response.RESPONSE_TYPE_SUCCESS) {
          _logTable.querySelectorAll("tr:not(.empty_row)").forEach((TableRowElement li) => li.remove());
          _logTable.classes.add("empty");
          _pElm.querySelectorAll("i").forEach((Element e) => e.text = "0");
          _updateNum();
        }
      });
      evt.preventDefault();
    });

  }
}
