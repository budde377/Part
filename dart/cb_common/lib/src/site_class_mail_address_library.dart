part of site_classes;

abstract class MailAddressLibrary{

  MailAddress get catchallAddress;

  List<MailAddress> get addresses;

  Future<Response<MailAddress>> createAddress(String localPart);

  Future<Response<MailAddress>> deleteAddress(MailAddress localPart);

  Future<Response<MailAddress>> createCatchallAddress();

  Future<Response<MailAddressLibrary>> deleteCatchallAddress();

  MailDomain get domain;

  MailDomainLibrary get domainLibrary;

}


class AJAXMailAddressLibrary extends MailAddressLibrary{


  final MailDomainLibrary domainLibrary;

  final MailDomain domain;

  final Map<String, MailAddress> _addresses;

  MailAddress _catchallAddress;

  AJAXMailAddressLibrary(this._addresses, this.domain, [this._catchallAddress=null]): domainLibrary = domain.domainLibrary;

  AJAXMailAddressLibrary.fromJSONObject(JSONObject object, this.domain):
  domainLibrary = domain.domainLibrary,
  _catchallAddress = object.variables['catchall_address'] == null?null:new AJAXMailAddress.fromJSONObject(object.variables['catchall_address'], this),
  _addresses = new Map.fromIterable(object.variables['addresses'], key:(JSONObject object)=>object.variables['local_part'], value:(JSONObject object) => new AJAXMailAddress.fromJSONObject(object, this));


}