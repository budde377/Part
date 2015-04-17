part of elements;

class EditorCommandExecutor {
  static final Map<Element, EditorCommandExecutor> _cache = new Map<Element, EditorCommandExecutor>();

  final Element element;

  Function _listenerChain = () {
  };

  bool _inElement = false;

  Function _listenerFunction;


  StreamController _onFormatBlockController = new StreamController.broadcast();

  factory EditorCommandExecutor(Element element) => _cache.putIfAbsent(element, () => new EditorCommandExecutor._internal(element));

  EditorCommandExecutor._internal(this.element){
    _listenerFunction = ([Event _]) {
      _inElement = element.contains(window.getSelection().baseNode);
      _listenerChain();
    };
    element.ownerDocument.onSelectionChange.listen(_listenerFunction);
  }


  void _execCommand(String command, {bool user_interface:false, String value:""}) {
    element.ownerDocument.execCommand(command, user_interface, value);
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

  void toggleSuperScript() => _execCommand("superscript");

  void toggleSubScript() => _execCommand("subscript");

  void toggleStrikeThrough() => _execCommand("strikethrough");

  void createLink(String address) => _execCommand("createLink", value:address);

  void unlink() => _execCommand("unlink");

  void indent() => _execCommand("indent");

  void outdent() => _execCommand("outdent");

  void insertHtml(String html) => _execCommand('insertHTML', value:html);

  void insertFragment(DocumentFragment fragmet) => insertHtml(fragmet.innerHtml);

  void setFontSize(int size) => _execCommand('fontsize', value:"${Math.max(1, Math.min(7, size))}");

  void setForeColor(Color color) => _execCommand('foreColor', value:"#" + color.hex);

  void setBackColor(Color color) => _execCommand('backColor', value:"#" + color.hex);

  void _formatBlock(String tagName) {
    _execCommand('formatBlock', value:tagName);
    _onFormatBlockController.add(null);
  }

  void insertParagraph() => _execCommand('insertparagraph');

  void formatBlockP() => _formatBlock('p');

  void formatBlockDiv() => _formatBlock('div');

  void formatBlockH1() => _formatBlock('h1');

  void formatBlockH2() => _formatBlock('h2');

  void formatBlockH3() => _formatBlock('h3');

  void formatBlockH4() => _formatBlock('h4');

  void formatBlockH5() => _formatBlock('h5');

  void formatBlockH6() => _formatBlock('h6');

  void formatBlockBlockquote() => _formatBlock('blockquote');

  void formatBlockPre() => _formatBlock('pre');

  bool _commandState(String command) => _inElement && element.ownerDocument.queryCommandState(command);

  String _commandValue(String command) => _inElement ? element.ownerDocument.queryCommandValue(command) : "";

  bool get bold => _commandState("bold");

  bool get italic => _commandState("italic");

  bool get underline => _commandState("underline");

  bool get unorderedList => _commandState("insertunorderedlist");

  bool get orderedList => _commandState("insertorderedlist");

  bool get alignLeft => _commandState('justifyleft');

  bool get alignRight => _commandState('justifyright');

  bool get alignCenter => _commandState('justifycenter');

  bool get alignJust => _commandState('justifyfull');

  bool get superScript => _commandState('superscript');

  bool get subScript => _commandState('subscript');

  bool get strikeThrough => _commandState('strikethrough');

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


  Stream get onFormatBlock => _onFormatBlockController.stream;

}


abstract class _EditorHandler {
  final DivElement element;

  final Element dataElement;

  _EditorHandler(this.element, this.dataElement);

}

class _EditorGalleryHandler implements _EditorHandler {
  final DivElement element;

  final Element dataElement = new ImageElement();

  List<_EditorImageHandler> _children = new List<_EditorImageHandler>();

  _EditorImageHandler original;

  DivElement _imageCount = new DivElement(), _previewContent = new DivElement();

  InfoBox _infoBox;

  _EditorGalleryHandler(_EditorImageHandler h) : element = h.element, original = h {
    _infoBox = new InfoBox.elementContent(_previewContent);
    _children.add(h);
    element.classes.add('gallery');
    element.append(_imageCount);
    _imageCount
      ..classes.add('image_count')
      ..text = "0";

  }

  void addHandlerToGallery(_EditorHandler h) {
    if (h is _EditorGalleryHandler) {
      _EditorGalleryHandler hh = h;
      _children.addAll(hh.children);
    } else if (h is _EditorImageHandler) {
      _children.add(h);
    }
    _imageCount.text = "${_children.length.toString()}";
    original._imageStandIn.style.backgroundImage = "url(${_children.last.dataElement.src})";
  }

  List<_EditorImageHandler> get children => new List<_EditorImageHandler>.from(_children);
}


class _EditorFileHandler implements _EditorHandler {
  final DivElement element = new DivElement();

  DivElement _fileStandIn = new DivElement();

  final AnchorElement dataElement;

  final ProgressBar progressBar = new ProgressBar();

  core.FileProgress _fileProgress;


  _EditorFileHandler(AnchorElement dataElement) : this.dataElement = dataElement {
    _setUp();
  }

  _EditorFileHandler.fileProgress(this.dataElement, core.FileProgress fileProgress, void ready()): _fileProgress = fileProgress{
    var size = new SpanElement();
    size.text = core.sizeToString(fileProgress.file.size);

    _fileStandIn
      ..text = fileProgress.file.name
      ..append(size);

    _fileProgress.onProgress.listen((_) => progressBar.percentage = _fileProgress.progress);
    _fileProgress.onPathAvailable.listen((_) {
      dataElement
        ..href = "/_files/" + _fileProgress.path
        ..text = fileProgress.file.name;
      progressBar.bar.remove();
      element.classes.remove('uploading');
      ready();
    });
    element
      ..classes.add('uploading')
      ..append(progressBar.bar);
    _setUp();
  }

  void _setUp() {
    element.append(_fileStandIn);
    _fileStandIn.classes.add('standin');
  }
}

class _EditorImageHandler implements _EditorHandler {
  final DivElement element = new DivElement();

  DivElement _imageStandIn = new DivElement();

  final ImageElement dataElement;

  final ProgressBar progressBar = new ProgressBar();

  core.FileProgress _fileProgress;


  _EditorImageHandler(ImageElement dataElement) : this.dataElement = dataElement {
    _setUp();
  }

  _EditorImageHandler.fileProgress(this.dataElement, core.FileProgress fileProgress, void ready()): _fileProgress = fileProgress{
    _fileProgress.onProgress.listen((_) => progressBar.percentage = _fileProgress.progress);
    _fileProgress.onPathAvailable.listen((_) {
      dataElement.src = "/_files/" + _fileProgress.path;
      progressBar.bar.remove();
      element.classes.remove('uploading');
      ready();
    });
    _fileProgress.onPreviewPathAvailable.listen((_) {
      _imageStandIn.style.backgroundImage = "url(\'${_fileProgress.previewPath}\')";
    });
    element
      ..classes.add('uploading')
      ..append(progressBar.bar);
    _setUp();
  }

