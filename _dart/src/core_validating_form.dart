part of core;

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

    _element.on.submit.add((Event e) {
      if (_element.classes.contains('invalid')) {
        e.preventDefault();
      }
    }, true);

    var inputs = _element.queryAll('input:not([type=submit]), textarea');
    inputs.forEach((Element element) {
      element.classes.add('valid');
      element.on.keyUp.add((Event e) => _checkElement(element));
      _checkElement(element);
    });
    var selects = _element.queryAll('select');
    selects.forEach((Element element) {
      element.classes.add('valid');
      element.on.change.add((Event e) => _checkElement(element));
      _checkElement(element);
    });
    _element.classes.add('initial');
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