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

  Stream<String> get onNameChange;

}


class AJAXMailMailbox extends MailMailbox {

  final MailDomain domain;

  final MailDomainLibrary domainLibrary;

  final MailAddressLibrary addressLibrary;

  final MailAddress address;

  String _name;

  JSONClient _client = new AJAXJSONClient();

  StreamController
  _onDeleteController = new StreamController(),
  _onNameChangeController = new StreamController();


  AJAXMailMailbox(MailAddress address, [this._name = ""]):
  this.address = address,
  domainLibrary = address.domainLibrary,
  domain = address.domain,
  addressLibrary = address.addressLibrary;

  AJAXMailMailbox.fromJSONObject(JSONObject object, MailAddress address):
  this.address = address,
  domainLibrary = address.domainLibrary,
  domain = address.domain,
  addressLibrary = address.addressLibrary,
  _name = object.variables['name'];


  String get name => _name;

  String get _functionStringSelector => "MailDomainLibrary.getDomain(${quoteString(domain.domainName)}).getAddressLibrary().getAddress(${quoteString(address.localPart)}).getMailbox()";

  FutureResponse<MailMailbox> changeInfo({String name, String password}) {

    var completer = new Completer<Response<MailMailbox>>();

    String functionString = _functionStringSelector;
    if (name != null && name != _name) {
      functionString += "..setName(${quoteString(name)})";
    }

    if (password != null) {
      functionString += "..setPassword(${quoteString(password)})";
    }

    functionString += "..getName()";

    ajaxClient.callFunctionString(functionString).thenResponse(onSuccess:(Response<String> response) {
      if (_name != response.payload) {
        _name = response.payload;
        completer.complete(this);
        _onNameChangeController.add(this);
      } else {
        completer.complete(this);
      }

    }, onError:completer.complete);

    return new FutureResponse(completer.future);
  }

  FutureResponse<MailMailbox> delete() {
    var completer = new Completer();
    address.deleteMailbox().thenResponse(onSuccess:(Response<MailMailbox> r){
      completer.complete(r.payload);
      _onDeleteController.add(r.payload);
    }, onError: completer.complete);

    return new FutureResponse(completer.future);
  }

  Stream<MailMailbox> get onDelete => _onDeleteController.stream;

  Stream<String> get onNameChange => _onNameChangeController.stream;

}