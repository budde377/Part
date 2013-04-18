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

  void _setUp() {
    _element.onSubmit.listen((Event e) {
      if (_element.classes.contains('invalid')) {
        e.preventDefault();
        e.stopImmediatePropagation();
      }
    }); //There used to be a sencond parameter, properly for execution on the way back or something. That is missing

    var inputs = _element.queryAll('input:not([type=submit]), textarea');
    inputs.forEach((Element element) {
      element.classes.add('valid');
      var val = element.value;
      element.onKeyUp.listen((Event e) {
        if(val == element.value){
          return;
        }
        _checkElement(element);
        val=element.value;
      });
    });
    var selects = _element.queryAll('select');
    selects.forEach((Element element) {
      element.classes.add('valid');
      element.onChange.listen((Event e) => _checkElement(element));
    });

    _element.classes.add('initial');
  }

  void validate([bool initial = true]){
    var inputs = _element.queryAll('input:not([type=submit]), textarea');
    inputs.forEach((Element element) {
      _checkElement(element);
    });
    var selects = _element.queryAll('select');
    selects.forEach((Element element) {
      _checkElement(element);
    });
    _checkElement(_element);
    if(initial){
      _element.classes.add('initial');
    }
  }

  void _checkElement(Element element) {
    _element.classes.remove('initial');
    var v = new Validator(element).valid;
    if (v && element.classes.contains('invalid')) {
      element.classes.add('valid');
      element.classes.remove('invalid');
      if (!_validForm && _element.query('input:not([type=submit]).invalid, textarea.invalid, select.invalid') == null) {
        _validForm = true;
        _changeToValid();
      }
    } else if (!v && element.classes.contains('valid')) {
      element.classes.remove('valid');
      element.classes.add('invalid');
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