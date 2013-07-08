part of site_classes;

typedef void PageInfoChangeListener(Page page);

abstract class Page {

  String get id;

  String get title;

  String get template;

  String get alias;

  bool get hidden;

  void changeInfo({String id, String title, String template, String alias, bool hidden, ChangeCallback callback});

  void registerListener(void f(Page page));

}

class JSONPage extends Page {
  String _id, _title, _template, _alias;

  bool _hidden = false;

  final List<PageInfoChangeListener> _listeners = [];

  final JSONClient _client;

  String get id => _id;

  String get title => _title;

  String get template => _template;

  String get alias => _alias;

  bool get hidden => _hidden;

  JSONPage(String id, String title, String template, String alias, bool hidden, JSONClient client):_client = client {
    _id = id;
    _title = title;
    _template = template;
    _alias = alias;
    _hidden = hidden;
  }

  void changeInfo({String id:null, String title:null, String template:null, String alias:null, bool hidden:null, ChangeCallback callback:null}) {
    id = id != null ? id : _id;
    title = title != null? title : _title;
    template = template != null ? template : _template;
    alias = alias != null? alias : _alias;
    hidden = hidden != null? hidden : _hidden;
    callback = callback != null? callback : (a1, [a2]) {
    };

    var function = new ChangePageInfoJSONFunction(_id, id, title, template, alias, hidden);
    var functionCallback = (JSONResponse response) {
      switch (response.type) {
        case RESPONSE_TYPE_SUCCESS:
          _id = id;
          _template = template;
          _title = title;
          _alias = alias;
          _hidden = hidden;
          _callListeners();
          callback(response.type);
          break;
        default:
          callback(response.type, response.error_code);
      }
    };
    _client.callFunction(function, functionCallback);
  }

  void _callListeners() {
    _listeners.forEach((void f(Page page)) {
      f(this);
    });
  }

  void registerListener(void f(Page page)) {
    _listeners.add(f);
  }

}
