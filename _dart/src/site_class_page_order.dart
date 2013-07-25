part of site_classes;

const PAGE_ORDER_CHANGE_DEACTIVATE = 1;
const PAGE_ORDER_CHANGE_ACTIVATE = 2;
const PAGE_ORDER_CHANGE_DELETE_PAGE = 3;
const PAGE_ORDER_CHANGE_CREATE_PAGE = 4;

typedef void PageOrderChangeListener(int changeType, [ Page page]);

abstract class PageOrder {

  Page get currentPage;

  List<Page> get currentPagePath;

  List<Page> pagePath(String page_id);

  List<Page> get activePages;

  List<Page> get inactivePages;

  bool isActive(String page_id);

  Map<String, Page> get pages;

  List<Page> listPageOrder({
                           String parent_id:null
                           });

  bool pageExists(String page_id);

  void deactivatePage(String page_id, [ChangeCallback callback = null]);

  void changePageOrder(List<String> page_id_list , {ChangeCallback callback:null,String parent_id:null});

  void registerListener(PageOrderChangeListener listener);

  void createPage(String title, [ChangeCallback callback = null]);

  void deletePage(String id, [ChangeCallback callback = null]);

}

class JSONPageOrder extends PageOrder {
  final String ajax_id;

  JSONClient _client;

  bool _hasBeenSetUp = false;

  final Map<String, Page> _pages = <String, Page>{
  };

  Map<String, List<String>> _pageOrder = {};

  final List<PageOrderChangeListener> _listeners = <PageOrderChangeListener>[];


  static Map<String, JSONPageOrder> _cache = <String, JSONPageOrder>{
  };

  String _currentPageId;

  factory JSONPageOrder(String ajax_id){
    var pageOrder = _retrieveInstance(ajax_id);
    pageOrder._setup();
    return pageOrder;
  }

  factory JSONPageOrder.initializeFromLists(String ajax_id, Map<String,List<Page>> pageOrderMap, List<Page> inactivePages, String current_page_id){
    var pageOrder = _retrieveInstance(ajax_id);
    pageOrder._setUpFromLists(pageOrderMap, inactivePages, current_page_id);
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
        _pageOrderBuilder(response.payload['page_order']);
        _callListeners(PAGE_ORDER_CHANGE_ACTIVATE);
      } else {
        throw "Could not load PageOrder. Returned response of type ${response.type}";
      }
    });

  }

  void _pageOrderBuilder(List<Map> list, {
  String parent:null
  }) {
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

  void _setUpFromLists(Map<String,List<Page>> pageOrder, List<Page> inactivePages, String current_page_id) {
    if (_hasBeenSetUp) {
      return;
    }
    _client = new AJAXJSONClient(ajax_id);
    _hasBeenSetUp = true;
    _currentPageId = current_page_id;

    pageOrder.forEach((String key,List<Page> l){
      _pageOrder[key] = l.map((Page p){
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
    var page = new JSONPage(o.variables['id'], o.variables['title'], o.variables['template'], o.variables['alias'], o.variables['hidden'],_client);
    _addPageListener(page);
    _pages[page.id] = page;
    return page.id;
  }

  void _addPageListener(Page page){
    page.registerListener((Page p){
      if(_pages.containsKey(p.id)){
        return;
      }
      var removeKey;
      _pages.forEach((String k,Page v){
        if(v == p){
          if(k == _currentPageId){
            _currentPageId= p.id;
          }
          if(_pageOrder.containsKey(k)){
            _pageOrder[p.id] = _pageOrder[k];
          }
          _pageOrder.forEach((lk,List<String> l){
            _pageOrder[lk] = l.map((String s)=>s==k?p.id:s);
          });

          removeKey = k;
        }
      });
      _pages.remove(removeKey);
      _pages[p.id] = p;
    });

  }

  void deactivatePage(String page_id, [ChangeCallback callback = null]) {
    var function = new DeactivatePageJSONFunction(page_id);
    _client.callFunction(function, (JSONResponse response) {
      if (callback != null) {
        callback(response.type, response.error_code);
      }
      if (response.type == RESPONSE_TYPE_SUCCESS) {
        _removeFromPageOrder(page_id);
        _callListeners(PAGE_ORDER_CHANGE_DEACTIVATE, _pages[page_id]);
      }
    });
  }

  void _removeFromPageOrder(String id) {
    if(_pageOrder.containsKey(id)){
      _pageOrder[id].forEach((String v){
        _removeFromPageOrder(v);
      });
      _pageOrder.remove(id);
    }
    _pageOrder.forEach((String k, List l) {
      _pageOrder[k].remove(id);
    });
  }

  void changePageOrder(List<String> page_id_list, {ChangeCallback callback:null,String parent_id:null}) {
    var function = new SetPageOrderJSONFunction(parent_id==null?"":parent_id, page_id_list);
    var functionCallback = (JSONResponse response) {
      if (callback != null) {
        callback(response.type, response.error_code);
      }
      if (response.type == RESPONSE_TYPE_SUCCESS) {
        var p,parent = (p = response.payload['parent']).length<=0?null:p;
        _pageOrder[parent] = response.payload['order'];
        _callListeners(PAGE_ORDER_CHANGE_ACTIVATE);
      }
    };
    _client.callFunction(function, functionCallback);
  }

  List<Page> listPageOrder({
                           String parent_id:null
                           }) => _pageOrder[parent_id] == null ? [] : _pageOrder[parent_id].map((String id) => _pages[id]).toList();


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

  List<Page> get currentPagePath{
    return pagePath(_currentPageId);

  }

  List<Page> pagePath(String page_id){
    if(!pageExists(page_id)){
      return [];
    }
    var path;
    if(!isActive(page_id)){
      path = new List<Page>();
      path.add(pages[page_id]);
    } else {

      path = _listPath(page_id);

    }
    return path.toList();
  }


  List<Page> _listPath(String page_id){
    var ret = new List<Page>();
    _pageOrder.forEach((String k, List<String> v){
      v.forEach((String id){
        if(page_id == id){
          if(k != null){
            var l = _listPath(k);
            l.forEach((Page p){
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

  void createPage(String title, [ChangeCallback callback = null]) {
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

  void deletePage(String id, [ChangeCallback callback = null]) {
    var function = new DeletePageJSONFunction(id);
    var functionCallback = (JSONResponse response) {
      if (response.type == RESPONSE_TYPE_SUCCESS) {
        var page = _pages[id];
        _pages.remove(id);
        _removeFromPageOrder(id);
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
