part of site_classes;

const USER_LIBRARY_CHANGE_DELETE = 1;
const USER_LIBRARY_CHANGE_CREATE = 2;

const USER_ROOT_PRIVILEGES = "root";
const USER_SITE_PRIVILEGES = "site";
const USER_PAGE_PRIVILEGES = "page";

typedef void UserLibraryChangeListener(int changeType, User user);

abstract class UserLibrary {

  void createUser(String mail, String privileges, [ChangeCallback callback]);

  void deleteUser(String username, [ChangeCallback callback]);

  void registerListener(UserLibraryChangeListener listener);

  Map<String, User> get userList;

  Map<String, User> get rootUserList;

  Map<String, User> get siteUserList;

  Map<String, User> get pageUserList;

  User get userLoggedIn;
}

class JSONUserLibrary extends UserLibrary {
  final String ajax_id;
  JSONClient _client;
  String _userLoggedInId;
  Map<String, User> _users = <String, User>{};
  List<String> _rootUsers = <String>[], _siteUsers = <String>[], _pageUsers = <String>[];
  final List<UserLibraryChangeListener> _listeners = <UserLibraryChangeListener>[];
  bool _hasBeenSetUp = false;

  static final Map<String, JSONUserLibrary> _cache = <String, JSONUserLibrary>{};

  JSONUserLibrary._internal(this.ajax_id);

  factory JSONUserLibrary(String ajax_id){
    var library = _retrieveInstance(ajax_id);
    library._setUp();

  }

  factory JSONUserLibrary.initializeFromLists(String ajax_id,
                                              List<User> rootUsers,
                                              List<User> siteUsers,
                                              List<User> pageUsers,
                                              String currentUserName){
    var library = _retrieveInstance(ajax_id);
    library._setUpFromLists(rootUsers, siteUsers, pageUsers,currentUserName);
  }

  static JSONUserLibrary _retrieveInstance(String ajax_id) {
    if (_cache.containsKey(ajax_id)) {
      return _cache[ajax_id];
    } else {
      var library = new JSONUserLibrary._internal(ajax_id);
      _cache[ajax_id] = library;
      return library;
    }
  }

  void _setUpFromLists(List<User> rootUsers,
                       List<User> siteUsers,
                       List<User> pageUsers,
                       String current_username) {
    if (_hasBeenSetUp) {
      return;
    }
    _hasBeenSetUp = true;

    _client = new AJAXJSONClient(ajax_id);

    _userLoggedInId = current_username;
    rootUsers.forEach((User u) {
      _users[u.username] = u;
      _rootUsers.add(u.username);
    });
    siteUsers.forEach((User u) {
      _users[u.username] = u;
      _siteUsers.add(u.username);
    });
    pageUsers.forEach((User u) {
      _users[u.username] = u;
      _pageUsers.add(u.username);
    });

  }


  void _setUp() {
    if (_hasBeenSetUp) {
      return;
    }
    _hasBeenSetUp = true;

    _client = new AJAXJSONClient(ajax_id);
    var function = new ListUsersJSONFunction();
    var functionCallback = (JSONResponse response) {
      if (response.type != RESPONSE_TYPE_SUCCESS) {
        return;
      }
      _userLoggedInId = response.payload['user_logged_in'];
      response.payload['root_users'].forEach((JSONObject o) =>
      _rootUsers.add(_addUserFromObjectToUsers(o)));
      response.payload['site_users'].forEach((JSONObject o) =>
      _siteUsers.add(_addUserFromObjectToUsers(o)));
      response.payload['page_users'].forEach((JSONObject o) =>
      _pageUsers.add(_addUserFromObjectToUsers(o)));
    };
    _client.callFunction(function, functionCallback);
  }

  String _addUserFromObjectToUsers(JSONObject o) {
    var user = new JSONUser(o.variables['username'], o.variables['mail'], o.variables['parent'], _client);
    _users[user.username] = user;
    return user.username;
  }


  void createUser(String mail, String privileges, [ChangeCallback callback]) {
    var function = new CreateUserJSONFunction(mail, privileges);
    var functionCallback = (JSONResponse response) {

      if (response.type = RESPONSE_TYPE_SUCCESS) {
        var o = response.payload;
        var username = _addUserFromObjectToUsers(o);
        switch (privileges) {
          case USER_ROOT_PRIVILEGES:
            _rootUsers.add(username);
            break;
          case USER_SITE_PRIVILEGES:
            _siteUsers.add(username);
            break;
          case USER_PAGE_PRIVILEGES:
            _pageUsers.add(username);
            break;
        }
        _callListeners(USER_LIBRARY_CHANGE_CREATE, _users[username]);
      }
      if (callback != null) {
        callback(response.type, response.error_code);
      }
    };
    _client.callFunction(function, functionCallback);

  }

  void deleteUser(String username, [ChangeCallback callback]) {
    var function = new DeleteUserJSONFunction(username);
    var functionCallback = (JSONResponse response) {

      if (response.type == RESPONSE_TYPE_SUCCESS) {
        var user = _users[username];
        _removeUser(username);
        _callListeners(USER_LIBRARY_CHANGE_DELETE, user);
      }
      if (callback != null) {
        callback(response.type, response.error_code);
      }
    };
    _client.callFunction(function, functionCallback);

  }

  void _removeUser(String username) {
    _users.remove(username);
    _rootUsers.remove(username);
    _siteUsers.remove(username);
    _pageUsers.remove(username);
  }

  void registerListener(UserLibraryChangeListener listener) {
    _listeners.add(listener);
  }

  void _callListeners(int changeType, User user) {
    _listeners.forEach((UserLibraryChangeListener listener) {
      listener(changeType, user);
    });
  }

  Map<String, User> get userList => new Map.from(_users);

  Map<String, User> get rootUserList => _userListToMap(_rootUsers);

  Map<String, User> get siteUserList => _userListToMap(_siteUsers);

  Map<String, User> get pageUserList => _userListToMap(_pageUsers);

  User get userLoggedIn => _users[_userLoggedInId];

  Map<String, User> _userListToMap(List<String> userList) {
    var returnMap = <String, User>{};
    userList.forEach((String username) {
      returnMap[username] = _users[username];
    });
    return returnMap;
  }
}