part of elements;

class FormHandler {
  final FormElement form;

  Function _submitFunction;


  static const String NOTION_TYPE_ERROR = "error";
  static const String NOTION_TYPE_INFORMATION = "info";
  static const String NOTION_TYPE_SUCCESS = "success";

  static final Map<FormElement, FormHandler> _cache = new Map<FormElement, FormHandler>();

  factory FormHandler(FormElement form) => _cache.putIfAbsent(form, () => new FormHandler._internal(form));

  Map<Element, Function > _clearFunctions = new Map<Element, Function>();

  FormHandler._internal(FormElement form):this.form = form;

  set submitFunction(bool f(Map<String, String> data)) {
    _submitFunction = f;
    form.onSubmit.listen((Event e) {
      e.preventDefault();
      e.stopImmediatePropagation();
      var data = <String, String>{
      };
      form.querySelectorAll('input:not([type=submit]), textarea, select').forEach((e) {
        if (e.name == "") {
          return;
        }
        if (e is SelectElement) {
          SelectElement ee = e;
          data[ee.name] = ee.value;
        } else if (e is InputElement) {
          InputElement ee = e;
          if (ee.type == "radio" || ee.type == "checkbox") {
            if (e.checked) {
              data[ee.name] = ee.value;
            }
          } else {
            data[ee.name] = ee.value;
          }
        } else if (e is TextAreaElement) {
          TextAreaElement ee = e;
          data[ee.name] = ee.value;
        }
      });
      if (!f(data)) {
        e.preventDefault();
        e.stopImmediatePropagation();
      }
    });
  }

  void setUpAJAXSubmit(String AJAXId, [void callbackSuccess(Map map), void callbackError()]) {
    HttpRequest req = new HttpRequest();
    req.onReadyStateChange.listen((Event e) {
      if (req.readyState == 4) {
        unBlur();
        try {
          Map responseData = JSON.decode(req.responseText);
          callbackSuccess(responseData);

        } catch (e) {
          callbackError();
        }

      }
    });
    form.onSubmit.listen((Event event) {
      blur();
      List<Element> elements = form.querySelectorAll("textarea, input:not([type=submit]), select");
      req.open(form.method.toUpperCase(), "?ajax=${Uri.encodeComponent(AJAXId)}");
      req.send(new FormData(form));
      event.preventDefault();
    });
  }

  void changeNotion(String message, String notion_type) {

    if (notion_type != NOTION_TYPE_SUCCESS && notion_type != NOTION_TYPE_ERROR && notion_type != NOTION_TYPE_INFORMATION) {
      return;
    }

    removeNotion();
    SpanElement notion = new SpanElement();
    notion.classes.add(notion_type);
    notion.classes.add("notion");
    notion.text = message;
    form.insertAdjacentElement("afterBegin", notion);

  }

  void clearForm() {
    List<Element> elements = form.querySelectorAll("textarea, input:not([type=submit]), select").toList();
    elements.removeWhere((Element e) => _clearFunctions.containsKey(e));

    _clearFunctions.forEach((Element elm, void c(Element e)) {
      c(elm);
    });

    elements.forEach((Element elm) {
      if (elm is InputElement) {
        InputElement elm2 = elm;
        if (elm2.type == "checkbox" || elm2.type == "radio") {
          elm2.checked = false;
        } else {
          elm2.value = "";
        }
      } else if (elm is TextAreaElement) {
        TextAreaElement elm2 = elm;
        elm2.value = "";
      } else if (elm is SelectElement) {
        SelectElement elm2 = elm;
        if (elm2.options.length == 0) {
          return;
        }
        if (BetterSelect.isHandling(elm2)) {
          new BetterSelect(elm2).value = elm2.options[0].value;
        } else {
          elm2.value = elm2.options[0].value;
        }


      }
    });
  }


  void overrideClear(Element e, void c(Element e)) {
    _clearFunctions[e] = c;
  }

  void removeNotion() {
    form.querySelectorAll("span.notion").forEach((Element e) {
      e.remove();
    });

  }

  void blur() {
    form.classes.add("blur");
    //form.insertAdjacentElement("afterBegin", filter);

  }

  void unBlur() {
    form.classes.remove("blur");
    //filter.remove();
  }

}


class Validator<E extends Element> {

  final E element;
  String errorMessage = "";

  bool _initial = true;

  final FormElement form;

  ValidatingForm _validatingForm;

  static final Map<Element, Validator> _cache = new Map<Element, Validator>();
  Function _validator = (_) {
    return true;
  };

  Function _dependency = () {
  };

  factory Validator(E element) => _cache.putIfAbsent(element, () => new Validator._internal(element));

  Validator._internal(E element) : this.form = (element is InputElement || element is TextAreaElement || element is SelectElement) ? element.form : null, this.element = element {
    _validatingForm = hasForm ? new ValidatingForm(form) : null;
    if (element.dataset.containsKey("error-message")) {
      errorMessage = element.dataset["error-message"];
    }

    if (!element.dataset.containsKey("validator-method") || !(element is InputElement || element is SelectElement || element is TextAreaElement)) {
      _initial = false;
      return;
    }
    switch (element.dataset["validator-method"]) {
      case "pattern":
        if (!element.dataset.containsKey("pattern")) {
          break;
        }
        addValidRegExpPatternValueValidator(new RegExp(element.dataset["pattern"]));
        break;
      case "mail":
        addValidMailValueValidator();
        break;
      case "url":
        addValidUrlValueValidator();
        break;
      case "non-empty":
        addNonEmptyValueValidator();
        break;
    }
    _initial = false;
  }

  ValidatingForm get validatingForm => _validatingForm;

  bool get hasForm => form != null;


  bool get valid {
    _dependency();
    return _validator(element);
  }


