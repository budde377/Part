part of site_classes;

abstract class MailAddressLibrary {

  MailAddress get catchallAddress;

  MailDomain get domain;

  MailDomainLibrary get domainLibrary;

  Map<String, MailAddress> get addresses;

  FutureResponse<MailAddress> createAddress(String localPart, {List<User> owners, List<String> target});

  FutureResponse<MailAddress> deleteAddress(MailAddress address);

  FutureResponse<MailAddress> createCatchallAddress();

  FutureResponse<MailAddressLibrary> deleteCatchallAddress();

  bool get hasCatchallAddress;

  Stream<MailAddress> get onCatchallChange;

  Stream<MailAddress> get onCreate;

  Stream<MailAddress> get onDelete;

}


class AJAXMailAddressLibrary extends MailAddressLibrary {


  final MailDomainLibrary domainLibrary;

  final MailDomain domain;

  final UserLibrary userLibrary;

  Map<String, MailAddress> _addresses;

  MailAddress _catchallAddress;

  StreamController
  _onDeleteController = new StreamController(),
  _onCreateController = new StreamController(),
  _onCatchallChangeController = new StreamController();



  AJAXMailAddressLibrary(this._addresses, MailDomain domain, this.userLibrary, [this._catchallAddress=null]):
  this.domain = domain,
  domainLibrary = domain.domainLibrary{
    _setUpListeners();
  }

  AJAXMailAddressLibrary.fromJSONObject(JSONObject object, MailDomain domain, this.userLibrary):
  this.domain = domain,
  domainLibrary = domain.domainLibrary{
    _catchallAddress = object.variables['catchall_address'] == null ? null : new AJAXMailAddress.fromJSONObject(object.variables['catchall_address'], this, userLibrary);
    _addresses = new LazyMap.fromFunctionMap(
        new Map.fromIterable(object.variables['addresses'],
        key:(JSONObject object) => object.variables['local_part'],
        value:(JSONObject object) => () => new AJAXMailAddress.fromJSONObject(object, this, userLibrary)));
    _setUpListeners();
  }
  void _setUpListeners() {
    _addresses.forEach((_, MailAddress value){
      _addListener(value);
    });
  }

  void _addListener(MailAddress m){
    var key = m.localPart;
    var s1 = m.onLocalPartChange.listen((_){
      _addresses.remove(key);
      _addresses[key = m.localPart] = m;
    });

    m.onDelete.listen((_){
      s1.cancel();
    });

  }

  MailAddress get catchallAddress => _catchallAddress;

  bool get hasCatchallAddress => _catchallAddress != null;

  Map<String, MailAddress> get addresses => new Map<String, MailAddress>.from(_addresses);


  FutureResponse<MailAddress> createAddress(String localPart, {List<User> owners, List<String> target}){
    var completer = new Completer();

    var fs = "";
    if(owners != null){
      fs += owners.fold("", (String prev, User u)=>prev+"..addOwner(${quoteString(u.username)})");
    }

    if(target == null){
      fs += target.reduce((String v, String e) => v+"..addTarget(${quoteString(e)})");
    }

    ajaxClient.callFunctionString(_getLibraryFunctionString+".createAddress(${quoteString(localPart)})$fs}").then((Response<JSONObject> response){
      if(response.type != Response.RESPONSE_TYPE_SUCCESS){
        completer.complete(response);
        return;
      }

      var address = new AJAXMailAddress.fromJSONObject(response.payload, this, userLibrary);
      _addListener(address);
      _addresses[address.localPart] = address;
      completer.complete(new Response.success(address));
      _onCreateController.add(address);

    });

    return new FutureResponse(completer.future);
  }

  String get _getLibraryFunctionString =>  "MailDomainLibrary.getDomain(${quoteString(domain.domainName)}).getAddressLibrary()";


  FutureResponse<MailAddress> deleteAddress(MailAddress address){
    var completer = new Completer();

    ajaxClient.callFunctionString(_getLibraryFunctionString+".deleteAddress(${_getLibraryFunctionString}.getAddress(${quoteString(address.localPart)}))").then((Response<JSONObject> response){
      if(response.type != Response.RESPONSE_TYPE_SUCCESS){
        completer.complete(response);
        return;
      }

      _addresses.remove(address.localPart);
      completer.complete(new Response.success(address));
      _onCreateController.add(address);

    });

    return new FutureResponse(completer.future);
  }

  FutureResponse<MailAddress> createCatchallAddress(){
    if(hasCatchallAddress){
      return new Future(()=>new Response.success(catchallAddress));
    }
    var completer = new Completer();

    ajaxClient.callFunctionString(_getLibraryFunctionString+".createCatchallAddress()").then((Response<JSONObject> response){
      if(response.type != Response.RESPONSE_TYPE_SUCCESS){
        completer.complete(response);
        return;
      }

      var address = new AJAXMailAddress.fromJSONObject(response.payload, this, userLibrary);
      _addListener(address);
      _catchallAddress = address;
      completer.complete(new Response.success(address));
      _onCatchallChangeController.add(address);
      _onCreateController.add(address);


    });

    return new FutureResponse(completer.future);
  }

  FutureResponse<MailAddressLibrary> deleteCatchallAddress(){
    if(!hasCatchallAddress){
      return new Future(()=>new Response.error(Response.ERROR_CODE_INVALID_INPUT));
    }
    var completer = new Completer();
    ajaxClient.callFunctionString(_getLibraryFunctionString+".deleteCatchallAddress()").then((Response<JSONObject> response){
      if(response.type != Response.RESPONSE_TYPE_SUCCESS){
        completer.complete(response);
        return;
      }
      var c = catchallAddress;
      _catchallAddress = null;
      completer.complete(new Response.success(c));
      _onCatchallChangeController.add(c);
      _onDeleteController.add(c);

    });

    return new FutureResponse(completer.future);
  }

  Stream<MailAddress> get onCatchallChange => _onCatchallChangeController.stream.asBroadcastStream();

  Stream<MailAddress> get onCreate => _onCreateController.stream.asBroadcastStream();

  Stream<MailAddress> get onDelete => _onDeleteController.stream.asBroadcastStream();


}