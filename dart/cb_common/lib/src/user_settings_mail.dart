part of user_settings;



class UserSettingsMailDomainLibrary implements MailDomainLibrary{

  final AJAXMailDomainLibrary domainLibrary;

  static UserSettingsMailDomainLibrary _cache;

  factory UserSettingsMailDomainLibrary() => _cache == null? _cache = new UserSettingsMailDomainLibrary._internal():_cache;

  UserSettingsMailDomainLibrary._internal() : domainLibrary = _generateLibrary();


  static AJAXMailDomainLibrary _generateLibrary(){

    var domainElementList = querySelectorAll("#UserSettingsContent #UserSettingsEditMailDomainList > li:not(.empty_list)");
    var domainNameList = domainElementList.map((LIElement li) => li.dataset['domain-name']);
    var domainElementMap = new Map<String, LIElement>.fromIterables(domainNameList, domainElementList);



    var addressLibraryGenerator = (MailDomain domain){
      var addressElementList = querySelectorAll("#UserSettingsContent #UserSettingsEditMailAddressList${domain.domainName} > li:not(.empty_list)");
      var addressLPList= addressElementList.map((LIElement li) => li.dataset['local-part']);
      var addressElementMap = new Map<String, LIElement>.fromIterables(addressLPList, addressElementList);

      var addressGenerator = (MailAddressLibrary lib, String localPart){
        var obj = addressElementMap[localPart];

        var targets = obj.dataset['targets'].split(" ");
        targets.removeWhere((String s) => s.isEmpty);

        var ownersStrings = obj.dataset['owners'].split(" ");
        ownersStrings.removeWhere((String s) => s.isEmpty);
        var owners = ownersStrings.map((String s)=> userLibrary.users[s]);

        return new AJAXMailAddress(localPart, lib, userLibrary,
        active: obj.dataset['active'] == "true",
        targets: targets,
        last_modified:new DateTime.fromMillisecondsSinceEpoch(int.parse(obj.dataset['last-modified'])*1000),
        owners:owners
        //TODO: add mailbox generator
        );
      };

      return new AJAXMailAddressLibrary(addressLPList, addressGenerator, domain, userLibrary);
    };

    var domainGenerator = (MailDomainLibrary library, String name) {
      var obj = domainElementMap[name];

      return new AJAXMailDomain(
          name,
          addressLibraryGenerator,
          library,
          userLibrary,
          description:obj.dataset['description'],
          active: obj.dataset['active'] == "true", // TODO: add alias target
          last_modified: new DateTime.fromMillisecondsSinceEpoch(int.parse(obj.dataset['last-modified']) * 1000)
      );
    };
    return new AJAXMailDomainLibrary(domainNameList, domainGenerator , userLibrary);


  }


}
