library user_settings;

import "dart:html";
import "dart:math" as Math;
import "dart:async";

import 'site_classes.dart';
import 'json.dart';
import 'core.dart';
import 'elements.dart';
import 'json.dart' ;
import 'pcre_syntax_checker.dart' as PCRE;

part 'src/user_settings_page_order.dart';
part 'src/user_settings_user_library.dart';
part 'src/user_settings_decoration.dart';
part 'src/user_settings_page_li.dart';

bool get pageOrderAvailable => query("#ActivePageList") != null && query("#InactivePageList") != null;

bool get userLibraryAvailable => query('#UserList') != null;

String dayNumberToName(int weekday) {
  var ret;
  switch (weekday) {
    case 1:
      ret = "mandag";
      break;
    case 2:
      ret = "tirsdag";
      break;
    case 3:
      ret = "onsdag";
      break;
    case 4:
      ret = "torsdag";
      break;
    case 5:
      ret = "fredag";
      break;
    case 6:
      ret = "lørdag";
      break;
    case 7:
      ret = "søndag";
      break;
  }
  return ret;
}

String monthNumberToName(int monthNumber) {
  var ret;
  switch (monthNumber) {
    case 1:
      ret = "januar";
      break;
    case 2:
      ret = "februar";
      break;
    case 3:
      ret = "marts";
      break;
    case 4:
      ret = "april";
      break;
    case 5:
      ret = "maj";
      break;
    case 6:
      ret = "juni";
      break;
    case 7:
      ret = "juli";
      break;
    case 8:
      ret = "august";
      break;
    case 9:
      ret = "september";
      break;
    case 10:
      ret = "oktober";
      break;
    case 11:
      ret = "november";
      break;
    case 12:
      ret = "december";
      break;
  }
  return ret;
}

String addLeadingZero(int i) => i < 10 ? "0$i" : "$i";

String dateString(DateTime dt) {
  var now = new DateTime.now();

  var returnString = "";
  if (now.day != dt.day || now.month != dt.month || now.year != dt.year) {
    returnString = "${dayNumberToName(dt.weekday)} ";
  } else {
    returnString = "i dag ";
  }

  if (dt.difference(now).inDays > 7) {
    returnString += "d. ${dt.day}. ${monthNumberToName(dt.month)} ${dt.year} ";
  }

  returnString += "kl. ${addLeadingZero(dt.hour)}:${addLeadingZero(dt.minute)}";

  return returnString.trim();
}

PageOrder get pageOrder => pageOrderAvailable ? new UserSettingsJSONPageOrder.initializeFromMenu(query("#ActivePageList"), query("#InactivePageList")) : null;

UserLibrary get userLibrary => userLibraryAvailable && pageOrderAvailable ? new UserSettingsJSONUserLibrary.initializeFromMenu(query('#UserList')) : null;

String _errorMessage(int error_code) {
  switch (error_code) {
    case JSONResponse.ERROR_CODE_PAGE_NOT_FOUND:
      return "Siden blev ikke fundet";
    case JSONResponse.ERROR_CODE_INVALID_PAGE_ID:
      return "Ugyldigt side id";
    case JSONResponse.ERROR_CODE_INVALID_PAGE_ALIAS:
      return "Ugyldigt side alias";
    case JSONResponse.ERROR_CODE_UNAUTHORIZED:
      return "Du har ikke de nødvendige rettigheder";
    case JSONResponse.ERROR_CODE_INVALID_USER_MAIL:
      return "Ugyldig mail-adresse";
    case JSONResponse.ERROR_CODE_INVALID_USER_NAME:
      return "Ugyldig brugernavn";
    case JSONResponse.ERROR_CODE_WRONG_PASSWORD:
      return "Forkert kodeord";
    case JSONResponse.ERROR_CODE_INVALID_PASSWORD:
      return "Ugyldigt kodeord";
    default:
      return null;
  }
}

