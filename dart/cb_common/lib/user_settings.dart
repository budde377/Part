library user_settings;

import "dart:html";
import "dart:math" as Math;
import "dart:async";

import 'initializers.dart';
import 'site_classes.dart';
import 'json.dart';
import 'core.dart' as core;
import 'elements.dart';

import 'pcre_syntax_checker.dart' as PCRE;


part 'src/user_settings_page_order.dart';
part 'src/user_settings_user_library.dart';
part 'src/user_settings_mail.dart';
part 'src/user_settings_decoration.dart';
part 'src/user_settings_page_li.dart';

bool get pageOrderAvailable => querySelector("#ActivePageList") != null && querySelector("#InactivePageList") != null;

bool get userLibraryAvailable => pageOrderAvailable && querySelector('#UserList') != null;

bool get mailDomainLibraryAvailable => userLibraryAvailable && querySelector('#UserSettingsContent #UserSettingsEditMailDomainList') != null;

PageOrder get pageOrder => pageOrderAvailable ? new UserSettingsJSONPageOrder() : null;

UserLibrary get userLibrary => userLibraryAvailable ? new UserSettingsJSONUserLibrary() : null;

MailDomainLibrary get mailDomainLibrary => mailDomainLibraryAvailable ? new UserSettingsMailDomainLibrary() : null;


String _errorMessage(int error_code) {
  switch (error_code) {
    case core.Response.ERROR_CODE_PAGE_NOT_FOUND:
      return "Siden blev ikke fundet";
    case core.Response.ERROR_CODE_INVALID_PAGE_ID:
      return "Ugyldigt side id";
    case core.Response.ERROR_CODE_INVALID_PAGE_ALIAS:
      return "Ugyldigt side alias";
    case core.Response.ERROR_CODE_UNAUTHORIZED:
      return "Du har ikke de nødvendige rettigheder";
    case core.Response.ERROR_CODE_INVALID_MAIL:
      return "Ugyldig mail-adresse";
    case core.Response.ERROR_CODE_INVALID_USER_NAME:
      return "Ugyldig brugernavn";
    case core.Response.ERROR_CODE_WRONG_PASSWORD:
      return "Forkert kodeord";
    case core.Response.ERROR_CODE_INVALID_PASSWORD:
      return "Ugyldigt kodeord";
    case core.Response.ERROR_CODE_COULD_NOT_PARSE_RESPONSE:
      return "Ugyldigt svar fra server";
    case core.Response.ERROR_CODE_NO_CONNECTION:
      return "Ingen forbindelse til serveren";
    default:
      return "Ukendt fejl";
  }
}

class UserSettingsInitializer extends core.Initializer {

  core.InitializerLibrary _initLib;

  UserSettingsInitializer(this._initLib);


  bool get canBeSetUp => pageOrderAvailable && userLibraryAvailable;

  void setUp() {
    var client = new AJAXJSONClient();
    var order = pageOrder, userLib = userLibrary;

    _initLib.registerInitializer(new TitleURLUpdateInitializer(order, client));
    _initLib.registerInitializer(new UserSettingsDecorationInitializer());
    _initLib.registerInitializer(new UserSettingsPageListsInitializer(order));
    _initLib.registerInitializer(new UserSettingsEditPageFormInitializer(order));
    _initLib.registerInitializer(new UserSettingsChangeUserInfoFormInitializer(userLib));
    _initLib.registerInitializer(new UserSettingsAddPageFormInitializer(order));
    _initLib.registerInitializer(new UserSettingsUserListInitializer(userLib));
    _initLib.registerInitializer(new UserSettingsAddUserFormInitializer(userLib));
    _initLib.registerInitializer(new UserSettingsPageUserListFormInitializer(userLib, order));
    _initLib.registerInitializer(new UserSettingsUpdateSiteInitializer());
    _initLib.registerInitializer(new UserSettingsLoggerInitializer());
    _initLib.registerInitializer(new UserSettingsMailInitializer());

    /* SET UP LOGIN USER MESSAGE*/
    var loginUserMessage = querySelector('#LoginUserMessage');
    var i = loginUserMessage.querySelector('i');
    userLibrary.userLoggedIn.onChange.listen((User u) {
      i.text = u.username;
    });

  }

}


class UserSettingsMailInitializer extends core.Initializer {

  UListElement mailDomainList = querySelector("#UserSettingsEditMailDomainList");

  UListElement mailAddressLists = querySelector("#UserSettingsEditMailAddressLists");

  UListElement domainAliasList = querySelector("#UserSettingsEditMailDomainAliasList");

  FormElement addDomainForm = querySelector("#UserSettingsEditMailAddDomainForm");

  FormElement addDomainAliasForm = querySelector("#UserSettingsEditMailAddDomainAliasForm");

  FormElement addAddressForm = querySelector("#UserSettingsEditMailAddAddressForm");


  bool get canBeSetUp => mailDomainLibraryAvailable && mailDomainList != null && domainAliasList != null && mailAddressLists != null;

  ElementList<UListElement> get addressLists => mailAddressLists.querySelectorAll("li > ul.address_list");

  /** Functions  that should be called on every domain, now and in the future **/
  List<Function> domainFunctions = [];
  List<Function> domainCreateFunctions = [];
  List<Function> domainDeleteFunctions = [];

  List<Function> addressFunctions = [];
  List<Function> addressDeleteFunctions = [];
  List<Function> addressCreateFunctions = [];

  MailAddress _currently_editing;
  LIElement _currently_editing_li;

  void setUp() {

    setUpMailDomainList();
    setUpAddDomainForm();
    setUpAddDomainAliasForm();
    setUpDomainAliasList();
    setUpAddAddressForm();
    setUpAddressList();

    setUpListeners();


  }

