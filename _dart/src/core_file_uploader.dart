part of core;

class ImageSize{
  final int maxHeight, maxWidth, minWidth, minHeight;
  ImageSize.atLeast(this.minWidth, this.minHeight): maxWidth = -1, maxHeight = -1;
  ImageSize.atMost(this.maxWidth, this.maxHeight): minWidth =-1,  minHeight = -1;
  ImageSize.exactHeight(int height): maxHeight = height, minHeight = height, maxWidth = -1, minWidth = -1;
  ImageSize.exactWidth(int width) : maxWidth = width, minWidth = width, maxHeight = minHeight = -1;
  ImageSize.exact(int width, int height) : maxWidth = width, minWidth = width, maxHeight = height, minHeight = height;
  Map<String, int> toJson() => {"maxHeight":maxHeight, "minHeight":minHeight, "maxWidth":maxWidth, "minWidth":minWidth};
}

class ListenerRegister {
  Map<String, Function> _listeners = new Map<String, Function>();

  void registerListener(String action, Function func) {
    var f = _listeners.putIfAbsent(action, () => (_) {
    });
    _listeners[action] = (List arguments) {
      f(arguments);
      Function.apply(func, arguments);
    };
  }

  void callListeners(String action, [List arguments = null]) => (_listeners.putIfAbsent(action, () => (_) {
  }))(arguments == null ? [] : arguments);

}


class FileProgress {
  static Map<File, FileProgress> _cache = new Map<File, FileProgress>();

  final File file;

  double _progress = 0;

  String _path, _previewPath;

  ListenerRegister _listeners = new ListenerRegister();

  factory FileProgress(File file) => _cache.putIfAbsent(file, () => new FileProgress._internal(file));

  FileProgress._internal(this.file);

  String get path => _path;

         set path(String p){
           if(_path != null){
             return;
           }
           progress = 1;
           _path = p;
           _notifyPath();
         }

  String get previewPath => _previewPath;

         set previewPath(String p){
           if(_previewPath != null){
             return;
           }
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


  void listenOnPreviewPathAvailable(void callback()) => _listeners.registerListener('preview', callback);
  void listenOnProgress(void callback()) => _listeners.registerListener('progress', callback);
  void listenOnPathAvailable(void callback()) => _listeners.registerListener('uploaded', callback);

  void _notifyProgress() => _listeners.callListeners('progress');
  void _notifyPath() => _listeners.callListeners('uploaded');
  void _notifyPreviewPath() => _listeners.callListeners('preview');



}

abstract class UploadStrategy{
  Pattern filter;
  void upload(FileProgress fileProgress, String data, {void callback(String path):null, void progress(double pct):null});

  const Pattern FILTER_IMAGE = "image/";
  const Pattern FILTER_VIDEO = "video/";

}

class AJAXImageURIUploadStrategy extends UploadStrategy{
  final String ajax_id;
  JSON.JSONClient _client;
  List<ImageSize> _sizes;

  AJAXImageURIUploadStrategy(this.ajax_id, [ImageSize size = null, ImageSize preview = null]){
    _sizes = [size, preview];
    filter = FILTER_IMAGE;
    _client = new JSON.AJAXJSONClient(ajax_id);

  }

  void upload(FileProgress fileProgress, String data, {void callback(String path):null, void progress(double pct):null}){
    fileProgress.previewPath = data;
    _client.callFunction(new JSON.UploadImageURIJSONFunction(fileProgress.file.name, data, _sizes), (JSON.JSONResponse response){
      if(progress != null){
        progress(1);
      }
      var c = (String path){
        fileProgress.path = path;
        if(callback != null){
          callback(path);
        }
      };
      c(response.type == JSON.RESPONSE_TYPE_SUCCESS?response.payload['path']:null);

    });
  }

}

class FileUploader {

  final UploadStrategy uploadStrategy;

  FileReader _reader = new FileReader();

  List<File> _queue = new List<File>();

  File _currentFile;

  FileProgress _currentFileProcess;

  int _size = 0, _uploaded = 0, _currentlyUploading = 0;

  ListenerRegister _listeners = new ListenerRegister();


  FileUploader(this.uploadStrategy) {
    _reader.onProgress.listen((ProgressEvent pe) => _currentFileProcess.progress = pe.loaded / (pe.total * 2));
    _reader.onLoadEnd.listen((_) {
      var fp = _currentFileProcess;
      uploadStrategy.upload(_currentFileProcess, _reader.result,progress:(double pct)=>fp.progress = 0.5 + pct / 2);
      _startUpload();
    });
  }

  double get progress => (_uploaded+_currentlyUploading)/_size;

  void uploadFiles(List<File> files) {
    files = files.toList();
    var s = _queue.length;
    if (uploadStrategy.filter != null) {
      files.removeWhere((File f) => !f.type.startsWith(uploadStrategy.filter));
    }
    files.forEach((File f) {
      _size += f.size;
      var fp = new FileProgress(f);
      fp.listenOnProgress((){
        _currentlyUploading = (fp.progress*f.size).toInt();
        _notifyProgress();
      });
      fp.listenOnPathAvailable((){
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

  void _startUpload(){
    if(_queue.length == 0){
      _notifyUploadDone();
      return;
    }
    _currentFile = _queue.removeAt(0);
    _currentFileProcess = new FileProgress(_currentFile);
    _reader.readAsDataUrl(_currentFile);

  }


  void listenProgress(void callback()) => _listeners.registerListener("progress", callback);

  void listenUploadDone(void callback()) => _listeners.registerListener("upload_done", callback);

  void listenFileAddedToQueue(void callback(FileProgress)) => _listeners.registerListener("file_added_to_queue", callback);

  void _notifyProgress() => _listeners.callListeners("progress");

  void _notifyUploadDone() => _listeners.callListeners("upload_done");

  void _notifyFileAddedToQueue(FileProgress progress) => _listeners.callListeners("file_added_to_queue", [progress]);

}