class UserSettingsInitializer extends Initializer {

  InitializerLibrary _initLib;

  UserSettingsInitializer(this._initLib);


  bool get canBeSetUp => pageOrderAvailable && userLibraryAvailable;

  void setUp() {

    new KeepAlive().start();

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

/* SET UP LOGIN USER MESSAGE*/
    var loginUserMessage = query('#LoginUserMessage');
    var i = loginUserMessage.query('i');
    userLibrary.userLoggedIn.onChange.listen((User u) {
      i.text = u.username;
    });

  }

}


class UserSettingsUpdateSiteInitializer extends Initializer {
  ButtonElement _checkButton = query("#UserSettingsContent button.update_check");

  SpanElement _checkTime = query("#UserSettingsContent .update_site span.check_time");

  JSONClient _client = new AJAXJSONClient();

  bool get canBeSetUp => _checkButton != null && _checkTime != null;

  void setUp() {
    var s = _checkButton.text;
    _checkButton.onClick.listen((_) {
      _checkButton.disabled = true;
      _checkButton.text = "Undersøger";
      _client.callFunction(new CheckForSiteUpdatesJSONFunction()).then((JSONResponse response) {
        _checkButton.text = s;
        _checkButton.disabled = false;
        _checkTime.text = dateString(new DateTime.now());
        if (response.type != JSONResponse.RESPONSE_TYPE_SUCCESS) {
          return;
        }
        if (response.payload) {
          dialogContainer.confirm("<b>Websitet kan opdateres! </b><br /> Hvis du opdaterer nu, skal siden genstartes, og det er derfor vigtigt at du gemmer alle ændringer du må have foretaget. <br /> Ønsker du at opdatere det nu?").result.then((bool b) {
            if (!b) {
              return;
            }
            var updateDone = false;
            window.onBeforeUnload.listen((BeforeUnloadEvent event) {
              if (updateDone) {
                return;
              }
              event.returnValue = "Websitet er i gang med at opdatere.";
            });

            var loader = dialogContainer.loading("Opdaterer websitet.<br />Luk ikke din browser!");
            _client.callFunction(new UpdateSiteJSONFunction()).then((JSONResponse response) {
              if (response.type == JSONResponse.RESPONSE_TYPE_ERROR) {
                loader.close();
              }
              loader.element..innerHtml = "Siden er opdateret.<br /> Hjemmesiden genindlæses."..classes.remove('loading');
              updateDone = true;

              var t = new Timer(new Duration(seconds:1), () {
                window.location.reload();
              });
            });

          });

        } else {
          dialogContainer.alert("Der blev ikke fundet nogen opdatering.<br /> Prøv igen senere.");
        }
      });
    });
  }
}

class UserSettingsPageUserListFormInitializer extends Initializer {

  UserLibrary _userLib;

  PageOrder _order;

  UListElement _pageUserList = query('#PageUserList');

  FormElement _addUserToPageForm = query('#AddUserToPageForm');

  UserSettingsPageUserListFormInitializer(UserLibrary this._userLib, PageOrder this._order);

  bool get canBeSetUp => _pageUserList != null && _order.currentPage != null;

