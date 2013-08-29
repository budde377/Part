part of core;

class DialogBox {
  final Element element;

  StreamController<DialogBox> _controller = new StreamController<DialogBox>();

  Stream<DialogBox> _stream;

  DialogBox(this.element);

  close() {
    element.remove();
    _controller.add(this);
  }

  Stream<DialogBox> get onClose => _stream == null ? _stream = _controller.stream.asBroadcastStream() : _stream;

  void open() {
  }

}

class AlertDialogBox extends DialogBox {
  DivElement _alertText = new DivElement();

  ButtonElement _okButton = new ButtonElement();

  AlertDialogBox(String alertText) : super(new DivElement()) {
    element.classes..add('dialog')..add('alert');
    _okButton..onClick.listen((_) {
      _completer.complete(true);
      close();
    })..text = "OK";
    _alertText.innerHtml = alertText;
    element..append(_alertText)..append(_okButton);

  }

}


class ConfirmDialogBox extends DialogBox {
  DivElement _confirmText = new DivElement();

  ButtonElement _confirmButton = new ButtonElement(), _cancelButton = new ButtonElement();

  Completer<bool> _completer = new Completer<bool>();

  ConfirmDialogBox(String confirmText) : super(new DivElement()) {
    element.classes..add('dialog')..add('confirm');
    _confirmButton..onClick.listen((_) => _completer.complete(true))..text = "Ja";
    _cancelButton..onClick.listen((_) => _completer.complete(false))..text = "Nej";
    _confirmText.innerHtml = confirmText;
    element..append(_confirmText)..append(_confirmButton)..append(_cancelButton);
    result.then((_) => close());
  }


  Future<bool> get result => _completer.future;

}

class TextInputDialogBox extends DialogBox {
  DivElement _text = new DivElement();

  ButtonElement _doneButton = new ButtonElement();

  InputElement _textInput = new InputElement();

  Completer<String> _completer = new Completer<String>();

  TextInputDialogBox(String message, {String value:""}) : super(new DivElement()) {
    element.classes..add('dialog')..add('text');
    _textInput..type = "text"..value = value;
    _doneButton..onClick.listen((_) {
      _completer.complete(_textInput.value);
      close();
    })..text = "Udfør";
    _text.innerHtml = message;
    element..append(_text)..append(_textInput)..append(_doneButton);
    _textInput.onKeyDown.listen((KeyboardEvent kev) {
      if (kev.keyCode != 13) {
        return;
      }
      _doneButton.focus();

    });
  }

  void open() {
    new Timer(Duration.ZERO, () {
      _textInput.focus();
    });
  }

  Future<String> get result => _completer.future;

}

class DialogContainer {
  static final _cache = new DialogContainer._internal();

  List<DialogBox> _pendingDialogs = new List<DialogBox>();

  DivElement dialogBg = new DivElement(), _container = new DivElement();

  DialogBox _currentDialog;

  factory DialogContainer() => _cache;

  DialogContainer._internal(){
    dialogBg.classes.add('dialog_bg');
    dialogBg.append(_container);
    _container.classes.add('dialog_container');


  }

  ConfirmDialogBox confirm(String text) {
    var dialog = new ConfirmDialogBox(text);
    addDialogBox(dialog);
    return dialog;
  }

  AlertDialogBox alert(String text) {
    var dialog = new AlertDialogBox(text);
    addDialogBox(dialog);
    return dialog;
  }


  TextInputDialogBox text(String message, {String value:""}) {
    var dialog = new TextInputDialogBox(message, value:value);
    addDialogBox(dialog);
    return dialog;
  }

  void addDialogBox(DialogBox dialog) {
    dialog.onClose.listen(_closeListener);
    if (dialogBg.parent != null) {
      _pendingDialogs.add(dialog);
      return;
    }
    _appendDialog(dialog);
    document.query('body').append(dialogBg);

  }

  void _appendDialog(DialogBox dialog) {
    _container.append(dialog.element);
    dialog.open();
    _currentDialog = dialog;
    escQueue.add(() {
      if (dialog != _currentDialog) {
        return false;
      }
      dialog.close();
      return true;
    });

  }

  void _closeListener(DialogBox dialog) {
    if (_pendingDialogs.length > 0) {
      _appendDialog(_pendingDialogs.removeAt(0));
    } else {
      _currentDialog = null;
      dialogBg.remove();

    }

  }


}

/*

class Dialog {
  static final _confirm = new Dialog._internal();

  final List<Function> _pendingDialogs = new List<Function>();

  Function _currentCallback;

  final DivElement dialogBg = new DivElement(), _alertDialog = new DivElement(), _confirmDialog = new DivElement(), _alertText = new DivElement(), _confirmText = new DivElement(), _container = new DivElement();

  final ButtonElement _okButton = new ButtonElement(), _confirmButton = new ButtonElement(), _cancelButton = new ButtonElement();

  factory Dialog(){
    return _confirm;
  }

  Dialog._internal(){
    dialogBg.classes.add('dialogBg');
    dialogBg.append(_container);
    _container.classes.add('dialogContainer');
    _alertDialog.classes..add('dialog')..add('alert');
    _confirmDialog.classes..add('dialog')..add('confirm');
    _alertDialog..append(_alertText)..append(_okButton);
    _confirmDialog..append(_confirmText)..append(_confirmButton)..append(_cancelButton);
    _okButton..onClick.listen((Event e) => _currentCallback())
             ..text = "OK";
    _confirmButton..onClick.listen((Event e) => _currentCallback(true))
                  ..text = "Ja";
    _cancelButton..onClick.listen((Event e) => _currentCallback(false))
                 ..text = "Nej";

  }



  Future<bool> confirm(String confirmText, {void callbackTrue():null, void callBackFalse():null}) {
    var completer = new Completer<bool>();
    _pendingDialogs.add(() {
      _emptyElement(_confirmText);
      _confirmText.appendHtml(confirmText);
      _currentCallback = (bool confirm) {
        completer.complete(confirm);
        if(confirm && callbackTrue != null){
          callbackTrue();
        } else if (!confirm && callBackFalse != null){
          callBackFalse();
        }
        _reset();
      };
      _alertDialog.remove();
      _container.append(_confirmDialog);

    });
    _notifyDialogChange();
    return completer.future;
  }

  void alert(String alertText, {void callback():null}) {
    _pendingDialogs.add(() {
      _emptyElement(_alertText);
      _alertText.appendHtml(alertText);
      _currentCallback = () {
        if(callback != null){
          callback();
        }
        _reset();
      };
      _confirmDialog.remove();
      _container.append(_alertDialog);
    });
    _notifyDialogChange();
  }



  void _notifyDialogChange() {
    if (_pendingDialogs.length > 0 && _currentCallback == null) {
      query('body').append(dialogBg);
      _disableScroll();
      _pendingDialogs.first();
      _pendingDialogs.removeAt(0);
    }
  }
  void _reset(){
    _enableScroll();
    dialogBg.remove();
    _currentCallback = null;
    _notifyDialogChange();

  }

  void _disableScroll(){

  }

  void _enableScroll(){
  }

  void _emptyElement(Element e){
    e.children.clear();
    e.text = "";
  }

}*/