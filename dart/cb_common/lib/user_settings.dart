library user_settings;

import "dart:html";
import "dart:math" as Math;
import "dart:async";

import 'initializers.dart';
import 'site_classes.dart';
import 'json.dart';
import 'core.dart' as core;
import 'elements.dart';

import 'pcre_syntax_checker.dart' as PCRE;


part 'src/user_settings_page_order.dart';
part 'src/user_settings_user_library.dart';
part 'src/user_settings_decoration.dart';
part 'src/user_settings_page_li.dart';

bool get pageOrderAvailable => querySelector("#ActivePageList") != null && querySelector("#InactivePageList") != null;

bool get userLibraryAvailable => querySelector('#UserList') != null;


PageOrder get pageOrder => pageOrderAvailable ? new UserSettingsJSONPageOrder() : null;

UserLibrary get userLibrary => userLibraryAvailable && pageOrderAvailable ? new UserSettingsJSONUserLibrary() : null;

String _errorMessage(int error_code) {
  switch (error_code) {
    case core.Response.ERROR_CODE_PAGE_NOT_FOUND:
      return "Siden blev ikke fundet";
    case core.Response.ERROR_CODE_INVALID_PAGE_ID:
      return "Ugyldigt side id";
    case core.Response.ERROR_CODE_INVALID_PAGE_ALIAS:
      return "Ugyldigt side alias";
    case core.Response.ERROR_CODE_UNAUTHORIZED:
      return "Du har ikke de nødvendige rettigheder";
    case core.Response.ERROR_CODE_INVALID_MAIL:
      return "Ugyldig mail-adresse";
    case core.Response.ERROR_CODE_INVALID_USER_NAME:
      return "Ugyldig brugernavn";
    case core.Response.ERROR_CODE_WRONG_PASSWORD:
      return "Forkert kodeord";
    case core.Response.ERROR_CODE_INVALID_PASSWORD:
      return "Ugyldigt kodeord";
    default:
      return null;
  }
}

class UserSettingsInitializer extends core.Initializer {

  core.InitializerLibrary _initLib;

  UserSettingsInitializer(this._initLib);


  bool get canBeSetUp => pageOrderAvailable && userLibraryAvailable;

  void setUp() {

    new core.KeepAlive().start();

    var client = new AJAXJSONClient();
    var order = pageOrder, userLib = userLibrary;

    _initLib.registerInitializer(new TitleURLUpdateInitializer(order, client));
    _initLib.registerInitializer(new UserSettingsDecorationInitializer());
    _initLib.registerInitializer(new UserSettingsPageListsInitializer(order));
    _initLib.registerInitializer(new UserSettingsEditPageFormInitializer(order));
    _initLib.registerInitializer(new UserSettingsChangeUserInfoFormInitializer(userLib));
    _initLib.registerInitializer(new UserSettingsAddPageFormInitializer(order));
    _initLib.registerInitializer(new UserSettingsUserListInitializer(userLib));
    _initLib.registerInitializer(new UserSettingsAddUserFormInitializer(userLib));
    _initLib.registerInitializer(new UserSettingsPageUserListFormInitializer(userLib, order));
    _initLib.registerInitializer(new UserSettingsUpdateSiteInitializer());
    _initLib.registerInitializer(new UserSettingsLoggerInitializer());

/* SET UP LOGIN USER MESSAGE*/
    var loginUserMessage = querySelector('#LoginUserMessage');
    var i = loginUserMessage.querySelector('i');
    userLibrary.userLoggedIn.onChange.listen((User u) {
      i.text = u.username;
    });

  }

}

class UserSettingsLoggerInitializer extends core.Initializer {

  TableElement _logTable = querySelector("#UserSettingsLogTable");

  AnchorElement _logLink = querySelector("#ClearLogLink");

  ParagraphElement _pElm = querySelector("#LogInfoParagraph");


  DivElement _numDiv = new DivElement();


  UserSettingsLoggerInitializer() {
    _numDiv.classes.add("num");
  }

  bool get canBeSetUp => _logTable != null && _logLink != null && _pElm != null;

