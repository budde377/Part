part of site_classes;

typedef void PageInfoChangeListener(Page page);

abstract class Page {

  String get id;

  String get title;

  String get template;

  String get alias;

  void changeInfo({String id, String title, String template, String alias, ChangeCallback callback});

  void registerListener(PageInfoChangeListener listener);

}

class JSONPage extends Page {
  String _id, _title, _template, _alias;
  final List<PageInfoChangeListener> _listeners = [];
  final JSONClient _client;

  String get id => _id;

  String get title => _title;

  String get template => _template;

  String get alias => _alias;

  JSONPage(String id, String title, String template, String alias, JSONClient client):_client = client {
    _id = id;
    _title = title;
    _template = template;
    _alias = alias;
  }

  void changeInfo({String id, String title, String template, String alias, ChangeCallback callback}) {
    id = ?id ? id : _id;
    title = ?title ? title : _title;
    template = ?template ? template : _template;
    alias = ?alias ? alias : _alias;
    callback = ?callback?callback:(a1,[a2]){};
    var function = new ChangePageInfoJSONFunction(_id,id, title, template, alias);
    var functionCallback = (JSONResponse response) {
      switch (response.type) {
        case RESPONSE_TYPE_SUCCESS:
          _id = id;
          _template = template;
          _title = title;
          _alias = alias;
          callListeners();
          callback(response.type);
          break;
        default:
          callback(response.type,response.error_code);
      }};
    _client.callFunction(function, functionCallback);
  }

  void callListeners() {
    _listeners.forEach((PageInfoChangeListener listener) {
      listener(this);
    });
  }

  void registerListener(PageInfoChangeListener listener) {
    _listeners.add(listener);
  }

}
