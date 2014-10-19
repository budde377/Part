part of site_classes;

abstract class MailAddress {

  String get localPart;

  MailDomain get domain;

  MailDomainLibrary get domainLibrary;

  MailAddressLibrary get addressLibrary;

  Future<Response<String>> changeLocalPart(String localPart);

  bool get hasMailbox;

  MailMailbox get mailbox;

  Future<Response<MailMailbox>> createMailbox(String name, String password);

  Future<Response<MailMailbox>> deleteMailbox();

  List<String> get targets;

  Future<Response<String>> addTarget(String target);

  Future<Response<String>> removeTarget(String target);

  String toString() => "$localPart@$domain";

  Future<Response<MailAddress>> delete();


  List<User> get owners;

  Future<Response<User>> addOwner(User user);

  Future<Response<User>> removeOwner(User user);

  DateTime get lastModified;

  bool get active;

  Future<Response<MailAddress>> deactivate();

  Future<Response<MailAddress>> activate();

  Future<Response<MailAddress>> toggleActive();

  Stream<MailAddress> get onLocalPartChange;

  Stream<MailMailbox> get onMailboxChange;

  Stream<String> get onAddTarget;

  Stream<String> get onRemoveTarget;

  Stream<String> get onTargetChange;

  Stream<User> get onAddOwner;

  Stream<User> get onRemoveOwner;

  Stream<bool> get onActiveChange;

  Stream<MailAddress> get onDelete;

}

class AJAXMailAddress extends MailAddress {

  final MailDomainLibrary domainLibrary;

  final MailDomain domain;

  final MailAddressLibrary addressLibrary;

  String _localPart;

  MailMailbox _mailbox;

  bool _active;

  List<String> _targets;

  List<User> _owners;

  DateTime _lastModified;

  final UserLibrary userLibrary;

  StreamController _onLocalPartChangeController = new StreamController(),
  _onMailboxChangeController = new StreamController(),
  _onAddTargetController = new StreamController(),
  _onRemoveTargetController = new StreamController(),
  _onTargetChangeController = new StreamController(),
  _onAddOwnerController = new StreamController(),
  _onRemoveOwnerController = new StreamController(),
  _onActiveChangeController = new StreamController(),
  _onDeleteController;


  AJAXMailAddress(this._localPart, MailAddressLibrary addressLibrary, this.userLibrary, {MailMailbox mailbox:null, bool active: true, List<String> targets:null, DateTime last_modified:null, List<User> owners:null}):
  this._active = active,
  this._mailbox = mailbox,
  this.addressLibrary = addressLibrary,
  this.domainLibrary = addressLibrary.domainLibrary,
  this.domain = addressLibrary.domain,
  this._targets = targets == null ? [] : targets,
  this._owners = owners == null? [] : owners,
  this._lastModified = (last_modified == null ? new DateTime.fromMillisecondsSinceEpoch(0) : last_modified);

  AJAXMailAddress.fromJSONObject(JSONObject object, MailAddressLibrary addressLibrary, this.userLibrary) :
  this.addressLibrary = addressLibrary,
  this.domainLibrary = addressLibrary.domainLibrary,
  this.domain = addressLibrary.domain,
  this._active = object.variables['active'],
  this._localPart = object.variables['local_part'],
  this._targets = object.variables['targets'],
  this._owners = object.variables['owners'].map((String s) => userLibrary.users[s]).toList(),
  this._lastModified = new DateTime.fromMillisecondsSinceEpoch(object.variables['last_modified'] * 1000){
    _mailbox = object.variables['mailbox'] == null ? null : new AJAXMailMailbox.fromJSONObject(object.variables['mailbox'], this);
  }


  String get localPart => _localPart;


  bool get hasMailbox => _mailbox != null;

  MailMailbox get mailbox => _mailbox;

  String get _functionStringSelector => "MailDomainLibrary.getDomain(${quoteString(domain.domainName)}).getAddressLibrary().getAddress(${quoteString(localPart)})";