  void setUpAddressList() {

    var domainToUl = (MailDomain d) => addressLists.firstWhere((UListElement ul) => ul.dataset['domain-name'] == d.domainName, orElse:() => null);

    var addressUpdateLi = (LIElement li, MailAddress address) {
      var can_edit = userLibrary.userLoggedIn.hasSitePrivileges || address.owners.contains(userLibrary.userLoggedIn);
      li
        ..dataset['local-part'] = address.localPart
        ..dataset['targets'] = address.targets.join(" ")
        ..dataset['last-modified'] = (address.lastModified.millisecondsSinceEpoch ~/ 1000).toString()
        ..dataset['owners'] = address.owners.map((User u) => u.username).join(" ")
        ..dataset['active'] = address.active.toString()
        ..dataset['has-mailbox'] = address.hasMailbox.toString()
        ..innerHtml = "${address.localPart == "" ? "<span class='asterisk'></span>" : address.localPart}@${address.domain.domainName}"
        ..innerHtml += can_edit ? "<div class='delete'></div>" : "";

      if (can_edit) {
        li.classes.add('can_edit');
      } else {
        li.classes.remove('can_edit');
      }

      if (address.hasMailbox) {
        li.dataset['mailbox-name'] = address.mailbox.name;
        li.dataset['mailbox-last-modified'] = (address.mailbox.lastModified.millisecondsSinceEpoch ~/ 1000).toString();
      }

    };

    var addressLi = (MailAddress address) {

      UListElement ul = domainToUl(address.domain);
      var li;
      if (ul != null && (li = ul.children.firstWhere((LIElement li) => li.dataset['local-part'] == address.localPart, orElse:() => null)) != null) {
        return li;
      }

      li = new LIElement();
      addressUpdateLi(li, address);
      return li;
    };

    var addressDomainLi = (MailDomain d) {
      var ul = domainToUl(d);

      if (ul != null) {
        return ul.parent;
      }

      var li = new LIElement();

      ul = new UListElement();

      ul.dataset['domain-name'] = d.domainName;

      d.addressLibrary.addresses.forEach((String s, MailAddress ma) {
        if (ma == d.addressLibrary.catchallAddress) {
          return;
        }
        ul.append(addressLi(ma));
      });

      sortListFromDataSet(ul, 'local-part');

      if (d.addressLibrary.hasCatchallAddress) {
        ul.append(addressLi(d.addressLibrary.catchallAddress));
      }


      li.append(ul);
      return li;
    };

    var updateAddressListHidden = () {
      mailAddressLists.querySelector('li ul.empty').hidden = mailAddressLists.querySelector('li ul:not(.empty) li') != null;
      mailAddressLists.querySelectorAll('li ul:not(.empty)').forEach((UListElement ul) {
        ul.hidden = ul.children.length == 0;
      });
    };


    addressFunctions.add((MailAddress a) {
      var l = (_) => addressUpdateLi(addressLi(a), a);
      a
        ..onActiveChange.listen(l)
        ..onOwnerChange.listen(l)
        ..onTargetChange.listen(l)
        ..onLocalPartChange.listen(l)
        ..onMailboxChange.listen((MailMailbox m) {
        l(m);
        if (m != null) {
          m.onNameChange.listen(l);
        }

      });

    });

    domainCreateFunctions.add((MailDomain d) {
      var li = addressDomainLi(d);
      mailAddressLists.append(li);
      sortListFromDataSet(mailAddressLists, 'domain-name');

    });

    domainDeleteFunctions.add((MailDomain d) {
      var li = addressDomainLi(d);
      li.remove();
    });


    addressFunctions.add((MailAddress a) {
      LIElement li = addressLi(a);

      if (!li.classes.contains('can_edit')) {
        return;
      }

      li.onClick.listen((MouseEvent evt) {
        if (evt.target != li) {
          return;
        }

        if (li.classes.contains('active')) {
          _contractAddAddressForm();
        } else {
          _showAddressInForm(a, li);
        }
      });

      var delete = li.querySelector('.delete');
      core.debug(a);
      delete.onClick.listen((_) {
        dialogContainer.confirm("Er du sikker på at du vil slette? <br /> Hvis der er tilknyttet en mailbox vil den også blive slettet ugenopretteligt").result.then((bool b) {
          if (!b) {
            return;
          }
          li.parent.classes.add('blur');
          a.delete().then((_) {
            li.parent.classes.remove('blur');
          });
        });

      });

    });

    addressCreateFunctions.add((MailAddress a) {
      var li = addressLi(a);
      var ul = addressDomainLi(a.domain).children[0];
      ul.append(li);
      sortChildren(ul, (LIElement li1, LIElement li2) => (li1.dataset['local-part'] == "" || li2.dataset['local-part'] == "" ? -1 : 1) * li1.dataset['local-part'].compareTo(li2.dataset['local-part']));
      updateAddressListHidden();
    });

    addressDeleteFunctions.add((MailAddress a) {
      addressLi(a).remove();
      updateAddressListHidden();
      if(a == _currently_editing){
        _contractAddAddressForm();
      }
    });


  }

  void _contractAddAddressForm(){
    new ExpanderElementHandler(addAddressForm.parent).contract();
  }

  void _restoreForm() {

    if (_currently_editing == null) {
      return;
    }
    mailAddressLists.querySelectorAll('li.active').forEach((LIElement li) => li.classes.remove('active'));
    addAddressForm.classes.remove('editing');
    _currently_editing = _currently_editing_li = null;
    var better_select = new BetterSelect(addAddressForm.querySelector("select[name=domain]"));
    better_select.disabled = false;
    var fh = new ValidatingForm(addAddressForm);
    fh
      ..formHandler.clearForm()
      ..validate(true);

  }

  void _showAddressInForm(MailAddress a, LIElement li) {
    if (addAddressForm == null) {
      return;
    }
    _restoreForm();
    li.classes.add('active');
    addAddressForm.classes.add('editing');


    var expander = new ExpanderElementHandler(addAddressForm.parent);
    expander.expand();
    var s;
    s = expander.onContract.listen((_) {
      _restoreForm();
      s.cancel();
    });

    _currently_editing = a;
    _currently_editing_li = li;

    var better_select = new BetterSelect(addAddressForm.querySelector("select[name=domain]"));
    better_select.disabled = true;

    better_select.value = a.domain.domainName;

    InputElement local_part = addAddressForm.querySelector("input[name=local_part]");
    local_part.value = a.localPart;

    InputElement targets = addAddressForm.querySelector("input[name=targets]");
    targets.value = a.targets.join(" ");

    a.owners.forEach((User u) {
      CheckboxInputElement c = addAddressForm.querySelector('input[name=user_${u.username}');
      c.checked = true;
    });

    CheckboxInputElement add_mailbox = addAddressForm.querySelector("input[name=add_mailbox]");
    add_mailbox.checked = a.hasMailbox;

    if (a.hasMailbox) {
      InputElement mailbox_owner = addAddressForm.querySelector("input[name=mailbox_owner_name]");
      mailbox_owner.value = a.mailbox.name;

    }
    new ValidatingForm(addAddressForm).validate(true);


  }

  bool get _isShowingAddressInForm => addAddressForm.classes.contains('editing');

  void _hideInfoBoxesOnContract(ValidatingForm form) {
    var e = new ExpanderElementHandler(form.element.parent);
    e.onContract.listen((_) {
      form.validate();
      form.formHandler.clearForm();
    });
  }

