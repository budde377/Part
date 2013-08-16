part of core;

abstract class SaveStrategy {
  void save(ContentEditor editor);
}

String sizeToString(int bytes) {
  var s = (bytes <= 102 ? "${bytes} B" : (bytes < 1024 * 1024 / 10 ? "${bytes / 1024} KB" : "${bytes / (1024 * 1024)} MB"));
  var r = new RegExp("([0-9]+\.?[0-9]?[0-9]?)[^ ]*(.+)");
  var m = r.firstMatch(s);
  return m[1] + m[2];
}


class JSONSaveStrategy implements SaveStrategy {
  final String ajax_id;

  Function _callback;

  JSON.JSONClient _jsonClient;

  JSONSaveStrategy(this.ajax_id, [void callback(int error_code)]): _callback = (callback == null?(_){}:callback) {
    _jsonClient = new JSON.AJAXJSONClient(ajax_id);

  }

  void save(ContentEditor editor) {
    var function = new JSON.AddContentJSONFunction(editor.id, editor.element.innerHtml);
    _jsonClient.callFunction(function, (JSON.JSONResponse response) {
      _callback(response.type == JSON.RESPONSE_TYPE_SUCCESS ? 0 : response.error_code);
    });

  }
}


class EditorCommandExecutor {
  static final Map<Element, EditorCommandExecutor> _cache = new Map<Element, EditorCommandExecutor>();

  final Element element;

  Function _listenerChain = () {
  };

  bool _inElement = false;

  Function _listenerFunction;

  factory EditorCommandExecutor(Element element) => _cache.putIfAbsent(element, () => new EditorCommandExecutor._internal(element));

  EditorCommandExecutor._internal(this.element){
    _listenerFunction = ([Event _]) {
      _inElement = element.contains(window.getSelection().baseNode);
      _listenerChain();
    };
    element.contentEditable = "true";
    element.document.onSelectionChange.listen(_listenerFunction);
  }


  void _execCommand(String command, {bool userinterface:false, String value:""}) {
    element.document.execCommand(command, userinterface, value);
  }

  void toggleBold() => _execCommand("bold");

  void toggleItalic() => _execCommand("italic");

  void toggleUnderline() => _execCommand("underline");

  void toggleStrikethrough() => _execCommand("strikethrough");

  void toggleSubscript() => _execCommand("subscript");

  void toggleSuperscript() => _execCommand("superscript");

  void removeFormat() => _execCommand("removeformat");

  void toggleOrderedList() => _execCommand("insertorderedlist");

  void toggleUnorderedList() => _execCommand("insertunorderedlist");

  void justifyCenter() => _execCommand("justifycenter");

  void justifyLeft() => _execCommand("justifyleft");

  void justifyRight() => _execCommand("justifyright");

  void justifyFull() => _execCommand("justifyfull");

  void indent() => _execCommand("indent");

  void outdent() => _execCommand("outdent");

  void setFontSize(int size) => _execCommand('fontsize', value:"${Math.max(1, Math.min(7, size))}");

  void setForeColor(Color color) => _execCommand('foreColor', value:"#" + color.hex);

  void setBackColor(Color color) => _execCommand('backColor', value:"#" + color.hex);

  void _formatBlock(String tagName) => _execCommand('formatBlock', value:tagName);

  void insertParagraph() => _execCommand('insertparagraph');

  void formatBlockP() => _formatBlock('p');

  void formatBlockDiv() => _formatBlock('div');

  void formatBlockH1() => _formatBlock('h1');

  void formatBlockH2() => _formatBlock('h2');

  void formatBlockH3() => _formatBlock('h3');

  void formatBlockBlockquote() => _formatBlock('blockquote');

  void formatBlockPre() => _formatBlock('pre');

  bool _commandState(String command) => _inElement && element.document.queryCommandState(command);

  String _commandValue(String command) => _inElement ? element.document.queryCommandValue(command) : "";

  bool get bold => _commandState("bold");

  bool get italic => _commandState("italic");

  bool get underline => _commandState("underline");

