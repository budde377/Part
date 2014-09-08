part of site_classes;


abstract class Site{

  Content operator[](String id);

}


class JSONSite implements Site{

  static final JSONSite _cache = new JSONSite._internal();

  Map<String,Content> _contentMap = new Map<String,Content>();

  factory JSONSite() => _cache;

  JSONSite._internal();

  Content operator[](String id) => _contentMap.putIfAbsent(id,()=>new JSONContent.site(id));
}


get site => new JSONSite();
