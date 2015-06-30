part of site_classes;

abstract class Updater {
  FutureResponse<bool> checkForUpdates([bool quick = false]);

  FutureResponse<Updater> update();

  FutureResponse<DateTime> lastChecked();

  FutureResponse<DateTime> lastUpdated();

  FutureResponse<String> getVersion();

  FutureResponse<Updater> disallowCheckOnLogin();

  FutureResponse<Updater> allowCheckOnLogin();

  FutureResponse<bool> isCheckOnLoginAllowed();

}


class AJAXUpdater extends Updater {

  static AJAXUpdater _cached;

  factory AJAXUpdater() => _cached == null ? _cached = new AJAXUpdater._internal() : _cached;

  AJAXUpdater._internal();


  FutureResponse<bool> checkForUpdates([bool quick = false]) => ajaxClient.callFunctionString("Updater.checkForUpdates(${FunctionStringCompiler.compile(quick)})");

  FutureResponse<Updater> update() {
    var c = new Completer<Response<Updater>>();
    ajaxClient.callFunctionString("Updater.update()").then((Response r) {
      if (r.type == Response.RESPONSE_TYPE_SUCCESS) {
        c.complete(new Response.success(this));
      } else {
        c.complete(new Response.error(r.error_code));
      }
    });
    return new FutureResponse(c.future);
  }

  FutureResponse<DateTime> lastChecked() {
    var c = new Completer<Response<DateTime>>();
    ajaxClient.callFunctionString("Updater.lastChecked()").then((Response<int> r) {
      if (r.type == Response.RESPONSE_TYPE_SUCCESS) {
        c.complete(new Response.success(new DateTime.fromMillisecondsSinceEpoch(r.payload * 1000)));
      } else {
        c.complete(new Response.error(r.error_code));
      }
    });
    return new FutureResponse(c.future);
  }

  FutureResponse<DateTime> lastUpdated() {
    var c = new Completer<Response<DateTime>>();
    ajaxClient.callFunctionString("Updater.lastChecked()").then((Response<int> r) {
      if (r.type == Response.RESPONSE_TYPE_SUCCESS) {
        c.complete(new Response.success(new DateTime.fromMillisecondsSinceEpoch(r.payload * 1000)));
      } else {
        c.complete(new Response.error(r.error_code));
      }
    });
    return new FutureResponse(c.future);
  }

  FutureResponse<String> getVersion() => ajaxClient.callFunctionString("Updater.getVersion()");

  FutureResponse<Updater> disallowCheckOnLogin(){
    var c = new Completer<Response<Updater>>();
    ajaxClient.callFunctionString("Updater.disallowCheckOnLogin()").then((Response r) {
      if (r.type == Response.RESPONSE_TYPE_SUCCESS) {
        c.complete(new Response.success(this));
      } else {
        c.complete(new Response.error(r.error_code));
      }
    });
    return new FutureResponse(c.future);
  }

  FutureResponse<Updater> allowCheckOnLogin(){
    var c = new Completer<Response<Updater>>();
    ajaxClient.callFunctionString("Updater.allowCheckOnLogin()").then((Response r) {
      if (r.type == Response.RESPONSE_TYPE_SUCCESS) {
        c.complete(new Response.success(this));
      } else {
        c.complete(new Response.error(r.error_code));
      }
    });
    return new FutureResponse(c.future);

  }

  FutureResponse<bool> isCheckOnLoginAllowed() => ajaxClient.callFunctionString("Updater.isCheckOnLoginAllowed()");

}