  void _setUp() {
    element.append(_imageStandIn);
    _imageStandIn.classes.add('standin');
  }

}


class _EditorFileContainer {
  static Map<Element, _EditorFileContainer> _cache = new Map<Element, _EditorFileContainer>();

  final Element element, trashcan;

  Element _dragging;

  StreamController<_EditorFileContainer> _change_controller = new StreamController<_EditorFileContainer>();
  Stream<_EditorFileContainer> _change_stream;


  factory _EditorFileContainer(Element element, Element trashCan) => _cache.putIfAbsent(element, () => new _EditorFileContainer._internal(element, trashCan));

  Map<Element, _EditorHandler> _handlerMap = new Map<Element, _EditorHandler>();


  _EditorFileContainer._internal(this.element, this.trashcan){
    element.hidden = true;
    _change_stream = _change_controller.stream.asBroadcastStream();

    trashcan
      ..onDragOver.listen((MouseEvent ev) => ev.preventDefault())
      ..onDrop.listen((MouseEvent ev) {
      if (_dragging == null) {
        return;
      }
      _dragging.remove();
      trashcan.classes.remove('hover');
      _notifyContentChange();
    })
      ..onDragEnter.listen((_) => trashcan.classes.add('hover'))
      ..onDragLeave.listen((_) => trashcan.classes.remove('hover'));

  }

  _EditorImageHandler addImage(ImageElement image, [core.FileProgress progress = null]) {
    element.hidden = false;
    var handler;
    if (progress == null) {
      handler = new _EditorImageHandler(image);
      _setUpImageDrag(handler);
    } else {
      handler = new _EditorImageHandler.fileProgress(image, progress, () => _setUpImageDrag(handler));
    }
    _handlerMap[handler.element] = handler;
    element.append(handler.element);
    _notifyContentChange();
    return handler;
  }

  _EditorFileHandler addFile(AnchorElement fileLink, [core.FileProgress progress = null]) {
    element.hidden = false;
    var handler;
    if (progress == null) {
      handler = new _EditorFileHandler(fileLink);
      _setUpDrag(handler);
    } else {
      handler = new _EditorFileHandler.fileProgress(fileLink, progress, () => _setUpDrag(handler));
    }
    _handlerMap[handler.element] = handler;
    element.append(handler.element);
    _notifyContentChange();
    return handler;
  }


  void _setUpImageDrag(_EditorImageHandler handler) {
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
      if (!((gallery = _handlerMap[handler.element]) is _EditorGalleryHandler)) {
        gallery = new _EditorGalleryHandler(handler);
        _handlerMap[handler.element] = gallery;
      }

      gallery.addHandlerToGallery(_handlerMap[_dragging]);
      _dragging.remove();
      _notifyContentChange();
    });
  }

  void _setUpDrag(_EditorHandler handler) {
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

  Stream<_EditorFileContainer> get onChange => _change_stream;

  void _notifyContentChange() => _change_controller.add(this);

}

_recursiveElementFind(Node element, bool check(Node)) {
  if (element == null || check(element)) {
    return element;
  }
  return _recursiveElementFind(element.parent, check);
}

abstract class EditorKeyboardAction {
  final ContentEditor editor;

  EditorKeyboardAction(this.editor);

  bool check(KeyboardEvent event);

  void performAction(KeyboardEvent event);

}


class SaveEditorKeyboardAction extends EditorKeyboardAction {
  SaveEditorKeyboardAction(ContentEditor editor) : super(editor);

  bool check(KeyboardEvent event) => event.keyCode == 83 && event.ctrlKey;

  void performAction(KeyboardEvent event) {
    event.preventDefault();
    editor.save();
  }


}

class AddLinkEditorKeyboardAction extends EditorKeyboardAction {
  AddLinkEditorKeyboardAction(ContentEditor editor) : super(editor);

  String link;
  Range range;
  bool url;

  bool check(KeyboardEvent event) {
    if (event.keyCode != 32) {
      return false;
    }
    var selection = window.getSelection();
    if (selection.rangeCount == 0) {
      return false;
    }

    range = selection.getRangeAt(0);
    var endOffset = range.endOffset;
    if (range.startOffset != endOffset || range.startContainer != range.endContainer) {
      return false;
    }

    var parentNode = range.startContainer;

    if (_recursiveElementFind(parentNode, (Node node) => node is AnchorElement) != null) {
      return false;
    }

    var value = parentNode.nodeValue;
    if (value == null) {
      return false;
    }

    var match = new RegExp(r"\s([^\s]+)$").firstMatch(" " + value.substring(0, endOffset));
    if (match == null) {
      return false;
    }

    link = match.group(1);

    return (url = core.validUrl(link)) || core.validMail(link);
  }

  void performAction(KeyboardEvent event) {
    range.setStart(range.startContainer, range.endOffset - link.length);
    var selection = window.getSelection();
    selection
      ..removeAllRanges()
      ..addRange(range);

    editor.executor.createLink(url ? link : "mailto:$link");
    range.setStart(range.endContainer, range.endOffset);
    selection
      ..removeAllRanges()
      ..addRange(range);

    event.preventDefault();

  }
}


abstract class EditorClickActionItem<T extends Element> {
  final ContentEditor editor;
  final ButtonElement button = new ButtonElement();
  T target;
  InfoBox infoBox;


  EditorClickActionItem(this.editor);

  void setUpButton(InfoBox infobox, T target) {
    this.target = target;
    this.infoBox = infobox;
  }

  T actionTarget(MouseEvent event);

}

class UnlinkEditorClickActionItem extends EditorClickActionItem<AnchorElement> {


  UnlinkEditorClickActionItem(ContentEditor editor) : super(editor) {
    button
      ..classes.add('unlink')
      ..onClick.listen((MouseEvent event) {
      event.preventDefault();
      _selectNode(target);
      editor.executor.unlink();
      infoBox.remove();
      editor.executor.triggerCommandStateChangeListener();
    });
  }


  AnchorElement actionTarget(MouseEvent event) => _recursiveElementFind(event.target, (Node element) => element is AnchorElement);


}

_selectNodeContents(Node node) {
  var range = document.createRange();
  range.selectNodeContents(node);
  _selectRange(range);
}

_selectNode(Node node) {
  var range = document.createRange();
  range.selectNode(node);
  _selectRange(range);
}

_selectRange(Range range) {
  window.getSelection()
    ..removeAllRanges()
    ..addRange(range);
}

class OpenEditorClickActionItem extends EditorClickActionItem<AnchorElement> {


