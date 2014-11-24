part of site_classes;


abstract class Site{

  Content operator[](String id);

}


class AJAXSite implements Site{

  static final AJAXSite _cache = new AJAXSite._internal();

  Map<String,Content> _contentMap = new Map<String,Content>();

  factory AJAXSite() => _cache;

  AJAXSite._internal();

  Content operator[](String id) => _contentMap.putIfAbsent(id,()=>new AJAXContent.site(id));
}


get site => new AJAXSite();