  void _updateNum() {
    if (_logTable.classes.contains("empty")) {
      _numDiv.remove();
      return;
    }
    _numDiv.text = _logTable.querySelectorAll("tr:not(.empty_row)").length.toString();
    var p = querySelector("#UserSettingsMenu .log");
    p.append(_numDiv);

  }

  void setUp() {
    _updateNum();
    _logTable.querySelectorAll(".dumpfile a").forEach((AnchorElement a) {
      a.onClick.listen((MouseEvent evt) {
        var loader = dialogContainer.loading("Henter log filen");
        ajaxClient.callFunctionString("Logger.getContextAt(${a.dataset["id"]})").then((JSONResponse resp) {
          if (resp.type != core.Response.RESPONSE_TYPE_SUCCESS) {
            loader.close();
            return;
          }
          var button = new ButtonElement(), pre = new PreElement();
          button.text = "Luk";
          button.onClick.listen((_) => loader.close());
          pre.classes.add("code");
          pre.text = resp.payload;
          loader.element
            ..children.clear()
            ..append(pre)
            ..append(button);
          loader.stopLoading();
          core.escQueue.add(() {
            loader.close();
            return true;
          });
        });
      });
    });


    _logLink.onClick.listen((MouseEvent evt) {
      ajaxClient.callFunctionString("Logger.clearLog()").then((JSONResponse response) {
        if (response.type == core.Response.RESPONSE_TYPE_SUCCESS) {
          _logTable.querySelectorAll("tr:not(.empty_row)").forEach((TableRowElement li) => li.remove());
          _logTable.classes.add("empty");
          _pElm.querySelectorAll("i").forEach((Element e) => e.text = "0");
          _updateNum();
        }
      });
      evt.preventDefault();
    });

  }
}


class UserSettingsUpdateSiteInitializer extends core.Initializer {
  ButtonElement _checkButton = querySelector("#UserSettingsContent button.update_check");

  SpanElement _checkTime = querySelector("#UserSettingsContent .update_site span.check_time");

  JSONClient _client = new AJAXJSONClient();

  DivElement _updateInformationMessage = querySelector("#UpdateInformationMessage");

  bool _canBeUpdated;

  bool get canBeSetUp => _checkButton != null && _checkTime != null && _updateInformationMessage != null;

  void setUp() {
    _canBeUpdated = !_updateInformationMessage.hidden;


    _checkButton.onClick.listen((_) {
      _updateCheckButton(true);
      if (!_canBeUpdated) {

        _client.callFunctionString("Updater.checkForUpdates()").then((JSONResponse response) {
          _checkTime.text = core.dateString(new DateTime.now());
          if (response.type != core.Response.RESPONSE_TYPE_SUCCESS) {
            _canBeUpdated = false;
            _updateCheckButton();
            return;
          }
          if (response.payload) {
            _canBeUpdated = true;
            dialogContainer.confirm("<b>Hjemmesiden kan opdateres! </b><br /> Hvis du opdaterer nu, skal siden genstartes, og det er derfor vigtigt at du gemmer alle ændringer du må have foretaget. <br /> Ønsker du at opdatere det nu?").result.then((bool b) {
              if (!b) {
                return;
              }
              _updateSite();
            });

          } else {
            _canBeUpdated = false;
            dialogContainer.alert("Der blev ikke fundet nogen opdatering.<br /> Prøv igen senere.");
          }
          _updateCheckButton();
        });

      } else {
        _updateSite();
      }

    });
    _updateInformationMessage.querySelector("a").onClick.listen((_) => _updateSite());
  }

  void _updateCheckButton([bool searching = false]) {
    if (searching) {
      _checkButton.disabled = true;
      if (_canBeUpdated) {
        _checkButton.text = _checkButton.dataset["workUpdateValue"];
      } else {
        _checkButton.text = _checkButton.dataset["workCheckValue"];
      }
      return;
    }
    _checkButton.disabled = false;
    if (_canBeUpdated) {
      _updateInformationMessage.hidden = false;
      _checkButton.text = _checkButton.dataset["updateValue"];
    } else {
      _updateInformationMessage.hidden = true;
      _checkButton.text = _checkButton.dataset["checkValue"];
    }

  }