  bool get unorderedList => _commandState("insertunorderedlist");

  bool get orderedList => _commandState("insertorderedlist");

  bool get alignLeft => _commandState('justifyleft');

  bool get alignRight => _commandState('justifyright');

  bool get alignCenter => _commandState('justifycenter');

  bool get alignJust => _commandState('justifyfull');

  int get fontSize {
    var s = _commandValue('fontsize');
    if (s.isEmpty) {
      return -1;
    }
    return int.parse(s);
  }

  Color get foreColor => new Color.fromRGBString(_commandValue('foreColor'));

  Color get backColor => new Color.fromRGBString(_commandValue('backColor'));

  String get blockState => _commandValue('formatBlock');

  void triggerCommandStateChangeListener() => _listenerFunction();

  void listenQueryCommandStateChange(void listener()) {
    var l = _listenerChain;
    _listenerChain = () {
      l();
      listener();
    };
  }

}


abstract class EditorHandler {
  final DivElement element;

  final Element dataElement;

}

class EditorGalleryHandler implements EditorHandler {
  final DivElement element;

  final Element dataElement = new ImageElement();

  List<EditorImageHandler> _children = new List<EditorImageHandler>();

  EditorImageHandler original;

  DivElement _imageCount = new DivElement(), _previewContent = new DivElement();

  InfoBox _infoBox;

  EditorGalleryHandler(EditorImageHandler h) : element = h.element, original = h {
    _infoBox = new InfoBox.elementContent(_previewContent);
    _children.add(h);
    element.classes.add('gallery');
    element.append(_imageCount);
    _imageCount..classes.add('image_count')..text = "0";

  }

  void addHandlerToGallery(EditorHandler h) {
    if (h is EditorGalleryHandler) {
      EditorGalleryHandler hh = h;
      _children.addAll(hh.children);
    } else if (h is EditorImageHandler) {
      _children.add(h);
    }
    _imageCount.text = "${_children.length.toString()}";
    original._imageStandIn.style.backgroundImage = "url(${_children.last.dataElement.src})";
  }

  List<EditorImageHandler> get children => new List<EditorImageHandler>.from(_children);
}


class EditorFileHandler implements EditorHandler {
  final DivElement element = new DivElement();

  DivElement _fileStandIn = new DivElement();

  final AnchorElement dataElement;

  final ProgressBar progressBar = new ProgressBar();

  FileProgress _fileProgress;


  EditorFileHandler(AnchorElement dataElement) : this.dataElement = dataElement => _setUp();

  EditorFileHandler.fileProgress(this.dataElement, FileProgress fileProgress, void ready()): _fileProgress = fileProgress{
    var size = new SpanElement();
    size.text = sizeToString(fileProgress.file.size);

    _fileStandIn..text = fileProgress.file.name..append(size);

    _fileProgress.listenOnProgress(() => progressBar.percentage = _fileProgress.progress);
    _fileProgress.listenOnPathAvailable(() {
      dataElement..href = _fileProgress.path..text = fileProgress.file.name;
      progressBar.bar.remove();
      element.classes.remove('uploading');
      ready();
    });
    element..classes.add('uploading')..append(progressBar.bar);
    _setUp();
  }

  void _setUp() {
    element.append(_fileStandIn);
    _fileStandIn.classes.add('standin');
  }
}

class EditorImageHandler implements EditorHandler {
  final DivElement element = new DivElement();

  DivElement _imageStandIn = new DivElement();

  final ImageElement dataElement;

  final ProgressBar progressBar = new ProgressBar();

  FileProgress _fileProgress;


  EditorImageHandler(ImageElement dataElement) : this.dataElement = dataElement => _setUp();

  EditorImageHandler.fileProgress(this.dataElement, FileProgress fileProgress, void ready()): _fileProgress = fileProgress{
    _fileProgress.listenOnProgress(() => progressBar.percentage = _fileProgress.progress);
    _fileProgress.listenOnPathAvailable(() {
      dataElement.src = _fileProgress.path;
      progressBar.bar.remove();
      element.classes.remove('uploading');
      ready();
    });
    _fileProgress.listenOnPreviewPathAvailable(() => _imageStandIn.style.backgroundImage = "url(\'${_fileProgress.previewPath}\')");
    element..classes.add('uploading')..append(progressBar.bar);
    _setUp();
  }