  OpenEditorClickActionItem(ContentEditor editor) : super(editor) {
    button
      ..classes.add('open')
      ..onClick.listen((MouseEvent event) {
      event.preventDefault();
      window.open(target.href, "_blank");
      infoBox.remove();
    });
  }


  AnchorElement actionTarget(MouseEvent event) => _recursiveElementFind(event.target, (Node element) => element is AnchorElement);


}

class ImageEditorClickActionItem extends EditorClickActionItem<ImageElement> {

  ImageEditorClickActionItem(ContentEditor editor) : super(editor) {
    button
      ..classes.add('edit_image')
      ..onClick.listen((MouseEvent event) {
      event.preventDefault();
      infoBox.remove();

      var handler = new ImageEditorHandler.fromImage(target);
      handler.editor.maxWidth = editor.element.clientWidth;
      handler.editor.minWidth = 50;
      handler.open();
      handler.onEdit.listen((String path) {
        target.src = path;
        editor.element.dispatchEvent(new Event("input"));
      });

    });
  }

  ImageElement actionTarget(MouseEvent event) => _recursiveElementFind(event.target, (Node element) => element is ImageElement);

}

class EmbedVideoEditorClickActionItem extends EditorClickActionItem<AnchorElement> {

  String _video_id;
  Element _found_link;
  InfoBox _info_box;
  Function _videoFunction;

  EmbedVideoEditorClickActionItem.youtube(ContentEditor editor) : super(editor) {
    button.classes.add('youtube');
    _videoFunction = core.youtubeVideoIdFromUrl;
    setupListener((int width, int height) => '<iframe width="$width" height="$height" src="//www.youtube.com/embed/$_video_id?badge=0&amp;modestbranding=1&amp;controls=1&amp;autohide=1&amp;showinfo=0&amp;rel=0&amp;fs=0" frameborder="0" allowfullscreen="" webkitallowfullscreen="" mozallowfullscreen=""></iframe>');

  }

  EmbedVideoEditorClickActionItem.vimeo(ContentEditor editor) : super(editor){
    button.classes.add('vimeo');
    _videoFunction = core.vimeoVideoIdFromUrl;
    setupListener((int width, int height) => '<iframe width="$width" height="$height" src="//player.vimeo.com/video/$_video_id?badge=0&amp;modestbranding=1&amp;controls=1&amp;autohide=1&amp;showinfo=0&amp;rel=0&amp;fs=0" frameborder="0" allowfullscreen="" webkitallowfullscreen="" mozallowfullscreen=""></iframe>');
  }

  void setupListener(String html(int width, int height)) {
    button.onClick.listen((MouseEvent event) {
      event.preventDefault();
      var width = editor.element.clientWidth;
      var height = (width * 9 / 16).ceil();
      _selectNode(_found_link);
      editor.executor.insertHtml(html(width, height));
      _info_box.remove();
      editor.executor.triggerCommandStateChangeListener();
    });
  }


  void setUpButton(InfoBox infobox, AnchorElement target) {
    _found_link = target;
    _video_id = _videoFunction(target.href);
    _info_box = infobox;
  }

  AnchorElement actionTarget(MouseEvent event) => _recursiveElementFind(event.target, _videoCheck);

  bool _videoCheck(element) {
    if (!(element is AnchorElement)) {
      return false;
    }
    return _videoFunction(element.href) != null;
  }


}


int _elementDepth(Element element) => element == null ? 0 : 1 + _elementDepth(element.parent);

class _EditorAction {

  _EditorAction(this.element, this.onClickAction, this.selectionStateChanger);

  _EditorAction.elementFromHtmlString(String html, this.onClickAction, this.selectionStateChanger) : element = new Element.html(html);

  _EditorAction.liElementWithInnerHtml(String innerHtml, this.onClickAction, this.selectionStateChanger, [List<String> element_class]) : element = new LIElement(){
    element.innerHtml = innerHtml;
    if (element_class != null) {
      element.classes.addAll(element_class);
    }
  }

  final Element element;

  final Function onClickAction, selectionStateChanger;
}

abstract class EditorMenuItem {

  final ButtonElement button;
  final DivElement menu;
  final ContentEditor editor;

  Stream get onMenuChange => new StreamController().stream;


  EditorMenuItem(this.editor): button = new ButtonElement(), menu = new DivElement() ;


  void showMenu() {

  }

  void hideMenu() {

  }


  InfoBox addTitleToElement(String title, Element element, [bool verify()]) {
    if (verify == null) {
      verify = () => true;
    }
    var box = new InfoBox(title);
    box
      ..backgroundColor = InfoBox.COLOR_BLACK
      ..reversed = true;
    element
      ..onMouseOver.listen((_) {
      if (!verify()) {
        return;
      }
      box.showBelowCenterOfElement(element);
    })
      ..onMouseOut.listen((_) => box.remove())
      ..onClick.listen((_) => box.remove());
    editor.onChange.listen((_) => box.remove());
    return box;
  }

}

class TextEditorMenuItem extends EditorMenuItem {

  final EditorCommandExecutor executor;
  final Element element;

  MenuOverflowHandler menuHandler;

  TextEditorMenuItem(ContentEditor editor) : super(editor), this.executor = editor.executor, this.element = editor.element {
    button.classes.add('text');
    addTitleToElement("Formater tekst", button);

    menu.classes.add('text_menu');


  }


  void showMenu() {
    if (menuHandler != null) {
      return;
    }
    menuHandler = new MenuOverflowHandler(menu);
    menuHandler.dropDown
      ..preventDefaultOnClick = true
      ..content.classes.add('submenu');


    if (editor.editorMode == ContentEditor.EDITOR_MODE_NORMAL) {
      menuHandler.addToMenu(_generateTextDropDownElement());
      menuHandler.addToMenu(_generateSizeDropDownElement());
      menuHandler.addToMenu(_generateColorDropDownElement());
    }


    _addTextIconToMenuHandler(menuHandler, "Fed skrift", "bold", () => executor.bold, executor.toggleBold);
    _addTextIconToMenuHandler(menuHandler, "Kursiv skrift", "italic", () => executor.italic, executor.toggleItalic);
    _addTextIconToMenuHandler(menuHandler, "Understreget skrift", "underline", () => executor.underline, executor.toggleUnderline);
    _addTextIconToMenuHandler(menuHandler, "Uordnet liste", "u_list", () => executor.unorderedList, executor.toggleUnorderedList);
    _addTextIconToMenuHandler(menuHandler, "Ordnet liste", "o_list", () => executor.orderedList, executor.toggleOrderedList);
    _addTextIconToMenuHandler(menuHandler, "Juster venstre", "a_left", () => executor.alignLeft, executor.justifyLeft);
    _addTextIconToMenuHandler(menuHandler, "Juster centreret", "a_center", () => executor.alignCenter, executor.justifyCenter);
    _addTextIconToMenuHandler(menuHandler, "Juster højre", "a_right", () => executor.alignRight, executor.justifyRight);
    _addTextIconToMenuHandler(menuHandler, "Juster lige", "a_just", () => executor.alignJust, executor.justifyFull);
    _addTextIconToMenuHandler(menuHandler, "Indryk mere", "p_indent", null, executor.indent);
    _addTextIconToMenuHandler(menuHandler, "Indryk mindre", "m_indent", null, executor.outdent);
    _addTextIconToMenuHandler(menuHandler, "Hævet skrift", "superscript", () => executor.superScript, executor.toggleSuperScript);
    _addTextIconToMenuHandler(menuHandler, "Sænket skrift", "subscript", () => executor.subScript, executor.toggleSubscript);
    _addTextIconToMenuHandler(menuHandler, "Gennemstreget", "strikethrough", () => executor.strikeThrough, executor.toggleStrikeThrough);
    _addTextIconToMenuHandler(menuHandler, "Indsæt link", "insert_link", null, _dialogLink);
    _addTextIconToMenuHandler(menuHandler, "Fjern formatering", "no_format", null, _clearFormat);


  }

