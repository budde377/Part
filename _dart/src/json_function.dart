part of json;

class JSONFunction {
  final Map arguments = new Map();

  final String name;

  final int id = new DateTime.now().millisecondsSinceEpoch;

  JSONFunction(String name):this.name = name;

  String get jsonString => JSON.stringify({
      "type":"function", "name":name, "id":id, "args":arguments
  });
}


class PageJSONFunction extends JSONFunction{
  PageJSONFunction(String name): super("Page.${name}");
}

class UserJSONFunction extends JSONFunction{
  UserJSONFunction(String name): super("User.${name}");
}

class UserPrivilegesJSONFunction extends JSONFunction{
  UserPrivilegesJSONFunction(String name): super("UserPrivileges.${name}");
}

class PageContentJSONFunction extends JSONFunction{
  PageContentJSONFunction(String name): super("PageContent.${name}");
}

class PageOrderJSONFunction extends JSONFunction{
  PageOrderJSONFunction(String name): super("PageContent.${name}");
}

class UsersJSONFunction extends JSONFunction{
  UsersJSONFunction(String name) : super("Users.${name}");
}

class FileUploadJSONFunction extends JSONFunction{
  FileUploadJSONFunction(String name) : super("FileUpload.${name}");
}

// User functions

class ChangeUserInfoJSONFunction extends UserJSONFunction {
  ChangeUserInfoJSONFunction(String username, String newUsername, String mail):super('changeUserInfo') {
    this.arguments['username'] = username;
    this.arguments['new_username'] = newUsername;
    this.arguments['mail'] = mail;
  }
}


class ChangeUserPasswordJSONFunction extends UserJSONFunction {
  ChangeUserPasswordJSONFunction(String username, String oldPassword, String newPassword):super('changeUserPassword') {
    this.arguments['username'] = username;
    this.arguments['old_password'] = oldPassword;
    this.arguments['new_password'] = newPassword;
  }
}



class DeleteUserJSONFunction extends UserJSONFunction {
  DeleteUserJSONFunction(String username):super('deleteUser') {
    this.arguments['username'] = username;
  }
}

class CreateUserJSONFunction extends UserJSONFunction {
  CreateUserJSONFunction(String mail, String privileges):super('createUser') {
    this.arguments['mail'] = mail;
    this.arguments['privileges'] = privileges;
  }
}

class UserLoginJSONFunction extends UserJSONFunction {
  UserLoginJSONFunction(String username, String password):super('userLogin') {
    this.arguments['username'] = username;
    this.arguments['password'] = password;
  }
}

// Page functions

class ChangePageInfoJSONFunction extends PageJSONFunction {
  ChangePageInfoJSONFunction(String page_id, String new_page_id, String title, String template, String alias, bool hidden):super('changePageInfo') {
    this.arguments['page_id'] = page_id;
    this.arguments['new_page_id'] = new_page_id;
    this.arguments['title'] = title;
    this.arguments['template'] = template;
    this.arguments['alias'] = alias;
    this.arguments['hidden'] = hidden;
  }
}


class DeletePageJSONFunction extends PageJSONFunction {
  DeletePageJSONFunction(String page_id):super('deletePage') {
    this.arguments['page_id'] = page_id;
  }
}

class CreatePageJSONFunction extends PageJSONFunction {
  CreatePageJSONFunction(String title):super('createPage') {
    this.arguments['title'] = title;
  }
}

class DeactivatePageJSONFunction extends PageJSONFunction {
  DeactivatePageJSONFunction(String page_id):super('deactivatePage') {
    this.arguments['page_id'] = page_id;
  }
}

// Page content functions
class AddPageContentJSONFunction extends PageContentJSONFunction{
  AddPageContentJSONFunction(String page_id, String id, String content): super('addPageContent'){
    this.arguments['page_id'] = page_id;
    this.arguments['id'] = id;
    this.arguments['content'] = content;
  }
}

class ListPageContentRevisionsJSONFunction extends PageContentJSONFunction{
  ListPageContentRevisionsJSONFunction(String page_id, String id, {int from:0, int to:-1, bool includeContent:false}) : super('listPageContentRevisions'){
    this.arguments['page_id'] = page_id;
    this.arguments['id'] = id;
    this.arguments['from'] = from;
    this.arguments['to'] = to;
    this.arguments['content'] = includeContent;
  }
}

class PageContentAtTimeJSONFunction extends PageContentJSONFunction{
  PageContentAtTimeJSONFunction(String page_id, String id, int time) : super('pageContentAtTime'){
    this.arguments['page_id'] = page_id;
    this.arguments['id'] = id;
    this.arguments['time'] = time;
  }
}

//User privileges functions

class AddUserPagePrivilegeJSONFunction extends UserJSONFunction {
  AddUserPagePrivilegeJSONFunction(String username, String page_id):super('addUserPagePrivilege'){
    this.arguments['username'] = username;
    this.arguments['page_id'] = page_id;
  }
}
class RevokeUserPagePrivilegeJSONFunction extends UserJSONFunction {
  RevokeUserPagePrivilegeJSONFunction(String username, String page_id):super('revokeUserPagePrivilege') {
    this.arguments['username'] = username;
    this.arguments['page_id'] = page_id;
  }
}


//Page order functions

class ListPagesJSONFunction extends PageOrderJSONFunction {
  ListPagesJSONFunction():super('listPages');
}

class SetPageOrderJSONFunction extends PageOrderJSONFunction {
  static const POSITION_LAST = -1;

  SetPageOrderJSONFunction(String parent, List<String> order):super('setPageOrder') {
    this.arguments['order'] = order;
    this.arguments['parent'] = parent;
  }
}

//Users functions


class ListUsersJSONFunction extends UsersJSONFunction {
  ListUsersJSONFunction():super('listUsers');
}


//File upload functions

class UploadImageURIJSONFunction extends FileUploadJSONFunction{
  UploadImageURIJSONFunction(String fileName, String data, [List<ImageTransform> sizes = null]): super('uploadImageURI'){
    this.arguments['data'] = data;
    this.arguments['fileName'] = fileName;
    this.arguments['sizes'] = sizes;
  }
}


class UploadFileURIJSONFunction extends FileUploadJSONFunction{
  UploadFileURIJSONFunction(String fileName, String data): super('uploadFileURI'){
    this.arguments['data'] = data;
    this.arguments['fileName'] = fileName;
  }
}

// Edit image functions