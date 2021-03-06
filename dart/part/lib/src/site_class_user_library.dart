part of site_classes;

const USER_LIBRARY_CHANGE_DELETE = 1;
const USER_LIBRARY_CHANGE_CREATE = 2;


class UserLibraryChangeEvent {
  static const CHANGE_DELETE = 1;
  static const CHANGE_CREATE = 2;

  final User user;
  final int type;

  UserLibraryChangeEvent(this.user, this.type);

}

abstract class UserLibrary extends GeneratorDependable<User>{

  FutureResponse<User> createUser(String mail, String privileges);

  FutureResponse<User> deleteUser(String username);


  Stream<UserLibraryChangeEvent> get onChange;


  Map<String, User> get users;

  Map<String, User> get rootUsers;

  Map<String, User> get siteUsers;

  Map<String, User> get pageUsers;

  User get userLoggedIn;

  FutureResponse<String> userLogin(String username, String password);

  FutureResponse forgotPassword(String username);

}

class AJAXUserLibrary extends UserLibrary {
  final PageOrder pageOrder;
  String _userLoggedInId;
  Map<String, User> _users = <String, User>{
  };
  bool _hasBeenSetUp = false;
  StreamController<UserLibraryChangeEvent> _changeController = new StreamController<UserLibraryChangeEvent>.broadcast();

  StreamController<User>
  _onUpdateController = new StreamController<User>.broadcast();


  AJAXUserLibrary(List<User> users, String currentUserName, PageOrder pageOrder) : this.pageOrder = pageOrder {
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


  String _addUserFromObjectToUsers(JSONObject o) {
    JSONObject privilegesObject = o.variables['privileges'];
    var pages = privilegesObject.variables['page_privileges'].map((String id) => pageOrder.pages[id]);
    var privileges = privilegesObject.variables['root_privileges'] ? User.PRIVILEGE_ROOT : (privilegesObject.variables['site_privileges'] ? User.PRIVILEGE_SITE : User.PRIVILEGE_PAGE);
    var user = new AJAXUser(o.variables['username'], o.variables['mail'], o.variables['parent'], o.variables['last-login'], privileges, pages);
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
    user.onChange.listen((_) => _onUpdateController.add(user));
  }

  FutureResponse<User> createUser(String mail, String privileges) {
    var completer = new Completer<Response<User>>();
    var functionCallback = (JSONResponse response) {

      if (response.type == Response.RESPONSE_TYPE_SUCCESS) {
        var o = response.payload;
        var username = _addUserFromObjectToUsers(o);
        var user = _users[username];
        _callListeners(UserLibraryChangeEvent.CHANGE_CREATE, user);
        completer.complete(new Response<User>.success(user));
      } else {
        completer.complete(new Response<User>.error(response.error_code));
      }

    };
    ajaxClient.callFunctionString("UserLibrary.createUserFromMail(${quoteString(mail)}, ${quoteString(privileges)})").then(functionCallback);
    return completer.future;
  }

  FutureResponse<User> deleteUser(String username) {
    var completer = new Completer<Response<User>>();
    var functionCallback = (JSONResponse response) {

      if (response.type == Response.RESPONSE_TYPE_SUCCESS) {
        var user = _users[username];
        _users.remove(username);
        _callListeners(USER_LIBRARY_CHANGE_DELETE, user);
        completer.complete(new Response<User>.success(user));
      } else {
        completer.complete(new Response<User>.error(response.error_code));
      }

    };
    ajaxClient.callFunctionString("UserLibrary.deleteUser(UserLibrary.getUser(${quoteString(username)}))").then(functionCallback);
    return completer.future;
  }

  Stream<UserLibraryChangeEvent> get onChange => _changeController.stream;

  void _callListeners(int changeType, User user) {
    _changeController.add(new UserLibraryChangeEvent(user, changeType));

  }

  Map<String, User> get users => new Map.from(_users);

  Iterable<User> get elements => _users.values;

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


  FutureResponse<String> userLogin(String username, String password) {
    var future = ajaxClient.callFunctionString('UserLibrary.userLogin(${quoteString(username)}, ${quoteString(password)})');
    future.thenResponse(onSuccess:(Response response) {
      _userLoggedInId = username;
    });
    return future;
  }

  FutureResponse forgotPassword(String username) => ajaxClient.callFunctionString('UserLibrary.forgotPassword(${quoteString(username)})');


  Stream<User> get onAdd => onChange.where((UserLibraryChangeEvent evt)=>evt.type == UserLibraryChangeEvent.CHANGE_CREATE).map((UserLibraryChangeEvent evt) => evt.user);

  Stream<User> get onRemove => onChange.where((UserLibraryChangeEvent evt)=>evt.type == UserLibraryChangeEvent.CHANGE_DELETE).map((UserLibraryChangeEvent evt) => evt.user);

  Stream<User> get onUpdate => _onUpdateController.stream;

}