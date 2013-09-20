part of core;

class ImageTransform{
  final int maxHeight, maxWidth, minWidth, minHeight;
  final bool dataURI;
  ImageTransform.atLeast(this.minWidth, this.minHeight, {bool dataURI:false}): maxWidth = -1, maxHeight = -1, this.dataURI = dataURI;
  ImageTransform.atMost(this.maxWidth, this.maxHeight, {bool dataURI:false}): minWidth =-1,  minHeight = -1, this.dataURI = dataURI;
  ImageTransform.exactHeight(int height, {bool dataURI:false}): maxHeight = height, minHeight = height, maxWidth = -1, minWidth = -1, this.dataURI = dataURI;
  ImageTransform.exactWidth(int width, {bool dataURI:false}) : maxWidth = width, minWidth = width, maxHeight = minHeight = -1, this.dataURI = dataURI;
  ImageTransform.exact(int width, int height, {bool dataURI:false}) : maxWidth = width, minWidth = width, maxHeight = height, minHeight = height, this.dataURI = dataURI;
  Map<String, int> toJson() => {"maxHeight":maxHeight, "minHeight":minHeight, "maxWidth":maxWidth, "minWidth":minWidth, "dataURI":dataURI};
}
// TODO Replace all usage of this with Streams!
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

  double _progress = 0.0;

  String _path, _previewPath;

  ListenerRegister _listeners = new ListenerRegister();

  factory FileProgress(File file) => _cache.putIfAbsent(file, () => new FileProgress._internal(file));

  FileProgress._internal(this.file);

  String get path => _path;

         set path(String p){
           if(_path != null){
             return;
           }
           progress = 1.0;
           _path = p;
           _notifyPath();
         }

  String get previewPath => _previewPath;

         set previewPath(String p){
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

  void read(FileReader reader,File file);

  const Pattern FILTER_IMAGE = "image/";
  const Pattern FILTER_VIDEO = "video/";

}

class AJAXImageURIUploadStrategy extends UploadStrategy{

  JSON.JSONClient _client;
  List<ImageTransform> _sizes;
  ImageTransform _size, _preview;

  AJAXImageURIUploadStrategy([ImageTransform size = null, ImageTransform preview = null]){
    _size = size;
    _preview = preview;
    _sizes = [size, preview];
    _sizes.removeWhere((ImageTransform s)=>s==null);
    filter = FILTER_IMAGE;
    _client = new JSON.AJAXJSONClient();

  }

  void upload(FileProgress fileProgress, String data, {void callback(String path):null, void progress(double pct):null}){
    fileProgress.previewPath = data;
    if(progress == null){
      progress = (_){};
    }
    _client.callFunction(new JSON.UploadImageURIJSONFunction(fileProgress.file.name, data, _sizes), progress).then((JSON.JSONResponse response){
      progress(1);
      var c = (String path){
        fileProgress.path = path;
        if(callback != null){
          callback(path);
        }
      };
      if(response.type == JSON.RESPONSE_TYPE_SUCCESS){
        c(_size == null?response.payload['path']:response.payload['thumbs'][0]);
        if(_preview != null){
          fileProgress.previewPath = response.payload['thumbs'][1];
        }
      } else {
        c(null);
      }
    });
  }

  void read(FileReader reader,File file) => reader.readAsDataUrl(file);

}

class AJAXFileURIUploadStrategy extends UploadStrategy{

  JSON.JSONClient _client;

  AJAXFileURIUploadStrategy(){
    _client = new JSON.AJAXJSONClient();

  }

  void upload(FileProgress fileProgress, String data, {void callback(String path):null, void progress(double pct):null}){
    _client.callFunction(new JSON.UploadFileURIJSONFunction(fileProgress.file.name, data)).then((JSON.JSONResponse response){
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
  void read(FileReader reader,File file) => reader.readAsDataUrl(file);

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
        var i = fp.progress*f.size;
        _currentlyUploading = i.isNaN || i.isInfinite? 0:i.toInt();
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
    uploadStrategy.read(_reader, _currentFile);

  }


  void listenProgress(void callback()) => _listeners.registerListener("progress", callback);

  void listenUploadDone(void callback()) => _listeners.registerListener("upload_done", callback);

  void listenFileAddedToQueue(void callback(FileProgress)) => _listeners.registerListener("file_added_to_queue", callback);

  void _notifyProgress() => _listeners.callListeners("progress");

  void _notifyUploadDone() => _listeners.callListeners("upload_done");

  void _notifyFileAddedToQueue(FileProgress progress) => _listeners.callListeners("file_added_to_queue", [progress]);

}