  void setUpAddAddressForm() {
    if (addAddressForm == null) {
      return;
    }

    var vForm = new ValidatingForm(addAddressForm);
    var formH = vForm.formHandler;
    _hideInfoBoxesOnContract(vForm);

    formH.submitFunction = (Map<String, String> m) {
      var addressLibrary = mailDomainLibrary.domains[m['domain']].addressLibrary;

      var mailbox_name, mailbox_password;
      var has_mailbox = m.containsKey('add_mailbox');
      if (has_mailbox) {
        mailbox_name = m['mailbox_owner_name'];
        mailbox_password = m['mailbox_password'];
      }

      var owners = [];

      m.forEach((String key, String value) {
        if (!key.startsWith("user_")) {
          return;
        }
        owners.add(userLibrary.users[value]);
      });


      var targets = m['targets'].split(" ");
      targets.removeWhere((String s) => s.isEmpty);

      var local_part = m['local_part'];


      if (_isShowingAddressInForm) {
        var results = [];

        if (local_part != _currently_editing.localPart) {
          results.add(_currently_editing.changeLocalPart(local_part));
        }

        var current_owners = _currently_editing.owners;

        current_owners.forEach((User o) {
          if (owners.contains(o)) {
            owners.remove(o);
          } else {
            results.add(_currently_editing.removeOwner(o));
          }
        });

        owners.forEach((User u) {
          results.add(_currently_editing.addOwner(u));
        });

        if (has_mailbox) {
          if (!_currently_editing.hasMailbox) {
            results.add(_currently_editing.createMailbox(mailbox_name, mailbox_password));
          } else {
            var c1 = mailbox_name != _currently_editing.mailbox.name ? mailbox_name : null;
            var c2 = mailbox_password != "" ? mailbox_password : null;
            if (c1 != null || c2 != null) {
              results.add(_currently_editing.mailbox.changeInfo(name:c1, password:c2));
            }

          }
        } else if (_currently_editing.hasMailbox) {
          results.add(_currently_editing.deleteMailbox());
        }

        formH.blur();
        int completed = 0;
        results.forEach((Future f) =>
        ((int i) =>
        f.then((_) {
          completed ++;
          savingBar.endJob(i);
          if(completed == results.length){
            formH.unBlur();
            _showAddressInForm(_currently_editing, _currently_editing_li);
          }
        }))(savingBar.startJob()));

        return true;
      }


      core.FutureResponse<MailAddress> f;

      if (local_part == "") {
        f = addressLibrary.createCatchallAddress(owners: owners, targets:targets, mailbox_name: mailbox_name, mailbox_password: mailbox_password);
      } else {
        f = addressLibrary.createAddress(local_part, owners: owners, targets:targets, mailbox_name: mailbox_name, mailbox_password: mailbox_password);
      }


      formH.blur();
      f
        ..then((_) {
        formH.unBlur();
      })
        ..thenResponse(onSuccess:(_) {
        _contractAddAddressForm();
      });

      return true;
    };

    var domain_select = addAddressForm.querySelector("select[name=domain]");
    var v_domain_select = new Validator(domain_select);
    v_domain_select.addValueValidator((String s) => mailDomainLibrary[s] != null);

    InputElement local_part = addAddressForm.querySelector('input[name=local_part]');

    var v_local_part = new Validator(local_part);
    v_local_part
      ..addValueValidator((String s) => !(
        mailDomainLibrary.domains.containsKey(domain_select.value) &&
        mailDomainLibrary.domains[domain_select.value].addressLibrary.addresses.containsKey(s) &&
        mailDomainLibrary.domains[domain_select.value].addressLibrary.addresses[s] != _currently_editing))
      ..dependOn(v_domain_select);


    CheckboxInputElement mailbox_checkbox = addAddressForm.querySelector('#UserSettingsEditMailAddAddressAddMailboxCheckbox');
    var v_mailbox_checkbox = new Validator(mailbox_checkbox);

    mailbox_checkbox.onChange.listen((_){
      if(!_isShowingAddressInForm ||  mailbox_checkbox.checked){
        return;
      }
      dialogContainer.confirm("Er du sikker på at du vil fjerne mailboxen? <br /> Dette kan ikke fortrydes!").result.then((bool b){
        mailbox_checkbox.checked = !b;
      });
    });

    InputElement target_input = addAddressForm.querySelector('input[name=targets]');
    var v_target_input = new Validator<InputElement>(target_input);
    v_target_input
      ..addValueValidator((String v) => v.split(" ").fold(true, (bool prev, String s) => prev && (s == "" || core.validMail(s.trim()))))
      ..addValueValidator((String v) => mailbox_checkbox.checked || core.nonEmpty(v))
      ..dependOn(v_mailbox_checkbox);

    target_input.onChange.listen((_) {
      if (!v_target_input.valid) {
        return;
      }
      var values = target_input.value.split(" ");
      values.removeWhere((String s) => s.isEmpty);
      target_input.value = values.join(" ");
    });

    TextInputElement mailbox_name = addAddressForm.querySelector('input[name=mailbox_owner_name]');
    PasswordInputElement mailbox_password_input_1 = addAddressForm.querySelector('input[name=mailbox_password]');
    PasswordInputElement mailbox_password_input_2 = addAddressForm.querySelector('input[name=mailbox_password_2]');


    var v1 = new Validator<TextInputElement>(mailbox_name);
    v1.addValueValidator((String s) => !mailbox_checkbox.checked || s != "");
    var v2 = new Validator<PasswordInputElement>(mailbox_password_input_1);
    v2.addValueValidator((String s) => !mailbox_checkbox.checked || s != "" || (_currently_editing != null && _currently_editing.hasMailbox)) ;
    var v3 = new Validator<PasswordInputElement>(mailbox_password_input_2);
    v3
      ..addValueValidator((String s) => !mailbox_checkbox.checked || s == mailbox_password_input_1.value)
      ..dependOn(v2);

    v1.dependOn(v_mailbox_checkbox);
    v2.dependOn(v_mailbox_checkbox);
    v3.dependOn(v_mailbox_checkbox);


    var userListLabel = querySelector("#UserSettingsEditMailAddAddressUserCheckListLabel");
    var userList = querySelector("#UserSettingsEditMailAddAddressUserCheckList");

    var updateUserLI = (User u, LIElement li, LabelElement label, CheckboxInputElement checkbox) {
      li.dataset['user-name'] = u.username;
      checkbox
        ..id = label.attributes['for'] = "UserSettingsEditMailAddAddressFormAddUserCheck" + u.username
        ..name = "user_${u.username}"
        ..value = label.text = u.username;
    };

    var addUserLiCheckboxListener = (User u, LIElement li, [LabelElement label = null, CheckboxInputElement checkbox=null]) {
      label = label == null ? li.querySelector("label") : label;
      checkbox = checkbox == null ? li.querySelector("input[type=checkbox]") : checkbox;

      u.onChange.listen((_) {
        if (u.privileges != User.PRIVILEGE_PAGE) {
          li.remove();
          return;
        }
        updateUserLI(u, li, label, checkbox);
        if (li.parent == null) {
          userList.append(li);
          sortListFromDataSet(userList, 'user-name');
        }

      });

    };

    var userLICheckBox = (User u) {

      var li = userList.children.firstWhere((LIElement li) => li.dataset['user-name'] == u.username, orElse:() => null);

      if (li != null) {

        return li;
      }


      li = new LIElement();
      li.dataset['user-name'] = u.username;
      var checkbox = new CheckboxInputElement();
      var label = new LabelElement();
      li
        ..append(checkbox)
        ..append(label);

      updateUserLI(u, li, label, checkbox);

      addUserLiCheckboxListener(u, li, checkbox);

      return li;
    };


    domainCreateFunctions.add((MailDomain d) {
      var o = new OptionElement();
      o.text = o.value = d.domainName;
      domain_select.append(o);
      sortSelectOptionsByValue(domain_select);
    });


    domainDeleteFunctions.add((MailDomain d) {
      if (d.domainName == domain_select.value) {
        new BetterSelect(domain_select).value = "";
      }
      domain_select.children.removeWhere((OptionElement o) => o.value == d.domainName);
    });


    var updateUserListHidden = () => userListLabel.hidden = userList.hidden = userList.children.length == 0;

    userLibrary.users.forEach((_, User u) {
      addUserLiCheckboxListener(u, userLICheckBox(u));
    });

    userLibrary.onChange.listen((UserLibraryChangeEvent evt) {
      switch (evt.type) {
        case UserLibraryChangeEvent.CHANGE_CREATE:
          if (evt.user.hasSitePrivileges) {
            break;
          }
          userList.append(userLICheckBox(evt.user));
          sortListFromDataSet(userList, 'user-name');
          updateUserListHidden();

          break;
        case UserLibraryChangeEvent.CHANGE_DELETE:


          userLICheckBox(evt.user).remove();
          updateUserListHidden();
          break;
      }

    }

    );

  }

