library site_classes;

import "dart:async";
import 'json.dart';
import 'core.dart';

part "src/site_class_content.dart";
part "src/site_class_site.dart";
part "src/site_class_page.dart";
part "src/site_class_page_order.dart";
part "src/site_class_user.dart";
part "src/site_class_user_library.dart";


class ChangeResponse<V> extends Response<V>{


  ChangeResponse.success([V payload = null]): super.success(payload);
  ChangeResponse.error(int error_code): super.error(error_code);

}








