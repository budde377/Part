part of user_settings;


class UserSettingsUserListInitializer extends core.Initializer {
  UserLibrary _userLib;

  UListElement _userList = querySelector('#UserList');

  UserSettingsUserListInitializer(UserLibrary this._userLib);

  bool get canBeSetUp => _userList != null;

  String _userPrivilegeString(User u, [bool simple=false]) => u.privileges == User.PRIVILEGE_ROOT ? (simple ? "root" : "Root") : (u.privileges == User.PRIVILEGE_SITE ? (simple ? "site" : "Website") : (simple ? "page" : "Side"));

  void _setDataset(LIElement li, User user) {
    li.dataset["username"] = user.username;
    li.dataset["privileges"] = _userPrivilegeString(user, true);
    li.dataset["lastLogin"] = user.lastLogin == null ? "" : (user.lastLogin.millisecondsSinceEpoch ~/ 1000).toString();
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
    var loginString = user.lastLogin == null ? "Aldrig" : core.dateString(user.lastLogin);
    var infoBox = new InfoBox("Sidst set: $loginString");
    var time = li.querySelector('.time');
    infoBox.backgroundColor = InfoBox.COLOR_BLACK;
    time.onMouseOver.listen((_) => infoBox.showAboveCenterOfElement(time));
    time.onMouseOut.listen((_) => infoBox.remove());

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
    v.addValidMailValueValidator();
    v.errorMessage = "Skal være gyldig E-mail";
    new Validator(userLevelSelect).addNonEmptyValueValidator();
    var validatingForm = new ValidatingForm(_addUserForm);
    validatingForm.validate();
    var decoration = new FormHandler(_addUserForm);
    decoration.submitFunction = (Map<String, String> data) {
      if (_addUserForm.classes.contains('initial')) {
        return false;
      }
      decoration.blur();
      _userLib.createUser(data['mail'], data['level']).then((core.Response response) {
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
