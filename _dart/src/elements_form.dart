part of elements;

class FormHandler {
  final FormElement form;
  final SpanElement filter = new SpanElement();

  Function _submitFunction;


  static const String NOTION_TYPE_ERROR = "error";
  static const String NOTION_TYPE_INFORMATION = "info";
  static const String NOTION_TYPE_SUCCESS = "success";

  static final Map<FormElement, FormHandler> _cache = new Map<FormElement, FormHandler>();

  factory FormHandler(FormElement form){
    if (_cache.containsKey(form)) {
      return _cache[form];
    }
    var betterForm = new FormHandler._internal(form);
    _cache[form] = betterForm;
    return betterForm;


  }

  FormHandler._internal(FormElement form):this.form = form{
    filter.classes.add('filter');
  }

    set submitFunction(bool f(Map<String,String> data)){
    _submitFunction = f;
    form.onSubmit.listen((Event e){
      e.preventDefault();
      e.stopImmediatePropagation();
      var data = <String,String>{};
      form.queryAll('input:not([type=submit]), textarea, select').forEach((Element e){
        if(e is SelectElement){
          SelectElement ee  = e;
          data[ee.name] = ee.value;
        } else if (e is InputElement){
          InputElement ee = e;
          data[ee.name] = ee.value;
        } else if(e is TextAreaElement){
          TextAreaElement ee = e;
          data[ee.name] = ee.value;
        }
      });
      if(!f(data)){
        e.preventDefault();
        e.stopImmediatePropagation();
      }
    });
  }

  void setUpAJAXSubmit(String AJAXId, [void callbackSuccess(Map map), void callbackError()]){
    HttpRequest req = new HttpRequest();
    req.onReadyStateChange.listen((Event e) {
      if (req.readyState == 4) {
        unBlur();
        try {
          Map responseData = JSON.decode(req.responseText);
          callbackSuccess(responseData);

        } catch(e) {
          callbackError();
        }

      }});
    form.onSubmit.listen((Event event) {
      blur();
      List<Element> elements = queryAll("textarea, input:not([type=submit]), select");
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

  void removeNotion() {
    form.queryAll("span.notion").forEach((Element e) {e.remove();});

  }

  void blur() {
    form.classes.add("blur");
    form.insertAdjacentElement("afterBegin", filter);

  }

  void unBlur() {
    form.classes.remove("blur");
    filter.remove();
  }

}




class Validator<E extends Element> {
  final E _element;
  String errorMessage = "";
  static final Map<Element, Validator> _cache = new Map<Element, Validator>();
  Function _validator = (e){return true;};



  factory Validator(E element){
    if (_cache.containsKey(element)) {
      return _cache[element];
    } else {
      var elem = new Validator._internal(element);
      _cache[element] = elem;
      return elem;
    }

  }

  Validator._internal(this._element);

  Element get element => _element;

  bool get valid =>_validator(_element);

  void set validator(bool f(E element)) {
    _validator = f;
  }
}

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
    inputs.forEach((InputElement element) {
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
    inputs.forEach((InputElement element) {
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
        var box = new InfoBox(v.errorMessage);
        box..backgroundColor = InfoBox.COLOR_RED
        ..showAboveCenterOfElement(element);
        _infoBoxMap[element] = box;
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