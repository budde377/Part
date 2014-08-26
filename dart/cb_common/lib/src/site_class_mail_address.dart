part of site_classes;

abstract class MailAddress{

  String get localPart;

  Future<ChangeResponse<String>> changeLocalPart(String localPart);

  bool get hasMailbox;

  MailMailbox get mailbox;

  Future<ChangeResponse<MailMailbox>> createMailbox();

  Future<ChangeResponse<MailMailbox>> deleteMailbox();

  List<String> get targets;

  Future<ChangeResponse<String>> addTarget(String target);

  Future<ChangeResponse<String>> removeTarget(String target);

  String toString() => "$localPart@$domain";

  Future<ChangeResponse<MailAddress>> delete();

  MailDomain get domain;

}