part of core;

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
