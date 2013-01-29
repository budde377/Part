library site_classes;

import "dart:async";
import "json.dart";

part "src/site_class_page.dart";
part "src/site_class_page_order.dart";
part "src/site_class_user.dart";
part "src/site_class_user_library.dart";

const CALLBACK_STATUS_SUCCESS = "success";
const CALLBACK_STATUS_ERROR = "error";


typedef void ChangeCallback(String status, [int error_code, dynamic payload]);







