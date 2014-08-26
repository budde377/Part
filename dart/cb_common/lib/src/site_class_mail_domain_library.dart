part of site_classes;


abstract class MailDomainLibrary{

  List<MailDomain> get domains;

  Future<ChangeResponse<MailDomain>> createDomain(String domainName);
  Future<ChangeResponse<MailDomain>> deleteDomain(MailDomain domain);

}