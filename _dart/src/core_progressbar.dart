part of core;

//typedef void ProgressBarListener(int pct);

class ProgressBar{
  final DivElement bar = new DivElement(), indicator = new DivElement();
//  final List<ProgressBarListener> listeners = new List<ProgressBarListener>();

  double _percentage = -1;

  ProgressBar(){
    bar.classes.add('progressBar');
    indicator.classes.add('indicator');
    bar.append(indicator);
  }

  set percentage(double pct) {
    if(pct == _percentage){
      return;
    }
    _percentage = Math.max(0,Math.min(1,pct));
    indicator.style.width = "${(_percentage*100).toInt()}%";
  }

  double get percentage => Math.max(_percentage,0);

}
