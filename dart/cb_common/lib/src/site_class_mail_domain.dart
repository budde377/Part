part of site_classes;

abstract class MailDomain{

  String get domainName;

  MailAddressLibrary get addressLibrary;

  MailDomainLibrary get domainLibrary;

  String get description;

  Future<Response<String>> changeDescription(String description);

  String toString() => domainName;

  Future<Response<MailDomain>> delete(String password);


  DateTime get lastModified;

  bool get active;

  Future<Response<MailDomain>> deactivate();

  Future<Response<MailDomain>> activate();

  Future<Response<MailDomain>> toggleActive();


}



class AJAXMailDomain extends MailDomain{

  final MailDomainLibrary domainLibrary;

  MailAddressLibrary _addressLibrary;

  final String domainName;

  bool _active;

  DateTime _lastModified;

  String _description;

  AJAXMailDomain(this.domainName, this._addressLibrary, this.domainLibrary, {String description:"", bool active:true, DateTime last_modified: null}) :
  _description = description,
  _active = active,
  _lastModified = last_modified == null? new DateTime.fromMillisecondsSinceEpoch(0):last_modified;


  AJAXMailDomain.fromJSONObject(JSONObject object, this.domainLibrary):
    this.domainName = object.variables['domain_name'],
    this._active = object.variables['active'],
    this._description = object.variables['description'],
    this._lastModified = new DateTime.fromMillisecondsSinceEpoch(object.variables['last_modifed']*1000){
    this._addressLibrary = new AJAXMailAddressLibrary.fromJSONObject(object.variables['addresses_library'], this);

  }


  Future<Response<MailDomain>> delete(String password){
    var completer = new Completer();
    domainLibrary.deleteDomain(this, password).then((Response response) =>
    completer.complete(response.type == Response.RESPONSE_TYPE_SUCCESS?new Response.success(this):response));

    return completer.future;
  }


  String get description => _description;

  Future<Response<String>> changeDescription(String description);


  bool get active => _active;

  Future<Response<MailDomain>> deactivate();

  Future<Response<MailDomain>> activate();

  Future<Response<MailDomain>> toggleActive();

  DateTime get lastModified;

  MailAddressLibrary get addressLibrary => _addressLibrary;


}