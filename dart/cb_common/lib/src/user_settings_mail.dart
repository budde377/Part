part of user_settings;



class UserSettingsMailDomainLibrary implements MailDomainLibrary{

  final AJAXMailDomainLibrary domainLibrary;

  static UserSettingsMailDomainLibrary _cache;

  factory UserSettingsMailDomainLibrary() => _cache == null? _cache = new UserSettingsMailDomainLibrary._internal():_cache;

  UserSettingsMailDomainLibrary._internal() : domainLibrary = _generateLibrary();


  static AJAXMailDomainLibrary _generateLibrary(){




    return new AJAXMailDomainLibrary();


  }


}