  void setUp() {
    var bar = new SavingBar();
    var pageUserSelect = query('#EditPageAddUserSelect'), pageUserLis = _pageUserList.queryAll('li');
    var addListener = (LIElement li) {
      var val = li.query('.val');
      var delete = li.query('.delete');
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
          var i = bar.startJob();
          user.revokePagePrivilege(_order.currentPage, (String status, [a, b]) {
            if (status == CALLBACK_STATUS_SUCCESS) {
              li.remove();
              var opt = new OptionElement();
              opt.text = opt.value = username;
              pageUserSelect.append(opt);
            }
            bar.endJob(i);
          });
        });
      });
    };
    var createUserLi = (User user) {
      var li = new LIElement();
      li.innerHtml = "<span class='val'>${user.username}</span><div class='delete link' title='Slet'>&nbsp;</div>";
      return li;
    };
    _userLib.onChange.listen((UserLibraryChangeEvent evt) {
      var changeType = evt.type, user = evt.user;
      if (changeType == UserLibraryChangeEvent.CHANGE_CREATE) {
        if (user.privileges != User.PRIVILEGE_PAGE) {
          var li = new LIElement();
          li.text = user.username;
          addListener(li);
          _pageUserList.append(li);
          pageUserLis = _pageUserList.queryAll('li');
        } else if (user.pages.contains(_order.currentPage)) {
          var li = createUserLi(user);
          addListener(li);
          _pageUserList.append(li);
          pageUserLis = _pageUserList.queryAll('li');

        } else if (pageUserSelect != null) {
          var opt = new OptionElement();
          opt.text = opt.value = user.username;
          pageUserSelect.append(opt);
        }
      } else {
        pageUserLis.forEach((LIElement li) {
          if ((li.query('.val') == null ? li.text : li.query('.val').text) == user.username) {
            li.remove();
          }
        });
        pageUserSelect.options.forEach((OptionElement o) {
          if (o.value == user.username) {
            o.remove();
          }
        });
      }
    });

    pageUserLis.forEach(addListener);
    if (_addUserToPageForm == null) {
      return;
    }
    new Validator(pageUserSelect).validator = (SelectElement e) => nonEmpty(e.value);
    new ValidatingForm(_addUserToPageForm).validate();
    var deco = new FormHandler(_addUserToPageForm);
    deco.submitFunction = (Map<String, String> data) {
      if (_addUserToPageForm.classes.contains('initial')) {
        return false;
      }
      deco.blur();
      var user = _userLib.users[data['username']];
      user.addPagePrivilege(_order.currentPage, (String status, [a, b]) {
        if (status == CALLBACK_STATUS_ERROR) {
          deco.unBlur();
          return false;
        }
        pageUserSelect.selectedOptions.first.remove();
        pageUserSelect.dispatchEvent(new Event('change'));
        var newLi = createUserLi(user);
        _pageUserList.append(newLi);
        pageUserLis = _pageUserList.queryAll('li');
        addListener(newLi);
        deco.unBlur();
      });

      return false;
    };

  }
}

class TitleURLUpdateInitializer extends Initializer {

  PageOrder _order;

  JSONClient _client;

  TitleURLUpdateInitializer(PageOrder this._order, JSONClient this._client);

  bool get canBeSetUp => _order.currentPage != null;

  void setUp() {
    var updateAddress = () {
      var currentPagePath = _order.currentPagePath;
      var currentPageAddress = currentPagePath.map((Page p) => p.id).join('/') ;
      var currentPageTitle = document.title.split(' - ').first + " - " + currentPagePath.map((Page p) => p.title).join(' - ');
      document.title = currentPageTitle;
      _client.urlPrefix = currentPageAddress;
      window.history.replaceState(null, currentPageTitle, "/" + currentPageAddress);
    };
    updateAddress();
    _order.currentPage.onChange.listen((_) {
      updateAddress();
    });
    _order.onUpdate.listen((_){
      updateAddress();
    });

  }

}

class UserSettingsUserListInitializer extends Initializer {
  UserLibrary _userLib;

  UListElement _userList = query('#UserList');

  UserSettingsUserListInitializer(UserLibrary this._userLib);

  bool get canBeSetUp => _userList != null;
  String _userPrivilegeString (User u, [bool simple=false]) => u.privileges == User.PRIVILEGE_ROOT ? (simple?"root":"Root") : (u.privileges == User.PRIVILEGE_SITE ? (simple?"site":"Website") : (simple?"page":"Side"));