  void _setUp() {
    element.append(_imageStandIn);
    _imageStandIn.classes.add('standin');
  }

}


class EditorFileContainer {
  static Map<Element, EditorFileContainer> _cache = new Map<Element, EditorFileContainer>();

  final Element element, trashcan;

  Element _dragging;

  ListenerRegister _listeners = new ListenerRegister();


  factory EditorFileContainer(Element element, Element trashCan) => _cache.putIfAbsent(element, () => new EditorFileContainer._internal(element, trashCan));

  Map<Element, EditorHandler> _handlerMap = new Map<Element, EditorHandler>();


  EditorFileContainer._internal(this.element, this.trashcan){
    element.hidden = true;
    trashcan..onDragOver.listen((MouseEvent ev) => ev.preventDefault())..onDrop.listen((MouseEvent ev) {
      if (_dragging == null) {
        return;
      }
      _dragging.remove();
      trashcan.classes.remove('hover');
      _notifyContentChange();
    })..onDragEnter.listen((_) => trashcan.classes.add('hover'))..onDragLeave.listen((_) => trashcan.classes.remove('hover'));

  }

  EditorImageHandler addImage(ImageElement image, [FileProgress progress = null]) {
    element.hidden = false;
    var handler;
    if (progress == null) {
      handler = new EditorImageHandler(image);
      _setUpImageDrag(handler);
    } else {
      handler = new EditorImageHandler.fileProgress(image, progress, () => _setUpImageDrag(handler));
    }
    _handlerMap[handler.element] = handler;
    element.append(handler.element);
    _notifyContentChange();
    return handler;
  }

  EditorFileHandler addFile(AnchorElement fileLink, [FileProgress progress = null]) {
    element.hidden = false;
    var handler;
    if (progress == null) {
      handler = new EditorFileHandler(fileLink);
      _setUpDrag(handler);
    } else {
      handler = new EditorFileHandler.fileProgress(fileLink, progress, () => _setUpDrag(handler));
    }
    _handlerMap[handler.element] = handler;
    element.append(handler.element);
    _notifyContentChange();
    return handler;
  }


  void _setUpImageDrag(EditorImageHandler handler) {
    _setUpDrag(handler);
    handler.element.onDragOver.listen((MouseEvent ev) {
      if (handler.element == _dragging) {
        return;
      }
      ev.preventDefault();
    });
    handler.element.onDrop.listen((_) {
      if (_dragging == null) {
        return;
      }
      var gallery;
      if (!((gallery = _handlerMap[handler.element]) is EditorGalleryHandler)) {
        gallery = new EditorGalleryHandler(handler);
        _handlerMap[handler.element] = gallery;
      }
      gallery.addHandlerToGallery(_handlerMap[_dragging]);
      _dragging.remove();
      _notifyContentChange();
    });
  }

  void _setUpDrag(EditorHandler handler) {
    handler.element.draggable = true;
    handler.element.onDragStart.listen((MouseEvent ev) {
      ev.dataTransfer.setData("text/html", handler.dataElement.outerHtml);
      handler.element.classes.add('dragging');
      _dragging = handler.element;
      trashcan.classes.add('trash_can');
    });

    handler.element.onDragEnd.listen((_) {
      handler.element.classes.remove('dragging');
      trashcan.classes.remove('trash_can');
      _dragging = null;
    });

  }


  void listenOnContentChange(void callback()) => _listeners.registerListener("change", callback);

  void _notifyContentChange() => _listeners.callListeners("change");

}

class Calendar {
  DateTime _showDate, _now = new DateTime.now();

  final DivElement element = new DivElement(), nav = new DivElement(), leftNav = new DivElement(), rightNav = new DivElement();
  final SpanElement navText = new SpanElement();

  TableElement _table = new TableElement();

