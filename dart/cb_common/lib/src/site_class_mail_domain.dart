part of site_classes;

abstract class MailDomain {

  String get domainName;

  MailAddressLibrary get addressLibrary;

  MailDomainLibrary get domainLibrary;

  String get description;

  FutureResponse<String> changeDescription(String description);

  String toString() => domainName;

  FutureResponse<MailDomain> delete(String password);

  FutureResponse<MailDomain> changeAliasTarget(MailDomain domain);

  FutureResponse<MailDomain> removeAliasTarget();

  bool get isDomainAlias;

  MailDomain get aliasTarget;


  DateTime get lastModified;

  bool get active;

  FutureResponse<bool> deactivate();

  FutureResponse<bool> activate();

  FutureResponse<bool> toggleActive();

  Stream<MailDomain> get onAliasTargetChange;

  Stream<String> get onDescriptionChange;

  Stream<MailDomain> get onDelete;

  Stream<bool> get onActiveChange;

}


class AJAXMailDomain extends MailDomain {

  final MailDomainLibrary domainLibrary;

  MailAddressLibrary _addressLibrary;

  final String domainName;

  final UserLibrary userLibrary;

  bool _active;

  DateTime _lastModified;

  String _description;

  MailDomain _aliasTarget;

  StreamController
  _onDeleteController,
  _onAliasTargetChange = new StreamController<bool>(),
  _onActiveChangeController = new StreamController<bool>(),
  _onDescriptionChangeController = new StreamController<String>();

  AJAXMailDomain(this.domainName, this._addressLibrary, this.domainLibrary, this.userLibrary, {String description:"", bool active:true, DateTime last_modified: null, MailDomain alias_target}) :
  _description = description,
  _active = active,
  _aliasTarget = alias_target,
  _lastModified = last_modified == null ? new DateTime.fromMillisecondsSinceEpoch(0) : last_modified;


  AJAXMailDomain.fromJSONObject(JSONObject object, this.domainLibrary, this.userLibrary):
  this.domainName = object.variables['domain_name'],
  this._active = object.variables['active'],
  this._description = object.variables['description'],
  this._lastModified = new DateTime.fromMillisecondsSinceEpoch(object.variables['last_modifed'] * 1000){
    this._addressLibrary = new AJAXMailAddressLibrary.fromJSONObject(object.variables['addresses_library'], this, userLibrary);
    this._aliasTarget = object.variables['alias_target'] == null?null:domainLibrary.domains[object.variables['alias_target'].variables['domain_name']];
  }

  String get _domainLibraryGetter => "MailDomainLibrary.getDomain(${quoteString(domainName)})";

  FutureResponse<MailDomain> delete(String password) => domainLibrary.deleteDomain(this, password);


  FutureResponse<MailDomain> changeAliasTarget(MailDomain domain){
    var completer = new Completer();
    ajaxClient.callFunctionString(_domainLibraryGetter+"..setAliasTarget(MailDomainLibrary.getDomain(${quoteString(domain.domainName)}))..getInstance()")
    .thenResponse(onError:completer.complete, onSuccess:(Response r){
      _aliasTarget = domain;
      _lastModified = new DateTime.fromMillisecondsSinceEpoch(r.payload.variables['last_modified'] * 1000);
      completer.complete(new Response.success(_aliasTarget));
      _onAliasTargetChange.add(_aliasTarget);
    });


    return completer.future;
  }

  FutureResponse<MailDomain> removeAliasTarget(){
    if(!isDomainAlias){
      return new FutureResponse.success(null);
    }
    var completer = new Completer();
    ajaxClient.callFunctionString(_domainLibraryGetter+"..removeAliasTarget(MailDomainLibrary.getDomain(${quoteString(_aliasTarget.domainName)}))..getInstance()")
    .thenResponse(onError:completer.complete, onSuccess:(Response r){
      _aliasTarget = null;
      _lastModified = new DateTime.fromMillisecondsSinceEpoch(r.payload.variables['last_modified'] * 1000);
      completer.complete(new Response.success(_aliasTarget));
      _onAliasTargetChange.add(_aliasTarget);
    });


    return completer.future;
  }

  bool get isDomainAlias => _aliasTarget != null;

  MailDomain get aliasTarget => _aliasTarget;


  String get description => _description;

  bool get active => _active;

  MailAddressLibrary get addressLibrary => _addressLibrary;

  FutureResponse<String> changeDescription(String description) {
    var completer = new Completer<Response<String>>();

    ajaxClient.callFunctionString(_domainLibraryGetter + ".setDescription(${quoteString(description)}).getInstance()").then((Response<JSONObject> response) {
      if (response.type != Response.RESPONSE_TYPE_SUCCESS) {
        completer.complete(response);
        return;
      }

      _description = response.payload.variables['description'];
      _lastModified = new DateTime.fromMillisecondsSinceEpoch(response.payload.variables['last_modified'] * 1000);
      completer.complete(new Response.success(_description));
      _onDescriptionChangeController.add(_description);
    });


    return new FutureResponse(completer.future);
  }


  DateTime get lastModified => _lastModified;

  FutureResponse<bool> deactivate() {
    if (!active) {
      return new Future(() => active);
    }

    var completer = new Completer<Response<String>>();

    ajaxClient.callFunctionString(_domainLibraryGetter + ".deactivate().getInstance()").then((Response<JSONObject> response) {
      if (response.type != Response.RESPONSE_TYPE_SUCCESS) {
        completer.complete(response);
        return;
      }

      _active = response.payload.variables['active'];

      _lastModified = new DateTime.fromMillisecondsSinceEpoch(response.payload.variables['last_modified'] * 1000);
      completer.complete(new Response.success(_active));
      _onActiveChangeController.add(_active);

    });


    return new FutureResponse(completer.future);
  }

  FutureResponse<bool> activate() {
    if (active) {
      return new Future(() => active);
    }

    var completer = new Completer<Response<String>>();

    ajaxClient.callFunctionString(_domainLibraryGetter + ".activate().getInstance()").then((Response<JSONObject> response) {
      if (response.type != Response.RESPONSE_TYPE_SUCCESS) {
        completer.complete(response);
        return;
      }

      _active = response.payload.variables['active'];
      _lastModified = new DateTime.fromMillisecondsSinceEpoch(response.payload.variables['last_modified'] * 1000);
      completer.complete(new Response.success(_active));
      _onActiveChangeController.add(_active);
    });


    return new FutureResponse(completer.future);
  }

  FutureResponse<bool> toggleActive() => active?deactivate():activate();

  Stream<String> get onDescriptionChange => _onDescriptionChangeController.stream;

  Stream<MailDomain> get onDelete {
    if (_onDeleteController == null) {
      _onDeleteController = new StreamController();
      domainLibrary.onDelete.listen((MailDomain d) {
        if (d != this) {
          return;
        }
        _onDeleteController.add(d);
      });

    }

    return _onDeleteController.stream;
  }
  Stream<MailDomain> get onAliasTargetChange => _onAliasTargetChange.stream;

  Stream<bool> get onActiveChange => _onActiveChangeController.stream;
}