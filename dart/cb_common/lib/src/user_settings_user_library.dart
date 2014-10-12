part of user_settings;



class UserSettingsJSONUserLibrary implements UserLibrary {

  final UserLibrary _userLibrary;
  static final UserSettingsJSONUserLibrary _cache = new UserSettingsJSONUserLibrary._internal(querySelector('#UserList'));

  factory UserSettingsJSONUserLibrary() => _cache;

  UserSettingsJSONUserLibrary._internal(UListElement userList) : _userLibrary = _generateUserLibFromMenu(userList);

  static UserLibrary _generateUserLibFromMenu(UListElement userList){
    var lis = userList.querySelectorAll('li:not(.emptyListInfo)');
    var users = <User>[], currentUser = "";

    lis.forEach((LIElement li) {
      var aElement = li.querySelector('.val'), privileges = li.querySelector('.privileges');
      var username, mail, parent, lastLogin, t;
      parent = li.dataset["parent"];
      username = li.dataset["username"];
      mail = li.dataset["mail"];
      lastLogin = li.dataset["lastLogin"]== ""?null:int.parse(li.dataset["lastLogin"]);
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
      pageList.removeWhere((e)=>!(e is Page));
      var user = new JSONUser(username, mail, parent, lastLogin, privilege, pageList);
      users.add(user);
      if (li.classes.contains('current')) {
        currentUser = username;
      }

    });

    return new JSONUserLibrary(users, currentUser, pageOrder);
  }

  Future<core.Response<User>> createUser(String mail, String privileges) => _userLibrary.createUser(mail, privileges);

  Future<core.Response<User>> deleteUser(String username) => _userLibrary.deleteUser(username);

  Stream<UserLibraryChangeEvent> get onChange => _userLibrary.onChange;

  Map<String, User> get users => _userLibrary.users;

  Map<String, User> get rootUsers => _userLibrary.rootUsers;

  Map<String, User> get siteUsers => _userLibrary.siteUsers;

  Map<String, User> get pageUsers => _userLibrary.pageUsers;

  User get userLoggedIn => _userLibrary.userLoggedIn;

  Future<core.Response<String>> userLogin(String username, String password) => _userLibrary.userLogin(username, password);

  Future<core.Response> forgotPassword(String password) => _userLibrary.forgotPassword(password);

}