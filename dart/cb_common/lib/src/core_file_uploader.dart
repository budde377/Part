part of core;

/*
class ImageSizes {

  final int maxHeight, maxWidth, minWidth, minHeight;

  final bool dataURI;

  ImageSizes.atLeast(this.minWidth, this.minHeight, {bool dataURI:false}): maxWidth = -1, maxHeight = -1, this.dataURI = dataURI;

  ImageSizes.atMost(this.maxWidth, this.maxHeight, {bool dataURI:false}): minWidth =-1, minHeight = -1, this.dataURI = dataURI;

  ImageSizes.exactHeight(int height, {bool dataURI:false}): maxHeight = height, minHeight = height, maxWidth = -1, minWidth = -1, this.dataURI = dataURI;

  ImageSizes.exactWidth(int width, {bool dataURI:false}) : maxWidth = width, minWidth = width, maxHeight = -1, minHeight = -1, this.dataURI = dataURI;

  ImageSizes.exact(int width, int height, {bool dataURI:false}) : maxWidth = width, minWidth = width, maxHeight = height, minHeight = height, this.dataURI = dataURI;

  Map<String, int> toJson() => {
      "maxHeight":maxHeight, "minHeight":minHeight, "maxWidth":maxWidth, "minWidth":minWidth, "dataURI":dataURI
  };
}*/
class ImageSize {

  static const SCALE_METHOD_EXACT = 0;
  static const SCALE_METHOD_EXACT_WIDTH = 1;
  static const SCALE_METHOD_EXACT_HEIGHT = 2;
  static const SCALE_METHOD_PRECISE_INNER_BOX = 3;
  static const SCALE_METHOD_PRECISE_OUTER_BOX = 4;
  static const SCALE_METHOD_LIMIT_TO_INNER_BOX = 5;
  static const SCALE_METHOD_EXTEND_TO_INNER_BOX = 6;
  static const SCALE_METHOD_LIMIT_TO_OUTER_BOX = 7;
  static const SCALE_METHOD_EXTEND_TO_OUTER_BOX = 8;

  final int scaleMethod;

  final int height, width;

  final bool dataURI;

  ImageSize.scaleMethodPreciseInnerBox(this.width, this.height, {bool dataURI : false}): this.scaleMethod = ImageSize.SCALE_METHOD_PRECISE_INNER_BOX;

  ImageSize.scaleMethodPreciseOuterBox(this.width, this.height, {bool dataURI : false}): this.scaleMethod = ImageSize.SCALE_METHOD_PRECISE_OUTER_BOX;

  ImageSize.scaleMethodLimitToOuterBox(this.width, this.height, {bool dataURI : false}): this.scaleMethod = ImageSize.SCALE_METHOD_LIMIT_TO_OUTER_BOX;

  ImageSize.scaleMethodExtendToOuterBox(this.width, this.height, {bool dataURI : false}): this.scaleMethod = ImageSize.SCALE_METHOD_EXTEND_TO_OUTER_BOX;

  ImageSize.scaleMethodLimitToInnerBox(this.width, this.height, {bool dataURI : false}): this.scaleMethod = ImageSize.SCALE_METHOD_LIMIT_TO_INNER_BOX;

  ImageSize.scaleMethodExtendToInnerBox(this.width, this.height, {bool dataURI : false}): this.scaleMethod = ImageSize.SCALE_METHOD_EXTEND_TO_INNER_BOX;

  ImageSize.scaleMethodExact(this.width, this.height, {bool dataURI : false}): this.scaleMethod = ImageSize.SCALE_METHOD_EXACT;

  ImageSize.scaleMethodExactWidth(this.width, {bool dataURI : false}): this.height = -1, this.scaleMethod = ImageSize.SCALE_METHOD_EXACT_WIDTH;

  ImageSize.scaleMethodExactHeight(this.height, {bool dataURI : false}): this.width = -1, this.scaleMethod = ImageSize.SCALE_METHOD_EXACT_HEIGHT;


