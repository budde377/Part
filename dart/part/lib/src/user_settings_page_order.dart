part of user_settings;

String idFromAnchor(AnchorElement val) => val.href.substring(val.href.lastIndexOf("/") + 1);

class UserSettingsJSONPageOrder implements PageOrder {

  final PageOrder _pageOrder;
  static final UserSettingsJSONPageOrder _cache = new UserSettingsJSONPageOrder._internal(querySelector("#ActivePageList"), querySelector("#InactivePageList"));

  factory UserSettingsJSONPageOrder() => _cache;

  UserSettingsJSONPageOrder._internal(UListElement activePageList, UListElement inactivePageList) : _pageOrder = new AJAXPageOrder(_listToPageOrder(activePageList), _listToPages(inactivePageList), (() {
    var v;
    return (v = activePageList.querySelector('li.current')) == null ?
    ((v = inactivePageList.querySelector('li.current')) == null ? null : v.dataset["id"]) : v.dataset["id"];
  })());


  static Map<String, List<Page>> _listToPageOrder(UListElement list) {
    var returnMap = {
    };
    var recursiveMapBuilder;
    recursiveMapBuilder = (UListElement list, [String parent]) {
      var l = _listToPages(list);
      if (l.length == 0) {
        return;
      }
      returnMap[parent] = l;
      list.children.forEach((LIElement e) {
        if (e.dataset.containsKey("id")) {
          recursiveMapBuilder(e.querySelector('ul'), e.dataset["id"]);
        }
      });
    };
    recursiveMapBuilder(list);

    return returnMap;
  }

  static List<Page> _listToPages(UListElement list) {
    var lis = list.children.where((Element e) => !e.classes.contains('emptyListInfo'));
    var returnList = <Page>[];
    lis.forEach((LIElement li) {
      var id = li.dataset["id"];
      var title = li.dataset["title"];
      var template = li.dataset["template"];
      var alias = li.dataset["alias"];
      var hidden = li.dataset["hidden"] == "true";
      var page = new AJAXPage(id, title, template, alias, hidden);

      returnList.add(page);
    });
    return returnList;
  }


  Page get currentPage => _pageOrder.currentPage;

  List<Page> get currentPagePath => _pageOrder.currentPagePath;

  List<Page> pagePath(String page_id) => _pageOrder.pagePath(page_id);

  List<Page> get activePages => _pageOrder.activePages;

  List<Page> get inactivePages => _pageOrder.inactivePages;

  bool isActive(String page_id) => _pageOrder.isActive(page_id);

  Map<String, Page> get pages => _pageOrder.pages;

  List<Page> listPageOrder({String parent_id:null}) => _pageOrder.listPageOrder(parent_id:parent_id);

  bool pageExists(String page_id) => _pageOrder.pageExists(page_id);

  Future<core.Response<Page>> deactivatePage(String page_id) => _pageOrder.deactivatePage(page_id);

  Future<core.Response<PageOrder>> changePageOrder(List<String> page_id_list, {String parent_id:null}) => _pageOrder.changePageOrder(page_id_list, parent_id:parent_id);

  Future<core.Response<Page>> createPage(String title) => _pageOrder.createPage(title);

  Stream<PageOrderChange> get onChange => _pageOrder.onChange;

  Stream<Page> get onUpdate => _pageOrder.onUpdate;

  Stream<Page> get onAdd => _pageOrder.onAdd;

  Stream<Page> get onRemove => _pageOrder.onRemove;

  Stream<Page> get onDeactivate => _pageOrder.onDeactivate;

  Stream<Page> get onActivate => _pageOrder.onActivate;

  Iterable<Page> get elements => _pageOrder.elements;

  void every(void f(User)) => _pageOrder.every(f);

  Future<core.Response<Page>> deletePage(String id) => _pageOrder.deletePage(id);

  Page operator [](String id) => _pageOrder[id];

}

class OnePageUserSettingsPageOrder implements PageOrder {

  final PageOrder _ajax_page_order;

  static PageOrder _cache;

  factory OnePageUserSettingsPageOrder() => _cache == null ? _cache = new OnePageUserSettingsPageOrder._internal() : _cache;

  OnePageUserSettingsPageOrder._internal(): _ajax_page_order = (() {
    var current_page_element = querySelector("#UserSettingsCurrentPage");
    var id = current_page_element.dataset['id'];
    var title = current_page_element.dataset['title'];
    var alias = current_page_element.dataset['alias'];
    var template = current_page_element.dataset['template'];
    var hidden = current_page_element.dataset['hidden'] == 'true';
    return new AJAXPageOrder({
        null:[new AJAXPage(id, title, template, alias, hidden)]
    }, [], id);
  })();

  Page get currentPage => _ajax_page_order.currentPage;

  List<Page> get currentPagePath => _ajax_page_order.currentPagePath;

  List<Page> get inactivePages => _ajax_page_order.inactivePages;

  Stream<PageOrderChange> get onChange => _ajax_page_order.onChange;

  Iterable<Page> get elements => _ajax_page_order.elements;


  Stream<Page> get onActivate => _ajax_page_order.onActivate;


  core.FutureResponse<PageOrder> changePageOrder(List<String> page_id_list, {String parent_id}) =>
  _ajax_page_order.changePageOrder(page_id_list, parent_id:parent_id);


  List<Page> get activePages => _ajax_page_order.activePages;


  Stream<Page> get onAdd => _ajax_page_order.onAdd;


  Stream<Page> get onDeactivate => _ajax_page_order.onDeactivate;


  void every(void f(K)) => _ajax_page_order.every(f);


  bool pageExists(String page_id) => _ajax_page_order.pageExists(page_id);


  List<Page> listPageOrder({String parent_id}) => _ajax_page_order.listPageOrder(parent_id:parent_id);


  Map<String, Page> get pages => _ajax_page_order.pages;


  bool isActive(String page_id) => _ajax_page_order.isActive(page_id);


  List<Page> pagePath(String page_id) => _ajax_page_order.pagePath(page_id);


  Stream<Page> get onRemove => _ajax_page_order.onRemove;


  core.FutureResponse<Page> deactivatePage(String page_id) => _ajax_page_order.deactivatePage(page_id);


  Stream<Page> get onUpdate => _ajax_page_order.onUpdate;


  core.FutureResponse<Page> deletePage(String id) => _ajax_page_order.deletePage(id);


  core.FutureResponse<Page> createPage(String title) => _ajax_page_order.createPage(title);

  Page operator [](String id) => _ajax_page_order[id];

}

