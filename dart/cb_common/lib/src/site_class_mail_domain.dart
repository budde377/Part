part of site_classes;

abstract class MailDomain{

  String get domainName;

  MailAddressLibrary get addressLibrary;

  String get description;

  Future<Response<String>> changeDescription(String description);

  String toString() => domainName;

  Future<Response<MailDomain>> delete(String password);

  MailDomainLibrary get library;

  DateTime get lastModified;

}



class AJAXMailDomain extends MailDomain{

  String _domainName;

  MailAddressLibrary _addressLibrary;

  MailDomainLibrary _library;

  String _description = "";

  AJAXMailDomain(this._domainName, this._addressLibrary, this._library);


  AJAXMailDomain.fromJSONObject(JSONObject object, this._library):
    this._domainName = object.variables['domain_name'],
    this._description = object.variables['description'],
    _addressLibrary = new AJAXMailAddressLibrary.fromJSONObject(object.variables['addresses_library'], this, _library);

  String get domainName => _domainName;

  MailAddressLibrary get addressLibrary => _addressLibrary;

  Future<Response<MailDomain>> delete(String password){
    var completer = new Completer();
    _library.deleteDomain(this, password).then((Response response) =>
    completer.complete(response.type == Response.RESPONSE_TYPE_SUCCESS?new Response.success(this):response));

    return completer.future;
  }

  MailDomainLibrary get library => _library;

  String get description;

  Future<Response<String>> changeDescription(String description);




}