  void setUp() {
    var bar = new SavingBar();
    var addListener = (LIElement li) {
      var val = li.query('.val'), privileges = li.query('.privileges');
      var username = li.dataset['username'];
      _userLib.users[username].onChange.listen((User u) {
        val.text = u.username;
        val.href = "mailto:${u.mail}";
        privileges.text = "(${_userPrivilegeString(u)} Administrator)";
        li.dataset["username"] = u.username;
        li.dataset["privileges"] = _userPrivilegeString(u,true);
        li.dataset["mail"] = u.mail;
        li.dataset["pages"] = u.pages.map((Page p)=>p.id).join(" ");
      });
      var delete = li.query('.delete');
      if (delete == null) {
        return;
      }
      delete.onClick.listen((MouseEvent e) {
        var dialog = new DialogContainer();
        dialog.confirm("Er du sikker på at du vil slette denne bruger?").result.then((bool b) {
          if (!b) {
            return;
          }
          var i = bar.startJob();
          _userLib.deleteUser(username, (String status, [int error_code, p]) => bar.endJob(i));
        });
      });
    };
    var userLis = _userList.queryAll('li');
    userLis.forEach(addListener);
    _userLib.onChange.listen((UserLibraryChangeEvent evt) {
      var changeType = evt.type, user = evt.user;
      if (changeType == UserLibraryChangeEvent.CHANGE_CREATE) {
        var li = new LIElement();
        var privilege = _userPrivilegeString(user);
        var a = new AnchorElement();
        a..text = user.username..classes.add("val")..href = "mailto:${user.mail}";
        li.append(a);
        li.appendHtml(", <span class='privileges'>($privilege Administrator)</span> <span class='parent hidden'>${user.parent}</span> <div class='delete link' title='Slet'>&nbsp;</div>");
        _userList.append(li);
        userLis = _userList.queryAll('li');
        addListener(li);
      } else {
        userLis.forEach((LIElement li) {
          if (li.query('.val').text == user.username)li.remove();
        });
      }
    });

  }
}


class UserSettingsAddUserFormInitializer extends Initializer {
  UserLibrary _userLib;

  FormElement _addUserForm = query('#EditUsersAddUserForm');

  UserSettingsAddUserFormInitializer(UserLibrary this._userLib);

  bool get canBeSetUp => _addUserForm != null;

