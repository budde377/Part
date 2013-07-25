part of core;

class SavingBar{
  static final SavingBar _cached = new SavingBar._internal();
  factory SavingBar() => _cached;
  final ProgressBar _progressBar = new ProgressBar();
  final DivElement _statusBar = new DivElement(), _text = new DivElement();
  final BodyElement _body = query('body');
  int _jobId = 0;
  final List<int> _runningJobs = new List<int>();
  final List<int> _endedJobs = new List<int>();


  SavingBar._internal(){
    _statusBar.id = "StatusBar";
    _statusBar.append(_progressBar.bar);
    _text.classes.add('text');
    _statusBar.append(_text);
  }

  int startJob(){
    _jobId++;
    _runningJobs.add(_jobId);
    _updateStatusBar();
    return _jobId;
  }

  void endJob(int jobId){
    if(!_runningJobs.contains(jobId)){
      return;
    }
    _runningJobs.remove(jobId);
    _endedJobs.add(jobId);
    _updateStatusBar();
  }

  void _updateStatusBar(){
    if(_runningJobs.length == 0){
      _endedJobs.clear();
      new Timer(new Duration(milliseconds:500),(){
        if(_runningJobs.length == 0){
          _statusBar.remove();
        }
      });
      _updatePercentage(1.toDouble());
      return;
    }
    if(_endedJobs.length == 0 && _runningJobs.length == 1){
      _body.append(_statusBar);
    }
    var pct = (_endedJobs.length/(_runningJobs.length+_endedJobs.length));
    _updatePercentage(pct);
  }

  void _updatePercentage(double pct){
    _text.text = "Gemmer ændringer (${(pct*100).toInt()}%)";
    _progressBar.percentage = pct;
  }
}
