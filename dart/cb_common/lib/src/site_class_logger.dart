part of site_classes;


abstract class LogEntry {
  final DateTime time;
  final int id, level;
  final String message;

  Map _context;

  LogEntry(this.time, this.id, this.level, this.message, [Map context = null]) : this._context = context;

  Future<ChangeResponse<Map>> get context;

}


class AJAXLogEntry extends LogEntry {

  final Logger logger;


  AJAXLogEntry(this.logger, int time, int id, int level, String message, [Map context = null]) : super(new DateTime.fromMillisecondsSinceEpoch(time*1000), id, level, message, context);

  Future<ChangeResponse<Map>> get context {
    var c = new Completer();
    if (_context != null) {
      c.complete(new ChangeResponse.success(_context));
    } else {
      logger.contextAt(time).then((ChangeResponse response){
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

  Future<ChangeResponse<Logger>> emergency(String message, [Map context = null]) => log(LOG_LEVEL_EMERGENCY, message, context);

  Future<ChangeResponse<Logger>> alert(String message, [Map context = null]) => log(LOG_LEVEL_ALERT, message, context);

  Future<ChangeResponse<Logger>> critical(String message, [Map context = null]) => log(LOG_LEVEL_CRITICAL, message, context);

  Future<ChangeResponse<Logger>> error(String message, [Map context = null]) => log(LOG_LEVEL_ERROR, message, context);

  Future<ChangeResponse<Logger>> warning(String message, [Map context = null]) => log(LOG_LEVEL_WARNING, message, context);

  Future<ChangeResponse<Logger>> notice(String message, [Map context = null]) => log(LOG_LEVEL_NOTICE, message, context);

  Future<ChangeResponse<Logger>> info(String message, [Map context = null]) => log(LOG_LEVEL_INFO, message, context);

  Future<ChangeResponse<Logger>> debug(String message, [Map context = null]) => log(LOG_LEVEL_DEBUG, message, context);

  Future<ChangeResponse<Logger>> log(int level, String message, [Map context = null]);

  Future<ChangeResponse<List<LogEntry>>> listLog({int level:LOG_LEVEL_ALL, bool includeContext : true, DateTime time : null});

  Future<ChangeResponse<Logger>> clearLog();

  Future<ChangeResponse<Map>> contextAt(DateTime t);


}


class AJAXLogger extends Logger {
  static AJAXLogger _cached;

  factory AJAXLogger() => _cached == null ? _cached = new AJAXLogger._internal() : _cached;

  AJAXLogger._internal();

  Future<ChangeResponse<Logger>> log(int level, String message, [Map context = null]) {
    context = context == null ? [] : context;
    var c = new Completer<ChangeResponse<Logger>>();
    var fd = new FormData();
    fd.append("context", FunctionStringCompiler.compile(context));
    ajaxClient.callFunctionString("Logger.log($level, ${FunctionStringCompiler.compile(message)}, POST['context'])", form_data:fd).then((JSONResponse response) {
      if (response.type == Response.RESPONSE_TYPE_SUCCESS) {
        c.complete(new ChangeResponse.success(this));
      } else {
        c.complete(new ChangeResponse.error(response.error_code));
      }
    });
    return c.future;
  }

  Future<ChangeResponse<List<LogEntry>>> listLog({int level:Logger.LOG_LEVEL_ALL, bool includeContext : true, DateTime time : null}) {
    var c = new Completer<ChangeResponse<Logger>>();
    ajaxClient.callFunctionString("Logger.listLog($level, ${FunctionStringCompiler.compile(includeContext)}, ${FunctionStringCompiler.compile(time)})").then((JSONResponse response) {
      if (response.type == Response.RESPONSE_TYPE_SUCCESS) {
        c.complete(new ChangeResponse.success(response.payload.map((Map m) => new AJAXLogEntry(this,int.parse(m["time"]), int.parse(m["time"]), m["message"], m.containsKey("context") ? m["context"] : null))));
      } else {
        c.complete(new ChangeResponse.error(response.error_code));
      }
    });
    return c.future;

  }

  Future<ChangeResponse<Logger>> clearLog() {
    var c = new Completer<ChangeResponse<Logger>>();
    ajaxClient.callFunctionString("Logger.clearLog()").then((JSONResponse response) {
      if (response.type == Response.RESPONSE_TYPE_SUCCESS) {
        c.complete(new ChangeResponse.success(this));
      } else {
        c.complete(new ChangeResponse.error(response.error_code));
      }
    });
    return c.future;
  }

  Future<ChangeResponse<Map>> contextAt(DateTime t) {
    var c = new Completer();

    ajaxClient.callFunctionString("Logger.getContextAt(${FunctionStringCompiler.compile(t)})").then((JSONResponse response) {
      if (response.type == Response.RESPONSE_TYPE_ERROR) {
        c.complete(new ChangeResponse.error(response.error_code));
      } else {
        c.complete(new ChangeResponse.success(response.payload));
      }
    });


    return c.future;
  }


}