  ImageSize(this.width, this.height, this.scaleMethod, {bool dataURI : false}) : this.dataURI = dataURI;

  Map<String, int> toJson() => {
      "height":height, "width":width, "scaleMethod" : scaleMethod, "dataURI":dataURI
  };
}


class FileProgress {
  static Map<File, FileProgress> _cache = new Map<File, FileProgress>();

  final File file;

  double _progress = 0.0;

  String _path, _previewPath;

  StreamController<FileProgress> _progress_controller = new StreamController<FileProgress>(), _path_available_controller = new StreamController<FileProgress>(), _prev_path_available_controller = new StreamController<FileProgress>();

  Stream<FileProgress> _progress_stream, _path_available_stream, _prev_path_available_stream;

  factory FileProgress(File file) => _cache.putIfAbsent(file, () => new FileProgress._internal(file));

  FileProgress._internal(this.file);

  String get path => _path;

  set path(String p) {
    if (_path != null) {
      return;
    }
    progress = 1.0;
    _path = p;
    _notifyPath();
  }

  String get previewPath => _previewPath;

  set previewPath(String p) {
    _previewPath = p;
    _notifyPreviewPath();
  }

  double get progress => _progress;

  set progress(double progress) {
    if (_progress == progress) {
      return;
    }
    _progress = Math.max(0, Math.min(1, progress));
    _notifyProgress();
  }


  Stream<FileProgress> get onProgress => _progress_stream == null ? _progress_stream = _progress_controller.stream.asBroadcastStream() : _progress_stream;

  Stream<FileProgress> get onPathAvailable => _path_available_stream == null ? _path_available_stream = _path_available_controller.stream.asBroadcastStream() : _path_available_stream;

  Stream<FileProgress> get onPreviewPathAvailable => _prev_path_available_stream == null ? _prev_path_available_stream = _prev_path_available_controller.stream.asBroadcastStream() : _prev_path_available_stream;

  void _notifyProgress() => _progress_controller.add(this);

  void _notifyPath() => _path_available_controller.add(this);

  void _notifyPreviewPath() => _prev_path_available_controller.add(this);


}

abstract class UploadStrategy {
  Pattern filter;

  void upload(FileProgress fileProgress, String data, {void callback(String path):null, void progress(double pct):null});

  void read(FileReader reader, File file);

  static const Pattern FILTER_IMAGE = "image/";

  static const Pattern FILTER_VIDEO = "video/";

}

class AJAXImageURIUploadStrategy extends UploadStrategy {

  JSONClient _client;

  List<ImageSize> _sizes;

  ImageSize _size, _preview;

  AJAXImageURIUploadStrategy([ImageSize size = null, ImageSize preview = null]) {
    _size = size;
    _preview = preview;
    _sizes = [size, preview];
    _sizes.removeWhere((ImageSize s) => s == null);
    filter = UploadStrategy.FILTER_IMAGE;
    _client = new AJAXJSONClient();

  }

  void upload(FileProgress fileProgress, String data, {void callback(String path):null, void progress(num pct):null}) {
    fileProgress.previewPath = data;
    if (progress == null) {
      progress = (_) {
      };
    }
    _client.callFunction(new UploadImageURIJSONFunction(fileProgress.file.name, data, _sizes), progress).then((JSONResponse response) {
      progress(1);
      var c = (String path) {
        fileProgress.path = path;
        if (callback != null) {
          callback(path);
        }
      };
      if (response.type == JSONResponse.RESPONSE_TYPE_SUCCESS) {
        c(_size == null ? response.payload['path'] : response.payload['thumbs'][0]);
        if (_preview != null) {
          fileProgress.previewPath = response.payload['thumbs'][1];
        }
      } else {
        c(null);
      }
    });
  }

  void read(FileReader reader, File file) => reader.readAsDataUrl(file);

}

class AJAXFileURIUploadStrategy extends UploadStrategy {

  JSONClient _client;