  void _updateSite() {
    _updateCheckButton(true);
    var updateDone = false;
    window.onBeforeUnload.listen((BeforeUnloadEvent event) {
      if (updateDone) {
        return;
      }
      event.returnValue = "Websitet er i gang med at opdatere.";
    });

    var loader = dialogContainer.loading("Opdaterer websitet.<br />Luk ikke din browser!");
    _client.callFunctionString("Updater.update()").then((JSONResponse response) {
      if (response.type == core.Response.RESPONSE_TYPE_ERROR) {
        loader.close();
        _updateCheckButton();
        return;
      }
      _canBeUpdated = false;
      loader.element
        ..innerHtml = "Siden er opdateret.<br /> Hjemmesiden genindlæses.";
      loader.stopLoading();
      updateDone = true;
      _updateCheckButton();
      var t = new Timer(new Duration(seconds:1), () {
        window.location.reload();
      });
    });

  }


}

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
        user.revokePagePrivilege(_order.currentPage).then((ChangeResponse response) {
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
    new Validator(_pageUserSelect).validator = (SelectElement e) => core.nonEmpty(e.value);
    new ValidatingForm(_addUserToPageForm).validate();
    var deco = new FormHandler(_addUserToPageForm);
    deco.submitFunction = (Map<String, String> data) {
      if (_addUserToPageForm.classes.contains('initial')) {
        return false;
      }
      deco.blur();
      var user = _userLib.users[data['username']];
      user.addPagePrivilege(_order.currentPage).then((ChangeResponse response) {
        if (response.type == core.Response.RESPONSE_TYPE_ERROR) {
          deco.unBlur();
          return false;
        }
        _pageUserSelect.selectedOptions.first.remove();
        _pageUserSelect.dispatchEvent(new Event('change'));
        var newLi = createUserLi(user);
        _pageUserList.append(newLi);
        pageUserLis = _pageUserList.querySelectorAll('li');
        _addListener(newLi);
        deco.unBlur();
      });

      return false;
    };

  }
}


class UserSettingsUserListInitializer extends core.Initializer {
  UserLibrary _userLib;

  UListElement _userList = querySelector('#UserList');

  UserSettingsUserListInitializer(UserLibrary this._userLib);

  bool get canBeSetUp => _userList != null;

  String _userPrivilegeString(User u, [bool simple=false]) => u.privileges == User.PRIVILEGE_ROOT ? (simple ? "root" : "Root") : (u.privileges == User.PRIVILEGE_SITE ? (simple ? "site" : "Website") : (simple ? "page" : "Side"));

  void _setDataset(LIElement li, User user) {
    li.dataset["username"] = user.username;
    li.dataset["privileges"] = _userPrivilegeString(user, true);
    li.dataset["lastLogin"] = user.lastLogin == null ? "" : user.lastLogin.millisecondsSinceEpoch ~/ 1000;
    li.dataset["mail"] = user.mail;
    li.dataset["pages"] = user.pages.map((Page p) => p.id).join(" ");
  }

  void _addListener(LIElement li) {
    var val = li.querySelector('.val'), privileges = li.querySelector('.privileges');
    var username = li.dataset['username'];
    var user = _userLib.users[username];
    user.onChange.listen((User u) {
      val.text = u.username;
      val.href = "mailto:${u.mail}";
      privileges.text = "(${_userPrivilegeString(u)} Administrator)";
      _setDataset(li, u);
    });
    var loginString = user.lastLogin == null?"Aldrig":core.dateString(user.lastLogin);
    var infoBox = new InfoBox("Sidst set: $loginString");
    var time = li.querySelector('.time');
    infoBox.backgroundColor = InfoBox.COLOR_BLACK;
    time.onMouseOver.listen((_) => infoBox.showAboveCenterOfElement(time));
    time.onMouseOut.listen((_)=>infoBox.remove());

    var delete = li.querySelector('.delete');
    if (delete == null) {
      return;
    }
    delete.onClick.listen((MouseEvent e) {
      var dialog = new DialogContainer();
      dialog.confirm("Er du sikker på at du vil slette denne bruger?").result.then((bool b) {
        if (!b) {
          return;
        }
        var i = savingBar.startJob();
        _userLib.deleteUser(username).then((_) => savingBar.endJob(i));
      });
    });


  }

