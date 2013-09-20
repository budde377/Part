part of core;

class FormDecoration {
  final FormElement form;
  final SpanElement filter = new SpanElement();

  Function _submitFunction;


  static const String NOTION_TYPE_ERROR = "error";
  static const String NOTION_TYPE_INFORMATION = "info";
  static const String NOTION_TYPE_SUCCESS = "success";

  static final Map<FormElement, FormDecoration> _cache = new Map<FormElement, FormDecoration>();

  factory FormDecoration(FormElement form){
    if (_cache.containsKey(form)) {
      return _cache[form];
    }
    var betterForm = new FormDecoration._internal(form);
    _cache[form] = betterForm;
    return betterForm;


  }

  FormDecoration._internal(FormElement form):this.form = form{
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
          Map responseData = parse(req.responseText);
          callbackSuccess(responseData);

        } catch(e) {
          callbackError();
        }

      }});
    form.onSubmit.listen((Event event) {
      blur();
      List<Element> elements = queryAll("textarea, input:not([type=submit]), select");
      req.open(form.method.toUpperCase(), "?ajax=${Uri.encodeUriComponent(AJAXId)}");
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