  _addTextIconToMenuHandler(MenuOverflowHandler menuHandler, String title, String clss, bool verify(), void action()) {
    var button = new ButtonElement();
    button
      ..classes.add(clss);
    addTitleToElement(title, button);
    var trigger = () {
    };
    if (verify != null) {
      executor.listenQueryCommandStateChange(() => verify() ? button.classes.add('active') : button.classes.remove('active'));
      trigger = executor.triggerCommandStateChangeListener;
    }
    button.onClick.listen((_) {
      action();
      trigger();
    });

    menuHandler.addToMenu(button);

  }


  void _actionsSetup(EditorCommandExecutor executor, List<_EditorAction> actions, DropDown dropDown, dynamic state()) {
    actions.forEach((_EditorAction a) {
      if (a.onClickAction != null) {
        a.element.onMouseDown.listen((_) {
          a.onClickAction();
          dropDown.close();
        });
      }
    });
    executor.listenQueryCommandStateChange(() {
      var action = actions.firstWhere((_EditorAction a) => a.selectionStateChanger(state()), orElse:() => null);
      dropDown.text = action == null ? dropDown.text : action.element.text;
    });
    dropDown.preventDefaultOnClick = true;

  }

  Element _generateTextDropDownElement() {
    var actions = [
        new _EditorAction.liElementWithInnerHtml("<h1>Overskift 1</h1>", executor.formatBlockH1, (String s) => s == "h1", ['t_h1']),
        new _EditorAction.liElementWithInnerHtml("<h2>Overskift 2</h2>", executor.formatBlockH2, (String s) => s == "h2", ['t_h2']),
        new _EditorAction.liElementWithInnerHtml("<h3>Overskrift 3</h3>", executor.formatBlockH3, (String s) => s == "h3", ['t_h3']),
        new _EditorAction.liElementWithInnerHtml("<h4>Overskrift 4</h4>", executor.formatBlockH4, (String s) => s == "h4", ['t_h4']),
        new _EditorAction.liElementWithInnerHtml("<h4>Overskrift 5</h4>", executor.formatBlockH5, (String s) => s == "h5", ['t_h5']),
        new _EditorAction.liElementWithInnerHtml("<h4>Overskrift 6</h4>", executor.formatBlockH6, (String s) => s == "h6", ['t_h6']),
        new _EditorAction.liElementWithInnerHtml("<p>Normal tekst</p>", executor.formatBlockP, (String s) => s == "p", ['t_p']),
        new _EditorAction.liElementWithInnerHtml("<blockquote>Citat</blockquote>", executor.formatBlockBlockquote, (String s) => s == "blockquote", ['t_blockquote']),
        new _EditorAction.liElementWithInnerHtml("<pre>Kode</pre>", executor.formatBlockPre, (String s) => s == "pre", ['t_pre'])];


    var textType = new DropDown.fromLIList(actions.map((_EditorAction a) => a.element).toList());
    _actionsSetup(executor, actions, textType, () => executor.blockState);
    textType.element.classes.add('text_type');
    textType.text = "Normal tekst";
    return textType.element;
  }


  Element _generateSizeDropDownElement() {
    var sizeActions = [
        new _EditorAction.liElementWithInnerHtml(
            "<font size='1'>Lille</font>",
                () => executor.setFontSize(1),
                (int s) => s == 1),
        new _EditorAction.liElementWithInnerHtml(
            "Normal",
                () {
              executor.setFontSize(3);
              var fonts = element.querySelectorAll("font");
              fonts.forEach((Element e) {
                if (e.attributes['size'] != '3') {
                  return;
                }
                e.attributes.remove("size");
              });
            },
                (i) => ![1, 5, 7].contains(i)),
        new _EditorAction.liElementWithInnerHtml(
            "<font size='5'>Stor</font>",
                () => executor.setFontSize(5),
                (int s) => s == 5),
        new _EditorAction.liElementWithInnerHtml(
            "<font size='7'>Størst</font>",
                () => executor.setFontSize(7),
                (int s) => s == 7)];

    var textSize = new DropDown.fromLIList(sizeActions.map((_EditorAction e) => e.element).toList());
    textSize.element.classes.add('text_size');

    _actionsSetup(executor, sizeActions, textSize, () => executor.blockState == "p" ? executor.fontSize : -1);
    textSize.text = "Normal";
    return textSize.element;
  }


  Element _generateColorDropDownElement() {
    var
    colorContent = new DivElement(),
    colorSelect = new DropDown(colorContent),
    textColorPalette = new ColorPalette(),
    backgroundColorPalette = new ColorPalette(),
    colorLabel1 = new DivElement(),
    colorLabel2 = new DivElement();

    colorLabel1
      ..classes.add('color_label')
      ..text = "Tekstfarve";
    colorLabel2
      ..classes.add('color_label')
      ..text = "Baggrundsfarve";

    colorSelect.element.classes.add('color');
    colorSelect.preventDefaultOnClick = true;
    colorSelect.text = " ";
    colorContent
      ..append(colorLabel1)
      ..append(colorLabel2)
      ..append(textColorPalette.element)
      ..append(backgroundColorPalette.element);
    colorSelect.dropDownBox.element.classes.add('color_select');

    executor.listenQueryCommandStateChange(() {
      textColorPalette.selected = executor.foreColor;
      backgroundColorPalette.selected = executor.backColor;
    });

    textColorPalette.element.onChange.listen((_) {
      if (textColorPalette.selected != null) {
        executor.setForeColor(textColorPalette.selected);
        colorSelect.close();
      }
    });

    backgroundColorPalette.element.onChange.listen((_) {
      if (backgroundColorPalette.selected != null) {
        executor.setBackColor(backgroundColorPalette.selected);
        colorSelect.close();
      }
    });
    return colorSelect.element;
  }

