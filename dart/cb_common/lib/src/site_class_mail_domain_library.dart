part of site_classes;


abstract class MailDomainLibrary{

  Map<String,MailDomain> get domains;

  Future<Response<MailDomain>> createDomain(String domainName, String password);

  Future<Response<MailDomainLibrary>> deleteDomain(MailDomain domain, String password);

}



class AJAXMailDomainLibrary implements MailDomainLibrary{
  final Map<String, MailDomain> _domains;

  AJAXMailDomainLibrary(this._domains);

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

    });

    return completer.future;
  }

  Future<Response<MailDomainLibrary>> deleteDomain(MailDomain domain, String password){
    var completer = new Completer();
    var domainName = domain.domainName;

    ajaxClient.callFunctionString("MailDomainLibrary.deleteDomain(${quoteString(domainName)}, ${quoteString(password)})").then((Response<JSONObject> response){
      if(response.type != Response.RESPONSE_TYPE_SUCCESS){
        completer.complete(response);
        return;
      }

      var domain = new AJAXMailDomain.fromJSONObject(response.payload, this);
      _domains.remove(domain.domainName);
      completer.complete(new Response.success(this));

    });

    return completer.future;

  }

}