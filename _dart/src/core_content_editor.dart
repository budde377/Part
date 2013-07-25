part of core;

abstract class SaveStrategy {
  void save();
}

class JSONSaveStrategy implements SaveStrategy{
  final String ajax_id;
  final Function _callback;
  final SiteClasses.User _currentUser;

  JSON.JSONClient _jsonClient = new JSON.AJAXJSONClient(this.ajax_id, this._currentUser);

  JSONSaveStrategy(this.ajax_id, void callback(int error_code)): _callback = callback;

  void save(ContentEditor editor){
    var function = new JSON.SaveContentChangesJSONFunction(editor.id, editor.element.innerHtml, _currentUser.username);
    _jsonClient.callFunction(function, (JSON.JSONResponse response){
      _callback(response.type = JSON.RESPONSE_TYPE_SUCCESS?0:response.error_code);
    });

  }
}


class EditorCommandExecutor{
  static final Map<Element, EditorCommandExecutor> _cache = new Map<Element, EditorCommandExecutor>();
  final Element element;
  Function _listenerChain = (){};
  bool _inElement = false;

  factory EditorCommandExecutor(Element element) => _cache.putIfAbsent(element, ()=>new EditorCommandExecutor._internal(element));
  EditorCommandExecutor._internal(this.element){
    element.contentEditable="true";
    element.document.onSelectionChange.listen((e){
      _inElement = element.contains(window.getSelection().baseNode);
      _listenerChain();
    });
  }

  void _execCommand(String command, {bool userinterface:false, String value:""}){
    element.document.execCommand(command, userinterface, value);
  }

  void toggleBold()=>_execCommand("bold");
  void toggleItalic()=>_execCommand("italic");
  void toggleUnderline()=>_execCommand("underline");
  void toggleStrikethrough()=>_execCommand("strikethrough");
  void toggleSubscript()=>_execCommand("subscript");
  void toggleSuperscript()=>_execCommand("superscript");
  void removeFormat()=>_execCommand("removeformat");
  void toggleOrderedList()=>_execCommand("insertorderedlist");
  void toggleUnorderedList()=>_execCommand("insertunorderedlist");
  void justifyCenter()=>_execCommand("justifycenter");
  void justifyLeft()=>_execCommand("justifyleft");
  void justifyRight()=>_execCommand("justifyright");
  void justifyFull()=>_execCommand("justifyfull");
  void indent()=>_execCommand("indent");
  void outdent()=>_execCommand("outdent");
  void _formatBlock(String tagName)=>_execCommand('formatBlock', value:tagName);
  void formatBlockP() => _formatBlock('p');
  void formatBlockH1() => _formatBlock('h1');
  void formatBlockH2() => _formatBlock('h2');
  void formatBlockH3() => _formatBlock('h3');
  void formatBlockBlockquote() => _formatBlock('blockquote');
  void formatBlockPre() => _formatBlock('pre');

  bool _commandState(String command) => _inElement && element.document.queryCommandState(command);
  bool _commandValue(String command, String value) => _inElement && element.document.queryCommandValue(command ) == value;

  bool boldState() => _commandState("bold");
  bool italicState() => _commandState("italic");
  bool underlineState() => _commandState("underline");
  bool unorderedListState() => _commandState("insertunorderedlist");
  bool orderedListState() => _commandState("insertorderedlist");
  void blockPState() => _commandValue('formatBlock', 'p');
  void blockH1State() => _commandValue('formatBlock', 'h1');
  void blockH2State() => _commandValue('formatBlock', 'h2');
  void blockH3State() => _commandValue('formatBlock', 'h3');
  void blockBlockquoteState() => _commandValue('formatBlock', 'blockquote');
  void blockPreState() => _commandValue('formatBlock', 'pre');

  String get blockState => element.document.queryCommandValue("formatBlock");

  void listenQueryCommandStateChange(void listener()){
    var l = _listenerChain;
    _listenerChain = (){l(); listener();};
  }

}

class EditorAction{

  EditorAction(this.element, this.onClickAction, this.selectionStateChanger);

  EditorAction.elementFromHtmlString(String html, this.onClickAction, this.selectionStateChanger) : element = new Element.html(html);

  EditorAction.liElementWithInnerHtml(String innerHtml, this.onClickAction, this.selectionStateChanger) : element = new LIElement(){
    element.innerHtml = innerHtml;
  }

  final Element element;
  final Function onClickAction, selectionStateChanger;
}

class ContentEditor{
  final Element element;
  static final Map<Element, ContentEditor> _cache = new Map<Element, ContentEditor>();

  DivElement _wrapper, _topBar;
  EditorCommandExecutor _executor;


  factory ContentEditor(Element element) => _cache.putIfAbsent(element,()=>new ContentEditor._internal(element));

  ContentEditor._internal(this.element){
    _executor = new EditorCommandExecutor(element);
    _wrapper = new DivElement();
    _wrapper.append(_topBar = _generateToolBar());

    _wrapper.classes.add('editContentWrapper');
    element.insertAdjacentElement("afterEnd",_wrapper);
    _wrapper.append(element);
  }

  void open(){

  }
  void close(){

  }

  void save(SaveStrategy saveStrategy) => saveStrategy.save(this);


  LIElement _wrapHtmlStringWithLI(String s){
    var li = new LIElement();
    li.innerHtml = s;
    return li;
  }

