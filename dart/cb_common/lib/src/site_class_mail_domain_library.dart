part of site_classes;


abstract class MailDomainLibrary extends GeneratorDependable<MailDomain>{

  Map<String, MailDomain> get domains;

  FutureResponse<MailDomain> createDomain(String domainName, String password);

  FutureResponse<MailDomain> deleteDomain(MailDomain domain, String password);

  Stream<MailDomain> get onCreate;

  Stream<MailDomain> get onDelete;

  MailDomain operator [] (String key);

}


class AJAXMailDomainLibrary implements MailDomainLibrary {

  final UserLibrary userLibrary;

  LazyMap<String, MailDomain> _domains;

  StreamController<MailDomain>
  _onCreateController = new StreamController<MailDomain>(),
  _onDeleteController = new StreamController<MailDomain>();

  Stream<MailDomain>
  _onCreateStream,
  _onDeleteStream;


  AJAXMailDomainLibrary(Iterable<String> domainNames, MailDomain domainGenerator(MailDomainLibrary, String), this.userLibrary) {
    _domains = new LazyMap<String, MailDomain>.fromGenerator(domainNames, (String dn) => domainGenerator(this, dn));
  }

  factory AJAXMailDomainLibrary.fromJSONObject(JSONObject object, UserLibrary userLibrary){
    Map<String, JSONObject> objectDomains = object.variables['domains'];
    return new AJAXMailDomainLibrary(
        objectDomains.keys,
        (MailDomainLibrary library, String name) => new AJAXMailDomain.fromJSONObject(objectDomains[name], library, userLibrary),
        userLibrary);
  }


  Map<String, MailDomain> get domains => _domains;

  FutureResponse<MailDomain> createDomain(String domainName, String password) {
    var completer = new Completer();

    ajaxClient.callFunctionString("MailDomainLibrary.createDomain(${quoteString(domainName)}, ${quoteString(password)})").then((Response<JSONObject> response) {
      if (response.type != Response.RESPONSE_TYPE_SUCCESS) {
        completer.complete(response);
        return;
      }

      if(response.payload == null){
        completer.complete(new Response.error(Response.ERROR_CODE_UNKNOWN_ERROR));
        return;
      }

      var domain = new AJAXMailDomain.fromJSONObject(response.payload, this, userLibrary);
      _domains[domain.domainName] = domain;
      completer.complete(new Response.success(domain));
      _onCreateController.add(domain);

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

      if(response.payload != null){
        completer.complete(new FutureResponse.error(Response.ERROR_CODE_UNKNOWN_ERROR));
        return;
      }

      _domains.remove(domainName);
      completer.complete(new Response.success(domain));
      _onDeleteController.add(domain);
    });

    return new FutureResponse(completer.future);

  }

  Stream<MailDomain> get onCreate => _onCreateStream == null?_onCreateStream = _onCreateController.stream.asBroadcastStream():_onCreateStream;

  Stream<MailDomain> get onDelete => _onDeleteStream == null?_onDeleteStream = _onDeleteController.stream.asBroadcastStream():_onDeleteStream;

  MailDomain operator [] (String key) => domains[key];

}