part of site_classes;

abstract class User {

  static const PRIVILEGE_ROOT = 1;

  static const PRIVILEGE_SITE = 2;

  static const PRIVILEGE_PAGE = 3;

  String get username;

  String get mail;

  String get parent;

  DateTime get lastLogin;

  int get privileges;

  List<Page> get pages;

  Future<ChangeResponse<User>> changeInfo({String username:null, String mail:null});

  Future<ChangeResponse<User>> changePassword(String currentPassword, String newPassword);

  Future<ChangeResponse<User>> addPagePrivilege(Page page);

  Future<ChangeResponse<User>>revokePagePrivilege(Page page);

  bool get hasRootPrivileges;
  bool get hasSitePrivileges;

  bool canModifyPage(Page page);

  bool get canModifySite;

  Stream<User> get onChange;

}


class JSONUser extends User {
  String _username, _mail, _parent;

  DateTime _lastLogin;

  StreamController<User> _changeController = new StreamController<User>();
  Stream<User> _changeStream;

  final JSONClient _client;

  int _privileges;

  List<Page> _pages;

  JSONUser(String username, String mail, String parent, int lastLogin, int privileges, List<Page> pages, JSONClient client):_client = client {
    _username = username;
    _mail = mail;
    _parent = parent;
    _lastLogin = lastLogin == null?null:new DateTime.fromMillisecondsSinceEpoch(lastLogin*1000);
    _pages = privileges == User.PRIVILEGE_PAGE ? new List<Page>.from(pages) : <Page>[];
    _privileges = privileges;

  }

  String get username => _username;

  String get mail => _mail;

  String get parent => _parent;

  DateTime get lastLogin => _lastLogin;

  Future<ChangeResponse<User>> changeInfo({String username:null, String mail:null}) {
    var completer = new Completer<ChangeResponse<User>>();
    mail = mail != null ? mail : _mail;
    username = username != null ? username : _username;

    var jsonFunction = new ChangeUserInfoJSONFunction(_username, username, mail);
    _client.callFunction(jsonFunction).then( (JSONResponse response) {
      if (response.type == Response.RESPONSE_TYPE_SUCCESS) {
        _username = username;
        _mail = mail;
        _callListeners();
        completer.complete(new ChangeResponse<User>.success(this));
      } else {
        completer.complete(new ChangeResponse<User>.error(response.error_code));
      }
    });
    return completer.future;
  }

  Future<ChangeResponse<User>> changePassword(String currentPassword, String newPassword) {
    var completer = new Completer<ChangeResponse<User>>();
    var jsonFunction = new ChangeUserPasswordJSONFunction(_username, currentPassword, newPassword);
    _client.callFunction(jsonFunction).then((JSONResponse response) {
      if(response.type == Response.RESPONSE_TYPE_SUCCESS){
        completer.complete(new ChangeResponse<User>.success(this));
      } else {
        completer.complete(new ChangeResponse<User>.error(response.error_code));
      }

    });
    return completer.future;
  }

  Stream<User> get onChange => _changeStream == null? _changeStream = _changeController.stream.asBroadcastStream():_changeStream;

  void _callListeners() {
    _changeController.add(this);
  }

  int get privileges => _privileges;


  List<Page> get pages => new List<Page>.from(_pages);


  Future<ChangeResponse<User>> addPagePrivilege(Page page) {
    var completer = new Completer<ChangeResponse<User>>();
    var function = new AddUserPagePrivilegeJSONFunction(_username, page.id);
    _client.callFunction(function).then( (JSONResponse response) {
      if (response.type == Response.RESPONSE_TYPE_SUCCESS) {
        _pages.add(page);
        completer.complete(new ChangeResponse<User>.success(this));
        _callListeners();
      } else {
        completer.complete(new ChangeResponse<User>.error(response.error_code));
      }
    });
    return completer.future;
  }

  Future<ChangeResponse<User>> revokePagePrivilege(Page page) {
    var completer = new Completer<ChangeResponse<User>>();
    var function = new RevokeUserPagePrivilegeJSONFunction(_username, page.id);
    _client.callFunction(function).then((JSONResponse response) {
      if (response.type == Response.RESPONSE_TYPE_SUCCESS) {
        _pages.remove(page);
        _callListeners();
        completer.complete(new ChangeResponse<User>.success(this));
      } else {
        completer.complete(new ChangeResponse<User>.error(response.error_code));
      }
    });
    return completer.future;
  }

  bool get hasRootPrivileges => privileges == User.PRIVILEGE_ROOT;

  bool get hasSitePrivileges => privileges == User.PRIVILEGE_ROOT || privileges == User.PRIVILEGE_SITE;

  bool canModifyPage(Page page) => _privileges == User.PRIVILEGE_ROOT || _privileges == User.PRIVILEGE_SITE ||  _pages.map((Page p) => p.id).contains(page.id);

  bool get canModifySite => _privileges == User.PRIVILEGE_ROOT || _privileges == User.PRIVILEGE_SITE;

}