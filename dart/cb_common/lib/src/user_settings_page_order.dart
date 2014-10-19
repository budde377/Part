part of user_settings;

String idFromAnchor(AnchorElement val) => val.href.substring(val.href.lastIndexOf("/") + 1);

class UserSettingsJSONPageOrder implements PageOrder {

  final PageOrder _pageOrder;
  static final  UserSettingsJSONPageOrder _cache = new UserSettingsJSONPageOrder._internal(querySelector("#ActivePageList"), querySelector("#InactivePageList"));

  factory UserSettingsJSONPageOrder() => _cache;

  UserSettingsJSONPageOrder._internal(UListElement activePageList, UListElement inactivePageList) : _pageOrder = new AJAXPageOrder( _listToPageOrder(activePageList), _listToPages(inactivePageList), (() {
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

  Stream<PageOrderChange> get onUpdate => _pageOrder.onUpdate;

  Future<core.Response<Page>> deletePage(String id) => _pageOrder.deletePage(id);

  Page operator [](String id) => _pageOrder[id];

}