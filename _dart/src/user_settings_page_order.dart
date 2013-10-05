part of user_settings;

String idFromAnchor(AnchorElement val) => val.href.substring(val.href.lastIndexOf("/") + 1);

class UserSettingsJSONPageOrder implements PageOrder {

  final PageOrder _pageOrder;

  UserSettingsJSONPageOrder.initializeFromMenu(UListElement activePageList, UListElement inactivePageList) : _pageOrder = new JSONPageOrder.initializeFromLists( _listToPageOrder(activePageList), _listToPages(inactivePageList), (() {
    var v;
    return (v = activePageList.query('li.current a.val')) == null ? ((v = inactivePageList.query('li.current a.val')) == null ? null : idFromAnchor(v)) : idFromAnchor(v);
  })());

  UserSettingsJSONPageOrder() : _pageOrder = new JSONPageOrder();

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
      list.children.forEach((Element e) {
        var a;
        if ((a = e.query('a.val')) != null) {
          recursiveMapBuilder(e.query('ul'), idFromAnchor(a));
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
      var a = li.query('a.val'), templateElement = li.query('.template'), aliasElement = li.query('.alias');
      var id = idFromAnchor(a);
      var title = a.text.trim();
      var template = templateElement.text.trim();
      var alias = aliasElement.text.trim();
      var hidden = li.classes.contains('ishidden');
      var page = new JSONPage(id, title, template, alias, hidden, new AJAXJSONClient());

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

  void deactivatePage(String page_id, [ChangeCallback callback = null]) => _pageOrder.deactivatePage(page_id, callback);

  void changePageOrder(List<String> page_id_list, {ChangeCallback callback:null, String parent_id:null}) => _pageOrder.changePageOrder(page_id_list, callback:callback, parent_id:parent_id);

  void registerListener(PageOrderChangeListener listener) => _pageOrder.registerListener(listener);

  void createPage(String title, [ChangeCallback callback]) => _pageOrder.createPage(title, callback);

  void deletePage(String id, [ChangeCallback callback]) => _pageOrder.deletePage(id, callback);

}