  Map<int, TableCellElement> _cellMap = new Map<int, TableCellElement>();

  Calendar() {
    date = _now;
    leftNav.classes..add('nav')
    ..add('left_nav');
    leftNav.append(new DivElement());
    rightNav.classes..add('nav')
    ..add('right_nav');
    rightNav.append(new DivElement());

    rightNav.onClick.listen((_)=>showNextMonth());
    leftNav.onClick.listen((_)=>showPrevMonth());

    nav..append(leftNav)
    ..append(rightNav)
    ..append(navText)
    ..classes.add('calendar_nav');

    element..append(nav)..append(_table)..classes.add('calendar');
  }

  Element markDate(DateTime date){
    var cell = _createCell(date);
    cell.classes.add('marked');
    return cell;
  }

  String _dateToString(DateTime dt){
    var m = ["", "Jan", "Feb", "Mar", "Apr", "Maj", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dec"];
    return "${m[dt.month]} ${dt.year.toString()}";
  }
  void showNextMonth(){ date = new DateTime(_showDate.year, _showDate.month+1);}
  void showPrevMonth() {date = new DateTime(_showDate.year, _showDate.month-1);}

  DateTime get date => _showDate;

  set date(DateTime dt) {
    _showDate = dt;
    navText.text = _dateToString(_showDate);
    _table.queryAll('td.another_month').classes.remove('another_month');
    _table.children.clear();
    var d = dt.subtract(new Duration(days:dt.day+dt.weekday-(dt.day%7)-1));
    print(d.weekday);
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
    var cell = _cellMap.putIfAbsent(dt.year*10000+dt.month*100+dt.day, ()=>new TableCellElement());
    if(cell.text.length == 0){
      cell.text = "${dt.day}";
      if(dt.day == _now.day && dt.month == _now.month && dt.year == _now.year){
        cell.classes.add('today');
      }

    }
    if(dt.month != _showDate.month){
      cell.classes.add('another_month');
    }
    return cell;
  }
}


class EditorAction {

  EditorAction(this.element, this.onClickAction, this.selectionStateChanger);

  EditorAction.elementFromHtmlString(String html, this.onClickAction, this.selectionStateChanger) : element = new Element.html(html);

  EditorAction.liElementWithInnerHtml(String innerHtml, this.onClickAction, this.selectionStateChanger) : element = new LIElement(){
    element.innerHtml = innerHtml;
  }

  final Element element;

  final Function onClickAction, selectionStateChanger;
}


class ContentEditor {
  final Element element;

  static final Map<Element, ContentEditor> _cache = new Map<Element, ContentEditor>();

  DivElement _contentWrapper, _topBar, _toolBarPlaceholder = new DivElement(), _wrapper = new DivElement();

  EditorCommandExecutor _executor;

  Map<Element, Element> _elementToSubMenu = new Map<Element, Element>();


  factory ContentEditor(Element element) => _cache.putIfAbsent(element, () => new ContentEditor._internal(element));

  ContentEditor._internal(this.element){
    _toolBarPlaceholder.classes.add('tool_bar_placeholder');
    _executor = new EditorCommandExecutor(element);
    _contentWrapper = new DivElement();
    _contentWrapper.append(_wrapper);
    _wrapper..append(_topBar = _generateToolBar())..classes.add('tool_bar_wrapper');

    _contentWrapper.classes.add('edit_content_wrapper');
    element.insertAdjacentElement("afterEnd", _contentWrapper);
    _contentWrapper.append(element);
    window.onScroll.listen((_) => _updateBarPosition());

  }

  void open() {
    _updateBarPosition();
  }

  void close() {

  }

  void save(SaveStrategy saveStrategy) => saveStrategy.save(this);


