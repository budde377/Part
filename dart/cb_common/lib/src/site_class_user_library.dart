part of site_classes;

const USER_LIBRARY_CHANGE_DELETE = 1;
const USER_LIBRARY_CHANGE_CREATE = 2;


typedef void UserLibraryChangeListener(int changeType, User user);

class UserLibraryChangeEvent {
  static const CHANGE_DELETE = 1;
  static const CHANGE_CREATE = 2;

  final User user;
  final int type;

  UserLibraryChangeEvent(this.user, this.type);

}

abstract class UserLibrary {

  Future<ChangeResponse<User>> createUser(String mail, String privileges);

  Future<ChangeResponse<User>> deleteUser(String username);


  Stream<UserLibraryChangeEvent> get onChange;


  Map<String, User> get users;

  Map<String, User> get rootUsers;

  Map<String, User> get siteUsers;

  Map<String, User> get pageUsers;

  User get userLoggedIn;
}

class JSONUserLibrary extends UserLibrary {
  final PageOrder _pageOrder;
  String _userLoggedInId;
  Map<String, User> _users = <String, User>{
  };
  bool _hasBeenSetUp = false;
  Stream<UserLibraryChangeEvent> _changeStream;
  StreamController<UserLibraryChangeEvent> _changeController = new StreamController<UserLibraryChangeEvent>();


/*
  factory JSONUserLibrary(PageOrder pageOrder){
    var library = _retrieveInstance(pageOrder);
    library._setUp();

  }
*/

  JSONUserLibrary(List<User> users, String currentUserName, PageOrder pageOrder) {
    _setUpFromLists(users, currentUserName);

  }


  void _setUpFromLists(List<User> users, String current_username) {
    if (_hasBeenSetUp) {
      return;
    }
    _hasBeenSetUp = true;


    _userLoggedInId = current_username;
    users.forEach((User u) {
      _addUserListener(u);
      _users[u.username] = u;
    });

  }

/*
  void _setUp() {
    if (_hasBeenSetUp) {
      return;
    }
    _hasBeenSetUp = true;

    var function = new ListUsersJSONFunction();
    var functionCallback = (JSONResponse response) {
      if (response.type != Response.RESPONSE_TYPE_SUCCESS) {
        return;
      }
      _userLoggedInId = response.payload['user_logged_in'];

      response.payload['users'].forEach((JSONObject o) => _addUserFromObjectToUsers(o,response.payload['page_privileges'].containsKey(o.variables['username'])?response.payload['page_privileges'][o.variables['username']]:[]));

    };
    ajaxClient.callFunction(function).then(functionCallback);
  }
*/

  String _addUserFromObjectToUsers(JSONObject o, List<String> page_ids) {
    var privilegesString = o.variables['privileges'];
    var pages = page_ids.map((String id) => _pageOrder.pages[id]);
    var privileges = privilegesString == 'root' ? User.PRIVILEGE_ROOT : (privilegesString == 'site' ? User.PRIVILEGE_SITE : User.PRIVILEGE_PAGE);
    var user = new JSONUser(o.variables['username'], o.variables['mail'], o.variables['parent'], o.variables['last-login'], privileges, pages);
    _addUserListener(user);
    _users[user.username] = user;
    return user.username;
  }


  void _addUserListener(User user) {
    user.onChange.listen((User u) {
      if (_users.containsKey(u.username)) {
        return;
      }
      var removeKey;
      _users.forEach((String k, User v) {
        if (v == u) {
          if (k == _userLoggedInId) {
            _userLoggedInId = u.username;
          }
          removeKey = k;
        }
      });
      _users.remove(removeKey);
      _users[u.username] = u;
    });
  }

  Future<ChangeResponse<User>>createUser(String mail, String privileges) {
    var completer = new Completer<ChangeResponse<User>>();
    var functionCallback = (JSONResponse response) {

      if (response.type == Response.RESPONSE_TYPE_SUCCESS) {
        var o = response.payload;
        var username = _addUserFromObjectToUsers(o, []);
        var user = _users[username];
        _callListeners(UserLibraryChangeEvent.CHANGE_CREATE, user);
        completer.complete(new ChangeResponse<User>.success(user));
      } else {
        completer.complete(new ChangeResponse<User>.error(response.error_code));
      }

    };
    ajaxClient.callFunctionString("UserLibrary.createUserFromMail(${quoteString(mail)}, ${quoteString(privileges)})").then(functionCallback);
    return completer.future;
  }

  Future<ChangeResponse<User>> deleteUser(String username) {
    var completer = new Completer<ChangeResponse<User>>();
    var functionCallback = (JSONResponse response) {

      if (response.type == Response.RESPONSE_TYPE_SUCCESS) {
        var user = _users[username];
        _users.remove(username);
        _callListeners(USER_LIBRARY_CHANGE_DELETE, user);
        completer.complete(new ChangeResponse<User>.success(user));
      } else {
        completer.complete(new ChangeResponse<User>.error(response.error_code));
      }

    };
    ajaxClient.callFunctionString("UserLibrary.deleteUser(UserLibrary.getUser(${quoteString(username)}))").then(functionCallback);
    return completer.future;
  }

  Stream<UserLibraryChangeEvent> get onChange => _changeStream == null ? _changeStream = _changeController.stream.asBroadcastStream() : _changeStream;

  void _callListeners(int changeType, User user) {
    _changeController.add(new UserLibraryChangeEvent(user, changeType));

  }

  Map<String, User> get users => new Map.from(_users);

  Map<String, User> get rootUsers => _generateUserList(User.PRIVILEGE_ROOT);

  Map<String, User> get siteUsers => _generateUserList(User.PRIVILEGE_SITE);

  Map<String, User> get pageUsers => _generateUserList(User.PRIVILEGE_PAGE);

  User get userLoggedIn => _users[_userLoggedInId];

  Map<String, User> _generateUserList(int privilege) {
    var retMap = new Map<String, User>();
    _users.forEach((String k, User val) {
      if (val.privileges == privilege) {
        retMap[k] = val;
      }
    });
    return retMap;
  }

}