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
      var user = new AJAXUser(username, mail, parent, lastLogin, privilege, pageList);
      users.add(user);
      if (li.classes.contains('current')) {
        currentUser = username;
      }

    });

    return new AJAXUserLibrary(users, currentUser, pageOrder);
  }

  Future<core.Response<User>> createUser(String mail, String privileges) => _userLibrary.createUser(mail, privileges);

  Future<core.Response<User>> deleteUser(String username) => _userLibrary.deleteUser(username);

  Stream<UserLibraryChangeEvent> get onChange => _userLibrary.onChange;
  Stream<User> get onUpdate => _userLibrary.onUpdate;
  Stream<User> get onAdd => _userLibrary.onAdd;
  Stream<User> get onRemove => _userLibrary.onRemove;

  Map<String, User> get users => _userLibrary.users;

  Iterable<User> get elements => _userLibrary.elements;

  void every(void f(User)) => _userLibrary.every(f);

  Map<String, User> get rootUsers => _userLibrary.rootUsers;

  Map<String, User> get siteUsers => _userLibrary.siteUsers;

  Map<String, User> get pageUsers => _userLibrary.pageUsers;

  User get userLoggedIn => _userLibrary.userLoggedIn;

  Future<core.Response<String>> userLogin(String username, String password) => _userLibrary.userLogin(username, password);

  Future<core.Response> forgotPassword(String username) => _userLibrary.forgotPassword(username);

}


class NullUserLibrary implements UserLibrary{

  final UserLibrary _ajax_user_library = new AJAXUserLibrary([], null, pageOrder);

  static NullUserLibrary _cache;

  factory NullUserLibrary() => _cache==null?_cache = new NullUserLibrary._internal():_cache;


  NullUserLibrary._internal();

  Stream<UserLibraryChangeEvent> get onChange =>_ajax_user_library.onChange;

  Map<String, User> get pageUsers => _ajax_user_library.pageUsers;

  Stream<User> get onAdd  => _ajax_user_library.onAdd;

  Iterable<User> get elements => _ajax_user_library.elements;

  void every(void f(K)) => _ajax_user_library.every(f);


  core.FutureResponse forgotPassword(String username) => _ajax_user_library.forgotPassword(username);


  Map<String, User> get siteUsers => _ajax_user_library.siteUsers;


  Map<String, User> get rootUsers => _ajax_user_library.rootUsers;


  core.FutureResponse<User> deleteUser(String username) => _ajax_user_library.deleteUser(username);


  Stream<User> get onRemove => _ajax_user_library.onRemove;


  core.FutureResponse<String> userLogin(String username, String password) => _ajax_user_library.userLogin(username, password);


  Stream<User> get onUpdate => _ajax_user_library.onUpdate;


  User get userLoggedIn => _ajax_user_library.userLoggedIn;


  Map<String, User> get users => _ajax_user_library.users;


  core.FutureResponse<User> createUser(String mail, String privileges) => _ajax_user_library.createUser(mail, privileges);



}