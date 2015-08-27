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

  Stream<User> get onOwnerChange;

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

  StreamController
  _onLocalPartChangeController = new StreamController(),
  _onMailboxChangeController = new StreamController(),
  _onAddTargetController = new StreamController(),
  _onRemoveTargetController = new StreamController(),
  _onTargetChangeController = new StreamController(),
  _onAddOwnerController = new StreamController(),
  _onOwnerChangeController = new StreamController(),
  _onRemoveOwnerController = new StreamController(),
  _onActiveChangeController = new StreamController(),
  _onDeleteController;

  Stream
  _onLocalPartChangeStream,
  _onMailboxChangeStream,
  _onAddTargetStream,
  _onRemoveTargetStream,
  _onTargetChangeStream,
  _onAddOwnerStream,
  _onOwnerChangeStream,
  _onRemoveOwnerStream,
  _onActiveChangeStream,
  _onDeleteStream;


  AJAXMailAddress(this._localPart, MailAddressLibrary addressLibrary, this.userLibrary, {MailMailbox mailboxGenerator(MailAddress), bool active: true, Iterable<String> targets:null, DateTime last_modified:null, Iterable<User> owners:null}):
  this._active = active,
  this.addressLibrary = addressLibrary,
  this.domainLibrary = addressLibrary.domainLibrary,
  this.domain = addressLibrary.domain,
  this._targets = targets == null ? [] : targets.toList(),
  this._owners = owners == null ? [] : owners.toList(),
  this._lastModified = (last_modified == null ? new DateTime.fromMillisecondsSinceEpoch(0) : last_modified) {
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

  FutureResponse<String> changeLocalPart(String localPart) {
    var completer = new Completer();
    ajaxClient.callFunctionString(_functionStringSelector + "..setLocalPart(${quoteString(localPart)})..getInstance()").then((Response<JSONObject> response) {
      if (response.type != Response.RESPONSE_TYPE_SUCCESS) {
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

  void set _lastChangeInSeconds(int time) {
    _lastModified = new DateTime.fromMillisecondsSinceEpoch(time * 1000);
  }

  FutureResponse<MailMailbox> createMailbox(String name, String password) {
    if (hasMailbox) {
      return new FutureResponse.success(mailbox);
    }
    var completer = new Completer();
    ajaxClient.callFunctionString(_functionStringSelector + "..createMailbox(${quoteString(name)}, ${quoteString(password)})..getInstance()").then((Response<JSONObject> response) {
      if (response.type != Response.RESPONSE_TYPE_SUCCESS) {
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

  FutureResponse<MailMailbox> deleteMailbox() {
    if (hasMailbox) {
      return new FutureResponse.success(mailbox);
    }
    var completer = new Completer();
    ajaxClient.callFunctionString(_functionStringSelector + "..deleteMailbox()..getInstance()").then((Response<JSONObject> response) {
      if (response.type != Response.RESPONSE_TYPE_SUCCESS) {
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

  FutureResponse<String> addTarget(String target) {
    target = target.trim();
    if (targets.contains(target)) {
      return new FutureResponse.success(target);
    }

    return ajaxClient.callFunctionString(_functionStringSelector + "..addTarget(${quoteString(target)})..getInstance()").thenResponse(onSuccess:(Response<JSONObject> r) {

      _targets.add(target);
      _lastChangeInSeconds = r.payload.variables['last_modified'];
      _onTargetChangeController.add(target);
      _onAddTargetController.add(target);
      return new Response.success(target);
    });
  }

  FutureResponse<String> removeTarget(String target) {
    target = target.trim();
    if (!targets.contains(target)) {
      return new FutureResponse.success(target);
    }
    return ajaxClient.callFunctionString(_functionStringSelector + "..removeTarget(${quoteString(target)})..getInstance()").thenResponse(onSuccess:(Response<JSONObject> r) {
      _targets.remove(target);
      _lastChangeInSeconds = r.payload.variables['last_modified'];
      _onTargetChangeController.add(target);
      _onAddTargetController.add(target);
      return new Response.success(target);
    });
  }

  FutureResponse<MailAddress> delete() => addressLibrary.deleteAddress(this);

  List<User> get owners => new List.from(_owners);

  FutureResponse<User> addOwner(User user) {
    if (owners.contains(user)) {
      return new FutureResponse.success(user);
    }

    return ajaxClient.callFunctionString(_functionStringSelector + "..addOwner(UserLibrary.getUser(${quoteString(user.username)}))..getInstance()").thenResponse(onSuccess:(Response<JSONObject> r) {

      _owners.add(user);
      _lastChangeInSeconds = r.payload.variables['last_modified'];
      _onAddOwnerController.add(user);
      _onOwnerChangeController.add(user);
      return new Response.success(user);
    });
  }

  FutureResponse<User> removeOwner(User user) {
    if (!owners.contains(user)) {
      return new FutureResponse.success(user);
    }

    return ajaxClient.callFunctionString(_functionStringSelector + "..removeUser(UserLibrary.getUser(${quoteString(user.username)}))..getInstance()").thenResponse(onSuccess:(Response<JSONObject> r) {

      _owners.remove(user);
      _lastChangeInSeconds = r.payload.variables['last_modified'];
      _onRemoveOwnerController.add(user);
      _onOwnerChangeController.add(user);
      return new Response.success(user);
    });
  }

  DateTime get lastModified => _lastModified;

  bool get active => _active;

  FutureResponse<MailAddress> deactivate() =>
  ajaxClient.callFunctionString(_functionStringSelector + "..deactivate()..getInstance()").thenResponse(onSuccess:(Response<JSONObject> r) {

    _active = r.payload.variables['active'];
    _lastChangeInSeconds = r.payload.variables['last_modified'];
    _onActiveChangeController.add(active);
    return new Response.success(active);
  });

  FutureResponse<MailAddress> activate() =>
  ajaxClient.callFunctionString(_functionStringSelector + "..activate()..getInstance()").thenResponse(onSuccess:(Response<JSONObject> r) {
    _active = r.payload.variables['active'];
    _lastChangeInSeconds = r.payload.variables['last_modified'];
    _onActiveChangeController.add(active);
    return new Response.success(active);
  });


  FutureResponse<MailAddress> toggleActive() => active ? deactivate() : activate();

  Stream<MailAddress> get onLocalPartChange => _onLocalPartChangeStream == null ? _onLocalPartChangeStream = _onLocalPartChangeController.stream.asBroadcastStream() : _onLocalPartChangeStream;

  Stream<MailMailbox> get onMailboxChange => _onMailboxChangeStream == null ? _onMailboxChangeStream = _onMailboxChangeController.stream.asBroadcastStream() : _onMailboxChangeStream;

  Stream<String> get onAddTarget => _onAddTargetStream == null ? _onAddTargetStream = _onAddTargetController.stream.asBroadcastStream() : _onAddTargetStream;

  Stream<String> get onRemoveTarget => _onRemoveTargetStream == null ? _onRemoveTargetStream = _onRemoveTargetController.stream.asBroadcastStream() : _onRemoveTargetStream;

  Stream<User> get onAddOwner => _onAddOwnerStream == null ? _onAddOwnerStream = _onAddOwnerController.stream.asBroadcastStream() : _onAddOwnerStream;

  Stream<User> get onRemoveOwner => _onRemoveOwnerStream == null ? _onRemoveOwnerStream = _onRemoveOwnerController.stream.asBroadcastStream() : _onRemoveOwnerStream;

  Stream<bool> get onActiveChange => _onActiveChangeStream == null ? _onActiveChangeStream = _onActiveChangeController.stream.asBroadcastStream() : _onActiveChangeStream;

  Stream<String> get onTargetChange => _onTargetChangeStream == null ? _onTargetChangeStream = _onTargetChangeController.stream.asBroadcastStream() : _onTargetChangeStream;

  Stream<User> get onOwnerChange => _onOwnerChangeStream == null ? _onOwnerChangeStream = _onOwnerChangeController.stream.asBroadcastStream() : _onOwnerChangeStream;


  Stream<MailAddress> get onDelete {
    if (_onDeleteController == null) {
      _onDeleteController = new StreamController();
      _onDeleteStream = _onDeleteController.stream.asBroadcastStream();
      addressLibrary.onRemove.listen((MailAddress address) {
        if (address != this) {
          return;
        }
        _onDeleteController.add(address);
      });
    }

    return _onDeleteStream;
  }

}