  Future<Response<String>> changeLocalPart(String localPart){
    var completer = new Completer();
    ajaxClient.callFunctionString(_functionStringSelector+"..setLocalPart(${quoteString(localPart)})..getInstance()").then((Response<JSONObject> response){
      if(response.type != Response.RESPONSE_TYPE_SUCCESS){
        completer.complete(response);
        return;
      }

      var obj = response.payload;
      _localPart = obj.variables['local_part'];
      _lastChangeInSeconds = obj.variables['last_modified'];
      completer.complete(new Response.success(this));
      _onLocalPartChangeController.add(_localPart);

    });

    return completer.future;
  }

  void set _lastChangeInSeconds(int time){
    _lastModified = new DateTime.fromMillisecondsSinceEpoch(time*1000);
  }

  Future<Response<MailMailbox>> createMailbox(String name, String password){
    if(hasMailbox){
      return new Future(() => new Response.success(mailbox));
    }
    var completer = new Completer();
    ajaxClient.callFunctionString(_functionStringSelector+"..createMailbox(${quoteString(name)}, ${quoteString(password)})..getInstance()").then((Response<JSONObject> response){
      if(response.type != Response.RESPONSE_TYPE_SUCCESS){
        completer.complete(response);
        return;
      }

      var obj = response.payload;
      _mailbox = new AJAXMailMailbox.fromJSONObject(obj.variables['mailbox'], this);
      _lastChangeInSeconds = obj.variables['last_modified'];
      completer.complete(new Response.success(mailbox));
      _onMailboxChangeController.add(mailbox);

    });

    return completer.future;
  }

  Future<Response<MailMailbox>> deleteMailbox(){
    if(hasMailbox){
      return new Future(() => new Response.success(mailbox));
    }
    var completer = new Completer();
    ajaxClient.callFunctionString(_functionStringSelector+"..deleteMailbox()..getInstance()").then((Response<JSONObject> response){
      if(response.type != Response.RESPONSE_TYPE_SUCCESS){
        completer.complete(response);
        return;
      }

      var obj = response.payload;
      var m = mailbox;
      _mailbox = null;
      _lastChangeInSeconds = obj.variables['last_modified'];
      completer.complete(new Response.success(m));
      _onMailboxChangeController.add(null);

    });

    return completer.future;
  }

  List<String> get targets => new List.from(_targets, growable:false);

  Future<Response<String>> addTarget(String target);

  Future<Response<String>> removeTarget(String target);

  Future<Response<MailAddress>> delete();

  List<User> get owners => new List.from(_owners);

  Future<Response<User>> addOwner(User user);

  Future<Response<User>> removeOwner(User user);

  DateTime get lastModified;

  bool get active;

  Future<Response<MailAddress>> deactivate();

  Future<Response<MailAddress>> activate();

  Future<Response<MailAddress>> toggleActive();

  Stream<MailAddress> get onLocalPartChange => _onLocalPartChangeController.stream.asBroadcastStream();

  Stream<MailMailbox> get onMailboxChange => _onMailboxChangeController.stream.asBroadcastStream();

  Stream<String> get onAddTarget => _onAddTargetController.stream.asBroadcastStream();

  Stream<String> get onRemoveTarget => _onRemoveTargetController.stream.asBroadcastStream();

  Stream<User> get onAddOwner => _onAddOwnerController.stream.asBroadcastStream();

  Stream<User> get onRemoveOwner => _onRemoveOwnerController.stream.asBroadcastStream();

  Stream<bool> get onActiveChange => _onActiveChangeController.stream.asBroadcastStream();

  Stream<String> get onTargetChange=> _onTargetChangeController.stream.asBroadcastStream();

  Stream<MailAddress> get onDelete{
    if(_onDeleteController == null){
      _onDeleteController = new StreamController();
      addressLibrary.onDelete.listen((MailAddress address){
        if(address != this){
          return;
        }
        _onDeleteController.add(address);
      });
    }

    return _onDeleteController.stream.asBroadcastStream();
  }

}