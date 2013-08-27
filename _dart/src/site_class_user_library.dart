part of site_classes;

const USER_LIBRARY_CHANGE_DELETE = 1;
const USER_LIBRARY_CHANGE_CREATE = 2;


typedef void UserLibraryChangeListener(int changeType, User user);

abstract class UserLibrary {

  void createUser(String mail, String privileges, [ChangeCallback callback = null]);

  void deleteUser(String username, [ChangeCallback callback = null]);

  void registerListener(UserLibraryChangeListener listener);

  Map<String, User> get users;

  Map<String, User> get rootUsers;

  Map<String, User> get siteUsers;

  Map<String, User> get pageUsers;

  User get userLoggedIn;
}

class JSONUserLibrary extends UserLibrary {
  final String ajax_id;
  final PageOrder _pageOrder;
  JSONClient _client;
  String _userLoggedInId;
  Map<String, User> _users = <String, User>{};
  final List<UserLibraryChangeListener> _listeners = <UserLibraryChangeListener>[];
  bool _hasBeenSetUp = false;

  static final Map<String, JSONUserLibrary> _cache = <String, JSONUserLibrary>{};

  JSONUserLibrary._internal(this.ajax_id, this._pageOrder);

  factory JSONUserLibrary(String ajax_id, PageOrder pageOrder){
    var library = _retrieveInstance(ajax_id, pageOrder);
    library._setUp();

  }

  factory JSONUserLibrary.initializeFromLists(String ajax_id,
                                              List<User> users,
                                              String currentUserName,
                                              PageOrder pageOrder){
    var lib = _retrieveInstance(ajax_id, pageOrder);
    lib._setUpFromLists(users,currentUserName);
    return lib;
  }

  static JSONUserLibrary _retrieveInstance(String ajax_id, PageOrder pageOrder) {
    if (_cache.containsKey(ajax_id)) {
      return _cache[ajax_id];
    } else {
      var library = new JSONUserLibrary._internal(ajax_id, pageOrder);
      _cache[ajax_id] = library;
      return library;
    }
  }

  void _setUpFromLists(List<User> users,
                       String current_username) {
    if (_hasBeenSetUp) {
      return;
    }
    _hasBeenSetUp = true;

    _client = new AJAXJSONClient(ajax_id);

    _userLoggedInId = current_username;
    users.forEach((User u) {
      _addUserListener(u);
      _users[u.username] = u;
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

      response.payload['users'].forEach((JSONObject o) => _addUserFromObjectToUsers(o,response.payload['page_privileges'].containsKey(o.variables['username'])?response.payload['page_privileges'][o.variables['username']]:[]));

    };
    _client.callFunction(function).then(functionCallback);
  }

  String _addUserFromObjectToUsers(JSONObject o, List<String> page_ids) {
    var privilegesString = o.variables['privileges'];
    var pages = page_ids.map((String id) => _pageOrder.pages[id]);
    var privileges = privilegesString == 'root'?User.PRIVILEGE_ROOT:(privilegesString == 'site'?User.PRIVILEGE_SITE:User.PRIVILEGE_PAGE);
    var user = new JSONUser(o.variables['username'], o.variables['mail'], o.variables['parent'], privileges, pages , _client);
    _addUserListener(user);
    _users[user.username] = user;
    return user.username;
  }


  void _addUserListener(User user){
    user.registerListener((User u){
      if(_users.containsKey(u.username)){
        return;
      }
      var removeKey;
      _users.forEach((String k,User v){
        if(v == u){
          if(k == _userLoggedInId){
            _userLoggedInId = u.username;
          }
          removeKey = k;
        }
      });
      _users.remove(removeKey);
      _users[u.username] = u;
    });
  }

  void createUser(String mail, String privileges, [ChangeCallback callback = null]) {
    var function = new CreateUserJSONFunction(mail, privileges);
    var functionCallback = (JSONResponse response) {

      if (response.type == RESPONSE_TYPE_SUCCESS) {
        var o = response.payload;
        var username = _addUserFromObjectToUsers(o, []);
        _callListeners(USER_LIBRARY_CHANGE_CREATE, _users[username]);
      }
      if (callback != null) {
        callback(response.type, response.error_code);
      }
    };
    _client.callFunction(function).then(functionCallback);

  }

  void deleteUser(String username, [ChangeCallback callback = null]) {
    var function = new DeleteUserJSONFunction(username);
    var functionCallback = (JSONResponse response) {

      if (response.type == RESPONSE_TYPE_SUCCESS) {
        var user = _users[username];
        _users.remove(username);
        _callListeners(USER_LIBRARY_CHANGE_DELETE, user);
      }
      if (callback != null) {
        callback(response.type, response.error_code);
      }
    };
    _client.callFunction(function).then(functionCallback);

  }


  void registerListener(UserLibraryChangeListener listener) {
    _listeners.add(listener);
  }

  void _callListeners(int changeType, User user) {
    _listeners.forEach((UserLibraryChangeListener listener) {
      listener(changeType, user);
    });
  }

  Map<String, User> get users => new Map.from(_users);

  Map<String, User> get rootUsers => _generateUserList(User.PRIVILEGE_ROOT);

  Map<String, User> get siteUsers => _generateUserList(User.PRIVILEGE_SITE);

  Map<String, User> get pageUsers => _generateUserList(User.PRIVILEGE_PAGE);

  User get userLoggedIn => _users[_userLoggedInId];

  Map<String, User> _generateUserList(int privilege){
    var retMap = new Map<String,User>();
    _users.forEach((String k, User val){
      if(val.privilege == privilege){
        retMap[k] = val;
      }
    });
    return retMap;
  }

}