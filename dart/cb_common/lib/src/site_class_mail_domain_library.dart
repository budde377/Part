part of site_classes;


abstract class MailDomainLibrary extends GeneratorDependable<MailDomain> {

  Map<String, MailDomain> get domains;

  FutureResponse<MailDomain> createDomain(String domainName, String password);

  FutureResponse<MailDomain> deleteDomain(MailDomain domain, String password);

  MailDomain operator [] (String key);

}


class AJAXMailDomainLibrary implements MailDomainLibrary {

  final UserLibrary userLibrary;

  LazyMap<String, MailDomain> _domains;

  StreamController<MailDomain>
  _onAddController = new StreamController<MailDomain>.broadcast(),
  _onUpdateController = new StreamController<MailDomain>.broadcast(),
  _onRemoveController = new StreamController<MailDomain>.broadcast();


  AJAXMailDomainLibrary(Iterable<String> domainNames, MailDomain domainGenerator(MailDomainLibrary, String), this.userLibrary) {
    _domains = new LazyMap<String, MailDomain>.fromGenerator(domainNames, (String dn) => _generator_wrapper(domainGenerator(this, dn)));
  }

  factory AJAXMailDomainLibrary.fromJSONObject(JSONObject object, UserLibrary userLibrary){
    Map<String, JSONObject> objectDomains = object.variables['domains'];
    return new AJAXMailDomainLibrary(
        objectDomains.keys,
            (MailDomainLibrary library, String name) => new AJAXMailDomain.fromJSONObject(objectDomains[name], library, userLibrary),
        userLibrary);
  }


  MailDomain _generator_wrapper(MailDomain domain) {

    var l = (_) => _onUpdateController.add(domain);

    domain
      ..onActiveChange.listen(l)
      ..onDescriptionChange.listen(l)
      ..onAliasTargetChange.listen(l);


    return domain;
  }


  Map<String, MailDomain> get domains => _domains;

  FutureResponse<MailDomain> createDomain(String domainName, String password) {
    var completer = new Completer();

    ajaxClient.callFunctionString("MailDomainLibrary.createDomain(${quoteString(domainName)}, ${quoteString(password)})").then((Response<JSONObject> response) {
      if (response.type != Response.RESPONSE_TYPE_SUCCESS) {
        completer.complete(response);
        return;
      }

      if (response.payload == null) {
        completer.complete(new Response.error(Response.ERROR_CODE_UNKNOWN_ERROR));
        return;
      }

      var domain = _generator_wrapper(new AJAXMailDomain.fromJSONObject(response.payload, this, userLibrary));
      _domains[domain.domainName] = domain;
      completer.complete(new Response.success(domain));
      _onAddController.add(domain);

    });

    return new FutureResponse(completer.future);
  }

  FutureResponse<MailDomain> deleteDomain(MailDomain domain, String password) {
    var completer = new Completer();
    var domainName = domain.domainName;

    ajaxClient.callFunctionString("MailDomainLibrary..deleteDomain(MailDomainLibrary.getDomain(${quoteString(domainName)}), ${quoteString(password)})..getDomain(${quoteString(domainName)})").then((Response<JSONObject> response) {
      if (response.type != Response.RESPONSE_TYPE_SUCCESS) {
        completer.complete(response);
        return;
      }

      if (response.payload != null) {
        completer.complete(new FutureResponse.error(Response.ERROR_CODE_UNKNOWN_ERROR));
        return;
      }

      _domains.remove(domainName);
      completer.complete(new Response.success(domain));
      _onRemoveController.add(domain);
    });

    return new FutureResponse(completer.future);

  }

  Stream<MailDomain> get onAdd => _onAddController.stream;

  Stream<MailDomain> get onRemove => _onRemoveController.stream;

  Stream<MailDomain> get onUpdate => _onUpdateController.stream;

  MailDomain operator [] (String key) => domains[key];

}