  void addValidator(bool f(Element E)) {
    var v = _validator;
    _validator = (Element e) => v(e) && f(e);
    if (_initial) {
      return;
    }
    check(true);
  }

  void addValueValidator(bool f(String)) {
    addValidator((elm) => f(elm.value));
  }

  void addNonEmptyValueValidator() {
    addValueValidator(core.nonEmpty);
  }

  void addValidUrlValueValidator() {
    addValueValidator(core.validUrl);
  }

  void addValidMailValueValidator() {
    addValueValidator(core.validMail);
  }

  void addValidRegExpPatternValueValidator(RegExp pattern) {
    addValueValidator(pattern.hasMatch);
  }

  void check([bool ignore_initial=false]) {
    if (!hasForm) {
      return;
    }
    validatingForm.checkElement(element, ignore_initial);
  }

  void _addDependency(void f()) {
    var d = _dependency;
    _dependency = () {
      d();
      f();
    };
  }

  void dependOn(Validator v) {
    v._addDependency(() => check(true));
  }

}


class ValidatingForm {

  final FormElement element;

  static final Map<FormElement, ValidatingForm> _cache = new Map<FormElement, ValidatingForm>();

  bool _validForm = true;

  factory ValidatingForm(FormElement form, [bool initial = true]) {
    if (_cache.containsKey(form)) {
      return _cache[form];
    } else {
      var f = new ValidatingForm._internal(form);
      _cache[form] = f;
      f._setUp(initial);
      return f;
    }
  }

  ValidatingForm._internal(this.element);

  final Map<Element, String> _valueMap = new Map<Element, String>();

  final Map<Element, InfoBox> _infoBoxMap = new Map<Element, InfoBox>();

  String _valueFromInput(InputElement i) {
    if (i.type == "checkbox" || i.type == "radio") {
      return i.checked ? i.value : "";
    }

    return i.value;

  }

  void _listener(Element elm, bool h) {
    var v = new Validator(elm);
    if (v.errorMessage.length == 0) {
      return;
    }
    var box = _infoBoxMap[elm];
    if (box == null) {
      box = new InfoBox(v.errorMessage);
      box.backgroundColor = InfoBox.COLOR_RED;
      _infoBoxMap[elm] = box;
    }

    if (h == !box.visible) {
      return;
    }

    if (h) {
      box.remove();
    } else if (!v.valid && !elm.classes.contains('initial')) {
      box.showAboveCenterOfElement(elm);
    }

  }

  void _setUp(bool initial) {
    element.onSubmit.listen((Event e) {
      if (!_validForm) {
        e.preventDefault();
        e.stopImmediatePropagation();
      }
    });


    var inputs = element.querySelectorAll('input:not([type=submit]), textarea');
    inputs.forEach((InputElement elm) {
      var v = new Validator(elm);
      elm.onBlur.listen((_) => _listener(elm, true));
      elm.onFocus.listen((_) => _listener(elm, false));
      elm.classes
        ..add(v.valid ? 'valid' : 'invalid')
        ..add('initial');
      _valueMap[elm] = _valueFromInput(elm);
      var l = (_) {
        if (_valueMap[elm] == _valueFromInput(elm)) {
          return;
        }
        checkElement(elm);
        _listener(elm, false);
        _valueMap[elm] = _valueFromInput(elm);
      };
      elm.onChange
        ..listen(l);
      elm.onKeyUp
        ..listen(l);
    });
    var selects = element.querySelectorAll('select');
    selects.forEach((Element elm) {
      var l = (_) {
        checkElement(elm);
        _listener(elm, false);
      };

      var v = new Validator(elm);
      elm
        ..onBlur.listen((_) => _listener(elm, true))
        ..onFocus.listen((_) => _listener(elm, false));
      elm.classes
        ..add(v.valid ? 'valid' : 'invalid')
        ..add('initial');
      elm
        ..onChange.listen(l)
        ..on['update'].listen(l);
    });

    if (initial) {
      validate();
    }
    _updateFormValidStatus();
  }

  bool validate([bool initial = true]) {
    var inputs = element.querySelectorAll('input:not([type=submit]), textarea, select');
    inputs.forEach((InputElement elm) {
      checkElement(elm, initial);
      _valueMap[elm] = _valueFromInput(elm);
      if (initial) {
        elm.classes.add('initial');
      }
    });
    if (initial) {
      _infoBoxMap.clear();
      element.classes.add('initial');
    }
    return valid;
  }

  void checkElement(Element elm, [bool ignore_initial = false]) {
    if (!ignore_initial) {
      elm.classes.remove('initial');
      element.classes.remove('initial');
    }
    var v = new Validator(elm);
    if (v.valid && elm.classes.contains('invalid')) {
      elm.classes.add('valid');
      elm.classes.remove('invalid');
      _listener(elm, true);
      _updateFormValidStatus();
    } else if (!v.valid && elm.classes.contains('valid')) {
      elm.classes.remove('valid');
      elm.classes.add('invalid');
      _listener(elm, false);
      _updateFormValidStatus();
    }


  }

  void hideInfoBoxes() {
    _infoBoxMap.forEach((_, InfoBox b) {
      b.remove();
    });
  }

  void _updateFormValidStatus() {
    var q = element.querySelector('input:not([type=submit]).invalid, textarea.invalid, select.invalid') == null;
    if (!_validForm && q) {
      _validForm = true;
      _changeToValid();
    } else if (_validForm && !q) {
      _validForm = false;
      _changeToInvalid();
    }
  }

  void _changeToValid() {
    element.classes.add('valid');
    element.classes.remove('invalid');
  }

  void _changeToInvalid() {
    element.classes.add('invalid');
    element.classes.remove('valid');
  }


  FormHandler get formHandler => new FormHandler(element);

  bool get valid => _validForm;

}