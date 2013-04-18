part of json;

class JSONFunction {
  final Map arguments = new Map();
  final String name;
  final int id = new DateTime.now().millisecondsSinceEpoch;

  JSONFunction(String name):this.name = name;
  String get jsonString => JSON.stringify({"type":"function", "name":name, "id":id, "args":arguments});
}

class ChangeUserInfoJSONFunction extends JSONFunction {
  ChangeUserInfoJSONFunction(String username, String newUsername, String mail):super('changeUserInfo') {
    this.arguments['username'] = username;
    this.arguments['new_username'] = newUsername;
    this.arguments['mail'] = mail;
  }
}


class ChangeUserPasswordJSONFunction extends JSONFunction {
  ChangeUserPasswordJSONFunction(String username,String oldPassword, String newPassword):super('changeUserPassword') {
    this.arguments['username'] = username;
    this.arguments['old_password'] = oldPassword;
    this.arguments['new_password'] = newPassword;
  }
}

class ChangePageInfoJSONFunction extends JSONFunction {
  ChangePageInfoJSONFunction(String page_id, String new_page_id, String title, String template, String alias, bool hidden):super('changePageInfo') {
    this.arguments['page_id'] = page_id;
    this.arguments['new_page_id'] = new_page_id;
    this.arguments['title'] = title;
    this.arguments['template'] = template;
    this.arguments['alias'] = alias;
    this.arguments['hidden'] = hidden;
  }
}

class ListPagesJSONFunction extends JSONFunction {
  ListPagesJSONFunction():super('listPages');
}

class DeactivatePageJSONFunction extends JSONFunction {
  DeactivatePageJSONFunction(String page_id):super('deactivatePage') {
    this.arguments['page_id'] = page_id;
  }
}

class SetPageOrderJSONFunction extends JSONFunction {
  static const POSITION_LAST = -1;

  SetPageOrderJSONFunction(String parent, List<String> order):super('setPageOrder') {
    this.arguments['order'] = order;
    this.arguments['parent'] = parent;
  }
}

class DeletePageJSONFunction extends JSONFunction {
  DeletePageJSONFunction(String page_id):super('deletePage') {
    this.arguments['page_id'] = page_id;
  }
}

class CreatePageJSONFunction extends JSONFunction {
  CreatePageJSONFunction(String title):super('createPage') {
    this.arguments['title'] = title;
  }
}

class ListUsersJSONFunction extends JSONFunction {
  ListUsersJSONFunction():super('listUsers');
}

class DeleteUserJSONFunction extends JSONFunction {
  DeleteUserJSONFunction(String username):super('deleteUser') {
    this.arguments['username'] = username;
  }
}

class CreateUserJSONFunction extends JSONFunction {
  CreateUserJSONFunction(String mail, String privileges):super('createUser') {
    this.arguments['mail'] = mail;
    this.arguments['privileges'] = privileges;
  }
}

class UserLoginJSONFunction extends JSONFunction {
  UserLoginJSONFunction(String username, String password):super('userLogin'){
    this.arguments['username'] = username;
    this.arguments['password'] = password;
  }
}