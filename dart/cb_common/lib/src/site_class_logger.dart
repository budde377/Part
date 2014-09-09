part of site_classes;


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

  Future<ChangeResponse<Logger>> emergency(String message, Map context);

  Future<ChangeResponse<Logger>> alert(String message, Map context);

  Future<ChangeResponse<Logger>> critical(String message, Map context);

  Future<ChangeResponse<Logger>> error(String message, Map context);

  Future<ChangeResponse<Logger>> warning(String message, Map context);

  Future<ChangeResponse<Logger>> notice(String message, Map context);

  Future<ChangeResponse<Logger>> info(String message, Map context);

  Future<ChangeResponse<Logger>> debug(String message, Map context);

  Future<ChangeResponse<Logger>> log(int level, String message, Map context);

  Future<ChangeResponse<Logger>> listLog({int level:LOG_LEVEL_ALL, bool includeContext : true, DateTime time : null});



}