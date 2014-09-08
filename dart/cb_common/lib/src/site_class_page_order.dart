part of site_classes;

class PageOrderChange {
  static const PAGE_ORDER_CHANGE_DEACTIVATE = 1;
  static const PAGE_ORDER_CHANGE_ACTIVATE = 2;
  static const PAGE_ORDER_CHANGE_DELETE_PAGE = 3;
  static const PAGE_ORDER_CHANGE_CREATE_PAGE = 4;

  final int type;
  final Page page;

  PageOrderChange(this.type, this.page);

}

abstract class PageOrder {

  Page get currentPage;

  List<Page> get currentPagePath;

  List<Page> pagePath(String page_id);

  List<Page> get activePages;

  List<Page> get inactivePages;

  bool isActive(String page_id);

  Map<String, Page> get pages;

  List<Page> listPageOrder({String parent_id:null});

  bool pageExists(String page_id);

  Future<ChangeResponse<Page>> deactivatePage(String page_id);

  Future<ChangeResponse<PageOrder>> changePageOrder(List<String> page_id_list, {String parent_id:null});

  Stream<PageOrderChange> get onUpdate;

  Future<ChangeResponse<Page>> createPage(String title);

  Future<ChangeResponse<Page>> deletePage(String id);

  Page operator [](String id);

}

class JSONPageOrder extends PageOrder {

  bool _hasBeenSetUp = false;

  final Map<String, Page> _pages = new Map<String, Page>();

  Map<String, List<String>> _pageOrder = new Map<String, List<String>>();

  StreamController<PageOrderChange> _streamController = new StreamController<PageOrderChange>();
  Stream<PageOrderChange> _stream;


  String _currentPageId;

/*  factory JSONPageOrder(){
    _cache._setup();
    return _cache;
  }*/

  JSONPageOrder(Map<String, List<Page>> pageOrderMap, List<Page> inactivePages, String current_page_id){
    _setUpFromLists(pageOrderMap, inactivePages, current_page_id);

  }

/*
  void _setup() {
    if (_hasBeenSetUp) {
      return;
    }

    _hasBeenSetUp = true;
    var listFunction = new ListPagesJSONFunction();
    ajaxClient.callFunction(listFunction).then((JSONResponse response) {
      if (response.type == Response.RESPONSE_TYPE_SUCCESS) {
        _currentPageId = response.payload['current_page_id'];

        response.payload['inactive_pages'].forEach((JSONObject o) {
          _addPageFromObject(o);
        });
        _pageOrderBuilder(response.payload['page_order']);
        _callListeners(PageOrderChange.PAGE_ORDER_CHANGE_ACTIVATE);
      } else {
        throw "Could not load PageOrder. Returned response of type ${response.type}";
      }
    });

  }
  void _pageOrderBuilder(List<Map> list, {String parent:null}) {
    if (list.length <= 0) {
      return;
    }
    _pageOrder[parent] = [];
    list.forEach((Map m) {
      var n = _addPageFromObject(m['page']);
      _pageOrder[parent].add(n);
      _pageOrderBuilder(m['subpages'], parent:n);
    });

  }
*/

  void _setUpFromLists(Map<String, List<Page>> pageOrder, List<Page> inactivePages, String current_page_id) {
    if (_hasBeenSetUp) {
      return;
    }
    _hasBeenSetUp = true;
    _currentPageId = current_page_id;

    pageOrder.forEach((String key, List<Page> l) {
      _pageOrder[key] = l.map((Page p) {
        _pages[p.id] = p;
        _addPageListener(p);
        return p.id;
      }).toList();
    });
    inactivePages.forEach((Page p) {
      _pages[p.id] = p;
      _addPageListener(p);
    });
  }

  String _addPageFromObject(JSONObject o) {
    var page = new JSONPage(o.variables['id'], o.variables['title'], o.variables['template'], o.variables['alias'], o.variables['hidden']);
    _addPageListener(page);
    _pages[page.id] = page;
    return page.id;
  }

  void _addPageListener(Page page) {
    page.onChange.listen((Page p) {
      if (_pages.containsKey(p.id)) {
        return;
      }
      var removeKey;
      _pages.forEach((String k, Page v) {
        if (v == p) {
          if (k == _currentPageId) {
            _currentPageId = p.id;
          }
          if (_pageOrder.containsKey(k)) {
            _pageOrder[p.id] = _pageOrder[k];
          }
          _pageOrder.forEach((lk, List<String> l) {
            _pageOrder[lk] = l.map((String s) => s == k ? p.id : s).toList();
          });

          removeKey = k;
        }
      });
      _pages.remove(removeKey);
      _pages[p.id] = p;
    });

  }

  Future<ChangeResponse<Page>> deactivatePage(String page_id) {
    var completer = new Completer<ChangeResponse<Page>>();
    var function = new DeactivatePageJSONFunction(page_id);
    ajaxClient.callFunctionString("PageOrder.deactivatePage(PageOrder.getPage(${quoteString(page_id)}))").then((JSONResponse response) {
      if (response.type == Response.RESPONSE_TYPE_SUCCESS) {
        _removeFromPageOrder(page_id);
        _callListeners(PageOrderChange.PAGE_ORDER_CHANGE_DEACTIVATE, _pages[page_id]);
        completer.complete(new ChangeResponse<Page>.success(_pages[page_id]));
      } else {
        completer.complete(new ChangeResponse<Page>.error(response.error_code));
      }
    });
    return completer.future;
  }

