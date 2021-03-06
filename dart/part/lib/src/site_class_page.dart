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

  FutureResponse<Page> changeInfo({String id, String title, String template, String alias, bool hidden});

  Stream<Page> get onChange;

  Content operator [](String id);

  FutureResponse<Page> delete();

  Page get next;

  Page get previous;

  Page get parent;

  List<Page> get path;

  bool get active;

  bool get exists;


}

class AJAXPage extends Page {
  final PageOrder page_order;

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

  AJAXPage(this.page_order, String id, String title, String template, String alias, bool hidden) {
    _id = id;
    _title = title;
    _template = template;
    _alias = alias;
    _hidden = hidden;
  }

  FutureResponse<Page> changeInfo({String id:null, String title:null, String template:null, String alias:null, bool hidden:null}) {
    var functionString = "";
    functionString += id == null || id == _id ? "" : "..setID(${quoteString(id)})";
    functionString += title == null || title == _title ? "" : "..setTitle(${quoteString(title)})";
    functionString += template == null || template == _template ? "" : "..setTemplate(${quoteString(template)})";
    functionString += alias == null || alias == _alias ? "" : "..setAlias(${quoteString(alias)})";

    if (hidden && !_hidden) {
      functionString += "..hide()";
    } else if (!hidden && _hidden) {
      functionString += "..show()";
    }
    var completer = new Completer<Response<Page>>();
    var functionCallback = (JSONResponse response) {
      if (response.type == Response.RESPONSE_TYPE_SUCCESS) {
        JSONObject payload = response.payload;
        if (payload is JSONObject) {
          _id = payload.variables['id'];
          _template = payload.variables['template'];
          _title = payload.variables['title'];
          _alias = payload.variables['alias'];
          _hidden = payload.variables['hidden'];
          _callListeners();
        }
        completer.complete(new Response<Page>.success(this));
      } else {

        completer.complete(new Response<Page>.error(response.error_code));
      }
    };
    ajaxClient.callFunctionString("PageOrder.getPage(${quoteString(_id)})$functionString..getInstance()").then(functionCallback);
    return new FutureResponse(completer.future);
  }

  void _callListeners() {
    _changeController.add(this);

  }


  Content operator [](String id) => _content.putIfAbsent(id, () => new AJAXContent.page(this, id));

  Stream<Page> get onChange => _changeStream == null ? _changeStream = _changeController.stream.asBroadcastStream() : _changeStream;


  @override
  bool get active => page_order.isActive(this);

  @override
  FutureResponse<Page> delete() => page_order.deletePage(this);

  @override
  bool get exists => page_order.pageExists(this);

  @override
  Page get next => page_order.nextPage(this);

  @override
  Page get parent => page_order.parentPage(this);

  @override
  List<Page> get path => page_order.pagePath(this);

  @override
  Page get previous => page_order.previousPage(this);
}
