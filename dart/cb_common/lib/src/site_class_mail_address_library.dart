part of site_classes;

abstract class MailAddressLibrary {

  MailAddress get catchallAddress;

  MailDomain get domain;

  MailDomainLibrary get domainLibrary;

  Map<String, MailAddress> get addresses;

  Future<Response<MailAddress>> createAddress(String localPart);

  Future<Response<MailAddressLibrary>> deleteAddress(MailAddress address);

  Future<Response<MailAddress>> createCatchallAddress();

  Future<Response<MailAddressLibrary>> deleteCatchallAddress();


}


class AJAXMailAddressLibrary extends MailAddressLibrary {


  final MailDomainLibrary domainLibrary;

  final MailDomain domain;

  Map<String, MailAddress> _addresses;

  MailAddress _catchallAddress;

  AJAXMailAddressLibrary(this._addresses, MailDomain domain, [this._catchallAddress=null]):
  this.domain = domain,
  domainLibrary = domain.domainLibrary;

  AJAXMailAddressLibrary.fromJSONObject(JSONObject object, MailDomain domain):
  this.domain = domain,
  domainLibrary = domain.domainLibrary{
    _catchallAddress = object.variables['catchall_address'] == null ? null : new AJAXMailAddress.fromJSONObject(object.variables['catchall_address'], this);
    _addresses = new Map.fromIterable(object.variables['addresses'], key:(JSONObject object) => object.variables['local_part'], value:(JSONObject object) => new AJAXMailAddress.fromJSONObject(object, this));
  }

  MailAddress get catchallAddress => _catchallAddress;

  Map<String, MailAddress> get addresses => new Map<String, MailAddress>.from(_addresses);

  Future<Response<MailAddress>> createAddress(String localPart);

  Future<Response<MailAddressLibrary>> deleteAddress(MailAddress address);

  Future<Response<MailAddress>> createCatchallAddress();

  Future<Response<MailAddressLibrary>> deleteCatchallAddress();


}