  void _dialogLink() {
    var dialog = new DialogContainer();

    dialog.dialogBg.onMouseDown.listen((MouseEvent evt) {
      evt.preventDefault();
    });

    var selection = window.getSelection();
    if (!element.contains(selection.baseNode)) {
      return;
    }

    var ranges = [];
    for (var i = 0; i < selection.rangeCount; i++) {
      ranges.add(selection.getRangeAt(i));
    }

    selection.removeAllRanges();
    var box = dialog.text("Indtast link adresse", value:"http://");

    box.result.then((String s) {
      s = s.trim();
      ranges.forEach((Range r) => selection.addRange(r));
      if (s.length <= 0 || s == "http://") {
        return;
      }
      var r = ranges.first;
      var commonAncestorContainer = r.commonAncestorContainer;
      var parent = commonAncestorContainer;

      while (parent != null && parent.nodeType != Node.ELEMENT_NODE) {
        parent = parent.parentNode;
      }

      var linksBefore = parent.querySelectorAll("a");

      ranges.first.selectNode(element);
      executor.createLink(s);

      var linksAfter = parent.querySelectorAll("a");

      linksAfter.forEach((AnchorElement a) {
        if (!linksBefore.contains(a)) {
          a.target = "_blank";
        }
      });
    });
  }

  void _clearFormat() {
    var selection = window.getSelection();
    var range = selection.getRangeAt(0);
    Element commonAncestor = range.commonAncestorContainer;
    if (!(commonAncestor is Element)) {
      executor.removeFormat();
      return;
    }
    commonAncestor.querySelectorAll("*").forEach((Element elm) {
      if (!element.contains(elm) || !selection.containsNode(elm, true)) {
        return;
      }
      elm.attributes.remove("style");
    });
    executor.removeFormat();
  }

}

class FileEditorMenuItem extends EditorMenuItem {

  DivElement _uploadIconWrapper = new DivElement(),
  _uploadIcon = new DivElement(),
  _preview = new DivElement(),
  _fileUploadElementWrapper = new DivElement();

  FileUploadInputElement _fileUploadElement;

  core.FileUploader _uploader;
  _EditorFileContainer _file_container;

  void _setUpFileUploadElement() {
    if (_fileUploadElement != null) {
      _fileUploadElement.remove();
    }
    _fileUploadElement = new FileUploadInputElement();
    _fileUploadElement
      ..hidden = true
      ..multiple = true;
    _fileUploadElementWrapper.append(_fileUploadElement);

  }

  FileEditorMenuItem.file(ContentEditor editor) : super(editor){
    button.classes.add('file');
    menu.classes.add('file_menu');
    _preview.classes.add('image_preview');
    setup(new core.AJAXFileUploadStrategy(),
        (core.FileProgress fp, _EditorFileContainer container) => container.addFile(new AnchorElement(), fp));
  }

  FileEditorMenuItem.image(ContentEditor editor) : super(editor){
    button.classes.add('image');
    menu.classes.add('image_menu');
    _preview.classes.add('image_preview');
    setup(new core.AJAXImageUploadStrategy(
        new core.ImageSize.scaleMethodLimitToOuterBox(editor.element.clientWidth, 500),
        new core.ImageSize.scaleMethodLimitToOuterBox(70, 70, dataURI:true)),
        (core.FileProgress fp, _EditorFileContainer container) => container.addImage(new ImageElement(), fp));
  }

  void setup(core.UploadStrategy strategy, void listener(core.FileProgress progress, _EditorFileContainer container)) {
    addTitleToElement("Indsæt billede", button);
    menu.classes.add('upload_menu');


    _uploadIcon.classes.add('upload_icon');
    _uploadIconWrapper
      ..classes.add('upload_icon_wrapper')
      ..append(_uploadIcon);


    _setUpFileUploadElement();

    _preview.classes.add('preview');


    _uploader = new core.FileUploader(strategy);
    _file_container = new _EditorFileContainer(new DivElement(), _uploadIcon);
    _preview.append(_file_container.element);
    _uploader.onFileAddedToQueue.listen((core.FileProgress fp) => listener(fp, _file_container));
    menu
      ..append(_fileUploadElementWrapper)
      ..append(_preview)
      ..append(_uploadIconWrapper);
    _uploadIcon.onClick.listen((_) => _fileUploadElementWrapper.querySelector('input').click());
    _fileUploadElementWrapper.onChange.listen((_) {
      _uploader.uploadFiles(_fileUploadElement.files);
      _setUpFileUploadElement();
    });

  }

  Stream get onMenuChange => _file_container.onChange;

}

class HistoryEditorMenuItem extends EditorMenuItem {
  StreamController _menuChangeController = new StreamController.broadcast();

  final Calendar calendar = new Calendar();
  final UListElement historyList = new UListElement();
  final Map<TableCellElement, List<DateTime>> markMap = new Map<TableCellElement, List<DateTime>>();
  final Map<TableCellElement, List<LIElement>> payloadCache = new Map<TableCellElement, List<LIElement>>();
  final Map<Revision, LIElement> revisionElement = new Map<Revision, LIElement>();

  Element _currentCellElement;
  bool _has_been_setup = false;

  HistoryEditorMenuItem(ContentEditor editor) : super(editor) {
    button.classes.add('history');
    addTitleToElement("Se historik", button);
    menu.classes.add('history_menu');
    historyList.classes.add("history_list");
    menu
      ..append(calendar.element)
      ..append(historyList);


  }


  Element get _currentCell => _currentCellElement;

  set _currentCell(Element cell) {
    if (_currentCell != null) {
      _currentCell.classes.remove('current');
    }
    _currentCellElement = cell;

    cell.classes.add('current');
  }

  void showMenu() {
    if (_has_been_setup) {
      return;
    }
    _has_been_setup = true;
    _blurMenu();
    editor.content.changeTimes.then((List<DateTime> changeTimes) {
      _unBlurMenu();
      var last = new DateTime.fromMillisecondsSinceEpoch(0);

      changeTimes.forEach((DateTime dt) => markMap.putIfAbsent(calendar.markDate(dt), () => []).add(dt));

      markMap.forEach((TableCellElement cell, List<DateTime> times) {
        _setUpCell(cell, times, changeTimes.last);
        if (cell.classes.contains('today')) {
          cell.click();
        }
      });

      editor.content.onAddContent.listen((Revision r) {
        var cell = calendar.markDate(r.time);
        var li = _createLi(r);
        if (cell == _currentCell) {
          historyList.append(li);
        }
        li.classes.add("current");
        payloadCache.putIfAbsent(cell, () => []).add(li);
        markMap.putIfAbsent(cell, () => []).add(r.time);
        _setUpCell(cell, [r.time]);
        if (_currentCell == null && cell.classes.contains('today')) {
          cell.click();
        }
      });
    });
  }

