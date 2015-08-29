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

abstract class PageOrder extends GeneratorDependable<Page> {

  static const PAGE_ORDER_POSITION_LAST = -1;

  Page get currentPage;

  List<Page> get currentPagePath;

  Iterable<Page> get activePages;

  Iterable<Page> get inactivePages;

  Map<String, Page> get pages;

  Stream<Page> get onDelete;

  Stream<Page> get onCreate;

  Stream<Page> get onChangeOrder;

  Stream<Page> get onDeactivate;

  Stream<Page> get onActivate;

  Page parentPage(Page page);

  Page nextPage(Page page);

  Page previousPage(Page page);

  List<Page> pagePath(Page page);

  bool isActive(Page page);

  List<Page> listPageOrder([Page parent=null]);

  bool pageExists(Page page);

  FutureResponse<Page> deactivatePage(Page page);

  FutureResponse<Page> changePageOrder(Page page, {int place: PageOrder.PAGE_ORDER_POSITION_LAST, Page parent:null});

  FutureResponse<Page> createPage(String title);

  FutureResponse<Page> deletePage(Page page);

  GeneratorDependable<Page> pageOrderView([Page parent_page= null]);

  GeneratorDependable<Page> get inactivePageOrderView;

  Page operator [](String id);

}

class AJAXPageOrder extends PageOrder {

  Map<String, Page> _pages;
  Map<Page, List<Page>> _order;
  Page _current_page;
  List<Page> _inactive_pages;

  StreamController
  _onUpdateStreamController = new StreamController.broadcast(),
  _onActivateStreamController = new StreamController.broadcast(),
  _onChangeOrderStreamController = new StreamController.broadcast(),
  _onCreateStreamController = new StreamController.broadcast(),
  _onDeactivateStreamController = new StreamController.broadcast(),
  _onDeleteStreamController = new StreamController.broadcast();

  AJAXPageOrder(Iterable<Page> pages, Map<String, List<String>> page_order_map, Iterable<String> inactive_pages, String current_page_id) {
    _updatePagesMap(pages);
    pages.forEach((Page page) => page.onChange.listen(_onUpdateStreamController.add));
    onUpdate.listen((_) => _updatePagesMap(_pages.values));
    _order = new Map.fromIterable(
        page_order_map.keys,
        key:_page_from_id,
        value:(String id) => page_order_map[id].map(_page_from_id).toList());
    _current_page = _pages[current_page_id];
    _inactive_pages = inactive_pages.map(_page_from_id).toList();

  }

  void _updatePagesMap(Iterable<Page> pages) {
    _pages = new Map.fromIterable(pages, key: (Page p) => p.id);
  }

  Page _page_from_id(String id) => _pages[id];

  @override
  Page operator [](String id) => _page_from_id(id);

  @override
  Iterable<Page> get activePages => _pages.values.where((Page page) => !_inactive_pages.contains(page));

  @override
  Page get currentPage => _current_page;

  @override
  List<Page> get currentPagePath => pagePath(currentPage);

  @override
  Iterable<Page> get elements => _pages.values;

  @override
  Iterable<Page> get inactivePages => new List.from(_inactive_pages);

  @override
  Stream<Page> get onActivate => _onActivateStreamController.stream;

  @override
  Stream<Page> get onAdd => onCreate;

  @override
  Stream<Page> get onChangeOrder => _onChangeOrderStreamController.stream;

  @override
  Stream<Page> get onCreate => _onCreateStreamController.stream;

  @override
  Stream<Page> get onDeactivate => _onDeactivateStreamController.stream;

  @override
  Stream<Page> get onDelete => _onDeleteStreamController.stream;

  @override
  Stream<Page> get onRemove => onDelete;

  @override
  Stream<Page> get onUpdate => _onUpdateStreamController.stream;

  @override
  Map<String, Page> get pages => new Map.from(_pages);

  @override
  GeneratorDependable<Page> get inactivePageOrderView => new _InactivePageOrderViewGeneratorDependable(this);

  @override
  FutureResponse<Page> changePageOrder(Page page, {int place: PageOrder.PAGE_ORDER_POSITION_LAST, Page parent:null}) =>
  ajaxClient
  .callFunctionString("""
  PageOrder
  ..setPageOrder(
    PageOrder.getPage(${quoteString(page.id)}),
    $place
    ${parent == null ? "" : ", PageOrder.getPage(${quoteString(parent.id)})"})
  ..getPageOrder(
    ${parent == null ? "" : "PageOrder.getPage(${quoteString(parent.id)})"})""")
  .thenResponse(onSuccess:(Response<List<JSONObject>> response) {
    var new_order = response.payload.map((JSONObject object) => _pages[object.variables['id']]).toList();
    // Updating previous parents
    new_order.forEach((Page page) {
      if (_inactive_pages.contains(page)) {
        _inactive_pages.remove(page);
        _onActivateStreamController.add(page);
        return;
      }

      var old_parent = parentPage(page);
      if (old_parent == parent) {
        return;
      }
      _order[old_parent].remove(page);
      _onChangeOrderStreamController.add(old_parent);
    });
    //Setting new order
    _order[parent] = new_order;
    _onChangeOrderStreamController.add(parent);
    return new Response.success(parent);
  });

  @override
  FutureResponse<Page> createPage(String title) => ajaxClient
  .callFunctionString("PageOrder.createPage(${quoteString(title)})")
  .thenResponse(onSuccess:(Response response) {
    var page = new AJAXPage(
        this,
        response.payload.variables['id'],
        response.payload.variables['title'],
        response.payload.variables['template'],
        response.payload.variables['alias'],
        response.payload.variables['hidden']);
    _pages[page.id] = page;
    _inactive_pages.add(page);
    page.onChange.listen(_onUpdateStreamController.add);
    _onCreateStreamController.add(page);
    return new Response.success(page);
  });


