part of site_classes;


abstract class LogEntry {
  final DateTime time;
  final int id, level;
  final String message;

  Map _context;

  LogEntry(this.time, this.id, this.level, this.message, [Map context = null]) : this._context = context;

  FutureResponse<Map> get context;

}


class AJAXLogEntry extends LogEntry {

  final Logger logger;


  AJAXLogEntry(this.logger, int time, int id, int level, String message, [Map context = null]) : super(new DateTime.fromMillisecondsSinceEpoch(time*1000), id, level, message, context);

  FutureResponse<Map> get context {
    var c = new Completer();
    if (_context != null) {
      c.complete(new Response.success(_context));
    } else {
      logger.contextAt(time).then((Response response){
        if(response.type == Response.RESPONSE_TYPE_SUCCESS){
          _context = response.payload;
        }
        c.complete(response);
      });

    }

    return c.future;
  }

}

abstract class Logger {


  static const LOG_LEVEL_EMERGENCY = 1;
  static const LOG_LEVEL_ALERT = 2;
  static const LOG_LEVEL_CRITICAL = 4;
  static const LOG_LEVEL_ERROR = 8;
  static const LOG_LEVEL_WARNING = 16;
  static const LOG_LEVEL_NOTICE = 32;
  static const LOG_LEVEL_INFO = 64;
  static const LOG_LEVEL_DEBUG = 128;

  static const LOG_LEVEL_ALL = 255;

  FutureResponse<Logger> emergency(String message, [Map context = null]) => log(LOG_LEVEL_EMERGENCY, message, context);

  FutureResponse<Logger> alert(String message, [Map context = null]) => log(LOG_LEVEL_ALERT, message, context);

  FutureResponse<Logger> critical(String message, [Map context = null]) => log(LOG_LEVEL_CRITICAL, message, context);

  FutureResponse<Logger> error(String message, [Map context = null]) => log(LOG_LEVEL_ERROR, message, context);

  FutureResponse<Logger> warning(String message, [Map context = null]) => log(LOG_LEVEL_WARNING, message, context);

  FutureResponse<Logger> notice(String message, [Map context = null]) => log(LOG_LEVEL_NOTICE, message, context);

  FutureResponse<Logger> info(String message, [Map context = null]) => log(LOG_LEVEL_INFO, message, context);

  FutureResponse<Logger> debug(String message, [Map context = null]) => log(LOG_LEVEL_DEBUG, message, context);

  FutureResponse<Logger> log(int level, String message, [Map context = null]);

  FutureResponse<List<LogEntry>> listLog({int level:LOG_LEVEL_ALL, bool includeContext : true, DateTime time : null});

  FutureResponse<Logger> clearLog();

  FutureResponse<Map> contextAt(DateTime t);


}


class AJAXLogger extends Logger {
  static AJAXLogger _cached;

  factory AJAXLogger() => _cached == null ? _cached = new AJAXLogger._internal() : _cached;

  AJAXLogger._internal();

  FutureResponse<Logger> log(int level, String message, [Map context = null]) {
    context = context == null ? [] : context;
    var c = new Completer<Response<Logger>>();
    var fd = new FormData();
    fd.append("context", JSON.encode(context));
    ajaxClient.callFunctionString("Logger.log($level, ${FunctionStringCompiler.compile(message)}, Parser.parseJson(POST['context']))", form_data:fd).then((JSONResponse response) {
      if (response.type == Response.RESPONSE_TYPE_SUCCESS) {
        c.complete(new Response.success(this));
      } else {
        c.complete(new Response.error(response.error_code));
      }
    });
    return new FutureResponse(c.future);
  }

  FutureResponse<List<LogEntry>> listLog({int level:Logger.LOG_LEVEL_ALL, bool includeContext : true, DateTime time : null}) {
    var c = new Completer<Response<Logger>>();
    ajaxClient.callFunctionString("Logger.listLog($level, ${FunctionStringCompiler.compile(includeContext)}, ${FunctionStringCompiler.compile(time)})").then((JSONResponse response) {
      if (response.type == Response.RESPONSE_TYPE_SUCCESS) {
        c.complete(new Response.success(response.payload.map((Map m) => new AJAXLogEntry(this,int.parse(m["time"]), int.parse(m["time"]), m["message"], m.containsKey("context") ? m["context"] : null))));
      } else {
        c.complete(new Response.error(response.error_code));
      }
    });
    return new FutureResponse(c.future);

  }

  FutureResponse<Logger> clearLog() {
    var c = new Completer<Response<Logger>>();
    ajaxClient.callFunctionString("Logger.clearLog()").then((JSONResponse response) {
      if (response.type == Response.RESPONSE_TYPE_SUCCESS) {
        c.complete(new Response.success(this));
      } else {
        c.complete(new Response.error(response.error_code));
      }
    });
    return new FutureResponse(c.future);
  }

  FutureResponse<Map> contextAt(DateTime t) {
    var c = new Completer();

    ajaxClient.callFunctionString("Logger.getContextAt(${FunctionStringCompiler.compile(t)})").then((JSONResponse response) {
      if (response.type == Response.RESPONSE_TYPE_ERROR) {
        c.complete(new Response.error(response.error_code));
      } else {
        c.complete(new Response.success(response.payload));
      }
    });


    return new FutureResponse(c.future);
  }


}