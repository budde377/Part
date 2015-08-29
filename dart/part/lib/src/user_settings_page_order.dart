part of user_settings;

String idFromAnchor(AnchorElement val) => val.href.substring(val.href.lastIndexOf("/") + 1);

class UserSettingsJSONPageOrder extends PageOrder {

  PageOrder _page_order;
  List<Page> _pages = [];
  Map<String, List<String>> _order = {};
  Iterable<String> _inactive = [];
  String _current;

  static final UserSettingsJSONPageOrder _cache = new UserSettingsJSONPageOrder._internal(querySelector("#ActivePageList"), querySelector("#InactivePageList"));

  factory UserSettingsJSONPageOrder() => _cache;

  UserSettingsJSONPageOrder._internal(UListElement activePageList, UListElement inactivePageList){
    _build_page_order(activePageList, inactivePageList);
    _page_order = new AJAXPageOrder(_pages, _order, _inactive, _current);
  }

  void _build_page_order(UListElement activePageList, UListElement inactivePageList) {
    _inactive = inactivePageList.children.map(_element_to_object).where((element) => element != null).map((Page p) => p.id).toList();
    _order[null] = _recursive_build_order(activePageList);
  }

  List<String> _recursive_build_order(UListElement uListElement) {
    if (uListElement == null) {
      return [];
    }
    var list = [];
    uListElement.children.forEach((LIElement li) {
      var page = _element_to_object(li);
      if (page == null) {
        return;
      }
      list.add(page.id);
      _order[page.id] = _recursive_build_order(li.querySelector('ul'));
    });

    return list;
  }

  Page _element_to_object(LIElement li) {
    if (!li.dataset.containsKey('id')) {
      return null;
    }
    var page = new AJAXPage(
        this,
        li.dataset['id'],
        li.dataset['title'],
        li.dataset['template'],
        li.dataset['alias'],
        li.dataset['hidden'] == "true");
    _pages.add(page);
    if (li.classes.contains('current')) {
      _current = page.id;
    }
    return page;
  }

  @override
  Page operator [](String id) => _page_order[id];

  @override
  Iterable<Page> get activePages => _page_order.activePages;

  @override
  core.FutureResponse<Page> changePageOrder(Page page, {int place: PageOrder.PAGE_ORDER_POSITION_LAST, Page parent: null}) => _page_order.changePageOrder(page, place:place, parent:parent);

  @override
  core.FutureResponse<Page> createPage(String title) => _page_order.createPage(title);

  @override
  Page get currentPage => _page_order.currentPage;

  @override
  List<Page> get currentPagePath => _page_order.currentPagePath;

  core.FutureResponse<Page> deactivatePage(Page page) => _page_order.deactivatePage(page);

  @override
  core.FutureResponse<Page> deletePage(Page page) => _page_order.deletePage(page);

  @override
  Iterable<Page> get elements => _page_order.elements;

  @override
  Iterable<Page> get inactivePages => _page_order.inactivePages;

  @override
  bool isActive(Page page) => _page_order.isActive(page);

  @override
  List<Page> listPageOrder([Page parent = null]) => _page_order.listPageOrder(parent);

  @override
  Page nextPage(Page page) => _page_order.nextPage(page);

  @override
  Stream<Page> get onActivate => _page_order.onActivate;

  @override
  Stream<Page> get onAdd => _page_order.onAdd;

  @override
  Stream<Page> get onChangeOrder => _page_order.onChangeOrder;

  @override
  Stream<Page> get onCreate => _page_order.onCreate;

  @override
  Stream<Page> get onDeactivate => _page_order.onDeactivate;

  @override
  Stream<Page> get onDelete => _page_order.onDelete;

  @override
  Stream<Page> get onRemove => _page_order.onRemove;

  @override
  Stream<Page> get onUpdate => _page_order.onUpdate;

  @override
  bool pageExists(Page page) => _page_order.pageExists(page);

