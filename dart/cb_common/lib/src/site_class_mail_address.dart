part of site_classes;

abstract class MailAddress{

  String get localPart;

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

  MailDomain get domain;

  MailDomainLibrary get domainLibrary;

  MailAddressLibrary get addressLibrary;

  List<User> get owners;

  Future<Response<User>> addOwner(User user);

  Future<Response<User>> removeOwner(User user);

  DateTime get lastModified;

  bool get active;

  Future<Response<MailAddress>> deactivate();

  Future<Response<MailAddress>> activate();

  Future<Response<MailAddress>> toggleActive();


}

class AJAXMailAddress extends MailAddress{

  final MailDomainLibrary domainLibrary;

  final MailDomain domain;

  final MailAddressLibrary addressLibrary;

  String _localPart;

  MailMailbox _mailbox;

  AJAXMailAddress(this._localPart, this.addressLibrary, [this._mailbox = null]):
  this.domainLibrary = addressLibrary.domainLibrary,
  this.domain = addressLibrary.domain;

  AJAXMailAddress.fromJSONObject(JSONObject object, this.addressLibrary) :
    this.domainLibrary = addressLibrary.domainLibrary,
    this.domain = addressLibrary.domain,
    this._localPart = object.variables['local_part'],
    this._mailbox = object.variables['mailbox'] == null?null:new AJAXMailMailbox.fromJSONObject(object.variables['mailbox'], this);

/*
        $this->setVariable('active', $address->isActive());
        $this->setVariable('last_modified', $address->lastModified());
        $this->setVariable('targets', $address->getTargets());
        $this->setVariable('mailbox', $address->getMailbox());
 */

  String get localPart;

  Future<Response<String>> changeLocalPart(String localPart);

  bool get hasMailbox;

  MailMailbox get mailbox;

  Future<Response<MailMailbox>> createMailbox();

  Future<Response<MailMailbox>> deleteMailbox();

  List<String> get targets;

  Future<Response<String>> addTarget(String target);

  Future<Response<String>> removeTarget(String target);

  Future<Response<MailAddress>> delete();


  List<User> get owners;

  Future<Response<User>> addOwner(User user);

  Future<Response<User>> removeOwner(User user);

  DateTime get lastModified;

  bool get active;

  Future<Response<MailAddress>> deactivate();

  Future<Response<MailAddress>> activate();

  Future<Response<MailAddress>> toggleActive();



}