  void setUpListeners() {
    var caf = (List<Function> l) => (MailAddress d) => l.forEach((Function f) {
      f(d);
    });

    domainFunctions.add((MailDomain d) {
      var af = caf(addressFunctions);
      addressCreateFunctions.add(af);
      d.addressLibrary
        ..addresses.forEach((_, MailAddress a) => af(a))
        ..onCreate.listen(caf(addressCreateFunctions))
        ..onDelete.listen(caf(addressDeleteFunctions));

    });


    var cdf = (List<Function> l) => (MailDomain d) => l.forEach((Function f) {
      f(d);
    });
    var df = cdf(domainFunctions);


    mailDomainLibrary.domains.forEach((_, MailDomain domain) => df(domain));

    domainCreateFunctions.add(df);

    mailDomainLibrary.onCreate.listen(cdf(domainCreateFunctions));
    mailDomainLibrary.onDelete.listen(cdf(domainDeleteFunctions));

  }

  void setUpDomainAliasList() {

    var attachDeleteListener = (MailDomain domain, LIElement li, [DivElement delete = null]) {

      if (delete == null && (delete = li.querySelector('.delete')) == null) {
        return;
      }

      delete.onClick.listen((_) {
        dialogContainer.confirm("Er du sikker på at du vil slette?").result.then((bool v) {
          if (!v) {
            return;
          }
          blurElement(domainAliasList);
          domain.clearAliasTarget().then((_) {
            unBlurElement(domainAliasList);
          });
        });
      });
      var targetDomain = domain.aliasTarget;

      domain.onAliasTargetChange.listen((_) {
        if (domain.aliasTarget == null) {
          return;
        }
        li.children[2].text = li.dataset['to-domain'] = domain.aliasTarget.domainName;
      });


    };

    var createLi = (MailDomain d) {
      var li = domainAliasList.children.firstWhere((LIElement li) => li.dataset['from-domain'] == d.domainName, orElse:() => null);
      if (li != null) {
        attachDeleteListener(d, li);
        return li;
      }

      li = new LIElement();

      var divFrom = new DivElement();


      var divArrow = new DivElement();
      divArrow.classes.add('arrow');

      var divTo = new DivElement();

      var delete = new DivElement();
      delete.classes.add('delete');

      li
        ..append(divFrom)
        ..append(divArrow)
        ..append(divTo)
        ..append(delete);

      li
        ..dataset['from-domain'] = divFrom.text = d.domainName
        ..dataset['to-domain'] = divTo.text = d.aliasTarget == null ? "" : d.aliasTarget.domainName;

      attachDeleteListener(d, li, delete);
      return li;
    };


    domainFunctions.add((MailDomain d) {
      var li = createLi(d);
      d.onAliasTargetChange.listen((_) {
        if (d.aliasTarget == null) {
          li.remove();
        } else {
          domainAliasList.append(li);
        }
        sortListFromDataSet(domainAliasList, 'from-domain');
      });

      d.onDelete.listen((_) {
        li.remove();
        domainAliasList.children.removeWhere((LIElement li) => li.dataset['to-domain'] == d.domainName);
        sortListFromDataSet(domainAliasList, 'from-domain');
      });

    });


  }

  void setUpAddDomainAliasForm() {

    if (addDomainAliasForm == null) {
      return;
    }

    var vForm = new ValidatingForm(addDomainAliasForm);
    var formH = vForm.formHandler;

    _hideInfoBoxesOnContract(vForm);

    var domainOptionCheck = (MailDomain domain) => (OptionElement elm) => elm.value == domain.domainName;
    var optionFromDomain = (MailDomain domain) {
      var option = new OptionElement();
      option
        ..text = domain.domainName
        ..value = domain.domainName;
      return option;
    };


    SelectElement selectFrom = addDomainAliasForm.querySelector("select[name=from]");
    SelectElement selectTo = addDomainAliasForm.querySelector("select[name=to]");

    var resetSelect = (SelectElement select) => ([String v = null]) {
      if (v != null && v != select.value) {
        return;
      }
      new BetterSelect(select).value = "";
    };

    var resetFrom = resetSelect(selectFrom);
    var resetTo = resetSelect(selectTo);


    domainFunctions.add((MailDomain domain) {

      domain.onAliasTargetChange.listen((_) {
        if (domain.aliasTarget != null) {
          selectFrom.children.removeWhere(domainOptionCheck(domain));
          resetFrom();

        } else {
          selectFrom.append(optionFromDomain(domain));
          sortSelectOptionsByValue(selectFrom);
        }
      });
    });

    domainCreateFunctions.add((MailDomain domain) {
      selectTo.append(optionFromDomain(domain));
      sortSelectOptionsByValue(selectTo);
      if (domain.aliasTarget != null) {
        return;
      }

      selectFrom.append(optionFromDomain(domain));
      sortSelectOptionsByValue(selectFrom);

    });

    domainDeleteFunctions.add((MailDomain domain) {
      selectFrom.children.removeWhere(domainOptionCheck(domain));
      selectTo.children.removeWhere(domainOptionCheck(domain));
    });

    selectFrom.onChange.listen((_) {

      resetTo(selectFrom.value);

      selectTo.children.forEach((OptionElement o) {
        var to = mailDomainLibrary[o.value], from = mailDomainLibrary[selectFrom.value];
        to = mailDomainLibrary[o.value];
        from = mailDomainLibrary[selectFrom.value];
        if (from == null || to == null) {
          o.hidden = false;
          return;
        }

        if (from == to) {
          resetTo(o.value);
          o.hidden = true;
          return;
        }

        var t = to.aliasTarget;
        while (t != null) {
          if (t == from) {
            resetTo(o.value);
            o.hidden = true;
            return;
          }
          t = t.aliasTarget;
        }
        o.hidden = false;
      });
    });

    formH.submitFunction = (Map<String, String> map) {
      var domain = mailDomainLibrary[selectFrom.value];
      var domainTarget = mailDomainLibrary[selectTo.value];
      if (domain == null || domainTarget == null) {
        return true;
      }
      formH.blur();
      domain.changeAliasTarget(domainTarget).thenResponse(onSuccess:(_) {
        formH.unBlur();
      }, onError:(core.Response response) {
        formH.unBlur();
      });
      return true;
    };

  }

