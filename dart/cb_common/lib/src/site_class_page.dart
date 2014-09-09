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

 // bool get editable;

  Future<ChangeResponse<Page>> changeInfo({String id, String title, String template, String alias, bool hidden});

  Stream<Page> get onChange;

  Content operator [](String id);

}

class JSONPage extends Page {
  String _id, _title, _template, _alias;

  bool _hidden = false;


  Map<String, Content> _content = new Map<String, Content>();

  String get id => _id;

  String get title => _title;

  String get template => _template;

  String get alias => _alias;

  bool get hidden => _hidden;


  StreamController<Page> _changeController = new StreamController<Page>();

  Stream<Page> _changeStream;

  JSONPage(String id, String title, String template, String alias, bool hidden) {
    _id = id;
    _title = title;
    _template = template;
    _alias = alias;
    _hidden = hidden;
  }

  Future<ChangeResponse<Page>> changeInfo({String id:null, String title:null, String template:null, String alias:null, bool hidden:null}) {
    id = id != null ? id : _id;
    title = title != null ? title : _title;
    template = template != null ? template : _template;
    alias = alias != null ? alias : _alias;
    var hideFunction = "";
    if(hidden && !_hidden){
      hideFunction = "..hide()";
    } else if (!hidden && _hidden){
      hideFunction = "..show()";
    }
    hidden = hidden != null ? hidden : _hidden;
    var completer = new Completer<ChangeResponse<Page>>();
    var functionCallback = (JSONResponse response) {
      if (response.type == Response.RESPONSE_TYPE_SUCCESS) {
        JSONObject payload = response.payload;
        if(payload is JSONObject){
          _id = payload.variables['id'];
          _template = payload.variables['template'];
          _title = payload.variables['title'];
          _alias = payload.variables['alias'];
          _hidden = payload.variables['hidden'];
          _callListeners();
        }
        completer.complete(new ChangeResponse<Page>.success(this));
      } else {

        completer.complete(new ChangeResponse<Page>.error(response.error_code));
      }
    };
    ajaxClient.callFunctionString("PageOrder.getPage(${quoteString(id)})..setId(${quoteString(id)})..setTitle(${quoteString(title)})..setTemplate(${quoteString(template)})..setAlias(${quoteString(alias)})$hideFunction..getInstance()").then(functionCallback);
    return completer.future;
  }

  void _callListeners() {
    _changeController.add(this);

  }


  Content operator [](String id) => _content.putIfAbsent(id, () => new JSONContent.page(this, id));

  Stream<Page> get onChange => _changeStream == null ? _changeStream = _changeController.stream.asBroadcastStream() : _changeStream;

}
