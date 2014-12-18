library site_classes;

import "dart:async";
import "dart:html";
import "dart:convert";
import 'json.dart';
import 'core.dart';

part "src/site_class_content.dart";
part "src/site_class_site.dart";
part "src/site_class_page.dart";
part "src/site_class_page_order.dart";
part "src/site_class_user.dart";
part "src/site_class_user_library.dart";
part 'src/site_class_logger.dart';
part 'src/site_class_updater.dart';
part "src/site_class_mail_domain_library.dart";
part "src/site_class_mail_domain.dart";
part "src/site_class_mail_address_library.dart";
part "src/site_class_mail_address.dart";
part "src/site_class_mail_mailbox.dart";

Logger get logger => new AJAXLogger();
Updater get updater => new AJAXUpdater();


