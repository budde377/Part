part of user_settings;

// TODO fix
class UserSettingsAddPageFormInitializer extends core.Initializer {
  PageOrder _order;

  FormElement _addPageForm = querySelector("#EditPagesForm");

  UserSettingsAddPageFormInitializer(PageOrder this._order);

  bool get canBeSetUp => _addPageForm != null;

  void setUp() {
    var input = _addPageForm.querySelector('#EditPagesAddPage');

    new Validator(input)
      ..addNonEmptyValueValidator()
      ..errorMessage = "Titlen må ikke være tom";
    var validatingForm = new ValidatingForm(_addPageForm);
    validatingForm.validate();
    var decoration = new FormHandler(_addPageForm);
    decoration.submitFunction = (Map<String, String> data) {
      if (_addPageForm.classes.contains('initial')) {
        return false;
      }

      decoration.blur();
      _order.createPage(data['title']).then((core.Response response) {
        if (response.type == core.Response.RESPONSE_TYPE_SUCCESS) {
          input.value = "";
          input.blur();
          validatingForm.validate();
        }
        decoration.unBlur();
      });

      return false;
    };
  }

}



class UserSettingsPageListsInitializer extends core.Initializer {

  UListElement _activeList = querySelector("#ActivePageList"), _inactiveList = querySelector("#InactivePageList");

  PageOrder _order;

  UserSettingsPageListsInitializer(PageOrder this._order);

  bool get canBeSetUp => _activeList != null && _inactiveList != null;

  void setUp() {
    
  }

}
