part of site_classes;

abstract class MailDomain {

  String get domainName;

  MailAddressLibrary get addressLibrary;

  MailDomainLibrary get domainLibrary;

  String get description;

  Future<Response<String>> changeDescription(String description);

  String toString() => domainName;

  Future<Response<MailDomain>> delete(String password);


  DateTime get lastModified;

  bool get active;

  Future<Response<bool>> deactivate();

  Future<Response<bool>> activate();

  Future<Response<bool>> toggleActive();


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

  StreamController
  _onDeleteController,
  _onActiveChangeController = new StreamController<bool>(),
  _onDescriptionChangeController = new StreamController<String>();

  AJAXMailDomain(this.domainName, this._addressLibrary, this.domainLibrary, this.userLibrary, {String description:"", bool active:true, DateTime last_modified: null}) :
  _description = description,
  _active = active,
  _lastModified = last_modified == null ? new DateTime.fromMillisecondsSinceEpoch(0) : last_modified;


  AJAXMailDomain.fromJSONObject(JSONObject object, this.domainLibrary, this.userLibrary):
  this.domainName = object.variables['domain_name'],
  this._active = object.variables['active'],
  this._description = object.variables['description'],
  this._lastModified = new DateTime.fromMillisecondsSinceEpoch(object.variables['last_modifed'] * 1000){
    this._addressLibrary = new AJAXMailAddressLibrary.fromJSONObject(object.variables['addresses_library'], this, userLibrary);

  }

  String get _domainLibraryGetter => "MailDomainLibrary.getDomain(${quoteString(domainName)})";

  Future<Response<MailDomain>> delete(String password) => domainLibrary.deleteDomain(this, password);


  String get description => _description;

  bool get active => _active;

  MailAddressLibrary get addressLibrary => _addressLibrary;

  Future<Response<String>> changeDescription(String description) {
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


    return completer.future;
  }


  DateTime get lastModified => _lastModified;

  Future<Response<bool>> deactivate() {
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


    return completer.future;
  }

  Future<Response<bool>> activate() {
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


    return completer.future;
  }

  Future<Response<bool>> toggleActive() => active?deactivate():activate();

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

  Stream<bool> get onActiveChange => _onActiveChangeController.stream;
}