part of site_classes;

abstract class Content {
  final String id;

  Content(this.id);

  Future<List<DateTime>> get changeTimes;

  Future<Revision> operator [](DateTime time);

  Future<Revision> addContent(String content);

  Future<List<Revision>> listRevisions({DateTime from:null, DateTime to:null});

  Stream<Revision> get onAddContent;
}


abstract class JSONContentFunctionGenerator{

  JSONFunction generateListContentFunction({int from:0, int to: -1, bool includeContent:false});
  JSONFunction generateContentAtTimeFunction(num time);
  JSONFunction generateAddContentFunction(String content);
  String get id;
}


class JSONContentPageFunctionGenerator implements JSONContentFunctionGenerator{
  final Page page;
  final String _id;
  JSONContentPageFunctionGenerator (this.page, this._id);

  JSONFunction generateListContentFunction({int from:0, int to: -1, bool includeContent:false}) => new ListPageContentRevisionsJSONFunction(page.id, id , from:from, to:to, includeContent:includeContent);
  JSONFunction generateContentAtTimeFunction(num time) => new PageContentAtTimeJSONFunction(page.id, id, time);
  JSONFunction generateAddContentFunction(String content) => new AddPageContentJSONFunction(page.id, id, content);

  String get id => _id;
}

class JSONContentSiteFunctionGenerator implements JSONContentFunctionGenerator{
  final String _id;
  JSONContentSiteFunctionGenerator(this._id);

  JSONFunction generateListContentFunction({int from:0, int to: -1, bool includeContent:false}) => new ListSiteContentRevisionsJSONFunction(id , from:from, to:to, includeContent:includeContent);
  JSONFunction generateContentAtTimeFunction(num time) => new SiteContentAtTimeJSONFunction(id, time);
  JSONFunction generateAddContentFunction(String content) => new AddSiteContentJSONFunction(id, content);

  String get id => _id;

}

class JSONContent extends Content {
  JSONClient _client;
  final JSONContentFunctionGenerator contentStrategy;

  JSONContent(JSONContentFunctionGenerator contentStrategy, this._client): super(contentStrategy.id), this.contentStrategy = contentStrategy;

  JSONContent.page(Page page, String id, JSONClient client) : this(new JSONContentPageFunctionGenerator(page, id), client);

  JSONContent.site(String id, JSONClient client) : this(new JSONContentSiteFunctionGenerator(id), client);

  Map<DateTime, Revision> _revisions = new Map<DateTime, Revision>();

  StreamController<Revision> _streamController = new StreamController<Revision>();
  Stream<Revision> _stream;

  Future<List<DateTime>> get changeTimes {
    var completer = new Completer<List<DateTime>>();
    _client.callFunction(contentStrategy.generateListContentFunction()).then((JSONResponse response) {
      if (response.type == Response.RESPONSE_TYPE_SUCCESS) {
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

    _client.callFunction(contentStrategy.generateContentAtTimeFunction(time.millisecond~/1000)).then((JSONResponse response) {
      if (response.type != Response.RESPONSE_TYPE_SUCCESS) {
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
    _client.callFunction(contentStrategy.generateAddContentFunction(content)).then((JSONResponse response) {
      if (response.type != Response.RESPONSE_TYPE_SUCCESS) {
        completer.completeError(new Exception("Could not add content"));
        return;
      }
      var r;
      completer.complete(r = _generateRevision(new DateTime.fromMillisecondsSinceEpoch((response.payload is String?int.parse(response.payload):response.payload) * 1000), content));
      _streamController.add(r);
    });
    return completer.future;
  }

  Future<List<Revision>> listRevisions({DateTime from:null, DateTime to:null}) {
    var completer = new Completer<List<Revision>>();
    from = from == null ? new DateTime.fromMillisecondsSinceEpoch(0) : from;
    to = to == null ? new DateTime.now() : to;

    var fromm = from.millisecondsSinceEpoch ~/ 1000, too = to.millisecondsSinceEpoch ~/ 1000;
    _client.callFunction(contentStrategy.generateListContentFunction(from:fromm, to:too, includeContent:true)).then((JSONResponse response) {
      if (response.type == Response.RESPONSE_TYPE_SUCCESS) {
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
