part of site_classes;


abstract class MailMailbox{

  String get name;

  Future<ChangeResponse<MailMailbox>> changeInfo ({String name, String password});

  Future<ChangeResponse<MailMailbox>> delete ();

  MailAddress get address;


}



class AJAXMailMailbox extends MailMailbox{

  final MailDomain domain;

  final MailDomain domainLibrary;

  final MailDomain addressLibrary;

  final MailAddress address;

  String _name;

  JSONClient _client = new AJAXJSONClient();


  AJAXMailMailbox(MailAddress this.address, [this._name = ""]): domainLibrary = address.domainLibrary, domain = address.domain, addressLibrary = address.addressLibrary;

  AJAXMailMailbox.fromJSONObject(JSONObject, this.address): domainLibrary = address.domainLibrary, domain = address.domain, addressLibrary = address.addressLibrary;



  String get name => _name;

  Future<ChangeResponse<MailMailbox>> changeInfo ({String name, String password}) => null;

  Future<ChangeResponse<MailMailbox>> delete () => null;


}