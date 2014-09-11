part of site_classes;

abstract class Updater{
  Future<Response<bool>> checkForUpdates([bool quick = false]);

  Future<Response<Updater>> update();
  Future<Response<DateTime>> lastChecked();
  Future<Response<DateTime>> lastUpdated();
  Future<Response<String>> getVersion();

}


class AJAXUpdater extends Updater{

  static AJAXUpdater _cached;
  factory AJAXUpdater() => _cached == null?_cached = new AJAXUpdater._internal():_cached;

  AJAXUpdater._internal();


  Future<Response<bool>> checkForUpdates([bool quick = false]) => ajaxClient.callFunctionString("Updater.checkForUpdates(${FunctionStringCompiler.compile(quick)})");

  Future<Response<Updater>> update(){
    var c = new Completer<Response<Updater>>();
    ajaxClient.callFunctionString("Updater.update()").then((Response r){
      if(r.type == Response.RESPONSE_TYPE_SUCCESS){
        c.complete(new ChangeResponse.success(this));
      } else {
        c.complete(new ChangeResponse.error(r.error_code));
      }
    });
    return c.future;
  }
  Future<Response<DateTime>> lastChecked(){
    var c = new Completer<Response<Updater>>();
    ajaxClient.callFunctionString("Updater.lastChecked()").then((Response<int> r){
      if(r.type == Response.RESPONSE_TYPE_SUCCESS){
        c.complete(new ChangeResponse.success(new DateTime.fromMillisecondsSinceEpoch(r.payload*1000)));
      } else {
        c.complete(new ChangeResponse.error(r.error_code));
      }
    });
    return c.future;
  }
  Future<Response<DateTime>> lastUpdated(){
    var c = new Completer<Response<Updater>>();
    ajaxClient.callFunctionString("Updater.lastChecked()").then((Response<int> r){
      if(r.type == Response.RESPONSE_TYPE_SUCCESS){
        c.complete(new ChangeResponse.success(new DateTime.fromMillisecondsSinceEpoch(r.payload*1000)));
      } else {
        c.complete(new ChangeResponse.error(r.error_code));
      }
    });
    return c.future;
  }
  Future<Response<String>> getVersion() => ajaxClient.callFunctionString("Updater.getVersion()");

}