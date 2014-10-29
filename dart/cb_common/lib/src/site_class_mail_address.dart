part of site_classes;

abstract class MailAddress {

  String get localPart;

  MailDomain get domain;

  MailDomainLibrary get domainLibrary;

  MailAddressLibrary get addressLibrary;

  FutureResponse<String> changeLocalPart(String localPart);

  bool get hasMailbox;

  MailMailbox get mailbox;

  FutureResponse<MailMailbox> createMailbox(String name, String password);

  FutureResponse<MailMailbox> deleteMailbox();

  List<String> get targets;

  FutureResponse<String> addTarget(String target);

  FutureResponse<String> removeTarget(String target);

  String toString() => "$localPart@$domain";

  FutureResponse<MailAddress> delete();


  List<User> get owners;

  FutureResponse<User> addOwner(User user);

  FutureResponse<User> removeOwner(User user);

  DateTime get lastModified;

  bool get active;

  FutureResponse<MailAddress> deactivate();

  FutureResponse<MailAddress> activate();

  FutureResponse<MailAddress> toggleActive();

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


  AJAXMailAddress(this._localPart, MailAddressLibrary addressLibrary, this.userLibrary, {MailMailbox mailboxGenerator(MailAddress), bool active: true, Iterable<String> targets:null, DateTime last_modified:null, Iterable<User> owners:null}):
  this._active = active,
  this.addressLibrary = addressLibrary,
  this.domainLibrary = addressLibrary.domainLibrary,
  this.domain = addressLibrary.domain,
  this._targets = targets == null ? [] : targets.toList(),
  this._owners = owners == null? [] : owners.toList(),
  this._lastModified = (last_modified == null ? new DateTime.fromMillisecondsSinceEpoch(0) : last_modified){
    _mailbox = mailboxGenerator(this);
  }

  AJAXMailAddress.fromJSONObject(JSONObject object, MailAddressLibrary addressLibrary, this.userLibrary) :
  this.addressLibrary = addressLibrary,
  this.domainLibrary = addressLibrary.domainLibrary,
  this.domain = addressLibrary.domain,
  this._active = object.variables['active'],
  this._localPart = object.variables['local_part'],
  this._targets = object.variables['targets'],
  this._lastModified = new DateTime.fromMillisecondsSinceEpoch(object.variables['last_modified'] * 1000){
    _owners = object.variables['owners'].map((String s) => userLibrary.users[s]).toList();
    _mailbox = object.variables['mailbox'] == null ? null : new AJAXMailMailbox.fromJSONObject(object.variables['mailbox'], this);
  }


  String get localPart => _localPart;


  bool get hasMailbox => _mailbox != null;

  MailMailbox get mailbox => _mailbox;

  String get _functionStringSelector => "MailDomainLibrary.getDomain(${quoteString(domain.domainName)}).getAddressLibrary().getAddress(${quoteString(localPart)})";

  FutureResponse<String> changeLocalPart(String localPart){
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

    return new FutureResponse(completer.future);
  }

  void set _lastChangeInSeconds(int time){
    _lastModified = new DateTime.fromMillisecondsSinceEpoch(time*1000);
  }

  FutureResponse<MailMailbox> createMailbox(String name, String password){
    if(hasMailbox){
      return new FutureResponse.success(mailbox);
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

    return new FutureResponse(completer.future);
  }

  FutureResponse<MailMailbox> deleteMailbox(){
    if(hasMailbox){
      return new FutureResponse.success(mailbox);
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

    return new FutureResponse(completer.future);
  }

  List<String> get targets => new List.from(_targets, growable:false);

  FutureResponse<String> addTarget(String target){
    target = target.trim();
    if(targets.contains(target)){
      return new FutureResponse.success(target);
    }

    var completer = new Completer();

    ajaxClient.callFunctionString(_functionStringSelector+"..addTarget(${quoteString(target)})..getInstance()").thenResponse(onSuccess:(Response<JSONObject> r){

      _targets.add(target);
      _lastChangeInSeconds = r.payload.variables['last_modified'];
      completer.complete(new Response.success(target));
      _onTargetChangeController.add(target);
      _onAddTargetController.add(target);


    }, onError:completer.complete);

    return new FutureResponse(completer.future);
  }

  FutureResponse<String> removeTarget(String target){
    target = target.trim();
    if(!targets.contains(target)){
      return new FutureResponse.success(target);
    }

    var completer = new Completer();

    ajaxClient.callFunctionString(_functionStringSelector+"..removeTarget(${quoteString(target)})..getInstance()").thenResponse(onSuccess:(Response<JSONObject> r){


      _targets.remove(target);
      _lastChangeInSeconds = r.payload.variables['last_modified'];
      completer.complete(new Response.success(target));
      _onTargetChangeController.add(target);
      _onAddTargetController.add(target);

    }, onError:completer.complete);

    return new FutureResponse(completer.future);
  }

  FutureResponse<MailAddress> delete() => addressLibrary.deleteAddress(this);

  List<User> get owners => new List.from(_owners);

  FutureResponse<User> addOwner(User user){
    if(owners.contains(user)){
      return new FutureResponse.success(user);
    }

    var completer = new Completer();

    ajaxClient.callFunctionString(_functionStringSelector+"..addOwner(UserLibrary.getUser(${quoteString(user.username)}))..getInstance()").thenResponse(onSuccess:(Response<JSONObject> r){

      _owners.add(user);
      _lastChangeInSeconds = r.payload.variables['last_modified'];
      completer.complete(new Response.success(user));
      _onAddOwnerController.add(user);


    }, onError:completer.complete);

    return new FutureResponse(completer.future);
  }

  FutureResponse<User> removeOwner(User user){
    if(!owners.contains(user)){
      return new FutureResponse.success(user);
    }

    var completer = new Completer();

    ajaxClient.callFunctionString(_functionStringSelector+"..removeUser(UserLibrary.getUser(${quoteString(user.username)}))..getInstance()").thenResponse(onSuccess:(Response<JSONObject> r){

      _owners.remove(user);
      _lastChangeInSeconds = r.payload.variables['last_modified'];
      completer.complete(new Response.success(user));
      _onRemoveOwnerController.add(user);

    }, onError:completer.complete);

    return new FutureResponse(completer.future);
  }

  DateTime get lastModified => _lastModified;

  bool get active => _active;

  FutureResponse<MailAddress> deactivate(){
    var completer = new Completer();

    ajaxClient.callFunctionString(_functionStringSelector+"..deactivate()..getInstance()").thenResponse(onSuccess:(Response<JSONObject> r){

      _active = r.payload.variables['active'];
      _lastChangeInSeconds = r.payload.variables['last_modified'];
      completer.complete(new Response.success(active));
      _onActiveChangeController.add(active);

    }, onError:completer.complete);

    return new FutureResponse(completer.future);
  }

  FutureResponse<MailAddress> activate(){
    var completer = new Completer();

    ajaxClient.callFunctionString(_functionStringSelector+"..activate()..getInstance()").thenResponse(onSuccess:(Response<JSONObject> r){

      _active = r.payload.variables['active'];
      _lastChangeInSeconds = r.payload.variables['last_modified'];
      completer.complete(new Response.success(active));
      _onActiveChangeController.add(active);

    }, onError:completer.complete);

    return new FutureResponse(completer.future);

  }

  FutureResponse<MailAddress> toggleActive() => active?deactivate():activate();

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