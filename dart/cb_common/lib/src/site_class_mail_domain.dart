part of site_classes;

abstract class MailDomain{

  String get domainName;

  List<MailAddress> get addresses;

  Future<ChangeResponse<MailAddress>> createAddress(String localPart);
  Future<ChangeResponse<MailAddress>> deleteAddress(MailAddress localPart);

  String toString() => domainName;

  Future<ChangeResponse<MailDomain>> delete();

  MailDomainLibrary get library;

}