  @override
  core.GeneratorDependable<Page> pageOrderView([Page parent_page = null]) => _page_order.pageOrderView(parent_page);

  @override
  List<Page> pagePath(Page page) => _page_order.pagePath(page);

  @override
  Map<String, Page> get pages => _page_order.pages;

  @override
  Page parentPage(Page page) => _page_order.parentPage(page);

  @override
  Page previousPage(Page page) => _page_order.previousPage(page);

  @override
  core.GeneratorDependable<Page> get inactivePageOrderView => _page_order.inactivePageOrderView;
}

class OnePageUserSettingsPageOrder extends PageOrder {

  PageOrder _page_order;

  static PageOrder _cache;

  factory OnePageUserSettingsPageOrder() => _cache == null ? _cache = new OnePageUserSettingsPageOrder._internal() : _cache;

  OnePageUserSettingsPageOrder._internal(){
    var current_page_element = querySelector("#UserSettingsCurrentPage");
    var id = current_page_element.dataset['id'];
    var title = current_page_element.dataset['title'];
    var alias = current_page_element.dataset['alias'];
    var template = current_page_element.dataset['template'];
    var hidden = current_page_element.dataset['hidden'] == 'true';
    var active = current_page_element.dataset['active'] == 'true';

    _page_order = new AJAXPageOrder(
        [new AJAXPage(this, id, title, template, alias, hidden)],
        active ? {null:[id]} : {},
        active ? [] : [id], id);
  }

  @override
  Page operator [](String id) => _page_order[id];

  @override
  Iterable<Page> get activePages => _page_order.activePages;

  @override
  core.FutureResponse<Page> changePageOrder(Page page, {int place: PageOrder.PAGE_ORDER_POSITION_LAST, Page parent: null}) => _page_order.changePageOrder(page, place:place, parent:parent);

  @override
  core.FutureResponse<Page> createPage(String title) => _page_order.createPage(title);

  @override
  Page get currentPage => _page_order.currentPage;

  @override
  List<Page> get currentPagePath => _page_order.currentPagePath;

  core.FutureResponse<Page> deactivatePage(Page page) => _page_order.deactivatePage(page);

  @override
  core.FutureResponse<Page> deletePage(Page page) => _page_order.deletePage(page);

  @override
  Iterable<Page> get elements => _page_order.elements;

  @override
  Iterable<Page> get inactivePages => _page_order.inactivePages;

  @override
  bool isActive(Page page) => _page_order.isActive(page);

  @override
  List<Page> listPageOrder([Page parent = null]) => _page_order.listPageOrder(parent);

  @override
  Page nextPage(Page page) => _page_order.nextPage(page);

  @override
  Stream<Page> get onActivate => _page_order.onActivate;

  @override
  Stream<Page> get onAdd => _page_order.onAdd;

  @override
  Stream<Page> get onChangeOrder => _page_order.onChangeOrder;

  @override
  Stream<Page> get onCreate => _page_order.onCreate;

  @override
  Stream<Page> get onDeactivate => _page_order.onDeactivate;

  @override
  Stream<Page> get onDelete => _page_order.onDelete;

  @override
  Stream<Page> get onRemove => _page_order.onRemove;

  @override
  Stream<Page> get onUpdate => _page_order.onUpdate;

  @override
  bool pageExists(Page page) => _page_order.pageExists(page);

  @override
  core.GeneratorDependable<Page> pageOrderView([Page parent_page = null]) => _page_order.pageOrderView(parent_page);

  @override
  List<Page> pagePath(Page page) => _page_order.pagePath(page);

  @override
  Map<String, Page> get pages => _page_order.pages;

  @override
  Page parentPage(Page page) => _page_order.parentPage(page);

  @override
  Page previousPage(Page page) => _page_order.previousPage(page);

  @override
  core.GeneratorDependable<Page> get inactivePageOrderView => _page_order.inactivePageOrderView;
}