  void setUp() {


    var userLis = _userList.querySelectorAll('li');
    userLis.forEach(_addListener);
    _userLib.onChange.listen((UserLibraryChangeEvent evt) {
      var changeType = evt.type, user = evt.user;
      if (changeType == UserLibraryChangeEvent.CHANGE_CREATE) {
        var li = new LIElement();
        var privilege = _userPrivilegeString(user);
        var a = new AnchorElement();
        a
          ..text = user.username
          ..classes.add("val")
          ..href = "mailto:${user.mail}";
        li.append(a);
        li.appendHtml(", <span class='privileges'>($privilege Administrator)</span> <div class='delete link' title='Slet'>&nbsp;</div><div class='time link'>&nbsp;</div>");
        _setDataset(li, user);


        _userList.append(li);
        userLis = _userList.querySelectorAll('li');
        _addListener(li);
      } else {
        userLis.forEach((LIElement li) {
          if (li.querySelector('.val').text == user.username)li.remove();
        });
      }
    });

  }
}


class UserSettingsAddUserFormInitializer extends core.Initializer {
  UserLibrary _userLib;

  FormElement _addUserForm = querySelector('#EditUsersAddUserForm');

  UserSettingsAddUserFormInitializer(UserLibrary this._userLib);

  bool get canBeSetUp => _addUserForm != null;

  void setUp() {
    var userMailField = _addUserForm.querySelector('#AddUserMailField'), userLevelSelect = _addUserForm.querySelector('#AddUserLevelSelect');
    var v = new Validator(userMailField);
    v.validator = (InputElement e) => core.validMail(e.value);
    v.errorMessage = "Skal være gyldig E-mail";
    new Validator(userLevelSelect).validator = (SelectElement e) => core.nonEmpty(e.value);
    var validatingForm = new ValidatingForm(_addUserForm);
    validatingForm.validate();
    var decoration = new FormHandler(_addUserForm);
    decoration.submitFunction = (Map<String, String> data) {
      if (_addUserForm.classes.contains('initial')) {
        return false;
      }
      decoration.blur();
      _userLib.createUser(data['mail'], data['level']).then( (ChangeResponse response) {
        if (response.type == core.Response.RESPONSE_TYPE_SUCCESS) {
          userMailField.value = "";
          validatingForm.validate();
          userMailField.blur();
        }
        decoration.unBlur();
      });
      return false;
    };
  }
}


class UserSettingsAddPageFormInitializer extends core.Initializer {
  PageOrder _order;

  FormElement _addPageForm = querySelector("#EditPagesForm");

  UserSettingsAddPageFormInitializer(PageOrder this._order);

  bool get canBeSetUp => _addPageForm != null;

