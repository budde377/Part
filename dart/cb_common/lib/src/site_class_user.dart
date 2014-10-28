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

  FutureResponse<User> changeInfo({String username:null, String mail:null});

  FutureResponse<User> changePassword(String currentPassword, String newPassword);

  FutureResponse<User> addPagePrivilege(Page page);

  FutureResponse<User>revokePagePrivilege(Page page);

  bool get hasRootPrivileges;
  bool get hasSitePrivileges;

  bool canModifyPage(Page page);

  bool get canModifySite;

  Stream<User> get onChange;

}


class AJAXUser extends User {
  String _username, _mail, _parent;

  DateTime _lastLogin;

  StreamController<User> _changeController = new StreamController<User>();
  Stream<User> _changeStream;

  int _privileges;

  List<Page> _pages;

  AJAXUser(String username, String mail, String parent, int lastLogin, int privileges, List<Page> pages) {
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

  FutureResponse<User> changeInfo({String username:null, String mail:null}) {
    var completer = new Completer<Response<User>>();

    var functionString = "";

    if(mail != null && mail != _mail){
      functionString += "..setMail(${quoteString(mail)})";
    }

    if(username != null && username != _username){
      functionString += "..setUsername(${quoteString(username)})";
    }

    mail = mail != null ? mail : _mail;
    username = username != null ? username : _username;

    ajaxClient.callFunctionString("UserLibrary.getUser(${quoteString(_username)})$functionString..getInstance()").then( (JSONResponse response) {
      if (response.type == Response.RESPONSE_TYPE_SUCCESS) {

        _username = response.payload.variables["username"];
        _mail = response.payload.variables["mail"];
        _callListeners();
        completer.complete(new Response<User>.success(this));
      } else {
        completer.complete(new Response<User>.error(response.error_code));
      }
    });
    return new FutureResponse(completer.future);
  }

  FutureResponse<User> changePassword(String currentPassword, String newPassword) {
    var completer = new Completer<Response<User>>();
    ajaxClient.callFunctionString("UserLibrary.getUser(${quoteString(_username)}).setPassword(${quoteString(currentPassword)}, ${quoteString(newPassword)})").then((JSONResponse response) {
      if(response.type == Response.RESPONSE_TYPE_SUCCESS){
        completer.complete(new Response<User>.success(this));
      } else {
        completer.complete(new Response<User>.error(response.error_code));
      }

    });
    return new FutureResponse(completer.future);
  }

  Stream<User> get onChange => _changeStream == null? _changeStream = _changeController.stream.asBroadcastStream():_changeStream;

  void _callListeners() {
    _changeController.add(this);
  }

  int get privileges => _privileges;


  List<Page> get pages => new List<Page>.from(_pages);


  FutureResponse<User> addPagePrivilege(Page page) {
    var completer = new Completer<Response<User>>();
    var pageIdString = quoteString(page.id);
    ajaxClient.callFunctionString("UserLibrary.getUser(${quoteString(username)}).getUserPrivileges()..addPagePrivileges($pageIdString)..hasPagePrivileges($pageIdString)").then( (JSONResponse response) {
      if (response.type == Response.RESPONSE_TYPE_SUCCESS && response.payload) {
        _pages.add(page);
        completer.complete(new Response<User>.success(this));
        _callListeners();
      } else {
        completer.complete(new Response<User>.error(response.error_code));
      }
    });
    return new FutureResponse(completer.future);
  }

  FutureResponse<User> revokePagePrivilege(Page page) {
    var pageIdString = quoteString(page.id);

    var completer = new Completer<Response<User>>();
    ajaxClient.callFunctionString("UserLibrary.getUser(${quoteString(username)}).getUserPrivileges()..revokePagePrivileges($pageIdString)..hasPagePrivileges($pageIdString)").then((JSONResponse response) {
      if (response.type == Response.RESPONSE_TYPE_SUCCESS && !response.payload) {
        _pages.remove(page);
        _callListeners();
        completer.complete(new Response<User>.success(this));
      } else {
        completer.complete(new Response<User>.error(response.error_code));
      }
    });
    return new FutureResponse(completer.future);
  }

  bool get hasRootPrivileges => privileges == User.PRIVILEGE_ROOT;

  bool get hasSitePrivileges => privileges == User.PRIVILEGE_ROOT || privileges == User.PRIVILEGE_SITE;

  bool canModifyPage(Page page) => _privileges == User.PRIVILEGE_ROOT || _privileges == User.PRIVILEGE_SITE ||  _pages.map((Page p) => p.id).contains(page.id);

  bool get canModifySite => _privileges == User.PRIVILEGE_ROOT || _privileges == User.PRIVILEGE_SITE;

}