  void _updateBarPosition() {
    if (!_wrapper.classes.contains('floating') && window.scrollY > _elementOffsetTop(_topBar)) {
      _toolBarPlaceholder.style.height = "${_topBar.clientHeight}px";
      _wrapper.insertAdjacentElement("afterEnd", _toolBarPlaceholder);
      _wrapper.style.width = "${_wrapper.clientWidth}px";
      _wrapper.classes.add('floating');
    } else if (window.scrollY <= _elementOffsetTop(_toolBarPlaceholder)) {
      _toolBarPlaceholder.remove();

      _wrapper..style.removeProperty("width")..classes.remove('floating');

    }
    _updatePlaceholder();

  }

  void _updatePlaceholder() {
    _wrapper.style.height = _topBar.getComputedStyle().height;
    if (_toolBarPlaceholder.parent == null) {
      return;
    }
    _toolBarPlaceholder.style.height = "${_topBar.clientHeight}px";
  }

  int _elementOffsetTop(Element e) => e == null ? 0 : e.offsetTop + _elementOffsetTop(e.offsetParent);


  void _setUpSubMenu(Element element, Element menu, Element subMenu, void menuFiller(Element)) {
    _elementToSubMenu[element] = subMenu;
    subMenu.classes.add('menu');
    element.onClick.listen((_) {
      var active = menu.query('.active');
      if (active == null) {
        element.classes.add('active');
        subMenu.hidden = false;
      } else {
        active.classes.remove('active');
        if (active != element) {
          _elementToSubMenu[active].hidden = true;
          element.classes.add('active');
        }
        subMenu.hidden = active == element;

      }

      if (subMenu.parent == null) {
        menu.parent.append(subMenu);
        menuFiller(subMenu);
      }
      _updatePlaceholder();
    });
  }


  DivElement _generateToolBar() {
    var bar = new DivElement(), textEdit = new ButtonElement(), addImage = new ButtonElement(), addFile = new ButtonElement(), history = new ButtonElement(), save = new ButtonElement(), wrapper = new DivElement(), textMenu = new DivElement(), imageMenu = new DivElement(), fileMenu = new DivElement(), historyMenu = new DivElement();

    bar.onMouseDown.listen((MouseEvent e) => e.preventDefault());
    textMenu.onMouseDown.listen((MouseEvent e) => e.preventDefault());

    textEdit.classes.add('text');
    _setUpSubMenu(textEdit, bar, textMenu, (Element e) => _fillTextMenu(e));
    _addTitleToElement("Formater tekst", textEdit);

    addImage.classes.add('image');
    _setUpSubMenu(addImage, bar, imageMenu, (Element e) => _fillUploadMenu(e, true));
    _addTitleToElement("Indsæt billede", addImage);

    addFile..classes.add('file');
    _setUpSubMenu(addFile, bar, fileMenu, (Element e) => _fillUploadMenu(e));
    _addTitleToElement("Indsæt fil", addFile);

    history..classes.add('history');
    _setUpSubMenu(history, bar, historyMenu, (Element e) => _fillHistoryMenu(e));
    _addTitleToElement("Se historik", history);

    save..classes.add('save')..classes.add('enabled')..onClick.listen((_) {
      var ss = new JSONSaveStrategy("EditContent");
      this.save(ss);
    });
    _addTitleToElement("Gem ændringer", save);

    bar.classes.add('tool_bar');

    bar..append(textEdit)..append(addImage)..append(addFile)..append(history)..append(save);

    wrapper.append(bar);


    return wrapper;
  }

