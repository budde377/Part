part of core;

class Dialog {
  static final _confirm = new Dialog._internal();

  final List<Function> _pendingDialogs = new List<Function>();

  Function _currentCallback;

  final DivElement _dialogBg = new DivElement(), _alertDialog = new DivElement(), _confirmDialog = new DivElement(), _alertText = new DivElement(), _confirmText = new DivElement(), _container = new DivElement();

  final ButtonElement _okButton = new ButtonElement(), _confirmButton = new ButtonElement(), _cancelButton = new ButtonElement();

  bool _alert;

  factory Dialog(){
    return _confirm;
  }

  Dialog._internal(){
    _dialogBg.classes.add('dialogBg');
    _dialogBg.append(_container);
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

  void confirm(String confirmText, {void callbackTrue():null, void callBackFalse():null}) {
    _pendingDialogs.add(() {
      _emptyElement(_confirmText);
      _confirmText.appendHtml(confirmText);
      _alert = false;
      _currentCallback = (bool confirm) {
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
  }

  void alert(String alertText, {void callback():null}) {
    _pendingDialogs.add(() {
      _emptyElement(_alertText);
      _alertText.appendHtml(alertText);
      _alert = true;
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
      query('body').append(_dialogBg);
      _disableScroll();
      _pendingDialogs.first();
      _pendingDialogs.removeAt(0);
    }
  }
  void _reset(){
    _enableScroll();
    _dialogBg.remove();
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

}