part of user_settings;



class UserSettingsJSONUserLibrary implements UserLibrary {

  final UserLibrary _userLibrary;

  UserSettingsJSONUserLibrary.initializeFromMenu(UListElement userList) : _userLibrary = _generateUserLibFromMenu(userList);

  static UserLibrary _generateUserLibFromMenu(UListElement userList){
    var lis = userList.queryAll('li:not(.emptyListInfo)');
    var users = <User>[], currentUser = "";

    lis.forEach((LIElement li) {
      var aElement = li.query('.val'), privileges = li.query('.privileges');
      var username, mail, parent, t;
      parent = li.dataset["parent"];
      username = li.dataset["username"];
      mail = li.dataset["mail"];
      var client = new AJAXJSONClient();
      var p = li.dataset["privileges"], privilege;
      switch(p){
        case "root":
        privilege = User.PRIVILEGE_ROOT;
      break;
        case "site":
        privilege = User.PRIVILEGE_SITE;
      break;
        default:
        privilege = User.PRIVILEGE_PAGE;
      break;
      }

      var pagesString = li.dataset["pages"];
      var pageStringList = privilege == User.PRIVILEGE_PAGE && pagesString != null? pagesString.trim().split(" ") : [];
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

  Stream<UserLibraryChangeEvent> get onChange => _userLibrary.onChange;

  Map<String, User> get users => _userLibrary.users;

  Map<String, User> get rootUsers => _userLibrary.rootUsers;

  Map<String, User> get siteUsers => _userLibrary.siteUsers;

  Map<String, User> get pageUsers => _userLibrary.pageUsers;

  User get userLoggedIn => _userLibrary.userLoggedIn;
}