  void _fillHistoryMenu(Element menu) {
    //TODO move responsibility for listing revisions to SaveStrategy. Add listener for change in SaveStrategy.
    var calendar = new Calendar(), historyList = new UListElement();
    menu
    ..classes.add('history_menu')
    ..classes.add('loading');

    historyList.classes.add("history_list");
    var client = new JSON.AJAXJSONClient("EditContent");
    client.callFunction(new JSON.ListContentRevisionsJSONFunction(id),(JSON.JSONResponse response){
      if(response.type != JSON.RESPONSE_TYPE_SUCCESS){
        return;
      }
      menu..classes.remove('loading')
      ..append(calendar.element)
      ..append(historyList);
      _updatePlaceholder();
      var last =new DateTime.fromMillisecondsSinceEpoch(0);
      var markMap = new Map<TableCellElement,List<int>>(), payloadCache = new Map<TableCellElement,List<LIElement>>();
      response.payload.forEach((int time){
        var date = new DateTime.fromMillisecondsSinceEpoch(time*1000);
        var el = calendar.markDate(date);
        markMap.putIfAbsent(el,()=>[]).add(time);
      });
      var current;
      markMap.forEach((TableCellElement cell, List<int> times){
        cell.onClick.listen((_){
          if(current != null){
            current.classes.remove('current');
          }
          current = cell;
          cell.classes.add('current');
          historyList.children.clear();
          _updatePlaceholder();
          if(payloadCache.containsKey(cell)){
            historyList.children.addAll(payloadCache[cell]);
            _updatePlaceholder();
            return;
          }
          historyList.classes.add('loading');
          client.callFunction(new JSON.ListContentRevisionsJSONFunction(id,from:times.first, to:times.last, includeContent:true), (JSON.JSONResponse response){
            historyList.classes.remove('loading');
            if(response.type != JSON.RESPONSE_TYPE_SUCCESS){
              return;
            }
            var l = payloadCache[cell] = new List<LIElement>();
            response.payload.forEach((Map<String, dynamic> map){
              var li = new LIElement(), dt = new DateTime.fromMillisecondsSinceEpoch(map['time']*1000);
              li.text = "${dt.hour<10?"0"+dt.hour.toString():dt.hour}:${dt.minute<10?"0"+dt.minute.toString():dt.minute}";
              l.add(li);
              historyList.append(li);
            });
            _updatePlaceholder();
          });
        });
        if(cell.classes.contains('today')){
          cell.click();
        }
      });
    });;

  }



  void _fillUploadMenu(DivElement menu, [bool images = false]) {
    menu.classes.add('upload_menu');

    var uploadIconWrapper = new DivElement(), uploadIcon = new DivElement(), fileUploadElementWrapper = new DivElement(), fileUploadElement = new FileUploadInputElement(), preview = new DivElement();
    uploadIcon.classes.add('upload_icon');
    fileUploadElement..hidden = true..multiple = true;

    uploadIconWrapper..classes.add('upload_icon_wrapper')..append(uploadIcon);

    fileUploadElementWrapper.append(fileUploadElement);

    preview.classes.add('preview');

    var uploadStrategy = images ? new AJAXImageURIUploadStrategy("EditContent", new ImageTransform.atMost(element.clientWidth, 500), new ImageTransform.atMost(70, 70, dataURI:true)) : new AJAXFileURIUploadStrategy('EditContent');
    var uploader = new FileUploader(uploadStrategy);
    var container = new EditorFileContainer(new DivElement(), uploadIcon);
    container.listenOnContentChange(() => _updatePlaceholder());

    if (images) {
      menu.classes.add('image_menu');
      preview..classes.add('image_preview')..append(container.element);
      uploader.listenFileAddedToQueue((FileProgress fp) => container.addImage(new ImageElement(), fp));
    } else {
      menu.classes.add('file_menu');
      preview..classes.add('file_preview')..append(container.element);
      uploader.listenFileAddedToQueue((FileProgress fp) => container.addFile(new AnchorElement(), fp));
    }


    menu..append(fileUploadElementWrapper)..append(preview)..append(uploadIconWrapper);
    uploadIcon.onClick.listen((_) => fileUploadElementWrapper.query('input').click());

    fileUploadElementWrapper.onChange.listen((_) {
      uploader.uploadFiles(fileUploadElementWrapper.query('input').files);
    });
  }