  LIElement _createLi(Revision revision) {
    if (revisionElement.containsKey(revision)) {
      return revisionElement[revision];
    }
    var li = new LIElement(), dt = revision.time;
    revisionElement[revision] = li;
    li.text = _timeString(dt);
    li.onMouseOver.listen((_) {
      var subscription;
      subscription = document.onMouseOut.listen((_) {
        editor.hidePreview();
        subscription.cancel();
      });
      editor.showPreview(revision);
    });
    li.onMouseOut.listen((MouseEvent ev) {
      ev.preventDefault();
    });
    li.onClick.listen((_) {
      _useRevision(revision);
    });
    editor.onChange.listen((_) {
      if (editor.currentRevision == revision || editor.currentRevision == null || !li.classes.contains('current')) {
        return;
      }
      li.classes.remove('current');
    });
    return li;
  }


  void _useRevision(Revision revision) {
    if (revision == editor.currentRevision && !editor.changed) {
      return;
    }
    var element = revisionElement[revision];
    element.classes.add('current');
    editor.useRevision(revision).then((bool success) {
      if (success) {
        return;
      }
      element.classes.remove('current');
    });
  }

  String _timeString(DateTime dateTime) => "${dateTime.hour < 10 ? "0" + dateTime.hour.toString() : dateTime.hour}:${dateTime.minute < 10 ? "0" + dateTime.minute.toString() : dateTime.minute}:${dateTime.second < 10 ? "0" + dateTime.second.toString() : dateTime.second}";

  Stream get onMenuChange => _menuChangeController.stream;

  void _setUpCell(TableCellElement cell, List<DateTime> times, [lastTime = null]) {
    var len = markMap[cell].length;
    addTitleToElement("Gemt $len gang${len > 1 ? "e" : ""}", cell);
    cell.onClick.listen((_) {
      _currentCell = cell;
      historyList.children.clear();
      if (payloadCache.containsKey(cell)) {
        historyList.children.addAll(payloadCache[cell]);
        _menuChangeController.add(null);
        return;
      }
      _blurMenu();
      editor.content.listRevisions(from:times.first, to:times.last).then((List<Revision> revisions) {
        _unBlurMenu();
        var list = payloadCache[cell] = new List<LIElement>();
        revisions.forEach((Revision revision) {
          var li = _createLi(revision);
          historyList.append(li);
          list.add(li);
        });
        if (editor.currentRevision == null && revisions.last.time == lastTime) {
          list.last.classes.add('current');
        }
        _menuChangeController.add(null);
      });
    });
  }

  void _blurMenu() {
    menu.classes.add('blur');
  }

  void _unBlurMenu() {
    menu.classes.remove('blur');
  }

}

class CloseEditorMenuItem extends EditorMenuItem {


  CloseEditorMenuItem(ContentEditor editor) : super(editor) {
    button.classes.add('close');
    addTitleToElement('Afslut redigering', button);
    button.onClick.listen((_) => editor.close());

  }

  DivElement get menu => null;

}

class SaveEditorMenuItem extends EditorMenuItem {
  InfoBox _saveBox;

  SaveEditorMenuItem(ContentEditor editor) : super(editor) {
    button.classes.add('save');
    _saveBox = addTitleToElement('Gem ændringer', button, () => editor.changed);
    editor.onChange.listen((_) {
      if (editor.changed) {
        button.classes.add('enabled');
      } else {
        button.classes.remove('enabled');
        _saveBox.remove();
      }
    });

    button.onClick.listen((_) => editor.save());

  }

  DivElement get menu => null;

}


class DraftEditorMenuItem extends EditorMenuItem {

  DraftEditorMenuItem(ContentEditor editor) : super(editor) {
    button
      ..classes.add('draft')
      ..hidden = !editor.content.hasDraft
      ..text = "Kladde findes"
      ..onClick.listen((_) {
      editor.useRevision(editor.content.draft);
      editor.content.clearDraft();
    })
      ..onMouseEnter.listen((_) {
      editor.showPreview(editor.content.draft);
    })
      ..onMouseLeave.listen((_) {
      editor.hidePreview();
    });

    addTitleToElement("Brug kladde", button);

    editor.content.onHasDraftChange.listen((bool b) => button.hidden = !b);
  }

  DivElement get menu => null;
}

class ContentEditor {


  static const int EDITOR_MODE_SIMPLE = 1;
  static const int EDITOR_MODE_NORMAL = 2;

  static Map<Element, ContentEditor> _cache = new Map<Element, ContentEditor>();

  factory ContentEditor(Element element, Content content, [int editor_mode = ContentEditor.EDITOR_MODE_NORMAL]) => _cache.putIfAbsent(element, () => new ContentEditor._internal(element, content, editor_mode));

  factory ContentEditor.getCached(Element elm) => _cache[elm];

  final Element element;

  final EditorCommandExecutor executor;

  final int editorMode;

  DivElement
  _contentWrapper = new DivElement(),
  _toolBar = new DivElement(),
  _toolBarWrapper = new DivElement(),
  _preview = new DivElement();


  final Content content;

  Revision _currentRevision;

  Revision _lastSavedRevision;

  StreamController<bool> _onContentChangeStreamController = new StreamController<bool>.broadcast();

  StreamController<bool> _onOpenChangeStreamController = new StreamController<bool>.broadcast();

  StreamController<Element> _onSaveStreamController = new StreamController<Element>.broadcast();

  List<EditorMenuItem> _menuItems = [];

  EditorMenuItem _currentMenuItem;

  List<EditorClickActionItem> _clickActionItems = [];

  List<EditorKeyboardAction> _keyDownActions = [];

  InfoBox _clickActionInfoBox;

  DivElement _clickActionInfoBoxElement = new DivElement();

  bool _inputSinceSave = false, _closed = true;

  int _hash;

  ContentEditor._internal(Element element, this.content, this.editorMode) : this.element = element, executor = new EditorCommandExecutor(element){

    _setUpListeners();
    _contentWrapper.classes.add('edit_content_wrapper');
    _toolBarWrapper.classes.add('tool_bar_wrapper');
    _preview.classes.add('preview');
    _preview.hidden = true;
    _lastSavedRevision = new Revision(null, element.innerHtml);

    addMenuItem(new TextEditorMenuItem(this));
    if (editorMode == EDITOR_MODE_NORMAL) {
      addMenuItem(new FileEditorMenuItem.image(this));
      addMenuItem(new FileEditorMenuItem.file(this));
    }
    addMenuItem(new HistoryEditorMenuItem(this));
    addMenuItem(new SaveEditorMenuItem(this));
    addMenuItem(new DraftEditorMenuItem(this));
    addMenuItem(new CloseEditorMenuItem(this));

    addClickActionItem(new UnlinkEditorClickActionItem(this));
    addClickActionItem(new OpenEditorClickActionItem(this));
    addClickActionItem(new EmbedVideoEditorClickActionItem.youtube(this));
    addClickActionItem(new EmbedVideoEditorClickActionItem.vimeo(this));
    addClickActionItem(new ImageEditorClickActionItem(this));

    addKeyDownAction(new SaveEditorKeyboardAction(this));
    addKeyDownAction(new AddLinkEditorKeyboardAction(this));
  }

