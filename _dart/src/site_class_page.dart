part of site_classes;

typedef void PageInfoChangeListener(Page page);

class Revision {
  final DateTime time;

  final String content;

  Revision(this.time, this.content);
}

abstract class PageContent {
  final String id;

  final Page page;

  PageContent(this.page, this.id);

  Future<List<DateTime>> get changeTimes;

  Future<Revision> operator [](DateTime time);

  Future<Revision> addContent(String content);

  Future<List<Revision>> listRevisions({DateTime from:null, DateTime to:null});

  Stream<Revision> get onAddContent;
}

class JSONPageContent extends PageContent {
  JSONClient _client;

  JSONPageContent(Page page, String id, this._client) :super(page, id);

  Map<DateTime, Revision> _revisions = new Map<DateTime, Revision>();

/*  DateTime _maxTo, _minFrom ;*/

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
/*    if (time.millisecondsSinceEpoch >= _minFrom.millisecondsSinceEpoch && time.millisecondsSinceEpoch <= _maxTo.millisecondsSinceEpoch) {
      completer.complete(_revisions.values.toList().reversed.firstWhere((Revision r)=>r.time.millisecondsSinceEpoch<=time.millisecondsSinceEpoch, orElse:()=>null));
    } else {*/
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
//    }
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
    /*if (_minFrom != null && from.millisecondsSinceEpoch < _minFrom.millisecondsSinceEpoch && to.millisecondsSinceEpoch > _maxTo.millisecondsSinceEpoch) {
      var first = false;
      listRevisions(from:from, to:_minFrom).then((List<Revision> l1) => listRevisions(from:_minFrom, to:_maxTo).then((List<Revision> l2) => listRevisions(from:_maxTo, to:to).then((List<Revision> l3) {
        var retList = new List.from(l1);
        retList..addAll(l2)..addAll(l3);
        completer.complete(retList);
      })));
    } else if (_minFrom == null || from.millisecondsSinceEpoch < _minFrom.millisecondsSinceEpoch || to.millisecondsSinceEpoch > _maxTo.millisecondsSinceEpoch) {
      if (_minFrom != null) {
        if (from.millisecondsSinceEpoch < _maxTo.millisecondsSinceEpoch && from.millisecondsSinceEpoch > _minFrom.millisecondsSinceEpoch && to.millisecondsSinceEpoch > _maxTo.millisecondsSinceEpoch) {
          from = _maxTo;
        } else if (to.millisecondsSinceEpoch > _minFrom.millisecondsSinceEpoch && to.millisecondsSinceEpoch < _maxTo.millisecondsSinceEpoch && from.millisecondsSinceEpoch < _minFrom.millisecondsSinceEpoch) {
          to = _minFrom;
        }
      }*/
      var fromm = from.millisecondsSinceEpoch ~/ 1000, too = to.millisecondsSinceEpoch ~/ 1000;
      _client.callFunction(new ListPageContentRevisionsJSONFunction(page.id, id, includeContent:true, to:too, from:fromm)).then((JSONResponse response) {
        if (response.type == RESPONSE_TYPE_SUCCESS) {
          List<Map<String, dynamic>> payload = response.payload == null? []:response.payload;
          completer.complete(payload.map((Map<String, dynamic> m) => _generateRevision(new DateTime.fromMillisecondsSinceEpoch(m['time'] * 1000), m['content'])).toList(growable:false));
          /*if (_minFrom == null) {
            _maxTo = to;
            _minFrom = from;
          } else {
            _maxTo = new DateTime.fromMillisecondsSinceEpoch(Math.max(to.millisecondsSinceEpoch, _maxTo.millisecondsSinceEpoch));
            _minFrom = new DateTime.fromMillisecondsSinceEpoch(Math.min(from.millisecondsSinceEpoch, _minFrom.millisecondsSinceEpoch));
          }*/

        } else {
          completer.completeError(new Exception("Could not list revisions"));
        }
      });
/*    } else {
      completer.complete(_revisions.values.where((Revision rev) => rev.time.millisecondsSinceEpoch >= from.millisecondsSinceEpoch && rev.time.millisecondsSinceEpoch <= to.millisecondsSinceEpoch).toList(growable:false));
    }*/


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

  PageContent operator [](String id);

}

class JSONPage extends Page {
  String _id, _title, _template, _alias;

  bool _hidden = false;

  final List<PageInfoChangeListener> _listeners = [];

  final JSONClient _client;

  Map<String, PageContent> _content = new Map<String, PageContent>();

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


  PageContent operator [](String id) => _content.putIfAbsent(id, () => new JSONPageContent(this, id, _client));

}
