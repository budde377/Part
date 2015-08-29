part of user_settings;

// TODO fix
class UserSettingsAddPageFormInitializer extends core.Initializer {
  PageOrder _order;

  FormElement _addPageForm = querySelector("#EditPagesForm");

  UserSettingsAddPageFormInitializer(PageOrder this._order);

  bool get canBeSetUp => _addPageForm != null;

  void setUp() {
    var input = _addPageForm.querySelector('#EditPagesAddPage');

    new Validator(input)
      ..addNonEmptyValueValidator()
      ..errorMessage = "Titlen må ikke være tom";
    var validatingForm = new ValidatingForm(_addPageForm);
    validatingForm.validate();
    var decoration = new FormHandler(_addPageForm);
    decoration.submitFunction = (Map<String, String> data) {
      if (_addPageForm.classes.contains('initial')) {
        return false;
      }

      decoration.blur();
      _order.createPage(data['title']).then((core.Response response) {
        if (response.type == core.Response.RESPONSE_TYPE_SUCCESS) {
          input.value = "";
          input.blur();
          validatingForm.validate();
        }
        decoration.unBlur();
      });

      return false;
    };
  }

}


class UserSettingsPageListsInitializer extends core.Initializer {

  Page _showing_page;
  List<core.Pair<Page, LIElement>> _showing_page_path = [];
  UListElement
  _activeList = querySelector("#ActivePageList"),
  _inactiveList = querySelector("#InactivePageList");
  DivElement
  _active_list_path = querySelector('#ActiveListPath');
  LIElement _inactive_element, _active_element;
  SpanElement _dot;

  UserSettingsPageListsInitializer() {
    _inactive_element = new Element.html(
        """
          <li>
            <a href="" class="val"> </a>
            <div class="link delete" title="Slet">&nbsp;</div>
            <div class="link activate" title="Aktiver">&nbsp;</div>
          </li>
          """);
    _active_element = new Element.html("""
        <li>
                <a href='' class='val'>Forside</a>
                <div class='link delete' title='Slet'>&nbsp;</div>
                <div class='link activate' title='Deaktiver'>&nbsp;</div>
                <div class='link showhide' title='Skjul'> &nbsp;</div>
                <div class='link subpages' title='Undersider'>&nbsp;</div>
                <ul  class='colorList '><li class='empty'>Der er ingen aktive sider</li></ul>
        </li>""");

  }

  bool get canBeSetUp => _activeList != null && _inactiveList != null && _active_list_path != null;

  void setUp() {
    new ElementChildrenGenerator<Page, LIElement>(
            (Page p) => _inactive_element.clone(true),
        _inactiveList,
        _pageSelector)
      ..dependsOn(pageOrder.inactivePageOrderView)
      ..addHandler(_inactiveHandler)
      ..addUpdater(_updater)
      ..onAdd.listen((_) => _moveEmptyLast(_inactiveList));

    _create_active_generator(_activeList);

    _dot = _active_list_path.querySelector('.dot');
    _dot.onClick.listen((_) {
      _activeList.classes.remove('show_sub_list');
      _activeList
      .querySelectorAll('ul.show_sub_list, li.has_sub_list')
      .forEach((Element e) => e.classes
        ..remove('show_sub_list')
        ..remove('has_sub_list'));
      _showing_page = null;
      _showing_page_path = [];
      _update_path();
    });
  }

  _pageSelector(LIElement li, _) => pageOrder[li.dataset['id']];


  _moveEmptyLast(UListElement list) => list.append(list.children.firstWhere((Element element) => element.classes.contains('empty'))
    ..remove());


  void _inactiveHandler(Page page, LIElement li) {
    _handler(page, li, _inactiveList);
    li.querySelector('div.link.activate').onClick.listen((_) => pageOrder.changePageOrder(page, parent:_showing_page));
  }

  void _activeHandler(Page page, LIElement li) {
    _handler(page, li, _activeList);
    li.querySelector('div.link.activate').onClick.listen((_) => pageOrder.deactivatePage(page));
    li.querySelector('div.link.showhide').onClick.listen((_) async {
      li.classes.add('blur');
      await page.changeInfo(hidden:!page.hidden);
      li.classes.remove('blur');
    });
    li.querySelector('div.link.subpages').onClick.listen((_) => _show_page(page, li));
    _create_active_generator(li.querySelector('ul'), page);


  }

