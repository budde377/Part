part of user_settings;


class UserSettingsMailInitializer extends core.Initializer {

  UListElement mailDomainList = querySelector("#UserSettingsEditMailDomainList");

  UListElement mailAddressLists = querySelector("#UserSettingsEditMailAddressLists");

  UListElement domainAliasList = querySelector("#UserSettingsEditMailDomainAliasList");

  FormElement addDomainForm = querySelector("#UserSettingsEditMailAddDomainForm");

  FormElement addDomainAliasForm = querySelector("#UserSettingsEditMailAddDomainAliasForm");

  FormElement addAddressForm = querySelector("#UserSettingsEditMailAddAddressForm");


  bool get canBeSetUp => mailDomainLibraryAvailable && mailDomainList != null && domainAliasList != null && mailAddressLists != null;


  MailAddress _currently_editing;
  LIElement _currently_editing_li;


  void setUp() {

    setUpMailDomainList();
    setUpAddDomainForm();
    setUpAddDomainAliasForm();
    setUpDomainAliasList();
    setUpAddAddressForm();
    setUpAddressList();


  }

  void setUpAddressList() {

    var gas = [];

    var deletes = [];

    var deleteListener = (MailAddress a, LIElement li) {
      var delete = li.querySelector('.delete');
      if(deletes.contains(delete)){
        return;
      }
      deletes.add(delete);
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
    };

    var g = new ElementChildrenGenerator<MailDomain, LIElement>((MailDomain d) {
      var li = new LIElement();
      var ul = new UListElement();
      li.append(ul);
      return li;
    }, mailAddressLists, (LIElement v, _) {
      if (v.children.length == 0 || !(v.children[0] is UListElement)) {
        return null;
      }
      return mailDomainLibrary[v.children[0].dataset['domain-name']];
    });

    g.addUpdater((MailDomain d, LIElement li) {
      li.children[0].dataset['domain-name'] = d.domainName;
    });


    g.addHandler((MailDomain d, LIElement domain_li) {
      var ul = domain_li.children[0];
      var ga = new ElementChildrenGenerator<MailAddress, LIElement>(
              (_) => new LIElement(),
          ul,
              (LIElement li, _) => d.addressLibrary[li.dataset['local-part']]);
      var sort = (_) => sortChildren(ga.element, (LIElement li1, LIElement li2) => (li1.dataset['local-part'] == "" || li2.dataset['local-part'] == "" ? -1 : 1) * li1.dataset['local-part'].compareTo(li2.dataset['local-part']));

      ga.addUpdater((MailAddress address, LIElement li) {
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

        deleteListener(address, li);
        if (can_edit) {
          li.classes.add('can_edit');
        } else {
          li.classes.remove('can_edit');
        }

        if (address.hasMailbox) {
          li.dataset['mailbox-name'] = address.mailbox.name;
          li.dataset['mailbox-last-modified'] = (address.mailbox.lastModified.millisecondsSinceEpoch ~/ 1000).toString();
        }
      });

      ga.addHandler((MailAddress a, _) {
        var l = (_) => ga.update(a);
        a
          ..onActiveChange.listen(l)
          ..onOwnerChange.listen(l)
          ..onTargetChange.listen(l)
          ..onLocalPartChange.listen((_) {
          l(null);
          sort(null);
        })
          ..onMailboxChange.listen((MailMailbox m) {
          l(null);
          if (m != null) {
            m.onNameChange.listen(l);
          }

        });

      });

      ga.addHandler((MailAddress a, LIElement li) {
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

        deleteListener(a, li);

      });

      gas.add(ga);

      ga.onEmpty.listen((_) {
        ul.hidden = true;
        if (gas.any((core.Generator g) => g.size > 0)) {
          return;
        }
        g.element.classes.add('empty');
      });

      ga.onNotEmpty.listen((_) => g.element.classes.remove('empty'));
      ga.onNotEmpty.listen((_){
        ul.hidden = false;
      });
      ga.onAdd.listen(sort);

      ga.onRemove.listen((core.Pair p) {
        if (p.k != _currently_editing) {
          return;
        }
        _contractAddAddressForm();
      });

      d.addressLibrary.onAdd.listen(ga.add);
      d.addressLibrary.onRemove.listen(ga.remove);


    });

    g.dependsOn(mailDomainLibrary);

    g.onAdd.listen((_) => sortListFromDataSet(mailAddressLists, 'domain-name'));

    g.onEmpty.listen((_) => g.element.classes.add('empty'));


  }

  void _contractAddAddressForm() {
    new ExpanderElementHandler(addAddressForm.parent).contract();
  }