  void sortListFromDataSet(UListElement ul, String key) {
    var emptyListElement = ul.querySelector('.empty_list');

    if (emptyListElement != null) {
      if (ul.children.length == 1) {
        emptyListElement.hidden = false;
        return;
      }
      emptyListElement.hidden = true;
    } else if (ul.children.isEmpty) {
      return;
    }

    sortChildren(ul,
        (Element e1, Element e2) =>
    (e1.dataset.containsKey(key) ? e1.dataset[key] : "").compareTo(e2.dataset.containsKey(key) ? e2.dataset[key] : ""));


  }

  void sortSelectOptionsByValue(SelectElement s) {
    sortChildren(s, (OptionElement o1, OptionElement o2) => o1.value.compareTo(o2.value));
  }

  void sortChildren(Element element, int sort(Element e1, Element e2)) {
    var l = element.children.toList();
    l.sort(sort);
    l.forEach((Element opt) {
      element.append(opt);
    });

  }

  void setUpAddDomainForm() {
    if (addDomainForm == null) {
      return;
    }

    var validatingForm = new ValidatingForm(addDomainForm);
    var domainNameInput = addDomainForm.querySelector("input[name=domain_name]");

    _hideInfoBoxesOnContract(validatingForm);

    new Validator<InputElement>(domainNameInput)
      ..addValueValidator((String value) => !mailDomainLibrary.domains.containsKey(value));

    validatingForm.formHandler.submitFunction = (Map<String, String > data) {
      validatingForm.formHandler.blur();
      mailDomainLibrary.createDomain(data['domain_name'], data['super_password']).thenResponse(onSuccess:(core.Response<MailDomain> response) {
        validatingForm.formHandler.clearForm();
        validatingForm.formHandler.unBlur();
      }, onError:(core.Response<MailDomain> response) {
        validatingForm.formHandler.unBlur();
      });


      return true;
    };

  }

  void blurElement(Element elm) {
    elm.classes.add('blur');
  }

  void unBlurElement(Element elm) {
    elm.classes.remove('blur');
  }

  void setUpMailDomainList() {


    var setLiDataSet = (LIElement li, MailDomain domain) {
      li
        ..dataset['last-modified'] = (domain.lastModified.millisecondsSinceEpoch ~/ 1000).toString()
        ..dataset['description'] = domain.description
        ..dataset['active'] = domain.active ? "true" : "false"
        ..dataset['domain-name'] = domain.domainName
        ..dataset['alias-target'] = domain.aliasTarget == null ? "" : domain.aliasTarget.domainName;
    };

    var addDomainLiListener = (MailDomain domain, LIElement li) {
      var listener = (_) {
        setLiDataSet(li, domain);
      };

      domain
        ..onActiveChange.listen(listener)
        ..onAliasTargetChange.listen(listener)
        ..onDescriptionChange.listen(listener);

      li.querySelector('.delete').onClick.listen((_) {
        var c = dialogContainer.password("Er du sikker på at du vil slette?");
        c.result.then((String s) {
          blurElement(mailDomainList);
          domain.delete(s).then((_) {
            unBlurElement(mailDomainList);
          });
        });
      });

    };

    var liFromDomain = (MailDomain domain) {
      var li = new LIElement();
      setLiDataSet(li, domain);
      li.text = domain.domainName;
      var delete = new DivElement();
      delete.classes.add('delete');
      li.append(delete);
      addDomainLiListener(domain, li);
      return li;
    };

    mailDomainList.children.forEach((LIElement li) {
      if (li.classes.contains('empty_list')) {
        return;
      }
      var name = li.dataset['domain-name'];
      var domain = mailDomainLibrary.domains[name];
      addDomainLiListener(domain, li);
    });


    domainDeleteFunctions.add((MailDomain domain) {
      mailDomainList.children.removeWhere((LIElement li) => li.dataset['domain-name'] == domain.domainName);
      sortListFromDataSet(mailDomainList, 'domain-name');
    });
    domainCreateFunctions.add((MailDomain domain) {
      mailDomainList.append(liFromDomain(domain));
      sortListFromDataSet(mailDomainList, 'domain-name');
    });
  }


}

class UserSettingsLoggerInitializer extends core.Initializer {

  TableElement _logTable = querySelector("#UserSettingsLogTable");

  AnchorElement _logLink = querySelector("#ClearLogLink");

  ParagraphElement _pElm = querySelector("#LogInfoParagraph");

  DivElement _numDiv = new DivElement();


  UserSettingsLoggerInitializer() {
    _numDiv.classes.add("num");
  }

  bool get canBeSetUp => _logTable != null && _logLink != null && _pElm != null;

  void _updateNum() {
    if (_logTable.classes.contains("empty")) {
      _numDiv.remove();
      return;
    }
    _numDiv.text = _logTable.querySelectorAll("tr:not(.empty_row)").length.toString();
    var p = querySelector("#UserSettingsMenu .log");
    p.append(_numDiv);

  }

