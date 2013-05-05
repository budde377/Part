part of site_classes;

typedef void UserInfoChangeListener(User user);

abstract class User {

  static const PRIVILEGE_ROOT = 1;
  static const PRIVILEGE_SITE = 2;
  static const PRIVILEGE_PAGE = 3;

  String get username;

  String get mail;

  String get parent;

  int get privilege;

  List<String> get page_ids;

  void changeInfo({String username, String mail, ChangeCallback callback});

  void changePassword(String currentPassword, String newPassword, [ChangeCallback callback]);

  void addPagePrivilege(String page_id, [ChangeCallback callback]);

  void revokePagePrivilege(String page_id, [ChangeCallback callback]);

  void registerListener(UserInfoChangeListener listener);
}


class JSONUser extends User {
  String _username, _mail, _parent;
  final JSONClient _client;
  final List<UserInfoChangeListener> _listeners = [];
  int _privileges;
  List<String> _page_ids;

  JSONUser(String username, String mail, String parent, int privileges, List<String> page_ids, JSONClient client):_client = client{
    _username = username;
    _mail = mail;
    _parent = parent;
    _page_ids = privileges == User.PRIVILEGE_PAGE?page_ids:null;
    _privileges = privileges;
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

  int get privilege => _privileges;

  List<String> get page_ids => _page_ids == null?[]:new List<String>.from(_page_ids);


  void addPagePrivilege(String page_id, [ChangeCallback callback]){
    var function = new AddUserPagePrivilegeJSONFunction(_username,page_id);
    _client.callFunction(function,(JSONResponse response){
      if(response.type==RESPONSE_TYPE_SUCCESS){
        _page_ids.add(page_id);
        callback(response.type);
        _callListeners();
      } else {
        callback(RESPONSE_TYPE_ERROR, response.error_code);
      }
    });
  }

  void revokePagePrivilege(String page_id, [ChangeCallback callback]){
    var function = new RevokeUserPagePrivilegeJSONFunction(_username,page_id);
    _client.callFunction(function,(JSONResponse response){
      if(response.type==RESPONSE_TYPE_SUCCESS){
        _page_ids.remove(page_id);
        callback(response.type);
        _callListeners();
      } else {
        callback(RESPONSE_TYPE_ERROR, response.error_code);
      }
    });
  }


}