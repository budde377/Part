part of json;

class JSONObject {
  final String name;
  final Map<String, dynamic> variables = new Map();

  JSONObject(String name) : this.name = name;

  String get jsonString => JSON.stringify({"type":"object", "name":name, "variables":variables});


}

class PageJSONObject extends JSONObject {
  PageJSONObject(String id, [String title="", String template = "", String alias = ""]):super("page") {
    this.variables['id'] = id;
    this.variables['title'] = title;
    this.variables['template'] = template;
    this.variables['alias'] = alias;
  }
}

class UserJSONObject extends JSONObject {
  UserJSONObject(String username, String email, [String parent = ""]):super("user") {
    this.variables['username'] = username;
    this.variables['mail'] = email;
    this.variables['parent'] = parent;
  }
}