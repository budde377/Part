part of user_settings;


class UserSettingsExpandDecoration extends ExpandDecoration {
  bool _expanded = false;
  bool _hasBeenInitialized = false;


  static UserSettingsExpandDecoration _cache;

  factory UserSettingsExpandDecoration(){
    if (_cache == null) {
      _cache = new UserSettingsExpandDecoration._internal(querySelector('#UserSettingsContainer'));
    }
    return _cache;
  }


  UserSettingsExpandDecoration._internal(Element element):super(element){
    if(element.classes.contains('expanded')){
      _expanded = true;
    }
  }


  void initialize() {
    if (_hasBeenInitialized) {
      return;
    }
    _hasBeenInitialized = true;
    var lastBottom;
    var first_1 = true;
    expandAnimation = new core.Animation(new Duration(milliseconds:200), (double pct) {
      if (first_1) {
        lastBottom = _lastBottom();
        first_1 = false;
      }
      element.style.height = "${(lastBottom * pct).toInt()}px";
    }, (success) {
      first_1 = true;
      _expanded = true;
      element.style.height = "auto";
    });
    var first_2 = true;
    contractAnimation = new core.Animation(new Duration(milliseconds:150), (double pct) {
      if (_expanded) {
        _expanded = false;
      }
      if (first_2) {
        lastBottom = _lastBottom();
        first_2 = false;
      }
      element.style.height = "${(lastBottom * (1 - pct))}px";
    }, (success) {
      first_2 = true;
    });
  }

  bool get expanded => _expanded;

  int _lastBottom() {
    var lastBottom = 0;
    element.children.forEach((Element elem) {
      lastBottom = Math.max(lastBottom, elem.offsetTop + elem.offsetHeight);
    });

    lastBottom = Math.max(70, lastBottom);
    return lastBottom;
  }

}