  void setUp() {
    var input = querySelector('#EditPagesAddPage'), v;
    (v = new Validator(input)).validator = (InputElement e) => core.nonEmpty(e.value);
    v.errorMessage = "Titlen må ikke være tom";
    var validatingForm = new ValidatingForm(_addPageForm);
    validatingForm.validate();
    var decoration = new FormHandler(_addPageForm);
    decoration.submitFunction = (Map<String, String> data) {
      if (_addPageForm.classes.contains('initial')) {
        return false;
      }

      decoration.blur();
      _order.createPage(data['title']).then((ChangeResponse response) {
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


class UserSettingsChangeUserInfoFormInitializer extends core.Initializer {
  UserLibrary _userLibrary;

  FormElement _userMailForm = querySelector('#UpdateUsernameMailForm'), _userPasswordForm = querySelector('#UpdatePasswordForm');


  UserSettingsChangeUserInfoFormInitializer(UserLibrary this._userLibrary);

  bool get canBeSetUp => _userMailForm != null && _userPasswordForm != null;

  void setUp() {
    var userNameInput = _userMailForm.querySelector('#EditUserEditUsernameField'), userMailInput = _userMailForm.querySelector('#EditUserEditMailField');
    var v1 = new Validator(userNameInput), v2 = new Validator(userMailInput);
    v1.validator = (InputElement e) => core.nonEmpty(e.value) && (e.value == userLibrary.userLoggedIn.username || userLibrary.users[e.value] == null);
    v1.errorMessage = "Brugernavn må ikke være tomt og skal være unikt";

    v2.validator = (InputElement e) => core.validMail(e.value);
    v2.errorMessage = "Skal være gyldig E-mail adresse";

    var validatingForm = new ValidatingForm(_userMailForm);
    validatingForm.validate(true);
    var decoForm = new FormHandler(_userMailForm);
    decoForm.submitFunction = (Map<String, String> data) {
      if (_userMailForm.classes.contains('initial')) {
        return false;
      }
      decoForm.blur();
      userLibrary.userLoggedIn.changeInfo(username:data['username'], mail:data['mail']).then((ChangeResponse response) {
        if (response.type == core.Response.RESPONSE_TYPE_ERROR) {
          var m;
          decoForm.changeNotion((m = _errorMessage(response.error_code)) != null ? m : "Ukendt fejl", FormHandler.NOTION_TYPE_ERROR);
        } else {
          decoForm.changeNotion("Ændringerne er gemt", FormHandler.NOTION_TYPE_SUCCESS);
          userNameInput.blur();
          userMailInput.blur();
          validatingForm.validate();
        }
        decoForm.unBlur();
      });

      return false;
    };

    var userOldPassword = _userPasswordForm.querySelector('#EditUserEditPasswordOldField'), userNewPassword = _userPasswordForm.querySelector('#EditUserEditPasswordNewField'), userNewPasswordRepeat = _userPasswordForm.querySelector('#EditUserEditPasswordNewRepField');
    var v3 = new Validator(userOldPassword), v4 = new Validator(userNewPassword), v5 = new Validator(userNewPasswordRepeat);
    v3.validator = (InputElement e) => core.nonEmpty(e.value);
    v3.errorMessage = v4.errorMessage = "Kodeord må ikke være tomt";
    v4.validator = (InputElement e) => core.nonEmpty(e.value);
    v5.validator = (InputElement e) => core.nonEmpty(e.value) && e.value == userNewPassword.value;
    v5.errorMessage = "Kodeordet skal gentages korrekt";
    var valPassForm = new ValidatingForm(_userPasswordForm);
    valPassForm.validate();
    var decoPassForm = new FormHandler(_userPasswordForm);
    decoPassForm.submitFunction = (Map<String, String> data) {
      if (_userPasswordForm.classes.contains('initial')) {
        return false;
      }
      decoPassForm.blur();
      userLibrary.userLoggedIn.changePassword(data['old_password'], data['new_password']).then((ChangeResponse response) {
        if (response.type == core.Response.RESPONSE_TYPE_ERROR) {
          var m;
          decoPassForm.changeNotion((m = _errorMessage(response.error_code)) != null ? m : "Ukendt fejl", FormHandler.NOTION_TYPE_ERROR);
        } else {
          decoPassForm.changeNotion("Dit kodeord er ændret", FormHandler.NOTION_TYPE_SUCCESS);
          userNewPassword.value = userOldPassword.value = userNewPasswordRepeat.value = "";
          userNewPassword.blur();
          userOldPassword.blur();
          userNewPasswordRepeat.blur();
          valPassForm.validate();
        }
        decoPassForm.unBlur();
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
    var submitButton = _editPageForm.querySelector('input[type=submit]');
    var validatingForm = new ValidatingForm(_editPageForm);

    var editIdField = _editPageForm.querySelector('#EditPageEditIDField'), editAliasField = _editPageForm.querySelector('#EditPageEditAliasField'), editTitleField = _editPageForm.querySelector('#EditPageEditTitleField'), editTemplateSelect = _editPageForm.querySelector('#EditPageEditTemplateSelect');

/* SET UP VALIDATOR */
    var v1, v2, v3;
    (v1 = new Validator(editTitleField)).validator = (InputElement e) => core.nonEmpty(e.value);
    (v2 = new Validator(editIdField)).validator = (InputElement e) => (_order.currentPage != null && e.value == _order.currentPage.id) || (new RegExp(r'^[0-9a-z\-_]+$', caseSensitive:false).hasMatch(e.value) && !_order.pageExists(e.value));
    (v3 = new Validator(editAliasField)).validator = (InputElement e) => e.value == "" || PCRE.checkPCRE(e.value);

    v1.errorMessage = "Titlen kan ikke være tom";
    v2.errorMessage = "ID kan kun indeholde symbolder a-z, 0-9, - eller _";
    v3.errorMessage = "Alias skal være et gyldig <i>PCRE</i>";

    validatingForm.validate();

/* SET UP DECORATION AND SUBMIT */

    var formDecoration = new FormHandler(_editPageForm);
    formDecoration.submitFunction = (Map<String, String> data) {
      if (_editPageForm.classes.contains('initial')) {
        return false;
      }
      formDecoration.blur();
      var template = _order.currentPage.template;
      _order.currentPage.changeInfo(title:data['title'], id:data['id'], template:data['template'], alias:data['alias']).then((ChangeResponse<Page> response) {
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


class UserSettingsPageListsInitializer extends core.Initializer {

  UListElement _activeList = querySelector("#ActivePageList"), _inactiveList = querySelector("#InactivePageList");

  PageOrder _order;

  UserSettingsPageListsInitializer(PageOrder this._order);

  bool get canBeSetUp => _activeList != null && _inactiveList != null;

  void setUp() {
    _activeList.querySelectorAll('li:not(.emptyListInfo)').forEach((LIElement element) {
      var e = new UserSettingsPageLi(element);
      e.setUp();
    });
    _inactiveList.querySelectorAll('li:not(.emptyListInfo)').forEach((LIElement element) {
      var e = new UserSettingsPageLi(element);
      e.setUp();
    });

    var updateListInfo = (UListElement ul, [bool active = true]) {
      var len = ul.querySelectorAll('li').length;
      if (len == 0) {
        var li = new LIElement();
        li.classes.add('emptyListInfo');
        li.text = active ? "Der er ingen aktive sider" : "Der er ingen inaktive sider";
        ul.append(li);
      } else if (len > 1 && ul.children.any((Element e) => e.classes.contains('emptyListInfo'))) {
//      ul.children.removeWhere((Element e) => e.classes.contains('emptyListInfo'));
//Fix until above works again
        ul.children.toList().forEach((Element e) {
          if (e.classes.contains('emptyListInfo')) e.remove();
        });
      }
    };

    var ULChangeListener = (UListElement ul, String parent) => (Event e) {
      var newOrder = [];
      ul.children.forEach((LIElement li) {
        var pageLi = new UserSettingsPageLi(li);
        newOrder.add(pageLi.page.id);
      });
      var i = savingBar.startJob();
      _order.changePageOrder(newOrder, parent_id:parent).then((_) => savingBar.endJob(i));
      e.stopPropagation();
    };
    _activeList.onChange.listen(ULChangeListener(_activeList, null));
    _activeList.querySelectorAll('ul').forEach((UListElement ul) => ul.onChange.listen(ULChangeListener(ul, new UserSettingsPageLi(ul.parent).page.id)));

    _order.onUpdate.listen((PageOrderChange change) {
      var page = change.page, changeType = change.type;
      switch (changeType) {
        case PageOrderChange.PAGE_ORDER_CHANGE_CREATE_PAGE:
          var pageLi = new UserSettingsPageLi.fromPage(page);
          pageLi.updateActive();
          _inactiveList.append(pageLi.li);
          updateListInfo(_inactiveList, false);

          break;
        case PageOrderChange.PAGE_ORDER_CHANGE_DELETE_PAGE:case PageOrderChange.PAGE_ORDER_CHANGE_DEACTIVATE:
          if (changeType == PageOrderChange.PAGE_ORDER_CHANGE_DELETE_PAGE) {
            var pageLi = new UserSettingsPageLi.fromPage(page);
            var parent = pageLi.li.parent;
            if (parent == _inactiveList) {
              pageLi.li.remove();
              updateListInfo(_inactiveList, false);
              break;
            }
            new ChangeableList(pageLi.li.parent);
            pageLi.li.remove();
            updateListInfo(parent);
          }

          _inactiveList.children.clear();

          var path = new UserSettingsActivePagesPath();
          var l = _order.inactivePages;

          l.sort((Page p1, Page p2) => p1.id.compareTo(p2.id));
          l.forEach((Page p) {
            var pageLi = new UserSettingsPageLi.fromPage(p);
            pageLi.updateActive();
            _inactiveList.append(pageLi.li);
          });
          var ulToUpdate = path.currentlyShowing == null ? _activeList : new UserSettingsPageLi.fromPage(path.currentlyShowing).li.querySelector('ul');

          updateListInfo(_inactiveList);
          updateListInfo(ulToUpdate);
          break;
        case PageOrderChange.PAGE_ORDER_CHANGE_ACTIVATE:
          var path = new UserSettingsActivePagesPath();
          var showingPage = path.currentlyShowing;
          path.reset();
          var recursiveBuilder;
          recursiveBuilder = (String parent, UListElement parentUl) {
            _order.listPageOrder(parent_id:parent).forEach((Page p) {
              var ul = new UListElement();
              var pageLi = new UserSettingsPageLi.fromPage(p);
              ul.classes
                ..add('colorList')
                ..add('draggable');
              ul.onChange.listen(ULChangeListener(ul, p.id));
              pageLi.updateActive();
//            pageLi.li.children.removeWhere((Element e) => e is UListElement);
//Fix until above works again
              pageLi.li.children.toList().forEach((Element e) {
                if (e is UListElement) e.remove();
              });

              pageLi.li.append(ul);
              new ChangeableList(parentUl).append(pageLi.li);
              recursiveBuilder(p.id, ul);
            });
            updateListInfo(parentUl);
          };

          _activeList.children.clear();
          recursiveBuilder(null, _activeList);
          updateListInfo(_inactiveList, false);
          if (showingPage != null) {
            path.showSubMenu(showingPage.id);
          }
          break;
      }
    });
  }

}


class UserSettingsDecorationInitializer extends core.Initializer {
  var _expandLink = querySelector("#UserSettingsExpandLink"), _contractLink = querySelector("#UserSettingsContractLink"), _container = querySelector("#UserSettingsContainer"), _slideElement = querySelector("#UserSettingsContent > ul"), _slideMenuList = querySelector("#UserSettingsMenu > ul");


  bool get canBeSetUp => _expandLink != null && _contractLink != null && _container != null && _slideElement != null && _slideMenuList != null;

  void setUp() {
    var expander = new UserSettingsExpandDecoration();
    _expandLink.onClick.listen((_) {
      expander.expand();
      core.escQueue.add(() {
        if (!expander._expanded) {
          return false;
        }
        var f = _container.querySelector(':focus');
        if (f != null) {
          f.blur();
        }
        expander.contract();
        return true;
      });
    });
    _contractLink.onClick.listen((_) => expander.contract());


    var linkExpander = new UserSettingsExpandLinkExpandDecoration(_expandLink);
    linkExpander.expandOnMouseOver = linkExpander.contractOnMouseOut = true;


    var slider = new UserSettingsSlideDecoration();
    var lis = _slideMenuList.querySelectorAll('ul > li');
    var i = 0;
    lis.forEach((LIElement li) {
      var index = i;
      li.onClick.listen((e) {
        slider.goToIndex(index);
        _slideMenuList.querySelector('.active').classes.remove('active');
        li.classes.add('active');
      });
      i++;
    });
  }
}