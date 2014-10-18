part of site_classes;


abstract class MailMailbox{

  MailDomain get domain;

  MailDomainLibrary get domainLibrary;

  MailAddressLibrary get addressLibrary;

  MailAddress get address;

  String get name;

  Future<ChangeResponse<MailMailbox>> changeInfo ({String name, String password});

  Future<ChangeResponse<MailMailbox>> delete ();


}



class AJAXMailMailbox extends MailMailbox{

  final MailDomain domain;

  final MailDomainLibrary domainLibrary;

  final MailAddressLibrary addressLibrary;

  final MailAddress address;

  String _name;

  JSONClient _client = new AJAXJSONClient();


  AJAXMailMailbox(MailAddress address, [this._name = ""]):
  this.address = address,
  domainLibrary = address.domainLibrary,
  domain = address.domain,
  addressLibrary = address.addressLibrary;

  AJAXMailMailbox.fromJSONObject(JSONObject object, MailAddress address):
  this.address = address,
  domainLibrary = address.domainLibrary,
  domain = address.domain,
  addressLibrary = address.addressLibrary,
  _name = object.variables['name'];


  String get name => _name;

  Future<ChangeResponse<MailMailbox>> changeInfo ({String name, String password}) => null;

  Future<ChangeResponse<MailMailbox>> delete () => null;


}