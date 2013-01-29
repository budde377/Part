part of core;

class Animation {

  bool exactHasBeenSeen = false, run = false;
  double startTime, duration, currentTime;
  Function animationFunction, callbackFunction;

  Animation(double duration, void animationFunction(double pct), [void callback(bool success)]) {
    this.animationFunction = animationFunction;
    this.duration = duration;
    this.callbackFunction = callback;
  }


  Animation start() {
    run = true;
    window.requestAnimationFrame((time) {
      startTime = time;
      _animate(time);});
    return this;
  }

  void _animate(double time) {
    currentTime = time - startTime;
    if (currentTime <= duration && run) {
      exactHasBeenSeen = currentTime == duration;
      animationFunction(currentTime / duration);
      window.requestAnimationFrame(_animate);
    } else {
      if (!exactHasBeenSeen && run) {
        animationFunction(1);
      }
      if (callbackFunction != null) {
        callbackFunction(run);
      }
      stop();
    }

  }

  Animation stop() {
    run = false;
    return this;
  }


}