  AJAXFileURIUploadStrategy() {
    _client = new AJAXJSONClient();

  }

  void upload(FileProgress fileProgress, String data, {void callback(String path):null, void progress(num pct):null}) {
    _client.callFunction(new UploadFileURIJSONFunction(fileProgress.file.name, data)).then((JSONResponse response) {
      if (progress != null) {
        progress(1);
      }
      var c = (String path) {
        fileProgress.path = path;
        if (callback != null) {
          callback(path);
        }
      };
      c(response.type == JSONResponse.RESPONSE_TYPE_SUCCESS ? response.payload['path'] : null);
    });
  }

  void read(FileReader reader, File file) => reader.readAsDataUrl(file);

}

class FileUploader {

  final UploadStrategy uploadStrategy;

  FileReader _reader = new FileReader();

  List<File> _queue = new List<File>();

  File _currentFile;

  FileProgress _currentFileProcess;

  int _size = 0, _uploaded = 0, _currentlyUploading = 0;

  StreamController<FileProgress> _file_added_to_queue_controller = new StreamController<FileProgress>();

  StreamController<FileUploader> _upload_done_controller = new StreamController<FileUploader>(), _progress_controller = new StreamController<FileUploader>();
  Stream<FileProgress> _file_added_to_queue_stream;
  Stream<FileUploader> _progress_stream, _upload_done_stream;


  FileUploader.ajaxImages([ImageSize size = null, ImageSize preview = null]):this(new AJAXImageURIUploadStrategy(size, preview));

  FileUploader.ajaxFiles():this(new AJAXFileURIUploadStrategy());


  FileUploader(this.uploadStrategy) {

    _progress_stream = _progress_controller.stream.asBroadcastStream();
    _upload_done_stream = _upload_done_controller.stream.asBroadcastStream();
    _file_added_to_queue_stream = _file_added_to_queue_controller.stream.asBroadcastStream();

    _reader.onProgress.listen((ProgressEvent pe) => _currentFileProcess.progress = pe.loaded / (pe.total * 2));
    _reader.onLoadEnd.listen((_) {
      var fp = _currentFileProcess;
      uploadStrategy.upload(_currentFileProcess, _reader.result, progress:(double pct) => fp.progress = 0.5 + pct / 2);
      _startUpload();
    });
  }

  double get progress => (_uploaded + _currentlyUploading) / _size;

  void uploadFiles(List<File> files) {
    files = files.toList();
    var s = _queue.length;
    if (uploadStrategy.filter != null) {
      files.removeWhere((File f) => !f.type.startsWith(uploadStrategy.filter));
    }
    files.forEach((File f) {
      _size += f.size;
      var fp = new FileProgress(f);
      fp.onProgress.listen((_) {
        var i = fp.progress * f.size;
        _currentlyUploading = i.isNaN || i.isInfinite ? 0 : i.toInt();
        _notifyProgress();
      });
      fp.onPathAvailable.listen((_) {
        _currentlyUploading = 0;
        _uploaded += f.size;
        _notifyProgress();
      });
      _notifyFileAddedToQueue(fp);
    });
    _queue.addAll(files);
    if (s != 0) {
      return;
    }
    _uploaded = 0;
    _startUpload();
  }

  void _startUpload() {
    if (_queue.length == 0) {
      _notifyUploadDone();
      return;
    }
    _currentFile = _queue.removeAt(0);
    _currentFileProcess = new FileProgress(_currentFile);
    uploadStrategy.read(_reader, _currentFile);

  }

  Stream<FileUploader> get onProgress => _progress_stream;

  Stream<FileUploader> get onUploadDone => _upload_done_stream;

  Stream<FileProgress> get onFileAddedToQueue => _file_added_to_queue_stream;

  void _notifyProgress() => _progress_controller.add(this);

  void _notifyUploadDone() => _upload_done_controller.add(this);

  void _notifyFileAddedToQueue(FileProgress progress) => _file_added_to_queue_controller.add(progress);

}