  @override
  FutureResponse<Page> deactivatePage(Page page) => ajaxClient
  .callFunctionString("PageOrder.deactivatePage(PageOrder.getPage(${quoteString(page.id)}))")
  .thenResponse(onSuccess:(Response response) {
    if (isActive(page)) {
      _innerDeactivate(page, parentPage(page));
    }
    return new Response.success(page);
  });

  void _innerDeactivate(Page page, Page parent) {
    _order[parent].remove(page);
    _inactive_pages.add(page);
    if (_order.containsKey(page)) {
      _order[page].toList().forEach((Page sub_page) => _innerDeactivate(sub_page, page));
    }
    _onChangeOrderStreamController.add(parent);
    _onDeactivateStreamController.add(page);
  }

  @override
  FutureResponse<Page> deletePage(Page page) => ajaxClient
  .callFunctionString("PageOrder.deletePage(PageOrder.getPage(${quoteString(page.id)}))")
  .thenResponse(onSuccess:(Response response) {
    _innerDelete(page);
    return new Response.success(page);
  });

  void _innerDelete(Page page) {
    if (isActive(page)) {
      var parent = parentPage(page);
      _order[parent].remove(page);
      if (_order.containsKey(page)) {
        _order[page].forEach((Page sub_page) => _innerDeactivate(sub_page, page));
      }
      _onChangeOrderStreamController.add(parent);
    } else {
      _inactive_pages.remove(page);
    }
    _pages.remove(page.id);
    _onDeleteStreamController.add(page);
  }

  @override
  bool isActive(Page page) => pageExists(page) && !_inactive_pages.contains(page);

  @override
  List<Page> listPageOrder([Page parent = null]) => _order.containsKey(parent) ? _order[parent].toList() : [];

  @override
  bool pageExists(Page page) => _pages.containsValue(page);

  @override
  GeneratorDependable<Page> pageOrderView([Page parent_page = null]) => new _PageOrderViewGeneratorDependable(this, parent_page);

  @override
  List<Page> pagePath(Page page) {
    var parent = parentPage(page);
    List<Page> parent_path = parent == null ? [] : pagePath(parent);
    parent_path.add(page);
    return parent_path.toList();
  }

  @override
  Page parentPage(Page page) => _order.keys.firstWhere((Page parent) => _order[parent].contains(page), orElse: () => null);


  @override
  Page nextPage(Page page) {
    if (!isActive(page)) {
      return null;
    }
    var parent = parentPage(page);
    var order = _order[parent];
    var index = order.indexOf(page);
    return index >= order.length ? null : _order[parent][index + 1];
  }

  @override
  Page previousPage(Page page) {
    if (!isActive(page)) {
      return null;
    }
    var parent = parentPage(page);
    var order = _order[parent];
    var index = order.indexOf(page);
    return index == 0 ? null : _order[parent][index - 1];
  }
}

class _InactivePageOrderViewGeneratorDependable extends GeneratorDependable<Page> {

  static final Map<PageOrder, _InactivePageOrderViewGeneratorDependable> _cache = {};

  final PageOrder page_order;

  List<Page> _elements = [];

  final StreamController<Page>
  _onAddStreamController = new StreamController.broadcast(),
  _onRemoveStreamController = new StreamController.broadcast();

  factory _InactivePageOrderViewGeneratorDependable(PageOrder page_order) => _cache.putIfAbsent(page_order, () => new _InactivePageOrderViewGeneratorDependable.internal(page_order));

  _InactivePageOrderViewGeneratorDependable.internal(this.page_order){
    page_order
      ..onAdd.listen(_onAddStreamController.add)
      ..onDeactivate.listen(_onAddStreamController.add)
      ..onDelete.listen(_onRemoveStreamController.add)
      ..onActivate.listen(_onRemoveStreamController.add);
  }

  @override
  Iterable<Page> get elements => page_order.elements;

  @override
  Stream<Page> get onAdd => _onAddStreamController.stream.where((Page page) => elements.contains(page));

  @override
  Stream<Page> get onRemove => _onRemoveStreamController.stream;

  @override
  Stream<Page> get onUpdate => page_order.onUpdate.where((Page page) => elements.contains(page));
}

class _PageOrderViewGeneratorDependable extends GeneratorDependable<Page> {
  final PageOrder page_order;
  final Page page;
  List<Page> _elements;

  final StreamController
  onAddController = new StreamController.broadcast(),
  onRemoveController = new StreamController.broadcast();

  static final Map<int, _PageOrderViewGeneratorDependable> _cache = {};

  _PageOrderViewGeneratorDependable._internal(this.page_order, this.page){
    _elements = page_order.listPageOrder(page);
    page_order.onChangeOrder.where((Page p) => p == page).listen((Page parent) {
      var new_order = page_order.listPageOrder(page);
      if (_elements.length < new_order.length) {
        onAddController.add(new_order.firstWhere((Page p) => !_elements.contains(p)));
      } else if (_elements.length > new_order.length) {
        onRemoveController.add(_elements.firstWhere((Page p) => !new_order.contains(p)));
      }
      _elements = new_order;
    });

  }

  factory _PageOrderViewGeneratorDependable(PageOrder pageOrder, Page page) => _cache.putIfAbsent(
      pageOrder.hashCode ^ page.hashCode,
          () => new _PageOrderViewGeneratorDependable._internal(pageOrder, page));


  @override
  Iterable<Page> get elements => page_order.listPageOrder(page);

  @override
  Stream<Page> get onAdd => onAddController.stream;

  @override
  Stream<Page> get onRemove => onRemoveController.stream;

  @override
  Stream<Page> get onUpdate => page_order.onUpdate.where((Page page) => elements.contains(page));

}
