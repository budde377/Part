part of site_classes;


abstract class MailMailbox {

  MailDomain get domain;

  MailDomainLibrary get domainLibrary;

  MailAddressLibrary get addressLibrary;

  MailAddress get address;

  String get name;

  FutureResponse<MailMailbox> changeInfo({String name, String password});

  FutureResponse<MailMailbox> delete();

  Stream<MailMailbox> get onDelete;

  Stream<MailMailbox> get onNameChange;

  DateTime get lastModified;

}


class AJAXMailMailbox extends MailMailbox {

  final MailDomain domain;

  final MailDomainLibrary domainLibrary;

  final MailAddressLibrary addressLibrary;

  final MailAddress address;

  DateTime _lastModified;

  String _name;

  StreamController
  _onDeleteController = new StreamController(),
  _onNameChangeController = new StreamController();

  Stream
  _onDeleteStream,
  _onNameChangeStream;


  AJAXMailMailbox(MailAddress address, {String name, DateTime lastModified}):
  _name = name,
  _lastModified = lastModified,
  this.address = address,
  domainLibrary = address.domainLibrary,
  domain = address.domain,
  addressLibrary = address.addressLibrary;

  AJAXMailMailbox.fromJSONObject(JSONObject object, MailAddress address):
  this.address = address,
  domainLibrary = address.domainLibrary,
  domain = address.domain,
  addressLibrary = address.addressLibrary,
  _name = object.variables['name'],
  _lastModified = new DateTime.fromMillisecondsSinceEpoch(object.variables['last_modified'] * 1000);


  String get name => _name;

  String get _functionStringSelector => "MailDomainLibrary.getDomain(${quoteString(domain.domainName)}).getAddressLibrary().getAddress(${quoteString(address.localPart)}).getMailbox()";

  FutureResponse<MailMailbox> changeInfo({String name, String password}) {
    String functionString = _functionStringSelector;
    //Setting name
    if (name != null && name != _name) {
      functionString += "..setName(${quoteString(name)})";
    }
    //Setting password
    if (password != null) {
      functionString += "..setPassword(${quoteString(password)})";
    }
    functionString += "..getInstance()";
    return ajaxClient.callFunctionString(functionString).thenResponse(onSuccess:(Response<JSONObject> response) {
      var n = response.payload.variables['name'];
      if (_name != n) {
        _name = n;
        _lastModified = new DateTime.fromMillisecondsSinceEpoch(response.payload.variables['last_modified'] * 1000);
        _onNameChangeController.add(this); //TODO removed suspicious control flow
      }
      return new Response.success(this);
    });

  }

  FutureResponse<MailMailbox> delete()  =>
    address.deleteMailbox().thenResponse(onSuccess:(Response<MailMailbox> response) {
      _onDeleteController.add(response.payload);
      return response;
    });


  Stream<MailMailbox> get onDelete => _onDeleteStream == null ? _onDeleteStream = _onDeleteController.stream.asBroadcastStream() : _onDeleteStream;

  Stream<MailMailbox> get onNameChange => _onNameChangeStream == null ? _onNameChangeStream = _onNameChangeController.stream.asBroadcastStream() : _onNameChangeStream;


  DateTime get lastModified => _lastModified;


}