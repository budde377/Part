part of user_settings;


class UserSettingsUpdateSiteInitializer extends core.Initializer {
  ButtonElement _checkButton = querySelector("#UserSettingsContent button.update_check");
  CheckboxInputElement _autoCheckBox = querySelector("#UserSettingsContent #UserSettingsUpdaterEnableAutoUpdate");

  SpanElement _checkTime = querySelector("#UserSettingsContent .update_site span.check_time");

  DivElement _updateInformationMessage = querySelector("#UpdateInformationMessage");

  bool _canBeUpdated;

  bool get canBeSetUp => _autoCheckBox != null && _checkButton != null && _checkTime != null && _updateInformationMessage != null;

  void setUp() {
    _canBeUpdated = !_updateInformationMessage.hidden;


    _checkButton.onClick.listen((_) {
      _updateCheckButton(true);
      if (!_canBeUpdated) {

        updater.checkForUpdates().then((core.Response<bool> response) {
          _checkTime.text = core.dateString(new DateTime.now());
          if (response.type != core.Response.RESPONSE_TYPE_SUCCESS) {
            _canBeUpdated = false;
            _updateCheckButton();
            return;
          }
          if (response.payload) {
            _canBeUpdated = true;
            dialogContainer.confirm("<b>Hjemmesiden kan opdateres! </b><br /> Hvis du opdaterer nu, skal siden genstartes, og det er derfor vigtigt at du gemmer alle ændringer du må have foretaget. <br /> Ønsker du at opdatere det nu?").result.then((bool b) {
              if (!b) {
                return;
              }
              _updateSite();
            });

          } else {
            _canBeUpdated = false;
            dialogContainer.alert("Der blev ikke fundet nogen opdatering.<br /> Prøv igen senere.");
          }
          _updateCheckButton();
        });

      } else {
        _updateSite();
      }

    });
    _updateInformationMessage.querySelector("a").onClick.listen((_) => _updateSite());


    _autoCheckBox.onChange.listen((Event event) {
      var checked = _autoCheckBox.checked;
      _autoCheckBox.checked = !checked;
      _autoCheckBox.parent.classes.add('blur');

      var responseHandler = (core.Response r) {
        if (r.type == core.Response.RESPONSE_TYPE_SUCCESS) {
          _autoCheckBox.checked = checked;
        }
        _autoCheckBox.parent.classes.remove('blur');
      };
      if (checked) {
        updater.allowCheckOnLogin().then(responseHandler);
      } else {
        updater.disallowCheckOnLogin().then(responseHandler);
      }

    });
  }

  void _updateCheckButton([bool searching = false]) {
    if (searching) {
      _checkButton.disabled = true;
      if (_canBeUpdated) {
        _checkButton.text = _checkButton.dataset["workUpdateValue"];
      } else {
        _checkButton.text = _checkButton.dataset["workCheckValue"];
      }
      return;
    }
    _checkButton.disabled = false;
    if (_canBeUpdated) {
      _updateInformationMessage.hidden = false;
      _checkButton.text = _checkButton.dataset["updateValue"];
    } else {
      _updateInformationMessage.hidden = true;
      _checkButton.text = _checkButton.dataset["checkValue"];
    }

  }

  void _updateSite() {
    _updateCheckButton(true);
    var updateDone = false;
    window.onBeforeUnload.listen((BeforeUnloadEvent event) {
      if (updateDone) {
        return;
      }
      event.returnValue = "Websitet er i gang med at opdatere.";
    });

    var loader = dialogContainer.loading("Opdaterer websitet.<br />Luk ikke din browser!");
    updater.update().then((core.Response response) {
      if (response.type == core.Response.RESPONSE_TYPE_ERROR) {
        loader.close();
        _updateCheckButton();
        return;
      }
      _canBeUpdated = false;
      loader.element
        ..innerHtml = "Siden er opdateret.<br /> Hjemmesiden genindlæses.";
      loader.stopLoading();
      updateDone = true;
      _updateCheckButton();
      new Timer(new Duration(seconds:1), () {
        window.location.reload();
      });
    });

  }


}