  void _removeFromPageOrder(String id) {
    if (_pageOrder.containsKey(id)) {
      var l = _pageOrder[id];
      var i = 0;
      while (l.length > 0) {
        _removeFromPageOrder(l.removeAt(0));
      }
      _pageOrder.remove(id);
    }
    _pageOrder.forEach((String k, List l) {
      _pageOrder[k].remove(id);
    });
  }

  Future<ChangeResponse<PageOrder>> changePageOrder(List<String> page_id_list, {String parent_id:null}) {
    var completer = new Completer<ChangeResponse<PageOrder>>();
    //var function = new SetPageOrderJSONFunction(parent_id == null ? "" : parent_id, page_id_list);
    var function = "PageOrder";

    var order = this.listPageOrder(parent_id:parent_id);

    var index = 0;
    var parentString = parent_id == null?"":", PageOrder.getPage(${quoteString(parent_id)})";
    page_id_list.forEach((String element){

      function += "..setPageOrder(PageOrder.getPage(${quoteString(element)}), $index $parentString)";
      order.removeWhere((Page p)=>p.id == element);
      index ++;
    });

    order.forEach((Page element){
      function += "..deactivatePage(PageOrder.getPage(${quoteString(element.id)}))";
    });

    var p = (parent_id == null ? "":"PageOrder.getPage(${quoteString(parent_id)})");
    function += "..getPageOrder($p)";

    //TODO make smaller function

    var functionCallback = (JSONResponse response) {
      if (response.type == Response.RESPONSE_TYPE_SUCCESS) {
        _pageOrder[parent_id] = response.payload != null?response.payload.map((JSONObject m) => m.variables["id"]).toList():[];
        _callListeners(PageOrderChange.PAGE_ORDER_CHANGE_ACTIVATE);
        completer.complete(new ChangeResponse.success(this));
      } else {
        completer.complete(new ChangeResponse.error(response.error_code));
      }
    };
    ajaxClient.callFunctionString(function).then(functionCallback);
    return completer.future;
  }

  List<Page> listPageOrder({String parent_id:null}) => _pageOrder[parent_id] == null ? [] : _pageOrder[parent_id].map((String id) => _pages[id]).toList();


  Map<String, Page> get pages => new Map<String, Page>.from(_pages);

  List<Page> get activePages {
    var l = pages.values.toList();
    l.add(null);
    return l.expand((Page p) => p == null ? listPageOrder() : listPageOrder(parent_id:p.id)).toList();
  }

  List<Page> get inactivePages {
    var map = new Map.from(_pages);
    activePages.forEach((Page p) {
      map.remove(p.id);
    });
    return map.values.toList();
  }

  List<Page> get currentPagePath {
    return pagePath(_currentPageId);

  }

  List<Page> pagePath(String page_id) {
    if (!pageExists(page_id)) {
      return [];
    }
    var path;
    if (!isActive(page_id)) {
      path = new List<Page>();
      path.add(pages[page_id]);
    } else {

      path = _listPath(page_id);

    }
    return path.toList();
  }


  List<Page> _listPath(String page_id) {
    var ret = new List<Page>();
    _pageOrder.forEach((String k, List<String> v) {
      v.forEach((String id) {
        if (page_id == id) {
          if (k != null) {
            var l = _listPath(k);
            l.forEach((Page p) {
              ret.add(p);
            });
          }
          ret.add(_pages[page_id]);
          return ret;
        }
      });
    });
    return ret;
  }

  bool isActive(String page_id) => activePages.contains(_pages[page_id]);

  Page get currentPage => _pages[_currentPageId];

  void _callListeners(int changeType, [Page page = null]) {
    _streamController.add(new PageOrderChange(changeType, page));
  }

  Future<ChangeResponse<Page>> createPage(String title) {
    var completer = new Completer<ChangeResponse<Page>>();
    var function = new CreatePageJSONFunction(title);
    var functionCallback = (JSONResponse response) {
      if (response.type == Response.RESPONSE_TYPE_SUCCESS) {
        String id = _addPageFromObject(response.payload);
        _callListeners(PageOrderChange.PAGE_ORDER_CHANGE_CREATE_PAGE, _pages[id]);
        completer.complete(new ChangeResponse.success(_pages[id]));
      } else {
        completer.complete(new ChangeResponse.error(response.error_code));
      }
    };
    ajaxClient.callFunctionString("PageOrder.createPage(${quoteString(title)})").then(functionCallback);
    return completer.future;
  }

  Future<ChangeResponse<Page>> deletePage(String id) {
    var completer = new Completer<ChangeResponse<Page>>();
    var function = new DeletePageJSONFunction(id);
    var functionCallback = (JSONResponse response) {
      if (response.type == Response.RESPONSE_TYPE_SUCCESS) {
        var page = _pages[id];
        _pages.remove(id);
        _removeFromPageOrder(id);
        _callListeners(PageOrderChange.PAGE_ORDER_CHANGE_DELETE_PAGE, page);
        completer.complete(new ChangeResponse.success(page));
      } else {
        completer.complete(new ChangeResponse.error(response.error_code));
      }

    };
    ajaxClient.callFunctionString("PageOrder.deletePage(PageOrder.getPage(${quoteString(id)}))").then(functionCallback);
    return completer.future;
  }

  bool pageExists(String page_id) => _pages[page_id] != null;

  Stream<PageOrderChange> get onUpdate => _stream == null ? _stream = _streamController.stream.asBroadcastStream() : _stream;

  Page operator [](String id) => pages[id];


}
