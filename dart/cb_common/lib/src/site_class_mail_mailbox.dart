part of site_classes;


abstract class MailMailbox{

  String get name;

  Future<ChangeResponse<bool>> checkPassword (String password);

  Future<ChangeResponse<MailMailbox>> changeInfo ({String name, String password});

  Future<ChangeResponse<MailMailbox>> delete ();

  MailAddress get address;


}



class JSONMailMailbox extends MailMailbox{

  final MailAddress address;

  String _name;

  JSONClient _client = new AJAXJSONClient();

  JSONMailMailbox(MailAddress this.address, [this._name = ""]);

  Future<ChangeResponse<bool>> checkPassword (String password);

  Future<ChangeResponse<MailMailbox>> changeInfo ({String name, String password});

  Future<ChangeResponse<MailMailbox>> delete ();


}