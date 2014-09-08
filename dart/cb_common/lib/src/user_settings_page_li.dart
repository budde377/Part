part of user_settings;


class UserSettingsActivePagesPath {
  final DivElement _pagePath = querySelector('#ActiveListPath');

  List<Page> _currentlyShowingPath = [];

  List<Page> get currentlyShowingPath => _currentlyShowingPath.toList();

  Page get currentlyShowing => _currentlyShowingPath.length > 0 ? _currentlyShowingPath.last : null;

  SpanElement _up, _dot;


  static final UserSettingsActivePagesPath _cache = new UserSettingsActivePagesPath._internal();

  factory UserSettingsActivePagesPath() => _cache;

  UserSettingsActivePagesPath._internal(){
    _up = _pagePath.querySelector('.up');
    _dot = _pagePath.querySelector('.dot');
    _dot.onClick.listen((MouseEvent) => showSubMenu(""));
    _up.onClick.listen((e) => _pagePath.querySelectorAll('.dot,.file').reversed.toList()[1].click());
  }


  void showSubMenu(String page_id) {
    var activeList = querySelector('#ActivePageList');
    activeList.querySelectorAll('.showSubList, .hasSubList').forEach((Element e) => e.classes..remove('showSubList')..remove('hasSubList'));
    if (page_id == null || page_id == "") {
      activeList.classes.remove('showSubList');
      _up.classes.add('hidden');
      _currentlyShowingPath = [];
      _updatePath();
      return;
    }

    if (!pageOrder.isActive(page_id)) {
      return;
    }
    var pagePath = pageOrder.pagePath(page_id);
    _currentlyShowingPath = pagePath.toList();
    pagePath.forEach((Page p) {
      var li = new UserSettingsPageLi.fromPage(p);
      li.li.parent.classes.add('showSubList');
      li.li.classes.add('hasSubList');
    });
    var pageLi = new UserSettingsPageLi.fromPageId(page_id);
    activeList.classes.add('showSubList');
    _up.classes.remove('hidden');
    _updatePath();
  }

  void reset() => showSubMenu("");

  void _updatePath() {
    _pagePath.querySelectorAll(':not(.dot):not(.up)').forEach((Element e) {
      e.remove();
    });
    _currentlyShowingPath.forEach((Page p) {
      var newLink = new SpanElement(), newDivider = new SpanElement();
      newLink..classes.add('file')..text = p.title..onClick.listen((Event e) => showSubMenu(p.id));
      newDivider..text = "/";
      _pagePath..append(newLink)..append(newDivider);

    });
  }


}


class UserSettingsPageLi {
  static final Map<String, UserSettingsPageLi> _cache = new Map<String, UserSettingsPageLi>();

  static final UserSettingsActivePagesPath _pagesPath = new UserSettingsActivePagesPath();

  final LIElement li;

  final PageOrder _pageOrder;

  final UListElement _inactiveList = querySelector('#InactivePageList'), _activeList = querySelector('#ActivePageList');

  final Page page;

  bool _active;

  Element _activate, _hide, _delete, _subPagesButton;


  AnchorElement _anchor;


  factory UserSettingsPageLi(LIElement li){
    var id = li.dataset["id"];
    return _resolveInstance(id, () => new UserSettingsPageLi._internal(li, pageOrder, pageOrder.pages[id]));
  }

  factory UserSettingsPageLi.fromPage(Page page){
    return _resolveInstance(page.id, () => new UserSettingsPageLi._internal(new LIElement(), pageOrder, page));
  }

  factory UserSettingsPageLi.fromPageId(String page_id){
    return _resolveInstance(page_id, () => new UserSettingsPageLi._internal(new LIElement(), pageOrder, pageOrder.pages[page_id]));
  }


  static UserSettingsPageLi _resolveInstance(String page_id, Function newInstance) {
    if (_cache.containsKey(page_id)) {
      return _cache[page_id];
    } else {
      var instance = newInstance();
      _cache[page_id] = instance;
      return instance;
    }
  }

