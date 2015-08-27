part of user_settings;


class UserSettingsPageUserListFormInitializer extends core.Initializer {

  UserLibrary _userLib;

  PageOrder _order;

  UListElement _pageUserList = querySelector('#PageUserList');

  FormElement _addUserToPageForm = querySelector('#AddUserToPageForm');

  SelectElement _pageUserSelect = querySelector('#EditPageAddUserSelect');

  UserSettingsPageUserListFormInitializer(UserLibrary this._userLib, PageOrder this._order);

  bool get canBeSetUp => _pageUserList != null && _order.currentPage != null;

  LIElement createUserLi(User user) {
    var li = new LIElement();
    li.innerHtml = "<span class='val'>${user.username}</span><div class='delete link' title='Slet'>&nbsp;</div>";
    return li;
  }

  void _addListener(LIElement li) {
    var val = li.querySelector('.val');
    var delete = li.querySelector('.delete');
    var username = val == null ? li.text : val.text;
    var user = _userLib.users[username];
    user.onChange.listen((User u) {
      if (val == null) {
        li.text = u.username;
      } else {
        val.text = u.username;
      }
    });
    if (delete == null) {
      return;
    }
    delete.onClick.listen((MouseEvent e) {
      var dialogResult = new DialogContainer().confirm("Er du sikker på at du vil fjerne privilegierne?").result;
      dialogResult.then((bool b) {
        if (!b) {
          return;
        }
        var i = savingBar.startJob();
        user.revokePagePrivilege(_order.currentPage).then((core.Response response) {
          if (response.type == core.Response.RESPONSE_TYPE_SUCCESS) {
            li.remove();
            var opt = new OptionElement();
            opt.text = opt.value = username;
            _pageUserSelect.append(opt);
          }
          savingBar.endJob(i);
        });
      });
    });
  }

  void setUp() {
    var pageUserLis = _pageUserList.querySelectorAll('li');


    _userLib.onChange.listen((UserLibraryChangeEvent evt) {
      var changeType = evt.type, user = evt.user;
      if (changeType == UserLibraryChangeEvent.CHANGE_CREATE) {
        if (user.privileges != User.PRIVILEGE_PAGE) {
          var li = new LIElement();
          li.text = user.username;
          _addListener(li);
          _pageUserList.append(li);
          pageUserLis = _pageUserList.querySelectorAll('li');
        } else if (user.pages.contains(_order.currentPage)) {
          var li = createUserLi(user);
          _addListener(li);
          _pageUserList.append(li);
          pageUserLis = _pageUserList.querySelectorAll('li');

        } else if (_pageUserSelect != null) {
          var opt = new OptionElement();
          opt.text = opt.value = user.username;
          _pageUserSelect.append(opt);
        }
      } else {
        pageUserLis.forEach((LIElement li) {
          if ((li.querySelector('.val') == null ? li.text : li.querySelector('.val').text) == user.username) {
            li.remove();
          }
        });
        _pageUserSelect.options.forEach((OptionElement o) {
          if (o.value == user.username) {
            o.remove();
          }
        });
      }
    });

    pageUserLis.forEach(_addListener);
    if (_addUserToPageForm == null) {
      return;
    }
    new Validator(_pageUserSelect).addNonEmptyValueValidator();
    new ValidatingForm(_addUserToPageForm).validate();
    var deco = new FormHandler(_addUserToPageForm);
    deco.submitFunction = (Map<String, String> data) {
      if (_addUserToPageForm.classes.contains('initial')) {
        return false;
      }
      deco.blur();
      var user = _userLib.users[data['username']];
      user.addPagePrivilege(_order.currentPage).then((core.Response response) {
        if (response.type == core.Response.RESPONSE_TYPE_ERROR) {
          deco.unBlur();
          return false;
        }
        _pageUserSelect.selectedOptions.first.remove();
        new BetterSelect(_pageUserSelect).update();
        var newLi = createUserLi(user);
        _pageUserList.append(newLi);
        pageUserLis = _pageUserList.querySelectorAll('li');
        _addListener(newLi);
        deco.unBlur();
        deco.validatingForm.validate();
      });

      return false;
    };

  }
}


class UserSettingsEditPageFormInitializer extends core.Initializer {
  PageOrder _order;

  FormElement _editPageForm = querySelector('#EditPageForm');


  UserSettingsEditPageFormInitializer(PageOrder this._order);


  bool get canBeSetUp => _editPageForm != null;

  void setUp() {

    if (_editPageForm == null) {
      return;
    }
    var validatingForm = new ValidatingForm(_editPageForm);

    var editIdField = _editPageForm.querySelector('#EditPageEditIDField'),
    editAliasField = _editPageForm.querySelector('#EditPageEditAliasField'),
    editTitleField = _editPageForm.querySelector('#EditPageEditTitleField');

/* SET UP VALIDATOR */

    new Validator(editTitleField)
      ..addNonEmptyValueValidator()
      ..errorMessage = "Titlen kan ikke være tom";

    new Validator(editIdField)
      ..addValueValidator((String value) =>
    (_order.currentPage != null && value == _order.currentPage.id) ||
    (new RegExp(r'^[0-9a-z\-_]+$', caseSensitive:false).hasMatch(value) && _order[value] != null))
      ..errorMessage = "ID kan kun indeholde symbolder a-z, 0-9, - eller _";
    new Validator(editAliasField)
      ..addValueValidator((String value) => value == "" || PCRE.checkPCRE(value))
      ..errorMessage = "Alias skal være et gyldig <i>PCRE</i>";

    validatingForm.validate();

/* SET UP DECORATION AND SUBMIT */

    var formDecoration = new FormHandler(_editPageForm);
    formDecoration.submitFunction = (Map<String, String> data) {
      if (_editPageForm.classes.contains('initial')) {
        return false;
      }
      formDecoration.blur();
      var template = _order.currentPage.template;
      _order.currentPage.changeInfo(title:data['title'], id:data['id'], template:data['template'], alias:data['alias']).then((core.Response<Page> response) {
        if (response.type == core.Response.RESPONSE_TYPE_SUCCESS) {
          formDecoration.changeNotion("Ændringerne er gemt", FormHandler.NOTION_TYPE_SUCCESS);
          editTitleField.blur();
          editIdField.blur();
          editAliasField.blur();
          if (template != _order.currentPage.template) {
            dialogContainer.alert("Du har redigeret sidens type og skal derfor genindlæse siden.").onClose.listen((_) => window.location.reload());
          }

        } else {
          formDecoration.changeNotion(_errorMessage(response.error_code), FormHandler.NOTION_TYPE_ERROR);
        }
        formDecoration.unBlur();

      });
      return false;
    };

  }
}


