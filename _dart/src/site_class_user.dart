part of site_classes;

typedef void UserInfoChangeListener(User user);

abstract class User {

  String get username;

  String get mail;

  String get parent;

  void changeInfo({String username, String mail, ChangeCallback callback});

  void changePassword(String currentPassword, String newPassword, [ChangeCallback callback]);

  void registerListener(UserInfoChangeListener listener);
}


class JSONUser extends User {
  String _username, _mail, _parent;
  final JSONClient _client;
  final List<UserInfoChangeListener> _listeners = [];

  JSONUser(String username, String mail, String parent, JSONClient client):_client = client{
    _username = username;
    _mail = mail;
    _parent = parent;
  }

  String get username => _username;

  String get mail => _mail;

  String get parent => _parent;

  void changeInfo({String username, String mail, ChangeCallback callback}) {
    mail = ?mail ? mail : _mail;
    username = ?username ? username : _username;
    var jsonFunction = new ChangeUserInfoJSONFunction(_username, username, mail);
    _client.callFunction(jsonFunction, (JSONResponse response) {
      if (response.type == RESPONSE_TYPE_SUCCESS) {
        _username = username;
        _mail = mail;
        callback(CALLBACK_STATUS_SUCCESS);
        _callListeners();
      } else  {
        callback(CALLBACK_STATUS_ERROR, response.error_code);
      }
    });
  }

  void changePassword(String currentPassword, String newPassword, [ChangeCallback callback]) {
    var jsonFunction = new ChangeUserPasswordJSONFunction(_username,currentPassword, newPassword);
    _client.callFunction(jsonFunction, (JSONResponse response) {
      switch (response.type) {
        case RESPONSE_TYPE_SUCCESS:
          callback(response.type);
          break;
        default:
          callback(RESPONSE_TYPE_ERROR, response.error_code);
      }
    });
  }

  void registerListener(UserInfoChangeListener listener) {
    _listeners.add(listener);
  }

  void _callListeners() {
    _listeners.forEach((UserInfoChangeListener listener) {
      listener(this);
    });
  }

}