  void _fillTextMenu(DivElement menu) {

    menu.classes.add('text_menu');

    var menuHandler = new MenuOverflowHandler(menu);
    menuHandler.dropDown..preventDefaultOnClick = true..content.classes.add('submenu');


    var actionsSetup = (EditorCommandExecutor executor, List<EditorAction> actions, DropDown dropDown, dynamic state()) {
      actions.forEach((EditorAction a) {
        if (a.onClickAction != null) {
          a.element.onMouseDown.listen((_) {
            a.onClickAction();
            dropDown.close();
          });
        }
      });
      executor.listenQueryCommandStateChange(() {
        var action = actions.firstWhere((EditorAction a) => a.selectionStateChanger(state()), orElse:() => null);
        dropDown.text = action == null ? dropDown.text : action.element.text;
      });
      dropDown.preventDefaultOnClick = true;

    };

    var actions = [new EditorAction.liElementWithInnerHtml("<h2>Overskift</h2>", () => _executor.formatBlockH2(), (String s) => s == "h2"), new EditorAction.liElementWithInnerHtml("<h3>Underoverskrift</h3>", () => _executor.formatBlockH3(), (String s) => s == "h3"), new EditorAction.liElementWithInnerHtml("<p>Normal tekst</p>", () => _executor.formatBlockP(), (String s) => s == "p"), new EditorAction.liElementWithInnerHtml("<blockquote>Citat</blockquote>", () => _executor.formatBlockBlockquote(), (String s) => s == "blockquote"), new EditorAction.liElementWithInnerHtml("<pre>Kode</pre>", () => _executor.formatBlockPre(), (String s) => s == "pre")];

    var textType = new DropDown.fromLIList(actions.map((EditorAction a) => a.element).toList());


    actionsSetup(_executor, actions, textType, () => _executor.blockState);

    textType.element.classes.add('text_type');
    textType.text = "Normal tekst";
    menuHandler.addToMenu(textType.element);

    var sizeActions = [new EditorAction.liElementWithInnerHtml("<font size='1'>Lille</font>", () => _executor.setFontSize(1), (int s) => s == 1), new EditorAction.liElementWithInnerHtml("Normal", () {
      _executor.setFontSize(3);
      var fonts = element.queryAll("font");
      fonts.forEach((Element e) => e.attributes['size'] == '3' ? (() {
        e.attributes.remove("size");
      })() : () {
      });
    }, (i) => ![1, 5, 7].contains(i)), new EditorAction.liElementWithInnerHtml("<font size='5'>Stor</font>", () => _executor.setFontSize(5), (int s) => s == 5), new EditorAction.liElementWithInnerHtml("<font size='7'>Størst</font>", () => _executor.setFontSize(7), (int s) => s == 7)];

    var textSize = new DropDown.fromLIList(sizeActions.map((EditorAction e) => e.element).toList());
    textSize.element.classes.add('text_size');

    actionsSetup(_executor, sizeActions, textSize, () => _executor.blockState == "p" ? _executor.fontSize : -1);

    menuHandler.addToMenu(textSize.element);
    textSize.text = "Normal";

    var colorContent = new DivElement(), colorSelect = new DropDown(colorContent), textColorPalette = new ColorPalette(), backgroundColorPalette = new ColorPalette(), colorLabel1 = new DivElement(), colorLabel2 = new DivElement();

    colorLabel1..classes.add('color_label')..text = "Tekstfarve";
    colorLabel2..classes.add('color_label')..text = "Baggrundsfarve";

    colorSelect.element.classes.add('color');
    colorSelect.preventDefaultOnClick = true;
    colorSelect.text = " ";
    colorContent..append(colorLabel1)..append(colorLabel2)..append(textColorPalette.element)..append(backgroundColorPalette.element);
    colorSelect.dropDownBox.element.classes.add('color_select');

    _executor.listenQueryCommandStateChange(() {
      textColorPalette.selected = _executor.foreColor;
      backgroundColorPalette.selected = _executor.backColor;
    });

    textColorPalette.element.onChange.listen((_) {
      if (textColorPalette.selected != null) {
        _executor.setForeColor(textColorPalette.selected);
        colorSelect.close();
      }
    });

    backgroundColorPalette.element.onChange.listen((_) {
      if (backgroundColorPalette.selected != null) {
        _executor.setBackColor(backgroundColorPalette.selected);
        colorSelect.close();
      }
    });


    menuHandler.addToMenu(colorSelect.element);

    var textIconMap = {
        "bold":{
            "title":"Fed skrift", "selChange":() => _executor.bold, "func":() => _executor.toggleBold()
        }, "italic":{
            "title":"Kursiv skrift", "selChange":() => _executor.italic, "func":() => _executor.toggleItalic()
        }, "underline":{
            "title":"Understreget skrift", "selChange":() => _executor.underline, "func":() => _executor.toggleUnderline()
        }, "u_list":{
            "title":"Uordnet liste", "selChange":() => _executor.unorderedList, "func":() => _executor.toggleUnorderedList()
        }, "o_list":{
            "title":"Ordnet liste", "selChange":() => _executor.orderedList, "func":() => _executor.toggleOrderedList()
        }, "a_left":{
            "title":"Juster venstre", "selChange":() => _executor.alignLeft, "func":() => _executor.justifyLeft()
        }, "a_center":{
            "title":"Juster centreret", "selChange":() => _executor.alignCenter, "func":() => _executor.justifyCenter()
        }, "a_right":{
            "title":"Juster højre", "selChange":() => _executor.alignRight, "func":() => _executor.justifyRight()
        }, "a_just":{
            "title":"Juster lige", "selChange":() => _executor.alignJust, "func":() => _executor.justifyFull()
        }, "p_indent":{
            "title":"Indryk mere", "selChange":null, "func":() => _executor.indent()
        }, "m_indent":{
            "title":"Indryk mindre", "selChange":null, "func":() => _executor.outdent()
        }, "no_format":{
            "title":"Fjern formatering", "selChange":null, "func":() => _executor.removeFormat()
        }
    };

    textIconMap.forEach((String k, Map<String, dynamic> v) {
      var b = new ButtonElement();
      var i;
      b..classes.add(k);
      _addTitleToElement(v['title'], b);
      var f = () {
      };
      if (v['selChange'] != null) {
        _executor.listenQueryCommandStateChange(() => v['selChange']() ? b.classes.add('active') : b.classes.remove('active'));
        f = () => _executor.triggerCommandStateChangeListener();
      }
      b.onClick.listen((_) {
        v["func"]();
        f();
      });

      menuHandler.addToMenu(b);

    });


  }