  void addClickActionItem(EditorClickActionItem item) {
    _clickActionItems.add(item);
  }

  void addKeyDownAction(EditorKeyboardAction action) => _keyDownActions.add(action);

  void addMenuItem(EditorMenuItem item) {
    _menuItems.add(item);
    item.onMenuChange.listen((_) {
      if (item != _currentMenuItem) {
        return;
      }
      _updateToolbarPlaceholderPadding();
    });
    item.button.onClick.listen((_) => _toggleMenuItem(item));
  }

  void _toggleMenuItem(EditorMenuItem item) {
    if (item == _currentMenuItem) {
      _closeMenuItem();
    } else {
      _openMenuItem(item);
    }
    _updateToolbarPlaceholderPadding();
  }

  void _closeMenuItem() {
    if (_currentMenuItem == null) {
      return;
    }

    _currentMenuItem
      ..hideMenu()
      ..button.classes.remove('active')
      ..menu.remove();
    _currentMenuItem = null;
  }

  void _openMenuItem(EditorMenuItem item) {
    if (item.menu == null) {
      return;
    }

    _closeMenuItem();
    _currentMenuItem = item;
    _toolBarWrapper.append(item.menu);
    item
      ..button.classes.add('active')
      ..menu.classes.add('menu')
      ..showMenu();
  }

  Revision get currentRevision => _currentRevision;

  Stream<bool> get onChange => _onContentChangeStreamController.stream;

  Stream<bool> get onOpenChange => _onOpenChangeStreamController.stream;

  Stream get onFormatBlock => executor.onFormatBlock;

  Stream<Element> get onSave => _onSaveStreamController.stream;

  bool get isOpen => !_closed;

  void _setUpListeners() {
    element.onInput.listen((_) {
      _onContentChangeStreamController.add(true);
      _inputSinceSave = true;
    });

    element.onDoubleClick.listen((Event event) {
      if (!_closed) {
        return;
      }
      window.getSelection().removeAllRanges();
      open();
    });

    content.onAddContent.listen((Revision revision) {
      _currentRevision = revision;
      _notifyChange();
    });

    window.onBeforeUnload.listen((BeforeUnloadEvent event) {
      if (_closed || !changed) {
        return;
      }
      content.draft = _revisionNow;
    });

    element.onKeyDown.listen(_keyDownHandler);
    element.onPaste.listen(_pasteHandler);

    element.onClick.listen(_clickActionHandler);

    window
      ..onScroll.listen((_) => _updateBarPosition())
      ..onResize.listen((_) => _updateBarPosition());
  }


  void _clickActionHandler(MouseEvent event) {
    if (_closed) {
      return;
    }

    if (_clickActionInfoBox == null) {
      _clickActionInfoBox = new InfoBox.elementContent(_clickActionInfoBoxElement);
      _clickActionInfoBox
        ..backgroundColor = InfoBox.COLOR_GREYSCALE
        ..removeOnESC = true
        ..element.classes.add('edit_link_image_popup');
    }

    _clickActionInfoBox
      ..remove();
    _clickActionInfoBoxElement.children.clear();

    var box_target, box_target_depth = 0;
    _clickActionItems.forEach((EditorClickActionItem item) {
      var target = item.actionTarget(event);
      if (target == null) {
        return;
      }
      var depth = _elementDepth(target);
      if (depth < box_target_depth || box_target_depth == 0) {
        box_target = target;
        box_target_depth = depth;
      }
      item.setUpButton(_clickActionInfoBox, target);
      _clickActionInfoBoxElement.append(item.button);
    });
    if (box_target == null) {
      return;
    }
    _clickActionInfoBox.showAboveCenterOfElement(box_target);


  }

  void _pasteHandler(Event event) {
    var selection = window.getSelection();
    if (_closed || selection.rangeCount == 0) {
      return;
    }

    var types = event.clipboardData.types;
    var newHtml;
    if (types.contains('text/html')) {
      newHtml = event.clipboardData.getData('text/html');
    } else if (types.contains('text/plain')) {
      newHtml = event.clipboardData.getData('text/plain');
    } else {
      return;
    }

    executor.insertFragment(new DocumentFragment.html(newHtml));

    event.preventDefault();


  }

  void _keyDownHandler(KeyboardEvent kev) {
    if (_closed) {
      return;
    }
    _keyDownActions.forEach((EditorKeyboardAction action) {
      if (!action.check(kev)) {
        return;
      }
      action.performAction(kev);
    });

  }

  Future<bool> useRevision(Revision rev) {
    var completer = new Completer<bool>();

    if (changed && _inputSinceSave) {
      new DialogContainer()
      .confirm("Du forsøger at hente en tidligere version af siden, <br /> uden at have gemt dine ændringer. <br /> Er du sikker på at du vil fortsætte?")
      .result
      .then((bool b) {
        if (!b) {
          completer.complete(false);
          return;
        }
        _inputSinceSave = false;
        _loadRevision(rev);
        completer.complete(true);
      });
    } else {
      _loadRevision(rev);
      completer.complete(true);
    }
    return completer.future;
  }


  void _loadRevision(Revision rev) {
    element.setInnerHtml(rev.content, treeSanitizer:core.nullNodeTreeSanitizer);
    _currentRevision = rev;
    _notifyChange();

  }

  void showPreview(Revision rev) {
    _preview.setInnerHtml(rev.content, treeSanitizer:core.nullNodeTreeSanitizer);
    _hidePreview(false);

  }

  void hidePreview() {
    _hidePreview(true);

  }

  bool _hidePreview(bool hide) => element.hidden = !(_preview.hidden = hide);

  bool get changed => _currentHash != _hash;

  void toggelOpen() {
    if (_closed) {
      open();
    } else {
      close();
    }
  }


  void _addEscToQueue() {

    core.escQueue.add(() {
      close();
      return true;
    });
  }

  void open() {

    if (!_closed) {
      return;
    }
    _addEscToQueue();
    _updateToolbarPlaceholderPadding();
    element.contentEditable = "true";
    _closed = false;
    _onOpenChangeStreamController.add(isOpen);
    _menuItems.forEach((EditorMenuItem item) {
      _toolBar.append(item.button);
    });


    if (_contentWrapper.parent != null) {
      _toolBarWrapper.hidden = false;
      _updateBarPosition();
      return;
    }


    _contentWrapper.append(_toolBarWrapper);

    _toolBarWrapper.append(_toolBar);

    _toolBar
      ..onMouseDown.listen((MouseEvent e) => e.preventDefault())
      ..classes.add('tool_bar');


    element.insertAdjacentElement("afterEnd", _contentWrapper);
    _contentWrapper.append(element);
    element.insertAdjacentElement("afterEnd", _preview);

    _saveCurrentHash();
    _updateBarPosition();


  }

