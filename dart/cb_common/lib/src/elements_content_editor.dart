part of elements;

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

}


abstract class EditorHandler {
  final DivElement element;

  final Element dataElement;

  EditorHandler(this.element, this.dataElement);

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
    _imageCount
      ..classes.add('image_count')
      ..text = "0";

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

  core.FileProgress _fileProgress;


  EditorFileHandler(AnchorElement dataElement) : this.dataElement = dataElement {
    _setUp();
  }

  EditorFileHandler.fileProgress(this.dataElement, core.FileProgress fileProgress, void ready()): _fileProgress = fileProgress{
    var size = new SpanElement();
    size.text = core.sizeToString(fileProgress.file.size);

    _fileStandIn
      ..text = fileProgress.file.name
      ..append(size);

    _fileProgress.onProgress.listen((_) => progressBar.percentage = _fileProgress.progress);
    _fileProgress.onPathAvailable.listen((_) {
      dataElement
        ..href = "/_files/"+_fileProgress.path
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

class EditorImageHandler implements EditorHandler {
  final DivElement element = new DivElement();

  DivElement _imageStandIn = new DivElement();

  final ImageElement dataElement;

  final ProgressBar progressBar = new ProgressBar();

  core.FileProgress _fileProgress;


  EditorImageHandler(ImageElement dataElement) : this.dataElement = dataElement {
    _setUp();
  }

  EditorImageHandler.fileProgress(this.dataElement, core.FileProgress fileProgress, void ready()): _fileProgress = fileProgress{
    _fileProgress.onProgress.listen((_) => progressBar.percentage = _fileProgress.progress);
    _fileProgress.onPathAvailable.listen((_) {
      dataElement.src = "/_files/"+_fileProgress.path;
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


class EditorFileContainer {
  static Map<Element, EditorFileContainer> _cache = new Map<Element, EditorFileContainer>();

  final Element element, trashcan;

  Element _dragging;

  StreamController<EditorFileContainer> _change_controller = new StreamController<EditorFileContainer>();
  Stream<EditorFileContainer> _change_stream;


  factory EditorFileContainer(Element element, Element trashCan) => _cache.putIfAbsent(element, () => new EditorFileContainer._internal(element, trashCan));

  Map<Element, EditorHandler> _handlerMap = new Map<Element, EditorHandler>();


  EditorFileContainer._internal(this.element, this.trashcan){
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

  EditorImageHandler addImage(ImageElement image, [core.FileProgress progress = null]) {
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

  EditorFileHandler addFile(AnchorElement fileLink, [core.FileProgress progress = null]) {
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

  Stream<EditorFileContainer> get onChange => _change_stream;

  void _notifyContentChange() => _change_controller.add(this);

}



class LinkImageHandler {
  final Element element;

  final ContentEditor editor;

  bool _enabled;

  InfoBox _infoBox;

  DivElement _boxElement = new DivElement();

  ButtonElement _unlinkButton = new ButtonElement(), _openButton = new ButtonElement(), _editImageButton = new ButtonElement(), _youtubeButton = new ButtonElement(), _vimeoButton = new ButtonElement();

  AnchorElement _foundLink;

  ImageElement _foundImage;

  LinkImageHandler(this.element, this.editor) {
//    _imageEditor.open(element.querySelector('img'));
    _enabled = editor.isOpen;
    editor.onOpenChange.listen((bool b) {
      _setUp();
      _infoBox.remove();
      _enabled = b;
    });
  }

  void _setUp() {
    if (_infoBox != null) {
      return;
    }

    _unlinkButton
      ..classes.add('unlink')
      ..onClick.listen((MouseEvent mev) {
      mev.preventDefault();
      _foundLink.insertAdjacentHtml("afterEnd", _foundLink.innerHtml);
      _foundLink.remove();
      _infoBox.remove();
      editor.executor.triggerCommandStateChangeListener();
    });

    _youtubeButton
      ..classes.add('youtube')
      ..onClick.listen((MouseEvent mev) {
      mev.preventDefault();
      var id = core.youtubeVideoIdFromUrl(_foundLink.href);
      var width = element.clientWidth;
      var height = (width * 9 / 16).ceil();
      _foundLink.insertAdjacentHtml("afterEnd", '<iframe width="$width" height="$height" src="//www.youtube.com/embed/$id?badge=0&amp;modestbranding=1&amp;controls=1&amp;autohide=1&amp;showinfo=0&amp;rel=0&amp;fs=0" frameborder="0" allowfullscreen="" webkitallowfullscreen="" mozallowfullscreen=""></iframe>');
      _foundLink.remove();
      _infoBox.remove();
      editor.executor.triggerCommandStateChangeListener();
    });

    _vimeoButton
      ..classes.add('vimeo')
      ..onClick.listen((MouseEvent mev) {
      mev.preventDefault();
      var id = core.vimeoVideoIdFromUrl(_foundLink.href);
      var width = element.clientWidth;
      var height = (width * 9 / 16).ceil();
      _foundLink.insertAdjacentHtml("afterEnd", '<iframe width="$width" height="$height" src="//player.vimeo.com/video/$id?badge=0&amp;modestbranding=1&amp;controls=1&amp;autohide=1&amp;showinfo=0&amp;rel=0&amp;fs=0" frameborder="0" allowfullscreen="" webkitallowfullscreen="" mozallowfullscreen=""></iframe>');
      _foundLink.remove();
      _infoBox.remove();
      editor.executor.triggerCommandStateChangeListener();
    });
    _openButton
      ..classes.add('open')
      ..onClick.listen((MouseEvent mev) {
      mev.preventDefault();
      _infoBox.remove();
      window.open(_foundLink.href, "_blank");
    });
    _editImageButton
      ..classes.add('edit_image')
      ..onClick.listen((MouseEvent mev) {
      mev.preventDefault();
      _infoBox.remove();

      var handler = new ImageEditorHandler.fromImage(_foundImage);
      handler.editor.maxWidth = editor.element.clientWidth;
      handler.editor.minWidth = 50;
      handler.open();
      handler.onEdit.listen((ImageEditProperties p) {
        editor.element.dispatchEvent(new Event("input"));
      });

    });

    _infoBox = new InfoBox.elementContent(_boxElement);
    _infoBox
      ..backgroundColor = InfoBox.COLOR_GREYSCALE
      ..removeOnESC = true
      ..element.classes.add('edit_link_image_popup');

    document.onClick.listen(_clickHandler);

  }


  void _clickHandler(MouseEvent event) {
    if (!_enabled) {
      return;
    }
    var elm = event.toElement;
    if (_infoBox.element.contains(elm)) {
      return;
    }

    if (!element.contains(elm)) {
      _infoBox.remove();
      return;
    }
    _foundLink = _foundImage = null;

    while (elm != element && _foundLink == null) {
      _foundLink = elm is AnchorElement ? elm : null;
      if (_foundImage == null && elm is ImageElement) {
        _foundImage = elm;
      }
      elm = elm.parent;

    }
    if (_foundLink == null && _foundImage == null) {
      _infoBox.remove();
      return;
    }
    _boxElement.children.clear();
    if (_foundImage != null) {
      _boxElement.append(_editImageButton);
    }

    if (_foundLink != null) {
      _boxElement.append(_unlinkButton);
      if (core.youtubeVideoIdFromUrl(_foundLink.href) != null) {
        _boxElement.append(_youtubeButton);
      }
      if (core.vimeoVideoIdFromUrl(_foundLink.href) != null) {
        _boxElement.append(_vimeoButton);
      }
      _boxElement.append(_openButton);
    }
    _infoBox.showAboveCenterOfElement(_foundLink == null ? _foundImage : _foundLink);

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

  static const int EDITOR_MODE_SIMPLE = 1;
  static const int EDITOR_MODE_NORMAL = 2;

  static Map<Element, ContentEditor> _cache = new Map<Element, ContentEditor>();

  factory ContentEditor(Element element, Content content, [int editor_mode = ContentEditor.EDITOR_MODE_NORMAL]) => _cache.putIfAbsent(element, () => new ContentEditor._internal(element, content, editor_mode));

  factory ContentEditor.getCached(Element elm) => _cache[elm];

  final Element element;

  final EditorCommandExecutor executor;

  final int editorMode;

  DivElement _contentWrapper = new DivElement(), _topBar, _toolBarPlaceholder = new DivElement(), _wrapper = new DivElement(), _preview = new DivElement();

  Map<Element, Element> _elementToSubMenu = new Map<Element, Element>();

  Content _currentContent;

  Revision _currentRevision;

  Revision _lastSavedRevision;

  PropertyAnimation _previewAnimation;

  StreamController<bool> _onContentChangeStreamController = new StreamController<bool>();

  Stream<bool> _onContentChangeStream;

  StreamController<bool> _onOpenChangeStreamController = new StreamController<bool>();

  Stream<bool> _onOpenChangeStream;

  StreamController<Element> _onSaveStreamController = new StreamController<Element>();

  Stream<Element> _onSaveStream;


  bool _inputSinceSave = false, _closed = true;

  int _hash;

  ContentEditor._internal(Element element, this._currentContent, this.editorMode) : this.element = element, executor = new EditorCommandExecutor(element){

    _setUpStream();
    _toolBarPlaceholder.classes.add('tool_bar_placeholder');
    _contentWrapper.classes.add('edit_content_wrapper');
    _wrapper.classes.add('tool_bar_wrapper');
    _preview.classes.add('preview');
    _preview.hidden = true;
    _lastSavedRevision = new Revision(null, element.innerHtml);
    element.onDoubleClick.listen((Event event) {
      if (!_closed) {
        return;
      }
      window.getSelection().empty();
      open();
    });
    new LinkImageHandler(element, this);

  }


  Stream<bool> get onChange => _onContentChangeStream == null ? _onContentChangeStream = _onContentChangeStreamController.stream.asBroadcastStream() : _onContentChangeStream;

  Stream<bool> get onOpenChange => _onOpenChangeStream == null ? _onOpenChangeStream = _onOpenChangeStreamController.stream.asBroadcastStream() : _onOpenChangeStream;

  Stream<Element> get onSave => _onSaveStream == null ? _onSaveStream = _onSaveStreamController.stream.asBroadcastStream() : _onSaveStream;

  bool get isOpen => !_closed;

  void _setUpStream() {
    element.onInput.listen((_) => _onContentChangeStreamController.add(true));
    onChange.listen((bool b) {
      if (b) {
        _inputSinceSave = true;
      }
    });
  }

  Future<bool> _useRevision(Revision rev) {
    var completer = new Completer<bool>();

    if (changed && _inputSinceSave) {
      var dialog = new DialogContainer();
      var f = dialog.confirm("Du forsøger at hente en tidligere version af siden, <br /> uden at have gemt dine ændringer. <br /> Er du sikker på at du vil fortsætte?").result;
      f.then((bool b) {
        if (!b) {
          completer.complete(false);
          return;
        }
        _inputSinceSave = false;
        _loadRevision(rev);
        completer.complete(true);
      });
      return completer.future;
    }
    _loadRevision(rev);
    completer.complete(true);
    return completer.future;
  }


  void _loadRevision(Revision rev) {
    element.setInnerHtml(rev.content, treeSanitizer:core.nullNodeTreeSanitizer);
    _currentRevision = rev;
    _notifyChange();

  }

  void _showPreview(Revision rev) {
    _animatePreview(rev);

  }

  void _hidePreview() {
    _animatePreview();

  }

  void _animatePreview([Revision content]) {
    if (_previewAnimation == null) {
      _previewAnimation = new HeightPropertyAnimation(_contentWrapper);
      _previewAnimation.removePropertyOnComplete = true;
    }
    _contentWrapper.style.height = "${_contentWrapper.clientHeight.toString()}px";
    _previewAnimation.stop();
    var hidePreview = true;
    if (content != null) {
      _preview.setInnerHtml(content.content, treeSanitizer:core.nullNodeTreeSanitizer);
      hidePreview = false;
    }

    element.hidden = !(_preview.hidden = hidePreview);
    var images = hidePreview ? element.querySelectorAll("img") : _preview.querySelectorAll('img');
    if (images.length > 0) {
      var i = 0;
      images.forEach((ImageElement e) {
        e.onLoad.listen((_) {
          i++;
          if (i == images.length) {
            _previewAnimation.animateTo(maxChildrenHeight(_contentWrapper).toString(), onComplete:_updateBarPosition);
          }
        });
        if (e.complete) {
          e.dispatchEvent(new Event('load', canBubble:false));
        }

      });

    } else {
      _previewAnimation.animateTo(maxChildrenHeight(_contentWrapper).toString(), onComplete:_updateBarPosition);

    }
  }

  bool get changed => _currentHash != _hash;

  void toggelOpen() {
    if (_closed) {
      open();
    } else {
      close();
    }
  }


  void open() {
    core.escQueue.add(() {
      if (_closed) {
        return false;
      }
      close();
      return true;
    });


    if (!_closed) {
      return;
    }

    element.contentEditable = "true";
    _closed = false;
    _onOpenChangeStreamController.add(isOpen);


    if (_contentWrapper.parent != null) {
      _updateBarPosition();
      return;
    }
    window.onBeforeUnload.listen((BeforeUnloadEvent event) {
      if (_closed || !changed) {
        return;
      }
      event.returnValue = "Du har ikke gemt dine ændringer.";
    });
    element.onKeyDown.listen((KeyboardEvent kev) {
      if (_closed || kev.keyCode != 83 || !kev.ctrlKey) {
        return;
      }
      kev.preventDefault();
      save();
    });

    element.onKeyDown.listen((KeyboardEvent kev) {
      if (kev.keyCode != 32) {
        return;
      }
      var selection = window.getSelection();
      if (selection.rangeCount == 0) {
        return;
      }
      var range = selection.getRangeAt(0);
      var endOffset = range.startOffset, startOffset = range.endOffset;
      if (endOffset != startOffset) {
        return;
      }
      var parentNode = range.commonAncestorContainer;

      var q = parentNode.parent;
      while (q != null) {
        if (q is AnchorElement) {
          return;
        }
        q = q.parent;
      }

      var regex = new RegExp(r"\s([^\s]+)$");
      var value = parentNode.nodeValue;
      if (value == null) {
        return;
      }

      var match = regex.firstMatch(" " + value.substring(0, startOffset));

      if (match == null) {
        return;
      }

      var m = match.group(1);

      if (m.trim() != m) {
        return;
      }
      m = m.trim();

      if (!core.validUrl(m) && !core.validMail(m)) {
        return;
      }

      var start = startOffset - m.length;
      var t1 = new Text(value.substring(0, start)), t2 = new Text(" " + value.substring(startOffset));
      var p = parentNode.parent;
      p.insertBefore(t1, parentNode);
      var anchor = new AnchorElement();
      anchor.text = m;
      anchor.href = (core.validMail(m) ? "mailto:" : "") + m;
      anchor.target = "_blank";
      p.insertBefore(anchor, parentNode);
      p.insertBefore(t2, parentNode);
      kev.preventDefault();
      parentNode.remove();
      selection.setPosition(t2, 1);


    });

    _contentWrapper.append(_wrapper);
    _wrapper.style.height = "0";
    _wrapper.append(_topBar == null ? _topBar = _generateToolBar() : _topBar);
    element.insertAdjacentElement("afterEnd", _contentWrapper);
    _contentWrapper.append(element);
    element.insertAdjacentElement("afterEnd", _preview);
    window.onScroll.listen((_) => _updateBarPosition());
    window.onResize.listen((_) => _updateBarPosition());
    _saveCurrentHash();
    _updateBarPosition();

  }

  void close() {
    if (_closed) {
      return;
    }
    if (changed) {
      var dialog = new DialogContainer();
      var c = dialog.confirm("Du har ikke gemt dine ændringer. <br /> Er du sikker på at du vil afslutte?").result;
      c.then((bool b) {
        if (b) {
          _loadRevision(_lastSavedRevision);
          close();
        } else {
          open();
        }
      });
      return;
    }

    var b = _topBar.querySelector(".tool_bar button.active");
    if (b != null) {
      b.click();
    }

    _wrapper.style.height = "0";
    _toolBarPlaceholder.style.height = "0";

    element.contentEditable = "false";
    _closed = true;
    _onOpenChangeStreamController.add(isOpen);


  }

  void _saveCurrentHash() {
    _hash = _currentHash;

  }

  int get _currentHash => element.innerHtml.hashCode;


  void save() {
    if (!changed) {
      return;
    }

    var savingBar = new SavingBar();
    var jobId = savingBar.startJob();
    _inputSinceSave = false;
    var l = element.querySelectorAll("h2, h1, h3");

    l.forEach((Element h) {
      h.id = "";
    });

    l.forEach((Element h) {
      var id = h.text.replaceAll(new RegExp(r"[^a-zA-Z0-9]+"), "_");
      if (id.length == 0) {
        h.remove();
        return;
      }
      var base = id;
      var i = 1;
      while (querySelector("#$id") != null) {
        id = "${base}_$i";
        i++;
      }
      h.id = id;
    });
    _onSaveStreamController.add(element);
    var html = element.innerHtml;
    _currentContent.addContent(html).then((Revision rev) {
      _saveCurrentHash();
      savingBar.endJob(jobId);
      _lastSavedRevision = rev;

    });
  }

  void _updateBarPosition() {
    if (_closed) {
      return;
    }
    var floatCandidate = window.scrollY > _elementOffsetTop(_contentWrapper) + _contentWrapper.offsetHeight - _topBar.clientHeight;
    _wrapper.style.removeProperty("top");
    if (floatCandidate) {
      _wrapper.style.width = "${_wrapper.clientWidth}px";
      _wrapper.classes
        ..remove('floating')
        ..add('fixed');
      _wrapper.style.top = "${_contentWrapper.offsetHeight - _topBar.clientHeight}px";
    } else if (!_wrapper.classes.contains('floating') && window.scrollY > _elementOffsetTop(_topBar) && !floatCandidate) {
      _toolBarPlaceholder.style.height = "${_topBar.clientHeight}px";
      _wrapper.insertAdjacentElement("afterEnd", _toolBarPlaceholder);
      _wrapper.style.width = "${_wrapper.clientWidth}px";
      _wrapper.classes
        ..add('floating')
        ..remove('fixed');
    } else if (window.scrollY <= _elementOffsetTop(_toolBarPlaceholder)) {
      _toolBarPlaceholder.remove();
      _wrapper
        ..style.removeProperty("width")
        ..classes.remove('floating')
        ..classes.remove('fixed');

    }
    if (_wrapper.classes.contains('floating')) {
      _wrapper.style.left = "${_elementOffsetLeft(_contentWrapper) - window.scrollX}px";
    }

    _updatePlaceholder();

  }

  void _updatePlaceholder() {
    if (_closed) {
      return;
    }

    _wrapper.style.height = _topBar.getComputedStyle().height;
    if (_toolBarPlaceholder.parent == null) {
      return;
    }
    _toolBarPlaceholder.style.height = "${_topBar.clientHeight}px";
  }

  int _elementOffsetTop(Element e) => e == null ? 0 : e.offsetTop + _elementOffsetTop(e.offsetParent);

  int _elementOffsetLeft(Element e) => e == null ? 0 : e.offsetLeft + _elementOffsetLeft(e.offsetParent);


  void _setUpSubMenu(Element element, Element menu, Element subMenu, void menuFiller(Element)) {
    _elementToSubMenu[element] = subMenu;
    subMenu.classes.add('menu');
    element.onClick.listen((_) {
      var active = menu.querySelector('.active');
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

      if (element.classes.contains('active')) {
        core.escQueue.add(() {

          if (!element.classes.contains('active')) {
            return false;
          }

          element.click();
          return true;
        });
      }

      if (subMenu.parent == null) {
        menu.parent.append(subMenu);
        menuFiller(subMenu);
      }
      _updatePlaceholder();
    });
  }


  DivElement _generateToolBar() {
    var bar = new DivElement(), textEdit = new ButtonElement(), addImage = new ButtonElement(), addFile = new ButtonElement(), history = new ButtonElement(), saveElement = new ButtonElement(), closeElement = new ButtonElement(), wrapper = new DivElement(), textMenu = new DivElement(), imageMenu = new DivElement(), fileMenu = new DivElement(), historyMenu = new DivElement();

    bar.onMouseDown.listen((MouseEvent e) => e.preventDefault());
    textMenu.onMouseDown.listen((MouseEvent e) => e.preventDefault());

    textEdit.classes.add('text');
    _setUpSubMenu(textEdit, bar, textMenu, (Element e) => _fillTextMenu(e));
    _addTitleToElement("Formater tekst", textEdit);
    bar.append(textEdit);

    if (editorMode == ContentEditor.EDITOR_MODE_NORMAL) {

      addImage.classes.add('image');
      _setUpSubMenu(addImage, bar, imageMenu, (Element e) => _fillUploadMenu(e, true));
      _addTitleToElement("Indsæt billede", addImage);
      bar.append(addImage);

      addFile.classes.add('file');
      _setUpSubMenu(addFile, bar, fileMenu, (Element e) => _fillUploadMenu(e));
      _addTitleToElement("Indsæt fil", addFile);
      bar.append(addFile);

    }

    history.classes.add('history');
    _setUpSubMenu(history, bar, historyMenu, (Element e) => _fillHistoryMenu(e));
    _addTitleToElement("Se historik", history);
    bar.append(history);

    closeElement.classes.add('close');
    _addTitleToElement("Afslut redigering", closeElement);
    closeElement.onClick.listen((_) => close());
    bar.append(closeElement);

    saveElement.classes.add('save');
    var saveBox = new InfoBox("Gem ændringer");
    saveBox
      ..backgroundColor = InfoBox.COLOR_BLACK
      ..reversed = true;
    saveElement
      ..onMouseOver.listen((_) {
      if (changed) {
        saveBox.showBelowCenterOfElement(saveElement);
      }
    })
      ..onMouseOut.listen((_) => saveBox.remove())
      ..onClick.listen((_) {
      if (changed) {
        save();
      }
      saveBox.remove();
    });
    bar.append(saveElement);

    _currentContent.onAddContent.listen((_) => _notifyChange());

    onChange.listen((_) {
      if (changed) {
        saveElement.classes.add('enabled');
      } else {
        saveElement.classes.remove('enabled');
        saveBox.remove();
      }
    });


    bar.classes.add('tool_bar');


    wrapper.append(bar);


    return wrapper;
  }

  void _notifyChange() => _onContentChangeStreamController.add(false);

  void _fillHistoryMenu(Element menu) {
    var calendar = new Calendar(), historyList = new UListElement();
    menu
      ..classes.add('history_menu')
      ..classes.add('loading');

    historyList.classes.add("history_list");

    _currentContent.changeTimes.then((List<DateTime> changeTimes) {
      menu
        ..classes.remove('loading')
        ..append(calendar.element)
        ..append(historyList);
      _updatePlaceholder();

      var last = new DateTime.fromMillisecondsSinceEpoch(0);
      var markMap = new Map<TableCellElement, List<DateTime>>(), payloadCache = new Map<TableCellElement, List<LIElement>>();
      changeTimes.forEach((DateTime dt) {
        var el = calendar.markDate(dt);
        markMap.putIfAbsent(el, () => []).add(dt);

      });
      var currentCell;
      var createLi = (Revision revision, [UListElement historyList]) {
        var li = new LIElement(), dt = revision.time;
        li.text = "${dt.hour < 10 ? "0" + dt.hour.toString() : dt.hour}:${dt.minute < 10 ? "0" + dt.minute.toString() : dt.minute}:${dt.second < 10 ? "0" + dt.second.toString() : dt.second}";
        if (historyList != null) {
          historyList.append(li);
        }
        li.onMouseOver.listen((_) {
          var ss;
          ss = document.onMouseOut.listen((_) {
            _hidePreview();
            ss.cancel();
          });
          _showPreview(revision);
        });
        li.onMouseOut.listen((MouseEvent ev) {
          ev.preventDefault();
        });
        li.onClick.listen((_) {
          if (revision == _currentRevision && !changed) {
            return;
          }
          li.classes.add('current');
          _useRevision(revision).then((bool b) {
            if (b) {
              return;
            }
            li.classes.remove('current');
          });
        });
        onChange.listen((_) {
          if (_currentRevision == revision || _currentRevision == null || !li.classes.contains('current')) {
            return;
          }
          li.classes.remove('current');
        });
        return li;
      };
      var setUp = (TableCellElement cell, List<DateTime> times) {
        var len = markMap[cell].length;
        var box = new InfoBox("Gemt $len gang${len > 1 ? "e" : ""}");
        cell.onMouseOver.listen((_) => box.showBelowCenterOfElement(cell));
        cell.onMouseOut.listen((_) => box.remove());
        box.reversed = true;
        box.backgroundColor = InfoBox.COLOR_BLACK;
        cell.onClick.listen((_) {
          if (currentCell != null) {
            currentCell.classes.remove('current');
          }
          currentCell = cell;
          cell.classes.add('current');
          historyList.children.clear();
          _updatePlaceholder();
          if (payloadCache.containsKey(cell)) {
            historyList.children.addAll(payloadCache[cell]);
            _updatePlaceholder();
            return;
          }
          historyList.classes.add('loading');
          _currentContent.listRevisions(from:times.first, to:times.last).then((List<Revision> revisions) {
            historyList.classes.remove('loading');
            var l = payloadCache[cell] = new List<LIElement>();
            revisions.forEach((Revision revision) {
              var li = createLi(revision, historyList);
              l.add(li);
              if (_currentRevision == null && revision.time == changeTimes.last) {
                li.classes.add('current');
              }
            });
            _updatePlaceholder();
          });
        });
      };
      markMap.forEach((TableCellElement cell, List<DateTime> times) {
        setUp(cell, times);
        if (cell.classes.contains('today')) {
          cell.click();
        }
      });
      _currentContent.onAddContent.listen((Revision r) {
        var c = calendar.markDate(r.time);
        var li = createLi(r, c == currentCell ? historyList : null);
        li.classes.add("current");
        _currentRevision = r;
        payloadCache.putIfAbsent(c, () => []).add(li);
        markMap.putIfAbsent(c, () => []).add(r.time);
        setUp(c, [r.time]);
        if (currentCell == null && c.classes.contains('today')) {
          c.click();
        }
        _notifyChange();
      });
    });

  }


  void _fillUploadMenu(DivElement menu, [bool images = false]) {
    menu.classes.add('upload_menu');

    var uploadIconWrapper = new DivElement(), uploadIcon = new DivElement(), fileUploadElementWrapper = new DivElement(), fileUploadElement = new FileUploadInputElement(), preview = new DivElement();
    uploadIcon.classes.add('upload_icon');
    uploadIconWrapper
      ..classes.add('upload_icon_wrapper')
      ..append(uploadIcon);

    var setUpFileUpload = () {
      var fileUploadElement = new FileUploadInputElement();
      fileUploadElement
        ..hidden = true
        ..multiple = true;
      fileUploadElementWrapper.append(fileUploadElement);

      return fileUploadElement;
    };

    fileUploadElement = setUpFileUpload();
//TODO Fix close fileupload with ESC
    preview.classes.add('preview');

    var uploadStrategy = images ? new core.AJAXImageUploadStrategy(new core.ImageSize.scaleMethodLimitToOuterBox(element.clientWidth, 500), new core.ImageSize.scaleMethodLimitToOuterBox(70, 70, dataURI:true)) : new core.AJAXFileUploadStrategy();
    ;
    var uploader = new core.FileUploader(uploadStrategy);
    var container = new EditorFileContainer(new DivElement(), uploadIcon);
    container.onChange.listen((_) {
      _updatePlaceholder();
    });

    if (images) {
      menu.classes.add('image_menu');
      preview
        ..classes.add('image_preview')
        ..append(container.element);
      uploader.onFileAddedToQueue.listen((core.FileProgress fp) => container.addImage(new ImageElement(), fp));
    } else {
      menu.classes.add('file_menu');
      preview
        ..classes.add('file_preview')
        ..append(container.element);
      uploader.onFileAddedToQueue.listen((core.FileProgress fp) => container.addFile(new AnchorElement(), fp));
    }


    menu
      ..append(fileUploadElementWrapper)
      ..append(preview)
      ..append(uploadIconWrapper);
    uploadIcon.onClick.listen((_) => fileUploadElementWrapper.querySelector('input').click());

    fileUploadElementWrapper.onChange.listen((_) {
      uploader.uploadFiles(fileUploadElementWrapper.querySelector('input').files);
      fileUploadElement.remove();
      fileUploadElement = setUpFileUpload();
    });
  }

  void _fillTextMenu(DivElement menu) {

    menu.classes.add('text_menu');

    var menuHandler = new MenuOverflowHandler(menu);
    menuHandler.dropDown
      ..preventDefaultOnClick = true
      ..content.classes.add('submenu');


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

    if (editorMode == ContentEditor.EDITOR_MODE_NORMAL) {


      var actions = [new EditorAction.liElementWithInnerHtml("<h2>Overskift</h2>", () => executor.formatBlockH2(), (String s) => s == "h2"), new EditorAction.liElementWithInnerHtml("<h3>Underoverskrift</h3>", () => executor.formatBlockH3(), (String s) => s == "h3"), new EditorAction.liElementWithInnerHtml("<p>Normal tekst</p>", () => executor.formatBlockP(), (String s) => s == "p"), new EditorAction.liElementWithInnerHtml("<blockquote>Citat</blockquote>", () => executor.formatBlockBlockquote(), (String s) => s == "blockquote"), new EditorAction.liElementWithInnerHtml("<pre>Kode</pre>", () => executor.formatBlockPre(), (String s) => s == "pre")];

      var textType = new DropDown.fromLIList(actions.map((EditorAction a) => a.element).toList());


      actionsSetup(executor, actions, textType, () => executor.blockState);

      textType.element.classes.add('text_type');
      textType.text = "Normal tekst";
      menuHandler.addToMenu(textType.element);

      var sizeActions = [new EditorAction.liElementWithInnerHtml("<font size='1'>Lille</font>", () => executor.setFontSize(1), (int s) => s == 1), new EditorAction.liElementWithInnerHtml("Normal", () {
        executor.setFontSize(3);
        var fonts = element.querySelectorAll("font");
        fonts.forEach((Element e) => e.attributes['size'] == '3' ? (() {
          e.attributes.remove("size");
        })() : () {
        });
      }, (i) => ![1, 5, 7].contains(i)), new EditorAction.liElementWithInnerHtml("<font size='5'>Stor</font>", () => executor.setFontSize(5), (int s) => s == 5), new EditorAction.liElementWithInnerHtml("<font size='7'>Størst</font>", () => executor.setFontSize(7), (int s) => s == 7)];

      var textSize = new DropDown.fromLIList(sizeActions.map((EditorAction e) => e.element).toList());
      textSize.element.classes.add('text_size');

      actionsSetup(executor, sizeActions, textSize, () => executor.blockState == "p" ? executor.fontSize : -1);

      menuHandler.addToMenu(textSize.element);
      textSize.text = "Normal";


      var colorContent = new DivElement(), colorSelect = new DropDown(colorContent), textColorPalette = new ColorPalette(), backgroundColorPalette = new ColorPalette(), colorLabel1 = new DivElement(), colorLabel2 = new DivElement();

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
      menuHandler.addToMenu(colorSelect.element);

    }
    var dialog = new DialogContainer();

    dialog.dialogBg.onMouseDown.listen((MouseEvent evt) {
      evt.preventDefault();
//      evt.preventDefault();
    });

    var dialogLink = () {

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
    };

    var textIconMap = {
        "bold":{
            "title":"Fed skrift", "selChange":() => executor.bold, "func":() => executor.toggleBold()
        }, "italic":{
            "title":"Kursiv skrift", "selChange":() => executor.italic, "func":() => executor.toggleItalic()
        }, "underline":{
            "title":"Understreget skrift", "selChange":() => executor.underline, "func":() => executor.toggleUnderline()
        }, "u_list":{
            "title":"Uordnet liste", "selChange":() => executor.unorderedList, "func":() => executor.toggleUnorderedList()
        }, "o_list":{
            "title":"Ordnet liste", "selChange":() => executor.orderedList, "func":() => executor.toggleOrderedList()
        }, "a_left":{
            "title":"Juster venstre", "selChange":() => executor.alignLeft, "func":() => executor.justifyLeft()
        }, "a_center":{
            "title":"Juster centreret", "selChange":() => executor.alignCenter, "func":() => executor.justifyCenter()
        }, "a_right":{
            "title":"Juster højre", "selChange":() => executor.alignRight, "func":() => executor.justifyRight()
        }, "a_just":{
            "title":"Juster lige", "selChange":() => executor.alignJust, "func":() => executor.justifyFull()
        }, "p_indent":{
            "title":"Indryk mere", "selChange":null, "func":() => executor.indent()
        }, "m_indent":{
            "title":"Indryk mindre", "selChange":null, "func":() => executor.outdent()
        }, "superscript":{
            "title":"Hævet skrift", "selChange":() => executor.superScript, "func":() => executor.toggleSuperScript()
        }, "subscript":{
            "title":"Sænket skrift", "selChange":() => executor.subScript, "func":() => executor.toggleSubscript()
        }, "strikethrough":{
            "title":"Gennemstreget", "selChange":() => executor.strikeThrough, "func":() => executor.toggleStrikeThrough()
        }, "insert_link":{
            "title":"Indsæt link", "selChange":null, "func":dialogLink
        }, "no_format":{
            "title":"Fjern formatering", "selChange":null, "func":() {
              var selection = window.getSelection();
              var range = selection.getRangeAt(0);
              var commonAncestor = range.commonAncestorContainer;
              if (!(commonAncestor is Element)) {
                return;
              }
              commonAncestor.querySelectorAll("*").forEach((Element elm) {
                if (!selection.containsNode(elm, false)) {
                  return;
                }
                elm.attributes.remove("style");
              });
              executor.removeFormat();
            }
        }
    };

    textIconMap.forEach((String k, Map<String, dynamic> v) {
      var b = new ButtonElement();
      var i;
      b
        ..classes.add(k);
      _addTitleToElement(v['title'], b);
      var f = () {
      };
      if (v['selChange'] != null) {
        executor.listenQueryCommandStateChange(() => v['selChange']() ? b.classes.add('active') : b.classes.remove('active'));
        f = () => executor.triggerCommandStateChangeListener();
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
    i
      ..backgroundColor = InfoBox.COLOR_BLACK
      ..reversed = true;
    e
      ..onMouseOver.listen((_) => i.showBelowCenterOfElement(e))
      ..onMouseOut.listen((_) => i.remove())
      ..onClick.listen((_) => i.remove());
    onChange.listen((_) => i.remove());
    return i;
  }


//  String get id => element.id;

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
