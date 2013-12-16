part of site_classes;

class Revision {
  final DateTime time;

  final String content;

  Revision(this.time, this.content);
}

abstract class Page {

  String get id;

  String get title;

  String get template;

  String get alias;

  bool get hidden;

  void changeInfo({String id, String title, String template, String alias, bool hidden, ChangeCallback callback});

  Stream<Page> get onChange;

  Content operator [](String id);

}

class JSONPage extends Page {
  String _id, _title, _template, _alias;

  bool _hidden = false;

  final JSONClient _client;

  Map<String, Content> _content = new Map<String, Content>();

  String get id => _id;

  String get title => _title;

  String get template => _template;

  String get alias => _alias;

  bool get hidden => _hidden;

  StreamController<Page> _changeController = new StreamController<Page>();

  Stream<Page> _changeStream;

  JSONPage(String id, String title, String template, String alias, bool hidden, JSONClient client):_client = client {
    _id = id;
    _title = title;
    _template = template;
    _alias = alias;
    _hidden = hidden;
  }

  void changeInfo({String id:null, String title:null, String template:null, String alias:null, bool hidden:null, ChangeCallback callback:null}) {
    id = id != null ? id : _id;
    title = title != null ? title : _title;
    template = template != null ? template : _template;
    alias = alias != null ? alias : _alias;
    hidden = hidden != null ? hidden : _hidden;
    callback = callback != null ? callback : (a1, [a2, a3]) {
    };

    var function = new ChangePageInfoJSONFunction(_id, id, title, template, alias, hidden);
    var functionCallback = (JSONResponse response) {
      switch (response.type) {
        case JSONResponse.RESPONSE_TYPE_SUCCESS:
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
    _client.callFunction(function).then(functionCallback);
  }

  void _callListeners() {
    _changeController.add(this);

  }


  Content operator [](String id) => _content.putIfAbsent(id, () => new JSONContent.page(this, id, _client));

  Stream<Page> get onChange => _changeStream == null?_changeStream = _changeController.stream.asBroadcastStream():_changeStream;

}
