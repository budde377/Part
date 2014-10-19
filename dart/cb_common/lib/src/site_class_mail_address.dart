part of site_classes;

abstract class MailAddress {

  String get localPart;

  MailDomain get domain;

  MailDomainLibrary get domainLibrary;

  MailAddressLibrary get addressLibrary;

  Future<Response<String>> changeLocalPart(String localPart);

  bool get hasMailbox;

  MailMailbox get mailbox;

  Future<Response<MailMailbox>> createMailbox();

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

  DateTime _lastModified;

  AJAXMailAddress(this._localPart, MailAddressLibrary addressLibrary, {MailMailbox mailbox:null, bool active: true, List<String> targets:null, DateTime last_modified:null}):
  this._active = active,
  this._mailbox = mailbox,
  this.addressLibrary = addressLibrary,
  this.domainLibrary = addressLibrary.domainLibrary,
  this.domain = addressLibrary.domain,
  this._targets = targets == null ? [] : null,
  this._lastModified = (last_modified == null ? new DateTime.fromMillisecondsSinceEpoch(0) : last_modified);

  AJAXMailAddress.fromJSONObject(JSONObject object, MailAddressLibrary addressLibrary) :
  this.addressLibrary = addressLibrary,
  this.domainLibrary = addressLibrary.domainLibrary,
  this.domain = addressLibrary.domain,
  this._active = object.variables['active'],
  this._localPart = object.variables['local_part'],
  this._targets = object.variables['targets'],
  this._lastModified = new DateTime.fromMillisecondsSinceEpoch(object.variables['last_modified'] * 1000){
    _mailbox = object.variables['mailbox'] == null ? null : new AJAXMailMailbox.fromJSONObject(object.variables['mailbox'], this);

  }


}