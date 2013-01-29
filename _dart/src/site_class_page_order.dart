part of site_classes;

const PAGE_ORDER_CHANGE_DEACTIVATE = 1;
const PAGE_ORDER_CHANGE_ACTIVE_ORDER = 2;
const PAGE_ORDER_CHANGE_DELETE_PAGE = 3;
const PAGE_ORDER_CHANGE_CREATE_PAGE = 4;

typedef void PageOrderChangeListener(int changeType, [ Page page]);

abstract class PageOrder {

  Page get currentPage;

  List<Page> get activePages;

  List<Page> get inactivePages;

  bool pageExists(String page_id);

  void deactivatePage(String page_id, [ChangeCallback callback]);

  void changePageOrder(List<String> user_id_list, [ChangeCallback callback]);

  void registerListener(PageOrderChangeListener listener);

  void createPage(String title, [ChangeCallback callback]);

  void deletePage(String id, [ChangeCallback callback]);

}

class JSONPageOrder extends PageOrder {
  final String ajax_id;
  JSONClient _client;
  bool _hasBeenSetUp = false;
  final Map<String, Page> _pages = <String, Page>{};
  List<String> _pageOrder = <String>[];
  final List<PageOrderChangeListener> _listeners = <PageOrderChangeListener>[];


  static Map<String, JSONPageOrder> _cache = <String, JSONPageOrder>{};

  String _currentPageId;

  factory JSONPageOrder(String ajax_id){
    var pageOrder = _retrieveInstance(ajax_id);
    pageOrder._setup();
    return pageOrder;
  }

  factory JSONPageOrder.initializeFromLists(String ajax_id, List<Page> activePages, List<Page> inactivePages, String current_page_id){
    var pageOrder = _retrieveInstance(ajax_id);
    pageOrder._setUpFromLists(activePages, inactivePages, current_page_id);
    return pageOrder;
  }

  static JSONPageOrder _retrieveInstance(String ajax_id) {
    if (_cache.containsKey(ajax_id)) {
      return _cache[ajax_id];
    } else {
      var pageOrder = new JSONPageOrder._internal(ajax_id);
      _cache[ajax_id] = pageOrder;
      return pageOrder;
    }
  }

  JSONPageOrder._internal(this.ajax_id);

  void _setup() {
    if (_hasBeenSetUp) {
      return;
    }
    _hasBeenSetUp = true;
    _client = new AJAXJSONClient(ajax_id);
    var listFunction = new ListPagesJSONFunction();
    _client.callFunction(listFunction, (JSONResponse response) {
      if (response.type == RESPONSE_TYPE_SUCCESS) {
        _currentPageId = response.payload['current_page_id'];

        response.payload['inactive_pages'].forEach((JSONObject o) {
          _addPageFromObject(o);
        });
        response.payload['active_pages'].forEach((JSONObject o) {
          _pageOrder.add(_addPageFromObject(o));
        });
        _callListeners(PAGE_ORDER_CHANGE_ACTIVE_ORDER);
      } else {
        throw "Could not load PageOrder. Returned response of type ${response.type}";
      }
    });

  }

  void _setUpFromLists(List<Page> activePages, List<Page> inactivePages, String current_page_id) {
    if (_hasBeenSetUp) {
      return;
    }
    _client = new AJAXJSONClient(ajax_id);
    _hasBeenSetUp = true;
    _currentPageId = current_page_id;
    activePages.forEach((Page p) {
      _pages[p.id] = p;
      _pageOrder.add(p.id);
    });
    inactivePages.forEach((Page p) {
      _pages[p.id] = p;
    });
  }

  String _addPageFromObject(JSONObject o) {
    var page = new JSONPage(o.variables['id'], o.variables['title'], o.variables['template'], o.variables['alias'], _client);
    _pages[page.id] = page;
    return page.id;
  }

  void deactivatePage(String page_id, [ChangeCallback callback]) {
    var function = new DeactivatePageJSONFunction(page_id);
    _client.callFunction(function, (JSONResponse response) {
      if (callback != null) {
        callback(response.type, response.error_code);
      }
      if (RESPONSE_TYPE_SUCCESS) {
        _callListeners(PAGE_ORDER_CHANGE_DEACTIVATE, _pages[page_id]);
      }
    });
  }

  void changePageOrder(List<String> user_id_list, [ChangeCallback callback]) {
    var function = new SetPageOrderJSONFunction(user_id_list);
    var functionCallback = (JSONResponse response) {
      if (callback != null) {
        callback(response.type, response.error_code);
      }
      if (response.type == RESPONSE_TYPE_SUCCESS) {
        _pageOrder = response.payload;
        _callListeners(PAGE_ORDER_CHANGE_ACTIVE_ORDER);
      }
    };
    _client.callFunction(function, functionCallback);
  }

  List<Page> get activePages => _pageOrder.mappedBy((String id) => _pages[id]).toList();

  List<Page> get inactivePages {
    var map = new Map.from(_pages);
    _pageOrder.forEach((String id) {
      map.remove(id);
    });
    return map.values.toList();
  }

  Page get currentPage => _pages[_currentPageId];

  void registerListener(PageOrderChangeListener listener) {
    _listeners.add(listener);
  }

  void _callListeners(int changeType, [Page page = null]) {
    _listeners.forEach((PageOrderChangeListener listener) {
      if (page == null) {
        listener(changeType);
      } else {
        listener(changeType, page);
      }
    });
  }

  void createPage(String title, [ChangeCallback callback]) {
    var function = new CreatePageJSONFunction(title);
    var functionCallback = (JSONResponse response) {
      if (response.type == RESPONSE_TYPE_SUCCESS) {
        String id = _addPageFromObject(response.payload);
        _callListeners(PAGE_ORDER_CHANGE_CREATE_PAGE, _pages[id]);
      }
      if (callback != null) {
        callback(response.type, response.error_code);
      }
    };
    _client.callFunction(function, functionCallback);
  }

  void deletePage(String id, [ChangeCallback callback]) {
    var function = new DeletePageJSONFunction(id);
    var functionCallback = (JSONResponse response) {
      if (response.type == RESPONSE_TYPE_SUCCESS) {
        var page = _pages[id];
        _pages.remove(id);
        _pageOrder.remove(id);
        _callListeners(PAGE_ORDER_CHANGE_DELETE_PAGE, page);
      }

      if (callback != null) {
        callback(response.type, response.error_code);
      }
    };
    _client.callFunction(function, functionCallback);
  }

  bool pageExists(String page_id) => _pages[page_id] != null;

}
