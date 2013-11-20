part of site_classes;

typedef void UserInfoChangeListener(User user);

abstract class User {

  static const PRIVILEGE_ROOT = 1;

  static const PRIVILEGE_SITE = 2;

  static const PRIVILEGE_PAGE = 3;

  String get username;

  String get mail;

  String get parent;

  int get privileges;

  List<Page> get pages;

  void changeInfo({String username:null, String mail:null, ChangeCallback callback:null});

  void changePassword(String currentPassword, String newPassword, [ChangeCallback callback]);

  void addPagePrivilege(Page page, [ChangeCallback callback]);

  void revokePagePrivilege(Page page, [ChangeCallback callback]);

  bool get hasRootPrivileges;
  bool get hasSitePrivileges;

  bool canModifyPage(Page page);

  void registerListener(UserInfoChangeListener listener);

}


class JSONUser extends User {
  String _username, _mail, _parent;

  final JSONClient _client;

  final List<UserInfoChangeListener> _listeners = [];

  int _privileges;

  List<Page> _pages;

  JSONUser(String username, String mail, String parent, int privileges, List<Page> pages, JSONClient client):_client = client {
    _username = username;
    _mail = mail;
    _parent = parent;
    _pages = privileges == User.PRIVILEGE_PAGE ? new List<Page>.from(pages) : <Page>[];
    _privileges = privileges;

  }

  String get username => _username;

  String get mail => _mail;

  String get parent => _parent;

  void changeInfo({String username:null, String mail:null, ChangeCallback callback:null}) {
    mail = mail != null ? mail : _mail;
    username = username != null ? username : _username;
    callback = callback != null ? callback : (e1, [e2, e3]) {
    };
    var jsonFunction = new ChangeUserInfoJSONFunction(_username, username, mail);
    _client.callFunction(jsonFunction).then( (JSONResponse response) {
      if (response.type == JSONResponse.RESPONSE_TYPE_SUCCESS) {
        _username = username;
        _mail = mail;
        callback(CALLBACK_STATUS_SUCCESS);
        _callListeners();
      } else {
        callback(CALLBACK_STATUS_ERROR, response.error_code);
      }
    });
  }

  void changePassword(String currentPassword, String newPassword, [ChangeCallback callback]) {
    var jsonFunction = new ChangeUserPasswordJSONFunction(_username, currentPassword, newPassword);
    _client.callFunction(jsonFunction).then((JSONResponse response) {
      switch (response.type) {
        case JSONResponse.RESPONSE_TYPE_SUCCESS:
          callback(response.type);
          break;
        default:
          callback(JSONResponse.RESPONSE_TYPE_ERROR, response.error_code);
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

  int get privileges => _privileges;


  List<Page> get pages => new List<Page>.from(_pages);


  void addPagePrivilege(Page page, [ChangeCallback callback]) {
    var function = new AddUserPagePrivilegeJSONFunction(_username, page.id);
    _client.callFunction(function).then( (JSONResponse response) {
      if (response.type == JSONResponse.RESPONSE_TYPE_SUCCESS) {
        _pages.add(page);
        callback(response.type);
        _callListeners();
      } else {
        callback(JSONResponse.RESPONSE_TYPE_ERROR, response.error_code);
      }
    });
  }

  void revokePagePrivilege(Page page, [ChangeCallback callback]) {
    var function = new RevokeUserPagePrivilegeJSONFunction(_username, page.id);
    _client.callFunction(function).then((JSONResponse response) {
      if (response.type == JSONResponse.RESPONSE_TYPE_SUCCESS) {
        _pages.remove(page);
        callback(response.type);
        _callListeners();
      } else {
        callback(JSONResponse.RESPONSE_TYPE_ERROR, response.error_code);
      }
    });
  }

  bool get hasRootPrivileges => privileges == User.PRIVILEGE_ROOT;

  bool get hasSitePrivileges => privileges == User.PRIVILEGE_ROOT || privileges == User.PRIVILEGE_SITE;

  bool canModifyPage(Page page) => _privileges == User.PRIVILEGE_ROOT || _privileges == User.PRIVILEGE_SITE ||  _pages.map((Page p) => p.id).contains(page.id);


}