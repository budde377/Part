part of core;

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

  ImageSize.scaleMethodPreciseInnerBox(this.width, this.height, {bool dataURI : false}): this.scaleMethod = ImageSize.SCALE_METHOD_PRECISE_INNER_BOX, this.dataURI = dataURI;

  ImageSize.scaleMethodPreciseOuterBox(this.width, this.height, {bool dataURI : false}): this.scaleMethod = ImageSize.SCALE_METHOD_PRECISE_OUTER_BOX, this.dataURI = dataURI;

  ImageSize.scaleMethodLimitToOuterBox(this.width, this.height, {bool dataURI : false}): this.scaleMethod = ImageSize.SCALE_METHOD_LIMIT_TO_OUTER_BOX, this.dataURI = dataURI;

  ImageSize.scaleMethodExtendToOuterBox(this.width, this.height, {bool dataURI : false}): this.scaleMethod = ImageSize.SCALE_METHOD_EXTEND_TO_OUTER_BOX, this.dataURI = dataURI;

  ImageSize.scaleMethodLimitToInnerBox(this.width, this.height, {bool dataURI : false}): this.scaleMethod = ImageSize.SCALE_METHOD_LIMIT_TO_INNER_BOX, this.dataURI = dataURI;

  ImageSize.scaleMethodExtendToInnerBox(this.width, this.height, {bool dataURI : false}): this.scaleMethod = ImageSize.SCALE_METHOD_EXTEND_TO_INNER_BOX, this.dataURI = dataURI;

  ImageSize.scaleMethodExact(this.width, this.height, {bool dataURI : false}): this.scaleMethod = ImageSize.SCALE_METHOD_EXACT, this.dataURI = dataURI;

  ImageSize.scaleMethodExactWidth(this.width, {bool dataURI : false}): this.height = -1, this.scaleMethod = ImageSize.SCALE_METHOD_EXACT_WIDTH, this.dataURI = dataURI;

  ImageSize.scaleMethodExactHeight(this.height, {bool dataURI : false}): this.width = -1, this.scaleMethod = ImageSize.SCALE_METHOD_EXACT_HEIGHT, this.dataURI = dataURI;


  ImageSize(this.width, this.height, this.scaleMethod, {bool dataURI : false}) : this.dataURI = dataURI;

  Map<String, int> toJson() => {
      "height":height, "width":width, "scaleMethod" : scaleMethod, "dataURI":dataURI
  };

  String toFunctionString() => '["height" => $height, "width"=>$width, "scaleMethod" => $scaleMethod, "dataURI"=>$dataURI]';
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


  void uploadFile(FileProgress fileProgress, {void callback(String path):null, void progress(num pct):null});


  static const Pattern FILTER_IMAGE = "image/";

  static const Pattern FILTER_VIDEO = "video/";

}

class AJAXImageUploadStrategy extends UploadStrategy {


  Map<String, ImageSize> _sizes;

  ImageSize _size, _preview;

  AJAXImageUploadStrategy(ImageSize size, ImageSize preview) {
    _size = size;
    _preview = preview;
    filter = UploadStrategy.FILTER_IMAGE;

  }

  void uploadFile(FileProgress fileProgress, {void callback(String path):null, void progress(num pct):null}) {
    var reader = new FileReader();
    reader.onLoadEnd.listen((_) {
      fileProgress.previewPath = reader.result;
    });
    reader.readAsDataUrl(fileProgress.file);
    var form_data = new FormData();
    form_data.appendBlob('file', fileProgress.file, fileProgress.file.name);

    var sizesString = "[";
    sizesString += "'size'=>" + _size.toFunctionString();
    sizesString += ", ";
    sizesString += "'preview'=>" + _preview.toFunctionString();
    sizesString += "]";


    ajaxClient.callFunctionString("ImageFile.uploadFile(FILES['file'], $sizesString )", progress:progress, form_data:form_data).then((JSONResponse response) {
      progress(1);
      var c = (String path) {
        fileProgress.path = path;
        if (callback != null) {
          callback(path);
        }
      };
      if (response.type == Response.RESPONSE_TYPE_SUCCESS) {
        fileProgress.previewPath = response.payload['sizes']['preview'];
        c(response.payload['sizes']['size']);
      } else {
        c(null);
      }
    });
  }

}

class AJAXFileUploadStrategy extends UploadStrategy {


  AJAXFileUploadStrategy() ;

  void uploadFile(FileProgress fileProgress, {void callback(String path):null, void progress(num pct):null}) {
    var formData = new FormData();
    formData.appendBlob("file", fileProgress.file, fileProgress.file.name);
    ajaxClient.callFunctionString("File.uploadFile(FILES['file'])", progress:progress, form_data:formData).then((JSONResponse response) {
      if (progress != null) {
        progress(1);
      }
      var c = (String path) {
        fileProgress.path = path;
        if (callback != null) {
          callback(path);
        }
      };
      c(response.type == Response.RESPONSE_TYPE_SUCCESS ? response.payload : null);
    });
  }



}

class FileUploader {

  final UploadStrategy uploadStrategy;

  FileReader _reader = new FileReader();

  List<File> _queue = new List<File>();


  int _size = 0, _uploaded = 0, _currentlyUploading = 0;

  StreamController<FileProgress> _file_added_to_queue_controller = new StreamController<FileProgress>();

  StreamController<FileUploader> _upload_done_controller = new StreamController<FileUploader>(), _progress_controller = new StreamController<FileUploader>();
  Stream<FileProgress> _file_added_to_queue_stream;
  Stream<FileUploader> _progress_stream, _upload_done_stream;


  FileUploader.ajaxImages([ImageSize size = null, ImageSize preview = null]):this(new AJAXImageUploadStrategy(size, preview));

  FileUploader.ajaxFiles():this(new AJAXFileUploadStrategy());


  FileUploader(this.uploadStrategy) {

    _progress_stream = _progress_controller.stream.asBroadcastStream();
    _upload_done_stream = _upload_done_controller.stream.asBroadcastStream();
    _file_added_to_queue_stream = _file_added_to_queue_controller.stream.asBroadcastStream();

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

    var fp = new FileProgress(_queue.removeAt(0));

    uploadStrategy.uploadFile(fp, progress:(double pct) => fp.progress = 0.5 + pct / 2);
    _startUpload();
  }

  Stream<FileUploader> get onProgress => _progress_stream;

  Stream<FileUploader> get onUploadDone => _upload_done_stream;

  Stream<FileProgress> get onFileAddedToQueue => _file_added_to_queue_stream;

  void _notifyProgress() => _progress_controller.add(this);

  void _notifyUploadDone() => _upload_done_controller.add(this);

  void _notifyFileAddedToQueue(FileProgress progress) => _file_added_to_queue_controller.add(progress);

}