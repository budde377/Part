part of elements;

class FileDropAreaHandler {

  static final Map<Element, FileDropAreaHandler> _cache = new Map<Element, FileDropAreaHandler>();

  final Element element;

  final Element area = new DivElement();

  core.FileUploader _uploader;

  bool multiple = false;

  factory FileDropAreaHandler.ajaxImages(Element element, [core.ImageSize size = null, core.ImageSize preview = null]) => _cache.putIfAbsent(element, () => new FileDropAreaHandler._internal(element, new core.FileUploader.ajaxImages(size, preview)));

  factory FileDropAreaHandler.ajaxFiles(Element element) => _cache.putIfAbsent(element, () => new FileDropAreaHandler._internal(element, new core.FileUploader.ajaxFiles()));

  factory FileDropAreaHandler(Element element, core.FileUploader uploader) => _cache.putIfAbsent(element, () => new FileDropAreaHandler._internal(element, uploader));

  FileDropAreaHandler._internal(this.element, core.FileUploader uploader){
    element.classes.add('drop_area');
    area.classes.add('drop_goal');
    element.append(area);
    this.uploader = uploader;

    _setUpListeners();
  }

  void _setUpListeners() {
    element.onDragEnter.listen((MouseEvent ev) {
      area.classes.add('active');
    });
    area.onDragLeave.listen((MouseEvent ev) {
      area.classes.remove('active');
    });

    area.onDragOver.listen((MouseEvent ev) => ev.preventDefault());

    area.onDrop.listen((MouseEvent ev) {
      ev.preventDefault();
      var files = ev.dataTransfer.files;
      if (files.length == 0) {
        area.classes.remove('active');
        return;
      }
      files = multiple ? files : files.getRange(0, 1);
      _uploader.uploadFiles(files);
    });
  }

  core.FileUploader get uploader => _uploader;

  set uploader(core.FileUploader uploader) {
    _uploader = uploader;
    _uploader.onFileAddedToQueue.listen((core.FileProgress progress) {
      progress.onPathAvailable.listen((_) {
        area.classes.remove('active');
      });
    });
  }

}



