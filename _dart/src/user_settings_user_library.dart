part of user_settings;

String _valToMail(AnchorElement val) => val.href.substring(val.href.lastIndexOf(":") + 1);


class UserSettingsJSONUserLibrary implements UserLibrary {

  final UserLibrary _userLibrary;

  UserSettingsJSONUserLibrary.initializeFromMenu(UListElement userList) : _userLibrary = _generateUserLibFromMenu(userList);

  static UserLibrary _generateUserLibFromMenu(UListElement userList){
    var lis = userList.queryAll('li:not(.emptyListInfo)');
    var users = <User>[], currentUser = "";

    lis.forEach((LIElement li) {
      var parentElement = li.query('.parent'), aElement = li.query('.val'), privileges = li.query('.privileges'), pages = li.query('.pages');
      var username, mail, parent, t;
      parent = (t = parentElement.text.trim()).length == 0 ? null : t;
      username = aElement.text.trim();
      mail = _valToMail(aElement);
      var client = new AJAXJSONClient();
      var privilege = new RegExp('root', caseSensitive:false).hasMatch(privileges.text) ? User.PRIVILEGE_ROOT : (new RegExp('website', caseSensitive:false).hasMatch(privileges.text) ? User.PRIVILEGE_SITE : User.PRIVILEGE_PAGE);

      var pageStringList = privilege == User.PRIVILEGE_PAGE && pages != null ? pages.text.trim().split(" ") : [];
      var pageList = pageStringList.map((String id) => pageOrder.pages[id]).toList();
      var user = new JSONUser(username, mail, parent, privilege, pageList, client);
      users.add(user);
      if (li.classes.contains('current')) {
        currentUser = username;
      }

    });

    return new JSONUserLibrary.initializeFromLists(users, currentUser, pageOrder);
  }

  UserSettingsJSONUserLibrary() : _userLibrary = new JSONUserLibrary(pageOrder);

  void createUser(String mail, String privileges, [ChangeCallback callback = null]) => _userLibrary.createUser(mail, privileges, callback);

  void deleteUser(String username, [ChangeCallback callback = null]) => _userLibrary.deleteUser(username, callback);

  void registerListener(UserLibraryChangeListener listener) => _userLibrary.registerListener(listener);

  Map<String, User> get users => _userLibrary.users;

  Map<String, User> get rootUsers => _userLibrary.rootUsers;

  Map<String, User> get siteUsers => _userLibrary.siteUsers;

  Map<String, User> get pageUsers => _userLibrary.pageUsers;

  User get userLoggedIn => _userLibrary.userLoggedIn;
}