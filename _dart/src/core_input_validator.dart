part of core;

bool checkValidMail(String string) => new RegExp(r'^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$',caseSensitive:false).hasMatch(string);
bool checkNonEmpty(String string) => string.trim().length >0;


class Validator<E extends Element> {
  final E _element;
  String errorMessage = "";
  static final Map<Element, Validator> _cache = new Map<Element, Validator>();
  Function _validator = (e){return true;};



  factory Validator(E element){
    if (_cache.containsKey(element)) {
      return _cache[element];
    } else {
      var elem = new Validator._internal(element);
      _cache[element] = elem;
      return elem;
    }

  }

  Validator._internal(this._element);

  Element get element => _element;

  bool get valid =>_validator(_element);

  void set validator(bool f(E element)) {
    _validator = f;
  }
}