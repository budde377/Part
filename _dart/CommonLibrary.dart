library CommonLibrary;
import "dart:html";


class Animation {

  bool exactHasBeenSeen = false, run = false;
  double startTime, duration, currentTime;
  Function animationFunction;

  Animation(double duration, void animationFunction(double pct)) {
    this.animationFunction = animationFunction;
    this.duration = duration;
  }


  Animation start() {
    run = true;
    window.requestAnimationFrame((time) {
      startTime = time;
      _animate(time);});
    return this;
  }

  void _animate(double time){
    currentTime = time-startTime;
    if(currentTime <= duration && run){
      exactHasBeenSeen = currentTime == duration;
      animationFunction(currentTime/duration);
      window.requestAnimationFrame(_animate);
    } else {
      if(!exactHasBeenSeen){
        animationFunction(1);
      }
      stop();
    }

  }

  Animation stop(){
    run = false;
    return this;
  }


}