  void setUp() {
    var userMailField = _addUserForm.query('#AddUserMailField'), userLevelSelect = _addUserForm.query('#AddUserLevelSelect');
    var v = new Validator(userMailField);
    v.validator = (InputElement e) => validMail(e.value);
    v.errorMessage = "Skal være gyldig E-mail";
    new Validator(userLevelSelect).validator = (SelectElement e) => nonEmpty(e.value);
    var validatingForm = new ValidatingForm(_addUserForm);
    validatingForm.validate();
    var decoration = new FormHandler(_addUserForm);
    decoration.submitFunction = (Map<String, String> data) {
      if (_addUserForm.classes.contains('initial')) {
        return false;
      }
      decoration.blur();
      _userLib.createUser(data['mail'], data['level'], (String status, [int error_code, b]) {
        if (status == CALLBACK_STATUS_SUCCESS) {
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


class UserSettingsAddPageFormInitializer extends Initializer {
  PageOrder _order;

  FormElement _addPageForm = query("#EditPagesForm");

  UserSettingsAddPageFormInitializer(PageOrder this._order);

  bool get canBeSetUp => _addPageForm != null;

  void setUp() {
    var input = query('#EditPagesAddPage'), v;
    (v = new Validator(input)).validator = (InputElement e) => nonEmpty(e.value);
    v.errorMessage = "Titlen må ikke være tom";
    var validatingForm = new ValidatingForm(_addPageForm);
    validatingForm.validate();
    var decoration = new FormHandler(_addPageForm);
    decoration.submitFunction = (Map<String, String> data) {
      if (_addPageForm.classes.contains('initial')) {
        return false;
      }

      decoration.blur();
      _order.createPage(data['title'], (String status, [int error_code, dynamic payload]) {
        if (status == CALLBACK_STATUS_SUCCESS) {
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


class UserSettingsChangeUserInfoFormInitializer extends Initializer {
  UserLibrary _userLibrary;

  FormElement _userMailForm = query('#UpdateUsernameMailForm'), _userPasswordForm = query('#UpdatePasswordForm');


  UserSettingsChangeUserInfoFormInitializer(UserLibrary this._userLibrary);

  bool get canBeSetUp => _userMailForm != null && _userPasswordForm != null;

  void setUp() {
    var userNameInput = _userMailForm.query('#EditUserEditUsernameField'), userMailInput = _userMailForm.query('#EditUserEditMailField');
    var v1 = new Validator(userNameInput), v2 = new Validator(userMailInput);
    v1.validator = (InputElement e) => nonEmpty(e.value) && (e.value == userLibrary.userLoggedIn.username || userLibrary.users[e.value] == null);
    v1.errorMessage = "Brugernavn må ikke være tomt og skal være unikt";

    v2.validator = (InputElement e) => validMail(e.value);
    v2.errorMessage = "Skal være gyldig E-mail adresse";

    var validatingForm = new ValidatingForm(_userMailForm);
    validatingForm.validate(true);
    var decoForm = new FormHandler(_userMailForm);
    decoForm.submitFunction = (Map<String, String> data) {
      if (_userMailForm.classes.contains('initial')) {
        return false;
      }
      decoForm.blur();
      userLibrary.userLoggedIn.changeInfo(username:data['username'], mail:data['mail'], callback:(String status, [int error_code, b]) {
        if (status == CALLBACK_STATUS_ERROR) {
          var m;
          decoForm.changeNotion((m = _errorMessage(error_code)) != null ? m : "Ukendt fejl", FormHandler.NOTION_TYPE_ERROR);
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

    var userOldPassword = _userPasswordForm.query('#EditUserEditPasswordOldField'), userNewPassword = _userPasswordForm.query('#EditUserEditPasswordNewField'), userNewPasswordRepeat = _userPasswordForm.query('#EditUserEditPasswordNewRepField');
    var v3 = new Validator(userOldPassword), v4 = new Validator(userNewPassword), v5 = new Validator(userNewPasswordRepeat);
    v3.validator = (InputElement e) => nonEmpty(e.value);
    v3.errorMessage = v4.errorMessage = "Kodeord må ikke være tomt";
    v4.validator = (InputElement e) => nonEmpty(e.value);
    v5.validator = (InputElement e) => nonEmpty(e.value) && e.value == userNewPassword.value;
    v5.errorMessage = "Kodeordet skal gentages korrekt";
    var valPassForm = new ValidatingForm(_userPasswordForm);
    valPassForm.validate();
    var decoPassForm = new FormHandler(_userPasswordForm);
    decoPassForm.submitFunction = (Map<String, String> data) {
      if (_userPasswordForm.classes.contains('initial')) {
        return false;
      }
      decoPassForm.blur();
      userLibrary.userLoggedIn.changePassword(data['old_password'], data['new_password'], (String status, [int error_code, b]) {
        if (status == CALLBACK_STATUS_ERROR) {
          var m;
          decoPassForm.changeNotion((m = _errorMessage(error_code)) != null ? m : "Ukendt fejl", FormHandler.NOTION_TYPE_ERROR);
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

class UserSettingsEditPageFormInitializer extends Initializer {
  PageOrder _order;

  FormElement _editPageForm = query('#EditPageForm');


  UserSettingsEditPageFormInitializer(PageOrder this._order);


  bool get canBeSetUp => _editPageForm != null;

  void setUp() {

    if (_editPageForm == null) {
      return;
    }
    var submitButton = _editPageForm.query('input[type=submit]');
    var validatingForm = new ValidatingForm(_editPageForm);

    var editIdField = _editPageForm.query('#EditPageEditIDField'), editAliasField = _editPageForm.query('#EditPageEditAliasField'), editTitleField = _editPageForm.query('#EditPageEditTitleField'), editTemplateSelect = _editPageForm.query('#EditPageEditTemplateSelect');

/* SET UP VALIDATOR */
    var v1, v2, v3;
    (v1 = new Validator(editTitleField)).validator = (InputElement e) => nonEmpty(e.value);
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
      _order.currentPage.changeInfo(title:data['title'], id:data['id'], template:data['template'], alias:data['alias'], callback:(String status, [int error_code, dynamic payload]) {
        if (status == CALLBACK_STATUS_SUCCESS) {
          formDecoration.changeNotion("Ændringerne er gemt", FormHandler.NOTION_TYPE_SUCCESS);
          editTitleField.blur();
          editIdField.blur();
          editAliasField.blur();
          if (template != _order.currentPage.template) {
            dialogContainer.alert("Du har redigeret sidens type og skal derfor genindlæse siden.").onClose.listen((_) => window.location.reload());
          }

        } else {
          formDecoration.changeNotion(_errorMessage(error_code), FormHandler.NOTION_TYPE_ERROR);
        }
        formDecoration.unBlur();
      });
      return false;
    };

  }
}


class UserSettingsPageListsInitializer extends Initializer {

  UListElement _activeList = query("#ActivePageList"), _inactiveList = query("#InactivePageList");

  PageOrder _order;

  UserSettingsPageListsInitializer(PageOrder this._order);

  bool get canBeSetUp => _activeList != null && _inactiveList != null;

  void setUp() {
    _activeList.queryAll('li:not(.emptyListInfo)').forEach((LIElement element) {
      var e = new UserSettingsPageLi(element);
      e.setUp();
    });
    _inactiveList.queryAll('li:not(.emptyListInfo)').forEach((LIElement element) {
      var e = new UserSettingsPageLi(element);
      e.setUp();
    });

    var updateListInfo = (UListElement ul, [bool active = true]) {
      var len = ul.queryAll('li').length;
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
      var bar = new SavingBar();
      var i = bar.startJob();
      _order.changePageOrder(newOrder, callback:(String status, [int error_code, dynamic payload]) => bar.endJob(i), parent_id:parent);
      e.stopPropagation();
    };
    _activeList.onChange.listen(ULChangeListener(_activeList, null));
    _activeList.queryAll('ul').forEach((UListElement ul) => ul.onChange.listen(ULChangeListener(ul, new UserSettingsPageLi(ul.parent).page.id)));

    _order.onUpdate.listen((PageOrderChange change){
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
          var ulToUpdate = path.currentlyShowing == null ? _activeList : new UserSettingsPageLi.fromPage(path.currentlyShowing).li.query('ul');

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
              ul.classes..add('colorList')..add('draggable');
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


class UserSettingsDecorationInitializer extends Initializer {
  var _expandLink = query("#UserSettingsExpandLink"), _contractLink = query("#UserSettingsContractLink"), _container = query("#UserSettingsContainer"), _slideElement = query("#UserSettingsContent > ul"), _slideMenuList = query("#UserSettingsMenu > ul");


  bool get canBeSetUp => _expandLink != null && _contractLink != null && _container != null && _slideElement != null && _slideMenuList != null;

  void setUp() {
    var expander = new UserSettingsExpandDecoration();
    _expandLink.onClick.listen((_) {
      expander.expand();
      escQueue.add(() {
        if (!expander._expanded) {
          return false;
        }
        var f = _container.query(':focus');
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
    var lis = _slideMenuList.queryAll('ul > li');
    var i = 0;
    lis.forEach((LIElement li) {
      var index = i;
      li.onClick.listen((e) {
        slider.goToIndex(index);
        _slideMenuList.query('.active').classes.remove('active');
        li.classes.add('active');
      });
      i++;
    });
  }
}