  DivElement _generateToolBar(){
    var bar = new DivElement(),
        textEdit = new ButtonElement(),
        addImage = new ButtonElement(),
        addFile = new ButtonElement(),
        history = new ButtonElement(),
        save = new ButtonElement(),
        wrapper = new DivElement(),
        secondMenu = new DivElement();

    textEdit..classes.add('text')
            ..title = "Formater tekst"
            ..onClick.listen((MouseEvent e){
      textEdit.classes.toggle('active');
      secondMenu.hidden = !textEdit.classes.contains('active');
    });

    addImage..classes.add('image')
            ..title = "Indsæt billede";

    addFile..classes.add('file')
           ..title = "Indsæt fil";

    history..classes.add('history')
           ..title = "Se historik";

    save..classes.add('save')
        ..title = "Gem ændringer";

    bar.classes.add('toolBar');

    bar..append(textEdit)
       ..append(addImage)
       ..append(addFile)
       ..append(history)
       ..append(save);


    secondMenu.classes.add('menu');
    secondMenu.hidden = true;

    var actions = [
        new EditorAction.liElementWithInnerHtml("<h2>Overskift</h2>",()=>_executor.formatBlockH2(), (String s)=>s == "h2"),
        new EditorAction.liElementWithInnerHtml("<h3>Underoverskrift</h3>",()=>_executor.formatBlockH3(), (String s)=>s == "h3"),
        new EditorAction.liElementWithInnerHtml("<p>Normal tekst</p>",()=>_executor.formatBlockP(), (String s)=>s == "p"),
        new EditorAction.liElementWithInnerHtml("<blockquote>Citat</blockquote>",()=>_executor.formatBlockBlockquote(), (String s)=>s == "blockquote"),
        new EditorAction.liElementWithInnerHtml("<pre>Kode</pre>",()=>_executor.formatBlockPre(), (String s)=>s == "pre")];

    var textType = new DropDown.fromLIList(actions.map((EditorAction a)=>a.element).toList());
    textType.preventDefaultOnClick = true;

    actions.forEach((EditorAction a){
      if(a.onClickAction != null){
        a.element.onMouseDown.listen((_){
          a.onClickAction();
          textType.close();
        });
      }
    });

    _executor.listenQueryCommandStateChange((){
      var state = _executor.blockState;
      var action = actions.firstWhere((EditorAction a)=> a.selectionStateChanger(state), orElse:() => null);
      textType.text = action == null? textType.text: action.element.text;
    });
    textType.element.classes.add('text_type');
    textType.text = "Normal skrift";
    secondMenu.append(textType.element);

    var textSize = new DropDown.fromLIList(["<font size='1'>Lille</font>","Normal", "<font size='4'>Stor</font>", "<font size='6'>Størst</font>"].map(_wrapHtmlStringWithLI).toList());
    secondMenu.append(textSize.element);
    textSize.text = "Normal";


    var textIconMap = {"bold":{
                            "title":"Fed skrift",
                            "selChange":()=>_executor.boldState(),
                            "func":()=>_executor.toggleBold()},
                       "italic":{"title":"Kursiv skrift", "selChange":()=>_executor.italicState(), "func":()=>_executor.toggleItalic()},
                       "underline":{"title":"Understreget skrift", "selChange":()=>_executor.underlineState(), "func":()=>_executor.toggleUnderline()},
                       "color":{"title":"Farve", "selChange":null, "func":(){}},
                       "u_list":{"title":"Uordnet liste", "selChange":()=>_executor.unorderedListState(), "func":()=>_executor.toggleUnorderedList()},
                       "o_list":{"title":"Ordnet liste", "selChange":()=>_executor.orderedListState(), "func":()=>_executor.toggleOrderedList()},
                       "a_left":{"title":"Juster venstre", "selChange":null, "func":()=>_executor.justifyLeft()},
                       "a_center":{"title":"Juster centreret", "selChange":null, "func":()=>_executor.justifyCenter()},
                       "a_right":{"title":"Juster højre", "selChange":null, "func":()=>_executor.justifyRight()},
                       "a_just":{"title":"Juster lige", "selChange":null, "func":()=>_executor.justifyFull()},
                       "p_indent":{"title":"Indryk mere", "selChange":null, "func":()=>_executor.indent()},
                       "m_indent":{"title":"Indryk mindre", "selChange":null, "func":()=>_executor.outdent()},
                       "no_format":{"title":"Fjern formatering", "selChange":null, "func":()=>_executor.removeFormat()}
                      };

    textIconMap.forEach((String k, Map<String, dynamic> v){
      var divider = new DivElement();
      divider.classes.add('divider');

      secondMenu.append(divider);

      var b = new ButtonElement();
      var i;
      b..classes.add(k)
         ..onMouseOver.listen((MouseEvent e) => i = addInfoBoxToElement(b, v['title'], InfoBox.COLOR_BLACK))
         ..onMouseOut.listen((MouseEvent e)=> i.remove());
      b.onClick.listen((e)=>v["func"]());
      if(v['selChange'] != null){
        _executor.listenQueryCommandStateChange(()=>v['selChange']()?b.classes.add('active'):b.classes.remove('active'));
      }
      secondMenu.append(b);

    });


    wrapper.append(bar);
    wrapper.append(secondMenu);

    wrapper.classes.add('toolBarWrapper');

    return wrapper;
  }

  String get id => element.id;

}



