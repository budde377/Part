part of core;

typedef bool SubmitFunction(Map<String,String> data);

class FormDecoration {
  final FormElement form;
  final SpanElement filter = new SpanElement();

  SubmitFunction _submitFunction;


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


  void set submitFunction(SubmitFunction f){
    _submitFunction = f;
    form.onSubmit.listen((Event e){
      var data = <String,String>{};
      var input = form.queryAll('input:not([type=submit]), textarea, select').forEach((SelectElement e){
        data[e.name] = e.value;
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

  void setNotion(String message, String notion_type) {
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
