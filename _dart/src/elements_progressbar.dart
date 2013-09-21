part of elements;

//typedef void ProgressBarListener(int pct);

class ProgressBar {
  final DivElement bar = new DivElement(), indicator = new DivElement();

//  final List<ProgressBarListener> listeners = new List<ProgressBarListener>();

  double _percentage = -1.0;

  bool _showInfoBox = false;

  String _percentageString = "0%";

  final InfoBox infoBox = new InfoBox("0%");

  ProgressBar() {
    bar.classes.add('progress_bar');
    indicator.classes.add('indicator');
    bar.append(indicator);
    percentage = 0.0;
  }

  bool get showInfoBox => _showInfoBox;

  set showInfoBox(bool b) {
    if (b == _showInfoBox) {
      return;
    }
    _showInfoBox = b;
    if (_showInfoBox) {
      _showInfo();
    } else {
      infoBox.remove();
    }
  }

  set percentage(double pct) {
    if (pct == _percentage) {
      return;
    }
    pct = pct.isNaN?0:pct;
    _percentage = Math.max(0, Math.min(1, pct));
    _percentageString = "${( _percentage * 100).toInt()}%";
    indicator.style.width = _percentageString;
    if (_showInfoBox) {
      _showInfo();
    }


  }

  void _showInfo() {
    infoBox.infoHtml =_percentageString;
    infoBox.showAboveRightOfElement(indicator);
  }

  double get percentage => Math.max(_percentage, 0);

}


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
