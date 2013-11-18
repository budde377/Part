part of site_classes;

typedef void PageInfoChangeListener(Page page);

class Revision {
  final DateTime time;

  final String content;

  Revision(this.time, this.content);
}

abstract class Content {
  final String id;

  final Page page;

  Content(this.page, this.id);

  Future<List<DateTime>> get changeTimes;

  Future<Revision> operator [](DateTime time);

  Future<Revision> addContent(String content);

  Future<List<Revision>> listRevisions({DateTime from:null, DateTime to:null});

  Stream<Revision> get onAddContent;
}

class JSONPageContent extends Content {
  JSONClient _client;

  JSONPageContent(Page page, String id, this._client) :super(page, id);

  Map<DateTime, Revision> _revisions = new Map<DateTime, Revision>();

  StreamController<Revision> _streamController = new StreamController<Revision>();
  Stream<Revision> _stream;

  Future<List<DateTime>> get changeTimes {
    var completer = new Completer<List<DateTime>>();
    _client.callFunction(new ListPageContentRevisionsJSONFunction(page.id, id)).then((JSONResponse response) {
      if (response.type == RESPONSE_TYPE_SUCCESS) {
        List<int> payload = response.payload== null? []:response.payload;
        completer.complete(payload.map((int) => new DateTime.fromMillisecondsSinceEpoch(int * 1000)).toList(growable:false));
      } else {
        completer.completeError(new Exception("Could not list times"));
      }
    });
    return completer.future;
  }

  Future<Revision> operator [](DateTime time){
    var completer = new Completer<Revision>();

      _client.callFunction(new PageContentAtTimeJSONFunction(page.id, id, time.millisecondsSinceEpoch ~/ 1000)).then((JSONResponse response) {
        if (response.type != RESPONSE_TYPE_SUCCESS) {
          completer.completeError(new Exception("Could not get content at time"));
          return;
        }
        if (response.payload == null) {
          completer.complete(null);
          return;
        }

        completer.complete(_generateRevision(new DateTime.fromMillisecondsSinceEpoch(int.parse(response.payload['time']) * 1000), response.payload['content']));
      });
    return completer.future;
  }

  Future<Revision> addContent(String content) {
    var completer = new Completer<Revision>();
    _client.callFunction(new AddPageContentJSONFunction(page.id, id, content)).then((JSONResponse response) {
      if (response.type != RESPONSE_TYPE_SUCCESS) {
        completer.completeError(new Exception("Could not add content"));
        return;
      }
      var r;
      completer.complete(r = _generateRevision(new DateTime.fromMillisecondsSinceEpoch(int.parse(response.payload )* 1000), content));
      _streamController.add(r);
    });
    return completer.future;
  }

  Future<List<Revision>> listRevisions({DateTime from:null, DateTime to:null}) {
    var completer = new Completer<List<Revision>>();
    from = from == null ? new DateTime.fromMillisecondsSinceEpoch(0) : from;
    to = to == null ? new DateTime.now() : to;

      var fromm = from.millisecondsSinceEpoch ~/ 1000, too = to.millisecondsSinceEpoch ~/ 1000;
      _client.callFunction(new ListPageContentRevisionsJSONFunction(page.id, id, includeContent:true, to:too, from:fromm)).then((JSONResponse response) {
        if (response.type == RESPONSE_TYPE_SUCCESS) {
          List<Map<String, dynamic>> payload = response.payload == null? []:response.payload;
          completer.complete(payload.map((Map<String, dynamic> m) => _generateRevision(new DateTime.fromMillisecondsSinceEpoch(m['time'] * 1000), m['content'])).toList(growable:false));

        } else {
          completer.completeError(new Exception("Could not list revisions"));
        }
      });

    return completer.future;
  }

  Revision _generateRevision(DateTime time, String content) => _revisions.putIfAbsent(time, () => new Revision(time, content));


  Stream<Revision> get onAddContent => _stream == null? _stream=_streamController.stream.asBroadcastStream():_stream;

}

abstract class Page {

  String get id;

  String get title;

  String get template;

  String get alias;

  bool get hidden;

  void changeInfo({String id, String title, String template, String alias, bool hidden, ChangeCallback callback});

  void registerListener(void f(Page page));

  Content operator [](String id);

}

class JSONPage extends Page {
  String _id, _title, _template, _alias;

  bool _hidden = false;

  final List<PageInfoChangeListener> _listeners = [];

  final JSONClient _client;

  Map<String, Content> _content = new Map<String, Content>();

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
    title = title != null ? title : _title;
    template = template != null ? template : _template;
    alias = alias != null ? alias : _alias;
    hidden = hidden != null ? hidden : _hidden;
    callback = callback != null ? callback : (a1, [a2, a3]) {
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
    _client.callFunction(function).then(functionCallback);
  }

  void _callListeners() {
    _listeners.forEach((void f(Page page)) {
      f(this);
    });
  }

  void registerListener(void f(Page page)) {
    _listeners.add(f);
  }


  Content operator [](String id) => _content.putIfAbsent(id, () => new JSONPageContent(this, id, _client));

}
