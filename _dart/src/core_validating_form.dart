part of core;

typedef bool validator();

class ValidatingForm {
  final FormElement _element;

  static final Map<FormElement, ValidatingForm> _cache = new Map<FormElement, ValidatingForm>();

  bool _validForm = true;

  factory ValidatingForm(FormElement form) {
    if (_cache.containsKey(form)) {
      return _cache[form];
    } else {
      var f = new ValidatingForm._internal(form);
      _cache[form] = f;
      f._setUp();
      return f;
    }
  }

  ValidatingForm._internal(this._element);

  final Map<Element, String> _valueMap = new Map<Element, String>();

  final Map<Element, InfoBox> _infoBoxMap = new Map<Element, InfoBox>();

  void _setUp() {
    _element.onSubmit.listen((Event e) {
      if (_element.classes.contains('invalid')) {
        e.preventDefault();
        e.stopImmediatePropagation();
      }
    }); //There used to be a second parameter, properly for execution on the way back or something. That is missing

    var listener = (Element element,bool h)=>(Event e){
      if(_infoBoxMap.containsKey(element)){
        _infoBoxMap[element].element.hidden=h;
      }
    };


    var inputs = _element.queryAll('input:not([type=submit]), textarea');
    inputs.forEach((Element element) {
      element.onBlur.listen(listener(element,true));
      element.onFocus.listen(listener(element,false));
      element.classes.add('valid');
      _valueMap[element] = element.value;
      element.onKeyUp.listen((Event e) {
        if (_valueMap[element] == element.value) {
          return;
        }
        _checkElement(element);
        _valueMap[element] = element.value;
      });
    });
    var selects = _element.queryAll('select');
    selects.forEach((Element element) {
      element.onBlur.listen(listener(element,true));
      element.onFocus.listen(listener(element,false));
      element.classes.add('valid');
      element.onChange.listen((Event e) => _checkElement(element));
    });

    _element.classes.add('initial');
  }

  void validate([bool initial = true]) {
    var inputs = _element.queryAll('input:not([type=submit]), textarea');
    inputs.forEach((Element element) {
      if (_valueMap[element] != element.value) {
        _checkElement(element);
      }
      _valueMap[element] = element.value;
    });
    var selects = _element.queryAll('select');
    selects.forEach((Element element) {
      _checkElement(element);
    });
    _checkElement(_element);
    if (initial) {
      _infoBoxMap.forEach((Element e, InfoBox i){
        i.remove();
        e.classes..remove('invalid')
                 ..add('valid');
      });
      _infoBoxMap.clear();
      _element.classes.add('initial');
    }
  }

  void _checkElement(Element element) {
    _element.classes.remove('initial');
    var v = new Validator(element);
    if (v.valid && element.classes.contains('invalid')) {
      element.classes.add('valid');
      element.classes.remove('invalid');
      if(_infoBoxMap.containsKey(element)){
        _infoBoxMap[element].remove();
        _infoBoxMap.remove(element);
      }
      if (!_validForm && _element.query('input:not([type=submit]).invalid, textarea.invalid, select.invalid') == null) {
        _validForm = true;
        _changeToValid();
      }
    } else if (!v.valid && element.classes.contains('valid')) {
      element.classes.remove('valid');
      element.classes.add('invalid');
      if (v.errorMessage.length > 0) {
        (_infoBoxMap[element] = addInfoBoxToElement(element, v.errorMessage)).backgroundColor = InfoBox.COLOR_RED;
      }
      if (_validForm) {
        _validForm = false;
        _changeToInvalid();
      }
    }
  }

  void _changeToValid() {
    _element.classes.add('valid');
    _element.classes.remove('invalid');
  }

  void _changeToInvalid() {
    _element.classes.add('invalid');
    _element.classes.remove('valid');

  }

}