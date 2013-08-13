part of core;

abstract class SaveStrategy {
  void save();
}

class JSONSaveStrategy implements SaveStrategy {
  final String ajax_id;

  Function _callback;

  JSON.JSONClient _jsonClient;

  JSONSaveStrategy(this.ajax_id, void callback(int error_code)): _callback = callback{
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

}

class EditorGalleryHandler implements EditorHandler {
  final DivElement element;

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
/*    element.onMouseOver.listen((MouseEvent ev){
      _infobox.showAboveCenterOfElement(element);
    });
    element.onMouseOut.listen((_)=>_infobox.remove());*/


  }

  void addHandlerToGallery(EditorHandler h) {
    if (h is EditorGalleryHandler) {
      _children.addAll(h.children);
    } else if (h is EditorImageHandler) {
      _children.add(h);
    }
    _imageCount.text = "${_children.length.toString()}";
    original.imageStandIn.style.backgroundImage = "url(${_children.last.image.src})";
  }

  List<EditorImageHandler> get children => new List<EditorImageHandler>.from(_children);
}


class EditorImageHandler implements EditorHandler {
  final DivElement element = new DivElement(), imageStandIn = new DivElement();

  final ImageElement image;

  final ProgressBar progressBar = new ProgressBar();

  FileProgress _fileProgress;


  EditorImageHandler(this.image) => _setUp();

  EditorImageHandler.fileProgress(this.image, FileProgress fileProgress, void ready()): _fileProgress = fileProgress{
    _fileProgress.listenOnProgress(() => progressBar.percentage = _fileProgress.progress);
    var changePath = (String path) {
      imageStandIn.style.backgroundImage = "url(\'$path\')";
      image.src = path;
    };
    _fileProgress.listenOnPathAvailable(() {
      changePath(_fileProgress.path);
      progressBar.bar.remove();
      element.classes.remove('uploading');
      ready();
    });
    _fileProgress.listenOnPreviewPathAvailable(() => changePath(_fileProgress.previewPath));
    element..classes.add('uploading')..append(progressBar.bar);
    _setUp();
  }

  void _setUp() {
    element..append(imageStandIn);
    imageStandIn.classes.add('image_standin');
  }

}


class EditorImageContainer {
  static Map<Element, EditorImageContainer> _cache = new Map<Element, EditorImageHandler>();

  final Element element;

  Element _dragging;

  DivElement _trashCan = new DivElement();
  ListenerRegister _listeners = new ListenerRegister();


  factory EditorImageContainer(Element element) => _cache.putIfAbsent(element, () => new EditorImageContainer._internal(element));

  Map<Element, EditorHandler> _handlerMap = new Map<Element, EditorHandler>();

  OpacityExpandDecoration _trashAnimation;


  EditorImageContainer._internal(this.element){
    element.hidden = true;
    _trashCan..classes.add('trash_can')..style.opacity = "0"..onDragOver.listen((MouseEvent ev) => ev.preventDefault())..onDrop.listen((MouseEvent ev) {
      if (_dragging == null) {
        return;
      }
      _dragging.remove();
      _trashCan.classes.remove('hover');
      _notifyRemove();
    })..onDragEnter.listen((_) => _trashCan.classes.add('hover'))..onDragLeave.listen((_) => _trashCan.classes.remove('hover'));
    _trashAnimation = new OpacityExpandDecoration(this._trashCan, 0, 0.7, expandDuration:new Duration(milliseconds:100), contractDuration:new Duration(milliseconds:100));
    query('body').append(_trashCan);

  }

  EditorImageHandler addImage(ImageElement image, [FileProgress progress = null]) {
    element.hidden = false;
    var handler;
    if (progress == null) {
      hander = new EditorImageHandler(image, this);
      _setUpDrag(handler);
    } else {
      handler = new EditorImageHandler.fileProgress(image, progress, () => _setUpDrag(handler));
    }
    _handlerMap[handler.element] = handler;
    element.append(handler.element);
    return handler;
  }

  void _setUpDrag(EditorImageHandler handler) {
    handler.element.draggable = true;
    handler.element.onDragStart.listen((MouseEvent ev) {
      ev.dataTransfer.setData("text/html", handler.image.outerHtml);
      handler.element.classes.add('dragging');
      _dragging = handler.element;
      _trashAnimation.expand();
    });

    handler.element.onDragEnd.listen((_) {
      handler.element.classes.remove('dragging');
      _trashAnimation.contract();
      _dragging = null;
    });
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

    });
  }
  void listenOnRemove(void callback()) =>   _listeners.registerListener("remove", callback);
  void _notifyRemove() => _listeners.callListeners("remove");

}

class Calendar {
  DateTime _currentTime;

  final Element element = new DivElement();

  TableElement _table = new TableElement();

  Calendar() {
    date = new DateTime.now();
  }


  DateTime get date => _currentTime;

  set date(DateTime dt) {
    _currentTime = dt;
    var d = new DateTime(dt.year, dt.month, 1), row = new TableRowElement(), cell;
    for (var i = d.weekday - 1; i > 0; i--) {
      var dd = d.subtract(new Duration(days:i));
      row.append(_createCell(dd));
    }
    while (d.month == dt.month) {
      if (d.weekday == 7) {
        _table.append(row);
        row = new TableRowElement();
      }
      row.append(_createCell(d));
      d = d.add(new Duration(days:1));
    }
    for (var i = d.weekday + 1; i <= 7;i++) {
      d = d.add(new Duration(days:1));
      print(d);
      row.append(_createCell(d));
    }
    _table.append(row);

    print(_table.outerHtml);

  }

