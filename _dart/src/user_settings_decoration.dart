part of user_settings;

void expandSettingsOnNextPage(){
  var e = new UserSettingsExpandDecoration();
  if(!e.expanded){
    return;
  }
  var s = new UserSettingsSlideDecoration();
  new UserSettingsActiveForNextPage().nextActive = s.currentIndex;
}


class UserSettingsSlideDecoration extends SlideDecoration {

  int _currentIndex = 0;

  int _currentPosition = 0;

  Animation currentAnimation;
  static UserSettingsSlideDecoration _cache;

  factory UserSettingsSlideDecoration(){
    if (_cache == null) {
      var lis = queryAll('#UserSettingsMenu > ul > li');
      var startIndex = 0, ii = 0;
      lis.forEach((LIElement li) {
        if (li.classes.contains('active')) {
          startIndex = ii;
        }
        ii++;
      });
      _cache = new UserSettingsSlideDecoration._internal(query("#UserSettingsContent > ul"), lis.length, startIndex);
    }
    return _cache;
  }

  UserSettingsSlideDecoration._internal(Element elementToSlide, int numIndex, [int startIndex = 0]):super(numIndex, elementToSlide){
    if (startIndex <= 0 || startIndex >= numIndex) {
      return;
    }
    _currentIndex = startIndex;
    _currentPosition = -880 * startIndex;
  }


  void goToIndex(int index) {
    var newPos = -880 * index;
    if (currentAnimation != null) {
      currentAnimation.stop();
    }
    var c = _currentPosition, ci = currentIndex;
    currentAnimation = new Animation(new Duration(milliseconds:150 * (ci - index).abs()), (pct) {
      element.style.marginLeft = "${c + (newPos - c) * pct}px";
    }, (success) {
      if (!success) {
        element.style.marginLeft = "${newPos}px";
      }
    }).start();
    _currentPosition = newPos;
    _currentIndex = index;
  }

  int get currentIndex => _currentIndex;


}

class UserSettingsExpandDecoration extends ExpandDecoration {
  bool _expanded = false;
  bool _hasBeenInitialized = false;


  static UserSettingsExpandDecoration _cache;

  factory UserSettingsExpandDecoration(){
    if (_cache == null) {
      _cache = new UserSettingsExpandDecoration._internal(query('#UserSettingsContainer'));
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
    expandAnimation = new Animation(new Duration(milliseconds:200), (double pct) {
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
    contractAnimation = new Animation(new Duration(milliseconds:150), (double pct) {
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

class UserSettingsExpandLinkExpandDecoration extends ExpandDecoration {
  bool _expanded = false;

  bool _hasBeenInitialized = false;

  UserSettingsExpandLinkExpandDecoration(Element linkElement) : super(linkElement);

  void initialize() {
    if (_hasBeenInitialized) {
      return;
    }
    _hasBeenInitialized = true;
    expandAnimation = new Animation(new Duration(milliseconds:100), (double pct) {
      element.style.height = "${(10 * pct + 60).toInt()}px";
    });
    contractAnimation = new Animation(new Duration(milliseconds:200), (double pct) {
      element.style.height = "${(70 - 10 * pct).toInt()}px";
    });
  }

  bool get expanded => _expanded;

}

class UserSettingsActiveForNextPage {
  int _nextActive = -1;

  static UserSettingsActiveForNextPage _cached;

  factory UserSettingsActiveForNextPage(){
    if (_cached == null) {
      _cached = new UserSettingsActiveForNextPage._internal();
    }
    return _cached;
  }

  UserSettingsActiveForNextPage._internal();

  int get nextActive => _nextActive;

  set nextActive(int i) {
    if (i == _nextActive || i < 0) {
      return;
    }
    _nextActive = i;
    document.cookie = "expandSetting=;expires=Thu, 01 Jan 1970 00:00:01 GMT;path=/;";
    document.cookie = "expandSetting=$i;path=/;";

  }

}