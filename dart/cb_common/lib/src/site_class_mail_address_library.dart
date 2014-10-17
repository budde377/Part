part of site_classes;

abstract class MailAddressLibrary{

  MailAddress get catchallAddress;

  List<MailAddress> get addresses;

  Future<Response<MailAddress>> createAddress(String localPart);

  Future<Response<MailAddress>> deleteAddress(MailAddress localPart);

  Future<Response<MailAddress>> createCatchallAddress();

  Future<Response<MailAddressLibrary>> deleteCatchallAddress();


}


class AJAXMailAddressLibrary{


  final MailDomainLibrary _domainLibrary;

  final MailDomain _domain;

  final Map<String, MailAddress> _addresses;

  MailAddress _catchallAddress;

  AJAXMailAddressLibrary(this._addresses, this._domain, this._domainLibrary, [this._catchallAddress=null]);

  AJAXMailAddressLibrary.fromJSONObject(JSONObject object, this._domain, this._domainLibrary):
  _catchallAddress = object.variables['catchall_address'] == null?null:new AJAXMailAddress.fromJSONObject(object.variables['catchall_address'], this, _domain, _domainLibrary),
  _addresses = new Map.fromIterable(object.variables['addresses'], key:(JSONObject object)=>object.variables['local_part'], value:(JSONObject object) => new AJAXMailAddress.fromJSONObject(object, this, _domain, _domainLibrary));


}