  void _restoreForm() {
    var fh = new ValidatingForm(addAddressForm);

    if (_currently_editing == null) {
      fh
        ..formHandler.clearForm()
        ..validate(true);
      return;
    }
    mailAddressLists.querySelectorAll('li.active').forEach((LIElement li) => li.classes.remove('active'));
    addAddressForm.classes.remove('editing');
    _currently_editing = _currently_editing_li = null;
    var better_select = new BetterSelect(addAddressForm.querySelector("select[name=domain]"));
    better_select.disabled = false;
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
      CheckboxInputElement c = addAddressForm.querySelector('input[name=user_${u.username}]');
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

        var current_targets = _currently_editing.targets.toList();
        targets.forEach((String target){
          if(current_targets.contains(target)){
            current_targets.remove(target);
            return;
          }

          results.add(_currently_editing.addTarget(target));
        });

        current_targets.forEach((String target){
          results.add(_currently_editing.removeTarget(target));
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
        if(results.length == 0){
          return true;
        }

        formH.blur();
        int completed = 0;
        results.forEach((Future f) =>
        ((int i) =>
        f.then((_) {
          completed ++;
          savingBar.endJob(i);
          if (completed == results.length) {
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

    SelectElement domain_select = addAddressForm.querySelector("select[name=domain]");
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

    var domain_select_generator = new ElementChildrenGenerator<MailDomain, OptionElement>(
        optionFromDomain,
        domain_select,
            (OptionElement o, _) => mailDomainLibrary[o.value]);

    domain_select_generator.onAdd.listen((_) => sortSelectOptionsByValue(domain_select));
    domain_select_generator.onRemove.listen((core.Pair<MailDomain, OptionElement> pair) {
      if (pair.k.domainName != domain_select.value) {
        return;
      }
      new BetterSelect(domain_select).value = "";

    });
    domain_select_generator.dependsOn(mailDomainLibrary);


    CheckboxInputElement mailbox_checkbox = addAddressForm.querySelector('#UserSettingsEditMailAddAddressAddMailboxCheckbox');
    var v_mailbox_checkbox = new Validator(mailbox_checkbox);

    mailbox_checkbox.onChange.listen((_) {
      if (!_isShowingAddressInForm || mailbox_checkbox.checked) {
        return;
      }
      dialogContainer.confirm("Er du sikker på at du vil fjerne mailboxen? <br /> Dette kan ikke fortrydes!").result.then((bool b) {
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

    var g = new ElementChildrenGenerator<User, LIElement>((User u) {
      var li = new LIElement();
      li.dataset['user-name'] = u.username;
      var checkbox = new CheckboxInputElement();
      var label = new LabelElement();
      li
        ..append(checkbox)
        ..append(label);

      return li;
    },
    querySelector("#UserSettingsEditMailAddAddressUserCheckList"),
        (LIElement li, _) => userLibrary.users[li.dataset['user-name']]);

    g.addUpdater((User u, LIElement li) {
      if (u.hasSitePrivileges) {
        li.remove();
        return;
      }

      g.add(u);

      var checkbox = li.querySelector('input[type=checkbox]');
      var label = li.querySelector('label');
      li.dataset['user-name'] = u.username;
      checkbox
        ..id = label.attributes['for'] = "UserSettingsEditMailAddAddressFormAddUserCheck" + u.username
        ..name = "user_${u.username}"
        ..value = label.text = u.username;
    });

    g
      ..onAdd.listen((_) => sortListFromDataSet(g.element, 'user-name'))
      ..onUpdate.listen((_) => sortListFromDataSet(g.element, 'user-name'))
      ..onEmpty.listen((_) => userListLabel.hidden = g.element.hidden = true)
      ..onNotEmpty.listen((_) => userListLabel.hidden = g.element.hidden = false);


    g.dependsOn(userLibrary, whenAdd:(User u) => !u.hasSitePrivileges, whenUpdate: (User u) => g.contains(u) || !u.hasSitePrivileges);


  }


  void setUpDomainAliasList() {


    var g = new ElementChildrenGenerator<MailDomain, LIElement>((MailDomain d) {
      var li = new LIElement();

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

      return li;
    }, domainAliasList, (LIElement li, _) => mailDomainLibrary[li.dataset['from-domain']]);

    g.addUpdater((MailDomain d, LIElement li) {
      li
        ..dataset['from-domain'] = li.children[0].text = d.domainName
        ..dataset['to-domain'] = li.children[2].text = d.aliasTarget.domainName;
    });

    g.addHandler((MailDomain domain, LIElement li) {

      var delete = li.querySelector('.delete');

      if (delete == null) {
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

      domain.onAliasTargetChange.listen((_) {
        if (domain.aliasTarget == null) {
          return;
        }
        li.children[2].text = li.dataset['to-domain'] = domain.aliasTarget.domainName;
      });


    });

    g
      ..onEmpty.listen((_) => g.element.classes.add('empty'))
      ..onNotEmpty.listen((_) => g.element.classes.remove('empty'))
      ..onAdd.listen((_) => sortListFromDataSet(domainAliasList, 'from-domain'));

    mailDomainLibrary.every((MailDomain d) {
      d.onAliasTargetChange.listen((MailDomain target) {
        if (target == null) {
          g.remove(d);
        } else if (g.contains(d)) {
          g.update(d);
        } else {
          g.add(d);
        }
      });
    });

    g.addHandler((MailDomain d, _) => d.onDelete.listen(g.remove));


  }

  OptionElement optionFromDomain(MailDomain domain) {
    var option = new OptionElement();
    option
      ..text = domain.domainName
      ..value = domain.domainName;
    return option;
  }


  void setUpAddDomainAliasForm() {

    if (addDomainAliasForm == null) {
      return;
    }

    var vForm = new ValidatingForm(addDomainAliasForm);

    _hideInfoBoxesOnContract(vForm);

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

    var select_from_generator = new ElementChildrenGenerator<MailDomain, OptionElement>(
        optionFromDomain,
        selectFrom,
            (OptionElement o, _) => mailDomainLibrary[o.value]);

    var select_to_generator = new ElementChildrenGenerator<MailDomain, OptionElement>(
        optionFromDomain,
        selectTo,
            (OptionElement o, _) => mailDomainLibrary[o.value]);

    select_from_generator.dependsOn(mailDomainLibrary);

    select_from_generator.addUpdater((MailDomain d, OptionElement o) {
      if (o.hidden == d.isDomainAlias) {
        return;
      }
      resetFrom(d.domainName);
      o.hidden = d.isDomainAlias;
    });

    select_from_generator.onAdd.listen((_) => sortSelectOptionsByValue(selectFrom));
    select_to_generator.onAdd.listen((_) => sortSelectOptionsByValue(selectTo));

    select_to_generator.dependsOn(mailDomainLibrary);

    selectFrom.onChange.listen((_) {

      resetTo(selectFrom.value);

      selectTo.children.forEach((OptionElement o) {
        var to = mailDomainLibrary[o.value], from = mailDomainLibrary[selectFrom.value];

        if (from == null || to == null) {
          o.hidden = false;
          return;
        }

        if (from == to) {
          resetTo(to.domainName);
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


  }

  void sortListFromDataSet(UListElement ul, String key) =>
  sortChildren(ul,
      (Element e1, Element e2) =>
  (e1.dataset.containsKey(key) ? e1.dataset[key] : "").compareTo(e2.dataset.containsKey(key) ? e2.dataset[key] : ""));


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


  }

  void blurElement(Element elm) {
    elm.classes.add('blur');
  }

  void unBlurElement(Element elm) {
    elm.classes.remove('blur');
  }

  void setUpMailDomainList() {


    var domainListGenerator = new ElementChildrenGenerator<MailDomain, LIElement>((MailDomain domain) {
      var li = new LIElement();
      var delete = new DivElement();
      li.text = domain.domainName;
      delete.classes.add('delete');
      li.append(delete);
      return li;
    }, mailDomainList, (LIElement v, _) => mailDomainLibrary[v.dataset['domain-name']]);

    domainListGenerator.addHandler((MailDomain domain, LIElement li) {
    var delete = li.querySelector('.delete');
    if(delete == null){
      return;
    }

    delete.onClick.listen((_) {
        var c = dialogContainer.password("Er du sikker på at du vil slette?");
        c.result.then((String s) {
          blurElement(mailDomainList);
          domain.delete(s).then((_) {
            unBlurElement(mailDomainList);
          });
        });
      });

    });

    domainListGenerator.addUpdater((MailDomain domain, LIElement li) {
      li
        ..dataset['last-modified'] = (domain.lastModified.millisecondsSinceEpoch ~/ 1000).toString()
        ..dataset['description'] = domain.description
        ..dataset['active'] = domain.active ? "true" : "false"
        ..dataset['domain-name'] = domain.domainName
        ..dataset['alias-target'] = domain.aliasTarget == null ? "" : domain.aliasTarget.domainName;
    });

    domainListGenerator.onEmpty.listen((_) => domainListGenerator.element.classes.add('empty'));
    domainListGenerator.onNotEmpty.listen((_) => domainListGenerator.element.classes.remove('empty'));

    domainListGenerator.onAdd.listen((_) => sortListFromDataSet(domainListGenerator.element, 'domain-name'));

    domainListGenerator.dependsOn(mailDomainLibrary);
  }


}
