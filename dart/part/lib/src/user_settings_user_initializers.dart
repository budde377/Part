part of user_settings;


class UserSettingsChangeUserInfoFormInitializer extends core.Initializer {
  UserLibrary _userLibrary;

  FormElement _userMailForm = querySelector('#UpdateUsernameMailForm'), _userPasswordForm = querySelector('#UpdatePasswordForm');


  UserSettingsChangeUserInfoFormInitializer(UserLibrary this._userLibrary);

  bool get canBeSetUp => _userMailForm != null && _userPasswordForm != null;

  void setUp() {
    var userNameInput = _userMailForm.querySelector('#EditUserEditUsernameField'), userMailInput = _userMailForm.querySelector('#EditUserEditMailField');
    var v1 = new Validator(userNameInput), v2 = new Validator(userMailInput);
    v1
      ..addNonEmptyValueValidator()
      ..addValueValidator((String value) => (value == userLibrary.userLoggedIn.username || userLibrary.users[value] == null));
    v1.errorMessage = "Brugernavn må ikke være tomt og skal være unikt";

    v2.addValidMailValueValidator();
    v2.errorMessage = "Skal være gyldig E-mail adresse";

    var validatingForm = new ValidatingForm(_userMailForm);
    validatingForm.validate(true);
    var decoForm = new FormHandler(_userMailForm);
    decoForm.submitFunction = (Map<String, String> data) {
      if (_userMailForm.classes.contains('initial')) {
        return false;
      }
      decoForm.blur();
      userLibrary.userLoggedIn.changeInfo(username:data['username'], mail:data['mail']).then((core.Response response) {
        if (response.type == core.Response.RESPONSE_TYPE_ERROR) {
          var m;
          decoForm.changeNotion((m = _errorMessage(response.error_code)) != null ? m : "Ukendt fejl", FormHandler.NOTION_TYPE_ERROR);
        } else {
          decoForm.changeNotion("Ændringerne er gemt", FormHandler.NOTION_TYPE_SUCCESS);
          userNameInput.blur();
          userMailInput.blur();
          validatingForm.validate();
        }
        decoForm.unBlur();
      });

      return false;
    };

    var userOldPassword = _userPasswordForm.querySelector('#EditUserEditPasswordOldField'), userNewPassword = _userPasswordForm.querySelector('#EditUserEditPasswordNewField'), userNewPasswordRepeat = _userPasswordForm.querySelector('#EditUserEditPasswordNewRepField');
    var v3 = new Validator(userOldPassword), v4 = new Validator(userNewPassword), v5 = new Validator(userNewPasswordRepeat);
    v3.addNonEmptyValueValidator();
    v3.errorMessage = v4.errorMessage = "Kodeord må ikke være tomt";
    v4.addNonEmptyValueValidator();
    v5
      ..addNonEmptyValueValidator()
      ..addValueValidator((String value) => value == userNewPassword.value);
    v5.errorMessage = "Kodeordet skal gentages korrekt";
    var valPassForm = new ValidatingForm(_userPasswordForm);
    valPassForm.validate();
    var decoPassForm = new FormHandler(_userPasswordForm);
    decoPassForm.submitFunction = (Map<String, String> data) {
      if (_userPasswordForm.classes.contains('initial')) {
        return false;
      }
      decoPassForm.blur();
      userLibrary.userLoggedIn.changePassword(data['old_password'], data['new_password']).then((core.Response response) {
        if (response.type == core.Response.RESPONSE_TYPE_ERROR) {
          var m;
          decoPassForm.changeNotion((m = _errorMessage(response.error_code)) != null ? m : "Ukendt fejl", FormHandler.NOTION_TYPE_ERROR);
        } else {
          decoPassForm.changeNotion("Dit kodeord er ændret", FormHandler.NOTION_TYPE_SUCCESS);
          userNewPassword.value = userOldPassword.value = userNewPasswordRepeat.value = "";
          userNewPassword.blur();
          userOldPassword.blur();
          userNewPasswordRepeat.blur();
          valPassForm.validate();
        }
        decoPassForm.unBlur();
      });
      return false;
    };
  }
}
