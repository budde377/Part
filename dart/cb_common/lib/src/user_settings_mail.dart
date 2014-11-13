part of user_settings;


class UserSettingsMailDomainLibrary implements MailDomainLibrary {

  final AJAXMailDomainLibrary domainLibrary;

  static UserSettingsMailDomainLibrary _cache;

  factory UserSettingsMailDomainLibrary() => _cache == null ? _cache = new UserSettingsMailDomainLibrary._internal() : _cache;

  UserSettingsMailDomainLibrary._internal() : domainLibrary = _generateLibrary();


  static AJAXMailDomainLibrary _generateLibrary() {
    var domainElementList = querySelectorAll("#UserSettingsContent #UserSettingsEditMailDomainList > li:not(.empty_list)");
    var domainNameList = domainElementList.map((LIElement li) => li.dataset['domain-name']);
    var domainElementMap = new Map<String, LIElement>.fromIterables(domainNameList, domainElementList);


    var addressLibraryGenerator = (MailDomain domain) {
      var addressElementList = querySelectorAll("#UserSettingsEditMailAddressLists > li > ul").firstWhere((UListElement ul) => ul.dataset['domain-name'] == domain.domainName).querySelectorAll("li:not(.empty_list)");

      var addressLPList = addressElementList.map((LIElement li) => li.dataset['local-part']);
      var addressElementMap = new Map<String, LIElement>.fromIterables(addressLPList, addressElementList);

      var addressGenerator = (MailAddressLibrary lib, String localPart) {
        var obj = addressElementMap[localPart];

        var targets = obj.dataset['targets'].split(" ");
        targets.removeWhere((String s) => s.isEmpty);

        var ownersStrings = obj.dataset['owners'].split(" ");
        ownersStrings.removeWhere((String s) => s.isEmpty);

        var owners = ownersStrings.map((String s) => userLibrary.users[s]);

        var mailboxGenerator = (MailAddress address) {
          if (obj.dataset['has-mailbox'] == "false") {
            return null;
          }
          return new AJAXMailMailbox(address,
          name:obj.dataset['mailbox-name'],
          lastModified:new DateTime.fromMillisecondsSinceEpoch(int.parse(obj.dataset['mailbox-last-modified']) * 1000));

        };

        return new AJAXMailAddress(localPart, lib, userLibrary,
        active: obj.dataset['active'] == "true",
        targets: targets,
        last_modified:new DateTime.fromMillisecondsSinceEpoch(int.parse(obj.dataset['last-modified']) * 1000),
        owners:owners,
        mailboxGenerator: mailboxGenerator
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
          active: obj.dataset['active'] == "true",
          alias_target: obj.dataset['alias-target'],
          last_modified: new DateTime.fromMillisecondsSinceEpoch(int.parse(obj.dataset['last-modified']) * 1000)
      );
    };

    return new AJAXMailDomainLibrary(domainNameList, domainGenerator, userLibrary);


  }

  Stream<MailDomain> get onDelete => domainLibrary.onDelete;


  Stream<MailDomain> get onCreate => domainLibrary.onCreate;


  core.FutureResponse<MailDomain> deleteDomain(MailDomain domain, String password) => domainLibrary.deleteDomain(domain, password);


  core.FutureResponse<MailDomain> createDomain(String domainName, String password) => domainLibrary.createDomain(domainName, password);


  Map<String, MailDomain> get domains => domainLibrary.domains;

  MailDomain operator [](String key) => domainLibrary[key];

}