  void setUp() {
    _updateNum();

    var addDumpListener = (AnchorElement a) {
      a.onClick.listen((MouseEvent evt) {
        var loader = dialogContainer.loading("Henter log filen");
        logger.contextAt(new DateTime.fromMillisecondsSinceEpoch(int.parse(a.dataset["id"]) * 1000)).then((core.Response<Map> resp) {
          if (resp.type != core.Response.RESPONSE_TYPE_SUCCESS) {
            loader.close();
            return;
          }

          var button = new ButtonElement(), pre = new PreElement();
          button.text = "Luk";
          button.onClick.listen((_) => loader.close());
          pre.classes.add("code");
          pre.text = resp.payload.toString();
          loader.element
            ..children.clear()
            ..append(pre)
            ..append(button);
          loader.stopLoading();
          core.escQueue.add(() {
            loader.close();
            return true;
          });
        });
      });
    };

    logger.onLog.listen((LogEntry entry) {
      _logTable.classes.remove('empty');
      var row = _logTable.insertRow(0);
      var levelStrings = entry.levelStrings;
      row.classes.addAll(levelStrings);

      var c1 = row.addCell();
      c1
        ..title = levelStrings.map(core.upperCaseWords).join(" ")
        ..classes.add("level");
      var c2 = row.addCell();
      c2.text = entry.message;
      var c3 = row.addCell();
      c3.classes.add("dumpfile");
      if (entry.context != null) {
        var a = new AnchorElement();
        a
          ..dataset['id'] = entry.id.toString()
          ..href = "#";
        addDumpListener(a);
        c3.append(a);
      }
      var c4 = row.addCell();
      c4
        ..classes.add('date')
        ..text = core.dateString(entry.time);
      _updateNum();
    });

    _logTable.querySelectorAll(".dumpfile a").forEach(addDumpListener);


    _logLink.onClick.listen((MouseEvent evt) {
      _logTable.classes.add('blur');
      logger.clearLog().then((core.Response<Logger> response) {
        _logTable.classes.remove('blur');
        if (response.type == core.Response.RESPONSE_TYPE_SUCCESS) {
          _logTable.querySelectorAll("tr:not(.empty_row)").forEach((TableRowElement li) => li.remove());
          _logTable.classes.add("empty");
          _pElm.querySelectorAll("i").forEach((Element e) => e.text = "0");
          _updateNum();
        }
      });
      evt.preventDefault();
    });

  }
}


class UserSettingsUpdateSiteInitializer extends core.Initializer {
  ButtonElement _checkButton = querySelector("#UserSettingsContent button.update_check");
  CheckboxInputElement _autoCheckBox = querySelector("#UserSettingsContent #UserSettingsUpdaterEnableAutoUpdate");

  SpanElement _checkTime = querySelector("#UserSettingsContent .update_site span.check_time");

  JSONClient _client = new AJAXJSONClient();

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
      var t = new Timer(new Duration(seconds:1), () {
        window.location.reload();
      });
    });

  }


}

class UserSettingsPageUserListFormInitializer extends core.Initializer {

  UserLibrary _userLib;

  PageOrder _order;

  UListElement _pageUserList = querySelector('#PageUserList');

  FormElement _addUserToPageForm = querySelector('#AddUserToPageForm');

  SelectElement _pageUserSelect = querySelector('#EditPageAddUserSelect');

  UserSettingsPageUserListFormInitializer(UserLibrary this._userLib, PageOrder this._order);

  bool get canBeSetUp => _pageUserList != null && _order.currentPage != null;

  LIElement createUserLi(User user) {
    var li = new LIElement();
    li.innerHtml = "<span class='val'>${user.username}</span><div class='delete link' title='Slet'>&nbsp;</div>";
    return li;
  }

  void _addListener(LIElement li) {
    var val = li.querySelector('.val');
    var delete = li.querySelector('.delete');
    var username = val == null ? li.text : val.text;
    var user = _userLib.users[username];
    user.onChange.listen((User u) {
      if (val == null) {
        li.text = u.username;
      } else {
        val.text = u.username;
      }
    });
    if (delete == null) {
      return;
    }
    delete.onClick.listen((MouseEvent e) {
      var dialogResult = new DialogContainer().confirm("Er du sikker på at du vil fjerne privilegierne?").result;
      dialogResult.then((bool b) {
        if (!b) {
          return;
        }
        var i = savingBar.startJob();
        user.revokePagePrivilege(_order.currentPage).then((core.Response response) {
          if (response.type == core.Response.RESPONSE_TYPE_SUCCESS) {
            li.remove();
            var opt = new OptionElement();
            opt.text = opt.value = username;
            _pageUserSelect.append(opt);
          }
          savingBar.endJob(i);
        });
      });
    });
  }

  void setUp() {
    var pageUserLis = _pageUserList.querySelectorAll('li');


    _userLib.onChange.listen((UserLibraryChangeEvent evt) {
      var changeType = evt.type, user = evt.user;
      if (changeType == UserLibraryChangeEvent.CHANGE_CREATE) {
        if (user.privileges != User.PRIVILEGE_PAGE) {
          var li = new LIElement();
          li.text = user.username;
          _addListener(li);
          _pageUserList.append(li);
          pageUserLis = _pageUserList.querySelectorAll('li');
        } else if (user.pages.contains(_order.currentPage)) {
          var li = createUserLi(user);
          _addListener(li);
          _pageUserList.append(li);
          pageUserLis = _pageUserList.querySelectorAll('li');

        } else if (_pageUserSelect != null) {
          var opt = new OptionElement();
          opt.text = opt.value = user.username;
          _pageUserSelect.append(opt);
        }
      } else {
        pageUserLis.forEach((LIElement li) {
          if ((li.querySelector('.val') == null ? li.text : li.querySelector('.val').text) == user.username) {
            li.remove();
          }
        });
        _pageUserSelect.options.forEach((OptionElement o) {
          if (o.value == user.username) {
            o.remove();
          }
        });
      }
    });

    pageUserLis.forEach(_addListener);
    if (_addUserToPageForm == null) {
      return;
    }
    new Validator(_pageUserSelect).addNonEmptyValueValidator();
    new ValidatingForm(_addUserToPageForm).validate();
    var deco = new FormHandler(_addUserToPageForm);
    deco.submitFunction = (Map<String, String> data) {
      if (_addUserToPageForm.classes.contains('initial')) {
        return false;
      }
      deco.blur();
      var user = _userLib.users[data['username']];
      user.addPagePrivilege(_order.currentPage).then((core.Response response) {
        if (response.type == core.Response.RESPONSE_TYPE_ERROR) {
          deco.unBlur();
          return false;
        }
        _pageUserSelect.selectedOptions.first.remove();
        new BetterSelect(_pageUserSelect).update();
        var newLi = createUserLi(user);
        _pageUserList.append(newLi);
        pageUserLis = _pageUserList.querySelectorAll('li');
        _addListener(newLi);
        deco.unBlur();
      });

      return false;
    };

  }
}


class UserSettingsUserListInitializer extends core.Initializer {
  UserLibrary _userLib;

  UListElement _userList = querySelector('#UserList');

  UserSettingsUserListInitializer(UserLibrary this._userLib);

  bool get canBeSetUp => _userList != null;