  _create_active_generator(UListElement list, [parent=null]) {
    var generator = new ElementChildrenGenerator<Page, LIElement>(
            (Page p) => _active_element.clone(true),
        list,
        _pageSelector)
      ..dependsOn(pageOrder.pageOrderView(parent))
      ..addHandler(_activeHandler)
      ..addHandler(_activeDragHandler)
      ..addUpdater(_updater)
      ..onAdd.listen((_) => _moveEmptyLast(list));

    pageOrder.onChangeOrder.where((Page page) => page == parent).listen((Page page) => _reorder_page(page, list, generator.elements));

  }

  _reorder_page(Page page, UListElement list, Iterable<core.Pair<Page, LIElement>> elements) {
    var element_map = new Map.fromIterable(elements, key:(core.Pair p) => p.k, value:(core.Pair p) => p.v);
    pageOrder.listPageOrder(page).forEach((Page page) {
      if(!element_map.containsKey(page)){
        return;
      }
      list.append(element_map[page]);
    });
  }

  _show_page(Page page, LIElement li) {
    li
      ..parent.classes.add('show_sub_list')
      ..classes.add('has_sub_list');
    _showing_page = page;
    _showing_page_path.add(new core.Pair(page, li));
    _update_path();

  }

  void _update_path() {
    _active_list_path
      ..children.clear()
      ..append(_dot);
    for (int index = 0; index < _showing_page_path.length; index++) {
      var page = _showing_page_path[index].k, li = _showing_page_path[index].v;
      _active_list_path.appendText('/');
      var span = new SpanElement()
        ..text = page.title
        ..classes.add('file');
      _active_list_path.append(span);
      span.onClick.listen((_) {
        li.querySelectorAll('ul.show_sub_list, li.has_sub_list').forEach((UListElement ul) => ul.classes
          ..remove('show_sub_list')
          ..remove('has_sub_list'));
        _showing_page_path = _showing_page_path.sublist(0, index + 1);
        _showing_page = page;
        _update_path();
      });

    }

  }

  void _handler(Page page, LIElement li, UListElement list) {
    var delete = li.querySelector('div.link.delete');
    delete.onClick.listen((_) async {
      var confirm = await dialogContainer.confirm("Er du sikker på at du vil slette siden ${page.title}?").result;
      if (!confirm) {
        return;
      }
      list.classes.add('blur');
      await page.delete();
      list.classes.remove('blur');
    });
  }


  void _updater(Page page, LIElement li) {
    li
      ..dataset['id'] = page.id
      ..dataset['template'] = page.template
      ..dataset['title'] = page.title
      ..dataset['hidden'] = page.hidden ? 'true' : 'false'
      ..classes.toggle('current', pageOrder.currentPage == page)
      ..classes.toggle('ishidden', page.hidden);
    var a = li.querySelector('a');
    a.text = page.title;
  }

  void _activeDragHandler(Page page, LIElement li) {
    var index;
    li
      ..draggable = true
      ..onDragStart.listen((_) {
      li.classes.add('dragging');
      index = li.parent.children.indexOf(li);
    })
      ..onDragEnd.listen((_) async {
      li.classes.remove('dragging');
      var new_index = li.parent.children.indexOf(li);
      if (index == new_index) {
        return;
      }
      _activeList.classes.add('blur');
      await pageOrder.changePageOrder(page, place:new_index, parent:_showing_page);
      _activeList.classes.remove('blur');
    })
      ..onDragEnter.listen((_) => li.classes.add('drag_over'))
      ..onDragLeave.listen((_) => li.classes.remove('drag_over'))
      ..onDragOver.listen((MouseEvent event) {
      var dragging = li.parent.querySelector("li.dragging");
      if (dragging == li) {
        return;
      }
      li.insertAdjacentElement(event.offset.y < li.client.height / 2 ? 'beforeBegin' : 'afterEnd', dragging);
    });
  }
}
