part of site_classes;


abstract class MailDomainLibrary{

  Map<String,MailDomain> get domains;

  Future<Response<MailDomain>> createDomain(String domainName, String password);

  Future<Response<MailDomain>> deleteDomain(MailDomain domain, String password);

  Stream<MailDomain> get onCreate;

  Stream<MailDomain> get onDelete;


}



class AJAXMailDomainLibrary implements MailDomainLibrary{
  Map<String, MailDomain> _domains;

  StreamController<MailDomain>
  _onCreateController = new StreamController<MailDomain>(),
  _onDeleteController = new StreamController<MailDomain>();


  AJAXMailDomainLibrary(this._domains);

  AJAXMailDomainLibrary.fromJSONObject(JSONObject object){
    _domains = new Map<String, MailDomain>.fromIterable(object.variables['domains'],
    key:(JSONObject obj) => obj.variables['domain_name'],
    value:(JSONObject obj) => new AJAXMailDomain.fromJSONObject(obj, this));

  }



  Map<String,MailDomain> get domains => new Map<String, MailDomain>.from(_domains);

  Future<Response<MailDomain>> createDomain(String domainName, String password){
    var completer = new Completer();

    ajaxClient.callFunctionString("MailDomainLibrary.createDomain(${quoteString(domainName)}, ${quoteString(password)})").then((Response<JSONObject> response){
      if(response.type != Response.RESPONSE_TYPE_SUCCESS){
        completer.complete(response);
        return;
      }

      var domain = new AJAXMailDomain.fromJSONObject(response.payload, this);
      _domains[domain.domainName] = domain;
      completer.complete(new Response.success(domain));
      _onCreateController.add(domain);

    });

    return completer.future;
  }

  Future<Response<MailDomain>> deleteDomain(MailDomain domain, String password){
    var completer = new Completer();
    var domainName = domain.domainName;

    ajaxClient.callFunctionString("MailDomainLibrary.deleteDomain(${quoteString(domainName)}, ${quoteString(password)})").then((Response<JSONObject> response){
      if(response.type != Response.RESPONSE_TYPE_SUCCESS){
        completer.complete(response);
        return;
      }

      var domain = new AJAXMailDomain.fromJSONObject(response.payload, this);
      _domains.remove(domain.domainName);
      completer.complete(new Response.success(domain));
      _onDeleteController.add(domain);
    });

    return completer.future;

  }

  Stream<MailDomain> get onCreate => _onCreateController.stream.asBroadcastStream();

  Stream<MailDomain> get onDelete => _onDeleteController.stream.asBroadcastStream();
}