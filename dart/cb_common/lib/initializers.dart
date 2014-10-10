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

      var username = quoteString(data['username']);
      var password = quoteString(data['password']);

      client.callFunctionString("UserLibrary.userLogin($username, $password)").then((Response<String> response) {
        if (response.type == Response.RESPONSE_TYPE_ERROR) {
          form.unBlur();
          form.changeNotion("Ugyldigt login", FormHandler.NOTION_TYPE_ERROR);
        } else {
          form.changeNotion("Du er nu logget ind", FormHandler.NOTION_TYPE_SUCCESS);
          window.localStorage['user-login-token'] = response.payload;
          window.location.href = "/?" + new DateTime.now().millisecondsSinceEpoch.toString();
        }

      });
      return false;
    };
  }

}



class ForgotPasswordFormulaInitializer implements Initializer {
  FormElement _forgotForm = querySelector("form#UserForgotPasswordForm");

  bool get canBeSetUp => _forgotForm != null;

  void setUp() {
    var form = new FormHandler(_forgotForm);
    var client = new AJAXJSONClient();
    form.submitFunction = (Map<String, String> data) {
      form.blur();

      var mail = quoteString(data['mail']);

      client.callFunctionString("UserLibrary.forgotPassword($mail)").then((Response<String> response) {
        if (response.type == Response.RESPONSE_TYPE_ERROR) {
          form.unBlur();
          form.changeNotion("Fejl", FormHandler.NOTION_TYPE_ERROR);
        } else {
          form.changeNotion("Mail med nye oplysninger er sendt", FormHandler.NOTION_TYPE_SUCCESS);
          TextInputElement i = _forgotForm.querySelector("input[name=mail]");
          i.value = "";
          i.blur();
        }

      });
      return false;
    };
  }

}



class OnlineOfflineBodyClassInitializer implements Initializer{

  void setUp() {
    var f = (bool b){
      if(b){
        body.classes.remove('offline');
      } else {
        body.classes.add('offline');
      }

    };
    f(connection.hasConnection);
    connection.onHasConnectionChange.listen(f);
  }

  bool get canBeSetUp => true;

}