  InfoBox _addTitleToElement(String title, Element e) {
    var i = new InfoBox(title);

    i..backgroundColor = InfoBox.COLOR_BLACK..reversed = true;

    e..onMouseOver.listen((_) => i.showBelowCenterOfElement(e))..onMouseOut.listen((_) => i.remove())..onClick.listen((_) => i.remove());
    return i;
  }


  String get id => element.id;

}


class MenuOverflowHandler {

  final Element menu;

  final DropDown dropDown = new DropDown(new UListElement());

  static Map<Element, MenuOverflowHandler> _cache = new Map<Element, MenuOverflowHandler>();

  int _smallestOffsetToTop;

  factory MenuOverflowHandler(Element menu) => _cache.putIfAbsent(menu, () => new MenuOverflowHandler._internal(menu));


  MenuOverflowHandler._internal(this.menu) {
    dropDown.element.classes.add('overflow_container');
  }


  void addToMenu(Element element) {

    if (dropDown.element.parent == null) {
      var divider = new Element.html("<div class='divider' />");
      menu..append(element)..append(divider);

      if (_smallestOffsetToTop == null) {
        _smallestOffsetToTop = element.offsetTop;
      }
      if (_smallestOffsetToTop < element.offsetTop) {
        menu.append(dropDown.element);
        var e;
        var children = menu.children.toList();
        children.removeWhere((Element e) => e.classes.contains('drop_down'));
        while (children.length > 0 && ((e = children.removeLast()).offsetTop > _smallestOffsetToTop || dropDown.element.offsetTop > _smallestOffsetToTop)) {
          if (e.classes.contains('divider')) {
            e.remove();
          } else {
            var li = new LIElement();
            li.append(e);
            dropDown.content.insertAdjacentElement("afterBegin", li);
          }
        }

      }

    } else {
      var li = new LIElement();
      li.append(element);
      dropDown.content.append(li);
    }


  }
}
