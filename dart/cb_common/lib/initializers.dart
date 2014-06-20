library initializers;

import "core.dart";
import "site_classes.dart";
import "json.dart";
import "elements.dart";
import "dart:html";

class TitleURLUpdateInitializer extends Initializer {

  PageOrder _order;

  JSONClient _client;

  TitleURLUpdateInitializer(PageOrder this._order, JSONClient this._client);

  bool get canBeSetUp => _order.currentPage != null;

  void setUp() {
    var updateAddress = () {
      var currentPagePath = _order.currentPagePath;
      var currentPageAddress = currentPagePath.map((Page p) => p.id).join('/') ;
      var currentPageTitle = document.title.split(' - ').first + " - " + currentPagePath.map((Page p) => p.title).join(' - ');
      document.title = currentPageTitle;
      _client.urlPrefix = currentPageAddress;
      window.history.replaceState(null, currentPageTitle, "/" + currentPageAddress);
    };
    updateAddress();
    _order.currentPage.onChange.listen((_) {
      updateAddress();
    });
    _order.onUpdate.listen((_){
      updateAddress();
    });

  }

}






class LoginFormulaInitializer implements Initializer {
  FormElement _loginForm = querySelector("form#UserLoginForm");

  bool get canBeSetUp => _loginForm != null;

  void setUp() {
    var form = new FormHandler(_loginForm);
    var client = new AJAXJSONClient();
    form.submitFunction = (Map<String, String> data) {
      form.blur();
      client.callFunction(new UserLoginJSONFunction(data['username'], data['password'])).then((JSONResponse response) {
        if (response.type == Response.RESPONSE_TYPE_ERROR) {
          form.unBlur();
          switch (response.error_code) {
            case Response.ERROR_CODE_USER_NOT_FOUND:
              form.changeNotion("Ugyldig bruger", FormHandler.NOTION_TYPE_ERROR);
              break;
            case Response.ERROR_CODE_WRONG_PASSWORD:
              form.changeNotion("Ugyldig kodeord", FormHandler.NOTION_TYPE_ERROR);
              break;
          }
        } else {
          form.changeNotion("Du er nu logget ind", FormHandler.NOTION_TYPE_SUCCESS);
          window.location.href = "/?" + new DateTime.now().millisecondsSinceEpoch.toString();
        }

      });
      return false;
    };
  }

}