  UserSettingsPageLi._internal(this.li, this._pageOrder, this.page){
    _active = _pageOrder.isActive(page.id);
    _returnNewDivIfNecessary(li.querySelector('.padding'), ['padding'], true);
    _anchor = li.querySelector('a.val');
    if (_anchor == null) {
      _anchor = new AnchorElement();
      _anchor.text = page.title;
      _anchor.href = _pageListAsAddressString();
      _anchor.classes.add('val');
      li.append(_anchor);
    }
    //_handle = _returnNewDivIfNecessary(li.querySelector('.handle'), ['handle'], _active);
    _delete = _returnNewDivIfNecessary(li.querySelector('.delete'), ['link', 'delete'], true, title:'Slet');
    _activate = _returnNewDivIfNecessary(li.querySelector('.activate'), ['link', 'activate'], true, title:_pageOrder.isActive(page.id) ? 'Deaktiver' : 'Aktiver');
    _hide = _returnNewDivIfNecessary(li.querySelector('.showhide'), ['link', 'showhide'], _active, title:page.hidden ? "Vis" : "Skjul");
    _subPagesButton = _returnNewDivIfNecessary(li.querySelector('.subpages.link'), ['link', 'subpages'], _active, title:'Undersider');

    updateInfo();

    _setUpListeners();
  }

  void _setUpListeners() {
    page.onChange.listen((_) {
      updateInfo();
    });
    _hide.onClick.listen((MouseEvent event) {
      var i = savingBar.startJob();
      page.changeInfo(hidden:!page.hidden).then((_)=>savingBar.endJob(i));
    });
    var dialog = new DialogContainer();
    _activate.onClick.listen((MouseEvent event) {
      if (_pageOrder.isActive(page.id)) {
        var deactivate = () {
          var i = savingBar.startJob();
          _pageOrder.deactivatePage(page.id).then((_) => savingBar.endJob(i));
        };
        if(_pageOrder.listPageOrder(parent_id:page.id).length > 0){
          dialog.confirm("Denne side har undersider. <br /> Er du sikker på at du vil deaktivere siden og dens undersider?").result.then((bool b){
            if(!b){
              return;
            }
            deactivate();
          });
        } else {
          deactivate();
        }

      } else {
        var i = savingBar.startJob();
        var parent_id = _pagesPath.currentlyShowingPath.length > 0 ? _pagesPath.currentlyShowingPath.last.id : null;
        var newOrder = _pageOrder.listPageOrder(parent_id:parent_id);
        var newOrderString = newOrder.map((Page p) => p.id).toList();
        newOrderString .add(page.id);
        _pageOrder.changePageOrder(newOrderString , parent_id:parent_id).then((_) => savingBar.endJob(i));
      }
    });
    _subPagesButton.onClick.listen((MouseEvent event) {
      _pagesPath.showSubMenu(page.id);
    });
    _delete.onClick.listen((MouseEvent event) {
      dialog.confirm("Er du sikker på at du vil slette denne side?").result.then((bool b) {
        if(!b){
          return;
        }
        li.classes.add('blur');
        var i = savingBar.startJob();
        _pageOrder.deletePage(page.id).then((ChangeResponse response) {
          li.classes.remove('blur');
          savingBar.endJob(i);
        });
      });
    });
  }

  String _pageListAsAddressString() => "/" + _pageOrder.pagePath(page.id).map((Page p) => p.id).join("/");

  void updateInfo() {
    _anchor.href = _pageListAsAddressString();
    li.dataset["template"] = page.template;
    li.dataset["alias"]= page.alias;
    li.dataset['hidden'] = page.hidden?"true":"false";
    li.dataset['title'] = page.title;
    li.dataset['id'] = page.id;

    _anchor.text = page.title;
    if (page.hidden) {
      _hide.title = "Vis";
      li.classes.add('ishidden');
    } else {
      _hide.title = "Skjul";
      li.classes.remove('ishidden');
    }
  }

  void updateActive() {
    var a = _pageOrder.isActive(page.id);
    if (_active && !a) {
      _anchor.href = _pageListAsAddressString();
      _activate.title = "Aktiver";
      _hide.remove();
      _subPagesButton.remove();
      //_handle.remove();
      var ul;
      if ((ul = li.querySelector('ul')) != null) {
        ul.remove();
      }

    } else if (!_active && a) {
      _anchor.href = _pageListAsAddressString();
      _activate.title = "Deaktiver";
      li.insertBefore(_delete, _activate);
      li.append(_hide);
      li.append(_subPagesButton);

    }
    _active = a;
  }

  Element _returnNewDivIfNecessary(Element element, List<String> classes, bool add, {String title:"", String text:"&nbsp;"}) {
    if (element == null) {
      element = new DivElement();
      classes.forEach((String c) {
        element.classes.add(c);
      });
      element.title = title;
      element.appendHtml(text);
      if (add) {
        li.append(element);
      }
    }
    return element;
  }

  void setUp() {

  }


}

