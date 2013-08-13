part of core;


class ScrollBarsDecoration {

  final Element element, wrapper = new DivElement(), handle = new DivElement();

  ScrollBarsDecoration(this.element){
    element..insertAdjacentElement("afterEnd",wrapper);

    wrapper..append(element)
           ..append(handle)
           ..classes.add('scroll_bars');

    handle.classes.add("handle");


  }

}