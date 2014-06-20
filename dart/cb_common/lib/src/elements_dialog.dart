part of elements;

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

class LoadingDialog extends DialogBox{

  LoadingDialog(String loadingText) :super(new DivElement()){
    element.classes..add('dialog')..add('loading');
    element.innerHtml = loadingText;
  }

  void open(){
    core.escQueue.enabled = false;
  }

  void close(){
    core.escQueue.enabled = true;
    super.close();

  }

  void stopLoading(){
    element.classes.remove("loading");
  }

  void startLoading(){
    element.classes.add("loading");
  }



}


class DialogContainer {
  static final _cache = new DialogContainer._internal();

  List<DialogBox> _pendingDialogs = new List<DialogBox>();

  DivElement dialogBg = new DivElement(), _container = new DivElement(), _cell = new DivElement();

  DialogBox _currentDialog;

  factory DialogContainer() => _cache;

  DialogContainer._internal(){
    _cell.classes.add('cell');
    dialogBg.classes..add('full_background')..add('dialog_bg');
    dialogBg.append(_container);
    _container.append(_cell);
    _container.classes.add('container');


  }

  DialogBox dialog(Element element){
    var dialog = new DialogBox(element);
    addDialogBox(dialog);
    return dialog;
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

  LoadingDialog loading(String text){
    var dialog = new LoadingDialog(text);
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
    querySelector('body').append(dialogBg);
    enableNoScrollBody();

  }

  void _appendDialog(DialogBox dialog) {
    _cell.append(dialog.element);
    dialog.open();
    _currentDialog = dialog;
    core.escQueue.add(() {
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
      disableNoScrollBody();

    }

  }


}

DialogContainer get dialogContainer => new DialogContainer();


void enableNoScrollBody(){
  var body = querySelector('body');
  if(window.innerHeight >= body.scrollHeight){
    return;
  }
  body.style.top = "${-window.scrollY}px";
  body.classes.add('no_scroll');
}


void disableNoScrollBody(){
  var body = querySelector('body');
  body.classes.remove('no_scroll');
  var y = core.parseNumber(body.style.top);
  body.style.removeProperty('top');
  window.scrollTo(window.scrollX,y);


}

