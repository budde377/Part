part of site_classes;

abstract class MailDomain{

  String get domainName;

  MailAddressLibrary get addressLibrary;

  String get description;

  Future<Response<String>> changeDescription(String description);

  String toString() => domainName;

  Future<Response<MailDomain>> delete(String password);


  DateTime get lastModified;

  bool get active;

  Future<Response<MailAddress>> deactivate();

  Future<Response<MailAddress>> activate();

  Future<Response<MailAddress>> toggleActive();

  MailDomainLibrary get domainLibrary;

}



class AJAXMailDomain extends MailDomain{

  final MailDomainLibrary domainLibrary;

  String _domainName;

  MailAddressLibrary _addressLibrary;


  String _description = "";

  AJAXMailDomain(this._domainName, this._addressLibrary, this._library);


  AJAXMailDomain.fromJSONObject(JSONObject object, this._library):
    this._domainName = object.variables['domain_name'],
    this._description = object.variables['description'],
    _addressLibrary = new AJAXMailAddressLibrary.fromJSONObject(object.variables['addresses_library'], this);

  String get domainName => _domainName;

  MailAddressLibrary get addressLibrary => _addressLibrary;

  Future<Response<MailDomain>> delete(String password){
    var completer = new Completer();
    domainLibrary.deleteDomain(this, password).then((Response response) =>
    completer.complete(response.type == Response.RESPONSE_TYPE_SUCCESS?new Response.success(this):response));

    return completer.future;
  }


  String get description;

  Future<Response<String>> changeDescription(String description);


  bool get active;

  Future<Response<MailAddress>> deactivate();

  Future<Response<MailAddress>> activate();

  Future<Response<MailAddress>> toggleActive();


}