  TableCellElement _createCell(DateTime dt) {
    var cell = new TableCellElement();
    cell.text = "${dt.day}";
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

  DivElement _wrapper, _topBar, _toolBarPlaceholder = new DivElement();

  EditorCommandExecutor _executor;

  Map<Element, Element> _elementToSubMenu = new Map<Element, Element>();


  factory ContentEditor(Element element) => _cache.putIfAbsent(element, () => new ContentEditor._internal(element));

  ContentEditor._internal(this.element){
    _executor = new EditorCommandExecutor(element);
    _wrapper = new DivElement();
    _wrapper.append(_topBar = _generateToolBar());

    _wrapper.classes.add('edit_content_wrapper');
    element.insertAdjacentElement("afterEnd", _wrapper);
    _wrapper.append(element);
    window.onScroll.listen((_) => _updateBarPosition());

  }

  void open() {
    _updateBarPosition();
  }

  void close() {

  }

  void save(SaveStrategy saveStrategy) => saveStrategy.save(this);


  void _updateBarPosition() {
    if (_toolBarPlaceholder.parent != null) {
      _toolBarPlaceholder.style.height = "${_topBar.clientHeight}px";
    }
    if (!_topBar.classes.contains('floating') && window.scrollY > _elementOffsetTop(_topBar)) {
      _topBar.insertAdjacentElement("afterEnd", _toolBarPlaceholder);
      _toolBarPlaceholder.style.height = "${_topBar.clientHeight}px";
      _topBar.style.width = "${_topBar.clientWidth}px";
      _topBar.classes.add('floating');
    } else if (window.scrollY <= _elementOffsetTop(_toolBarPlaceholder)) {
      _toolBarPlaceholder.remove();
      _topBar..style.removeProperty("width")..classes.remove('floating');

    }


  }

  void _updatePlaceholder() {
    if (_toolBarPlaceholder.parent == null) {
      return;
    }
    _toolBarPlaceholder.style.height = "${_topBar.clientHeight}px";
  }

  int _elementOffsetTop(Element e) => e == null ? 0 : e.offsetTop + _elementOffsetTop(e.offsetParent);


  void _setUpSubMenu(Element element, Element menu, Element subMenu, void menuFiller(Element)) {
    _elementToSubMenu[element] = subMenu;

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
    var bar = new DivElement(), textEdit = new ButtonElement(), addImage = new ButtonElement(), addFile = new ButtonElement(), history = new ButtonElement(), save = new ButtonElement(), wrapper = new DivElement(), textMenu = new DivElement(), imageMenu = new DivElement();

    bar.onMouseDown.listen((MouseEvent e) => e.preventDefault());
    textMenu.onMouseDown.listen((MouseEvent e) => e.preventDefault());

    textEdit.classes.add('text');
    _setUpSubMenu(textEdit, bar, textMenu, (Element e) => _fillTextMenu(e));
    _addTitleToElement("Formater tekst", textEdit);

    addImage.classes.add('image');
    _setUpSubMenu(addImage, bar, imageMenu, (Element e) => _fillImageMenu(e));
    _addTitleToElement("Indsæt billede", addImage);

    addFile..classes.add('file');
    _addTitleToElement("Indsæt fil", addFile);

    history..classes.add('history');
    _addTitleToElement("Se historik", history);

    save..classes.add('save')..classes.add('enabled')..onClick.listen((_) {
      var ss = new JSONSaveStrategy("EditContent",(i) => print(i));
      this.save(ss);
    });
//_addTitleToElement("Gem ændringer", save);

    bar.classes.add('toolBar');

    bar..append(textEdit)..append(addImage)..append(addFile)..append(history)..append(save);

    wrapper.append(bar);


    wrapper.classes.add('toolBarWrapper');

    return wrapper;
  }


  void _fillImageMenu(DivElement menu) {
    menu.classes..add('menu')..add('image_menu');

    var uploadIconWrapper = new DivElement(), uploadIcon = new DivElement(), fileUploadElementWrapper = new DivElement(), fileUploadElement = new FileUploadInputElement(), imagePreview = new DivElement();

    uploadIcon.classes.add('upload_icon');
    fileUploadElement..hidden = true..multiple = true;

    uploadIconWrapper..classes.add('upload_icon_wrapper')..append(uploadIcon);
    var imageContainer = new EditorImageContainer(new DivElement());

    imagePreview..classes.add('image_preview')..append(imageContainer.element);

    fileUploadElementWrapper.append(fileUploadElement);

    menu..append(fileUploadElementWrapper)..append(imagePreview)..append(uploadIconWrapper);
    uploadIcon.onClick.listen((_) => fileUploadElementWrapper.query('input').click());

    var uploadIconAnimation = new BackgroundPositionExpandDecoration(uploadIcon, startY:10, endY:5, expandDuration:new Duration(milliseconds:100), contractDuration:new Duration(milliseconds:100));
    uploadIconAnimation.expandOnMouseOver = uploadIconAnimation.contractOnMouseOut = true;

    var uploader = new FileUploader(new AJAXImageURIUploadStrategy("EditContent",new ImageSize.atMost(element.clientWidth,-1),new ImageSize.atMost(70,70)));
    uploader.listenFileAddedToQueue((FileProgress fp) => imageContainer.addImage(new ImageElement(), fp));
    imageContainer.listenOnRemove(()=>_updateBarPosition());
    fileUploadElementWrapper.onChange.listen((_) {
      uploader.uploadFiles(fileUploadElementWrapper.query('input').files);
    });


  }

  void _fillTextMenu(DivElement menu) {

    menu.classes..add('menu')..add('text_menu');

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
