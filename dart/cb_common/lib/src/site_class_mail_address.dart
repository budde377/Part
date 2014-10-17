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

}

class AJAXMailAddress extends MailAddress{

  final MailDomainLibrary _domainLibrary;

  final MailDomain _domain;

  final MailAddressLibrary _addressLibrary;

  String _localPart;

  MailMailbox _mailbox;

  AJAXMailAddress(this._localPart, this._addressLibrary, this._domain, this._domainLibrary, [this._mailbox = null]);

  AJAXMailAddress.fromJSONObject(JSONObject object, this._addressLibrary, this._domain, this._domainLibrary) :
    this._localPart = object.variables['local_part'],
    this._mailbox = object.variables['mailbox'] == null?null:new AJAXMailMailbox.fromJSONObject(object.variables['mailbox']);



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

  MailDomain get domain;

  MailDomainLibrary get domainLibrary;

  MailAddressLibrary get addressLibrary;

  List<User> get owners;

  Future<Response<User>> addOwner(User user);

  Future<Response<User>> removeOwner(User user);

  DateTime get lastModified;

}