  Revision get _revisionNow => new Revision(new DateTime.now(), element.innerHtml);

  void close() {
    if (_closed) {
      return;
    }
    if (changed) {
      content.draft = _revisionNow;
      _loadRevision(_lastSavedRevision);
    }

    _closeMenuItem();

    _toolBarWrapper.hidden = true;
    element.contentEditable = "false";
    _closed = true;
    _onOpenChangeStreamController.add(isOpen);
    _updateToolbarPlaceholderPadding();

  }

  void _saveCurrentHash() {
    _hash = _currentHash;
  }

  int get _currentHash => element.innerHtml.hashCode;


  ElementList<HeadingElement> get headers => element.querySelectorAll("h2, h1, h3").toList();

  void save() {
    if (!changed) {
      return;
    }

    var savingBar = new SavingBar();
    var jobId = savingBar.startJob();
    _inputSinceSave = false;
    _updateHeaderIds();
    _onSaveStreamController.add(element);
    var html = element.innerHtml;
    content.addContent(html).then((Revision rev) {
      _saveCurrentHash();
      savingBar.endJob(jobId);
      _lastSavedRevision = rev;
    });
  }

  void _updateHeaderIds() {

    headers.forEach((Element header) {
      header.id = "";
      var id = header.text.replaceAll(new RegExp(r"[^a-zA-Z0-9]+"), "_");
      if (id.length == 0) {
        header.remove();
        return;
      }
      var base = id;
      var i = 1;
      while (querySelector("#$id") != null) {
        id = "${base}_$i";
        i++;
      }
      header.id = id;
    });
  }

  void _updateBarPosition() {
    if (_closed) {
      return;
    }
    if (!_toolBarWrapper.classes.contains('floating')) {
      if (!isTopVisible(_toolBarWrapper)) {
        _toolBarWrapper.style.width = _toolBarWrapper.getComputedStyle().width;
        _toolBarWrapper.classes.add('floating');
        _updateToolbarPlaceholderPadding();

      }
      return;
    }

    if (window.scrollY <= _contentWrapper.documentOffset.y) {
      _toolBarWrapper.classes.remove('floating');
      _toolBarWrapper.style.removeProperty('width');
      _updateToolbarPlaceholderPadding();
      return;
    }

    var contentWrapperBottom = _contentWrapper.documentOffset.y + _contentWrapper.offsetHeight;

    if (window.scrollY + _toolBarWrapper.offsetHeight >= contentWrapperBottom) {
      _toolBarWrapper.classes.add('fixed');
    } else {
      _toolBarWrapper.classes.remove('fixed');
    }

  }

  void _updateToolbarPlaceholderPadding() {


    if (_toolBarWrapper.classes.contains('floating') && !_toolBarWrapper.hidden) {
      _contentWrapper.style.paddingTop = _toolBarWrapper.getComputedStyle().height;
    } else {
      _contentWrapper.style.paddingTop = "";
    }
  }


  void _notifyChange() => _onContentChangeStreamController.add(false);


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
      menu
        ..append(element)
        ..append(divider);

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


/**
 * Looking for elements with the 'editable' class.
 * Getting:
 *    id : from data-id (if not found use id attribute)
 *    site-content : if data-site-content is true will use site content instead of page content (default: false)
 *    page-id : From data-page-id, default is current page. If site-content, does nothing.
 *              If page id not found fallback is current.
 *    editor-mode: From data-editor-mode, if simple will initialize simple editor else normal editor.
 *
 */

class EditorInitializer implements core.Initializer {

  final PageOrder pageOrder;

  final UserLibrary userLibrary;

  final Site site;

  EditorInitializer(this.site, this.pageOrder, this.userLibrary);


  bool get canBeSetUp => site != null && pageOrder != null && userLibrary != null && userLibrary.userLoggedIn != null;

  void setUp() {
    var user = userLibrary.userLoggedIn;
    var editableElements = querySelectorAll("div.editable");

    editableElements.forEach((DivElement div) {
      var id = (div.dataset.containsKey("id") ? div.dataset["id"] : div.id);
      var editorMode = (div.dataset["editorMode"] == "simple" ? ContentEditor.EDITOR_MODE_SIMPLE : ContentEditor.EDITOR_MODE_NORMAL);

      if (div.dataset["siteContent"] == "true") {
        if (!user.canModifySite) {
          return;
        }
        new ContentEditor(div, site[id], editorMode);
      } else {
        var p;
        var page = div.dataset.containsKey("pageId") && (p = pageOrder[div.dataset["pageId"]]) is Page ? p : pageOrder.currentPage;
        new ContentEditor(div, page[id], editorMode);

      }
    });
  }
}


class EditableContentNavigator<T extends Element> {
  final ContentEditor editor;
  final T element;
  final Function builder;

  EditableContentNavigator(ContentEditor this.editor, Content navContent, this.element, void builder(T element, ElementList<HeadingElement> l)) : this.builder = builder {
    editor.onChange.where((b) => b).listen((_) => builder(element, editor.headers));
    editor.onSave.listen((_) {
      builder(element, editor.headers);
      var jobId = savingBar.startJob();
      navContent.addContent(element.innerHtml).then((_) {
        savingBar.endJob(jobId);
      });
    });

  }

}


class UListEditableContentNavigator extends EditableContentNavigator<UListElement> {

  UListEditableContentNavigator(ContentEditor editor, Content navContent, UListElement element) : super(editor, navContent, element, defaultBuilder);


  static void defaultBuilder(UListElement element, ElementList<HeadingElement> l, [bool first = true]) {
    if (first) {
      element.children.clear();
    }
    var e, tagNameNumber = -1;
    while (l.length > 0) {
      e = l.first;
      var t = _tagNameNumber(e);
      if (tagNameNumber > t && !first) {
        return;
      }
      tagNameNumber = _tagNameNumber(e);
      var li = new LIElement();
      var a = new AnchorElement();
      li.classes.add(e.tagName);
      a
        ..text = e.text
        ..href = "#${e.id}";
      li.append(a);
      element.append(li);
      l.removeAt(0);
      if (l.length > 0 && _tagNameNumber(l.first) > tagNameNumber) {
        var ul = new UListElement();
        defaultBuilder(ul, l, false);
        li.append(ul);
      }

    }

  }


  static int _tagNameNumber(HeadingElement h) => h == null ? -1 : int.parse(h.tagName.substring(1));
}