  String _userPrivilegeString(User u, [bool simple=false]) => u.privileges == User.PRIVILEGE_ROOT ? (simple ? "root" : "Root") : (u.privileges == User.PRIVILEGE_SITE ? (simple ? "site" : "Website") : (simple ? "page" : "Side"));

  void _setDataset(LIElement li, User user) {
    li.dataset["username"] = user.username;
    li.dataset["privileges"] = _userPrivilegeString(user, true);
    li.dataset["lastLogin"] = user.lastLogin == null ? "" : (user.lastLogin.millisecondsSinceEpoch ~/ 1000).toString();
    li.dataset["mail"] = user.mail;
    li.dataset["pages"] = user.pages.map((Page p) => p.id).join(" ");
  }

  void _addListener(LIElement li) {
    var val = li.querySelector('.val'), privileges = li.querySelector('.privileges');
    var username = li.dataset['username'];
    var user = _userLib.users[username];
    user.onChange.listen((User u) {
      val.text = u.username;
      val.href = "mailto:${u.mail}";
      privileges.text = "(${_userPrivilegeString(u)} Administrator)";
      _setDataset(li, u);
    });
    var loginString = user.lastLogin == null ? "Aldrig" : core.dateString(user.lastLogin);
    var infoBox = new InfoBox("Sidst set: $loginString");
    var time = li.querySelector('.time');
    infoBox.backgroundColor = InfoBox.COLOR_BLACK;
    time.onMouseOver.listen((_) => infoBox.showAboveCenterOfElement(time));
    time.onMouseOut.listen((_) => infoBox.remove());

    var delete = li.querySelector('.delete');
    if (delete == null) {
      return;
    }
    delete.onClick.listen((MouseEvent e) {
      var dialog = new DialogContainer();
      dialog.confirm("Er du sikker på at du vil slette denne bruger?").result.then((bool b) {
        if (!b) {
          return;
        }
        var i = savingBar.startJob();
        _userLib.deleteUser(username).then((_) => savingBar.endJob(i));
      });
    });


  }

  void setUp() {


    var userLis = _userList.querySelectorAll('li');
    userLis.forEach(_addListener);
    _userLib.onChange.listen((UserLibraryChangeEvent evt) {
      var changeType = evt.type, user = evt.user;
      if (changeType == UserLibraryChangeEvent.CHANGE_CREATE) {
        var li = new LIElement();
        var privilege = _userPrivilegeString(user);
        var a = new AnchorElement();
        a
          ..text = user.username
          ..classes.add("val")
          ..href = "mailto:${user.mail}";
        li.append(a);
        li.appendHtml(", <span class='privileges'>($privilege Administrator)</span> <div class='delete link' title='Slet'>&nbsp;</div><div class='time link'>&nbsp;</div>");
        _setDataset(li, user);


        _userList.append(li);
        userLis = _userList.querySelectorAll('li');
        _addListener(li);
      } else {
        userLis.forEach((LIElement li) {
          if (li.querySelector('.val').text == user.username)li.remove();
        });
      }
    });

  }
}


class UserSettingsAddUserFormInitializer extends core.Initializer {
  UserLibrary _userLib;

  FormElement _addUserForm = querySelector('#EditUsersAddUserForm');

  UserSettingsAddUserFormInitializer(UserLibrary this._userLib);

  bool get canBeSetUp => _addUserForm != null;

  void setUp() {
    var userMailField = _addUserForm.querySelector('#AddUserMailField'), userLevelSelect = _addUserForm.querySelector('#AddUserLevelSelect');
    var v = new Validator(userMailField);
    v.addValidMailValueValidator();
    v.errorMessage = "Skal være gyldig E-mail";
    new Validator(userLevelSelect).addNonEmptyValueValidator();
    var validatingForm = new ValidatingForm(_addUserForm);
    validatingForm.validate();
    var decoration = new FormHandler(_addUserForm);
    decoration.submitFunction = (Map<String, String> data) {
      if (_addUserForm.classes.contains('initial')) {
        return false;
      }
      decoration.blur();
      _userLib.createUser(data['mail'], data['level']).then((core.Response response) {
        if (response.type == core.Response.RESPONSE_TYPE_SUCCESS) {
          userMailField.value = "";
          validatingForm.validate();
          userMailField.blur();
        }
        decoration.unBlur();
      });
      return false;
    };
  }
}


class UserSettingsAddPageFormInitializer extends core.Initializer {
  PageOrder _order;

  FormElement _addPageForm = querySelector("#EditPagesForm");

  UserSettingsAddPageFormInitializer(PageOrder this._order);

  bool get canBeSetUp => _addPageForm != null;

  void setUp() {
    var input = querySelector('#EditPagesAddPage');

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

class UserSettingsEditPageFormInitializer extends core.Initializer {
  PageOrder _order;

  FormElement _editPageForm = querySelector('#EditPageForm');


  UserSettingsEditPageFormInitializer(PageOrder this._order);


  bool get canBeSetUp => _editPageForm != null;

  void setUp() {

    if (_editPageForm == null) {
      return;
    }
    var submitButton = _editPageForm.querySelector('input[type=submit]');
    var validatingForm = new ValidatingForm(_editPageForm);

    var editIdField = _editPageForm.querySelector('#EditPageEditIDField'), editAliasField = _editPageForm.querySelector('#EditPageEditAliasField'), editTitleField = _editPageForm.querySelector('#EditPageEditTitleField'), editTemplateSelect = _editPageForm.querySelector('#EditPageEditTemplateSelect');

/* SET UP VALIDATOR */

    new Validator(editTitleField)
      ..addNonEmptyValueValidator()
      ..errorMessage = "Titlen kan ikke være tom";

    new Validator(editIdField)
      ..addValueValidator((String value) => (_order.currentPage != null && value == _order.currentPage.id) || (new RegExp(r'^[0-9a-z\-_]+$', caseSensitive:false).hasMatch(value) && !_order.pageExists(value)))
      ..errorMessage = "ID kan kun indeholde symbolder a-z, 0-9, - eller _";
    new Validator(editAliasField)
      ..addValueValidator((String value) => value == "" || PCRE.checkPCRE(value))
      ..errorMessage = "Alias skal være et gyldig <i>PCRE</i>";

    validatingForm.validate();

/* SET UP DECORATION AND SUBMIT */

    var formDecoration = new FormHandler(_editPageForm);
    formDecoration.submitFunction = (Map<String, String> data) {
      if (_editPageForm.classes.contains('initial')) {
        return false;
      }
      formDecoration.blur();
      var template = _order.currentPage.template;
      _order.currentPage.changeInfo(title:data['title'], id:data['id'], template:data['template'], alias:data['alias']).then((core.Response<Page> response) {
        if (response.type == core.Response.RESPONSE_TYPE_SUCCESS) {
          formDecoration.changeNotion("Ændringerne er gemt", FormHandler.NOTION_TYPE_SUCCESS);
          editTitleField.blur();
          editIdField.blur();
          editAliasField.blur();
          if (template != _order.currentPage.template) {
            dialogContainer.alert("Du har redigeret sidens type og skal derfor genindlæse siden.").onClose.listen((_) => window.location.reload());
          }

        } else {
          formDecoration.changeNotion(_errorMessage(response.error_code), FormHandler.NOTION_TYPE_ERROR);
        }
        formDecoration.unBlur();

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
    _activeList.querySelectorAll('li:not(.emptyListInfo)').forEach((LIElement element) {
      var e = new UserSettingsPageLi(element);
      e.setUp();
    });
    _inactiveList.querySelectorAll('li:not(.emptyListInfo)').forEach((LIElement element) {
      var e = new UserSettingsPageLi(element);
      e.setUp();
    });

    var updateListInfo = (UListElement ul, [bool active = true]) {
      var len = ul.querySelectorAll('li').length;
      if (len == 0) {
        var li = new LIElement();
        li.classes.add('emptyListInfo');
        li.text = active ? "Der er ingen aktive sider" : "Der er ingen inaktive sider";
        ul.append(li);
      } else if (len > 1 && ul.children.any((Element e) => e.classes.contains('emptyListInfo'))) {
//      ul.children.removeWhere((Element e) => e.classes.contains('emptyListInfo'));
//Fix until above works again
        ul.children.toList().forEach((Element e) {
          if (e.classes.contains('emptyListInfo')) e.remove();
        });
      }
    };

    var ULChangeListener = (UListElement ul, String parent) => (Event e) {
      var newOrder = [];
      ul.children.forEach((LIElement li) {
        var pageLi = new UserSettingsPageLi(li);
        newOrder.add(pageLi.page.id);
      });
      var i = savingBar.startJob();
      _order.changePageOrder(newOrder, parent_id:parent).then((_) => savingBar.endJob(i));
      e.stopPropagation();
    };
    _activeList.onChange.listen(ULChangeListener(_activeList, null));
    _activeList.querySelectorAll('ul').forEach((UListElement ul) => ul.onChange.listen(ULChangeListener(ul, new UserSettingsPageLi(ul.parent).page.id)));

    _order.onUpdate.listen((PageOrderChange change) {
      var page = change.page, changeType = change.type;
      switch (changeType) {
        case PageOrderChange.PAGE_ORDER_CHANGE_CREATE_PAGE:
          var pageLi = new UserSettingsPageLi.fromPage(page);
          pageLi.updateActive();
          _inactiveList.append(pageLi.li);
          updateListInfo(_inactiveList, false);

          break;
        case PageOrderChange.PAGE_ORDER_CHANGE_DELETE_PAGE:case PageOrderChange.PAGE_ORDER_CHANGE_DEACTIVATE:
          if (changeType == PageOrderChange.PAGE_ORDER_CHANGE_DELETE_PAGE) {
            var pageLi = new UserSettingsPageLi.fromPage(page);
            var parent = pageLi.li.parent;
            if (parent == _inactiveList) {
              pageLi.li.remove();
              updateListInfo(_inactiveList, false);
              break;
            }
            new ChangeableList(pageLi.li.parent);
            pageLi.li.remove();
            updateListInfo(parent);
          }

          _inactiveList.children.clear();

          var path = new UserSettingsActivePagesPath();
          var l = _order.inactivePages;

          l.sort((Page p1, Page p2) => p1.id.compareTo(p2.id));
          l.forEach((Page p) {
            var pageLi = new UserSettingsPageLi.fromPage(p);
            pageLi.updateActive();
            _inactiveList.append(pageLi.li);
          });
          var ulToUpdate = path.currentlyShowing == null ? _activeList : new UserSettingsPageLi.fromPage(path.currentlyShowing).li.querySelector('ul');

          updateListInfo(_inactiveList);
          updateListInfo(ulToUpdate);
          break;
        case PageOrderChange.PAGE_ORDER_CHANGE_ACTIVATE:
          var path = new UserSettingsActivePagesPath();
          var showingPage = path.currentlyShowing;
          path.reset();
          var recursiveBuilder;
          recursiveBuilder = (String parent, UListElement parentUl) {
            _order.listPageOrder(parent_id:parent).forEach((Page p) {
              var ul = new UListElement();
              var pageLi = new UserSettingsPageLi.fromPage(p);
              ul.classes
                ..add('colorList')
                ..add('draggable');
              ul.onChange.listen(ULChangeListener(ul, p.id));
              pageLi.updateActive();
//            pageLi.li.children.removeWhere((Element e) => e is UListElement);
//Fix until above works again
              pageLi.li.children.toList().forEach((Element e) {
                if (e is UListElement) e.remove();
              });

              pageLi.li.append(ul);
              new ChangeableList(parentUl).append(pageLi.li);
              recursiveBuilder(p.id, ul);
            });
            updateListInfo(parentUl);
          };

          _activeList.children.clear();
          recursiveBuilder(null, _activeList);
          updateListInfo(_inactiveList, false);
          if (showingPage != null) {
            path.showSubMenu(showingPage.id);
          }
          break;
      }
    });
  }

}


class UserSettingsDecorationInitializer extends core.Initializer {
  var _expandLink = querySelector("#UserSettingsExpandLink"), _contractLink = querySelector("#UserSettingsContractLink"), _container = querySelector("#UserSettingsContainer"), _slideElement = querySelector("#UserSettingsContent > ul"), _slideMenuList = querySelector("#UserSettingsMenu > ul");


  bool get canBeSetUp => _expandLink != null && _contractLink != null && _container != null && _slideElement != null && _slideMenuList != null;

  void setUp() {
    var expander = new UserSettingsExpandDecoration();
    _expandLink.onClick.listen((_) {
      expander.expand();
      core.escQueue.add(() {
        if (!expander._expanded) {
          return false;
        }
        var f = _container.querySelector(':focus');
        if (f != null) {
          f.blur();
        }
        expander.contract();
        return true;
      });
    });
    _contractLink.onClick.listen((_) => expander.contract());


    var linkExpander = new UserSettingsExpandLinkExpandDecoration(_expandLink);
    linkExpander.expandOnMouseOver = linkExpander.contractOnMouseOut = true;


    var slider = new UserSettingsSlideDecoration();
    var lis = _slideMenuList.querySelectorAll('ul > li');
    var i = 0;
    lis.forEach((LIElement li) {
      var index = i;
      li.onClick.listen((e) {
        slider.goToIndex(index);
        _slideMenuList.querySelector('.active').classes.remove('active');
        li.classes.add('active');
      });
      i++;
    });
  }
}