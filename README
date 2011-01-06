This is an update to the Yii plupload extension found here:
http://www.yiiframework.com/extension/pupload/

Here is the list of changes:

    * Updated Plupload to v1.3.0
    * Fixed the DIRECTORY_SEPARATOR problem (to be checked, I'm not working with Windows presently)
    * Added language option to config, to be used as 'language' => Yii::app()->language (see the example below). There are 3 supported languages in the i18n folder, but you can find more langauges in their forum http://www.plupload.com/punbb/viewforum.php?id=5
    * Added max_file_number option to config, to limit the number of files (implemented as suggested here http://www.plupload.com/punbb/viewtopic.php?id=113 )
    * Added autostart option to config, to autostart the upload after choosing files (implemented as suggested here http://www.plupload.com/punbb/viewtopic.php?id=90 )
    * Added reset_after_upload to config, option to reset the file dialog after the upload is complete (implemented as suggested here http://www.plupload.com/punbb/viewtopic.php?id=192 )
    * Added callbacks array, to be able to specify javascript callbacks for Plupload's Public Events (see documentation: http://www.plupload.com/plupload/docs/api/index.html#class_plupload.Uploader.html )
    * Added jquery_ui to config, option to enable the jQuery UI theme (see: http://www.plupload.com/example_jquery_ui.php)

Here is an example of use with the new options:

      $this->widget('application.extensions.plupload.PluploadWidget', array(
         'config' => array(
             //'runtimes' => 'gears,flash,silverlight,browserplus,html5',
             'url' => $this->createUrl('blob/uploadFilesPlupload'),
             //'max_file_size' => str_replace("M", "mb", ini_get('upload_max_filesize')),
             'max_file_size' => Yii::app()->params['maxFileSize'],
             'chunk_size' => '1mb',
             'unique_names' => true,
             'filters' => array(
                  array('title' => Yii::t('app', 'Images files'), 'extensions' => 'jpg,jpeg,gif,png'),
              ),
             'language' => Yii::app()->language,
             'max_file_number' => 1,
             'autostart' => true,
             'jquery_ui' => false,
             'reset_after_upload' => true,
         ),
         'callbacks' => array(
             'FileUploaded' => 'function(up,file,response){console.log(response.response);}',
         ),
         'id' => 'uploader'
      ));

And here's an example of the controller action, supporting chunked upload, adapted from upload.php example in the original Plupload package:

      public function actionUploadFilesPlupload()
      {
          // HTTP headers for no cache etc
          header('Content-type: text/plain; charset=UTF-8');
          header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
          header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
          header("Cache-Control: no-store, no-cache, must-revalidate");
          header("Cache-Control: post-check=0, pre-check=0", false);
          header("Pragma: no-cache");

          // Settings
          $targetDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "plupload";
          $cleanupTargetDir = false; // Remove old files
          $maxFileAge = 60 * 60; // Temp file age in seconds

          // 5 minutes execution time
          @set_time_limit(5 * 60);
          // usleep(5000);

          // Get parameters
          $chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
          $chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
          $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

          // Clean the fileName for security reasons
          $fileName = preg_replace('/[^\w\._\s]+/', '', $fileName);

          // Create target dir
          if (!file_exists($targetDir))
                  @mkdir($targetDir);

          // Remove old temp files
          if (is_dir($targetDir) && ($dir = opendir($targetDir))) {
                  while (($file = readdir($dir)) !== false) {
                          $filePath = $targetDir . DIRECTORY_SEPARATOR . $file;

                          // Remove temp files if they are older than the max age
                          if (preg_match('/\\.tmp$/', $file) && (filemtime($filePath) < time() - $maxFileAge))
                                  @unlink($filePath);
                  }

                  closedir($dir);
          } else
                  throw new CHttpException (500, Yii::t('app', "Can't open temporary directory."));

          // Look for the content type header
          if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
                  $contentType = $_SERVER["HTTP_CONTENT_TYPE"];

          if (isset($_SERVER["CONTENT_TYPE"]))
                  $contentType = $_SERVER["CONTENT_TYPE"];

          if (strpos($contentType, "multipart") !== false) {
                  if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                          // Open temp file
                          $out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
                          if ($out) {
                                  // Read binary input stream and append it to temp file
                                  $in = fopen($_FILES['file']['tmp_name'], "rb");

                                  if ($in) {
                                          while ($buff = fread($in, 4096))
                                                  fwrite($out, $buff);
                                  } else
                                          throw new CHttpException (500, Yii::t('app', "Can't open input stream."));

                                  fclose($out);
                                  unlink($_FILES['file']['tmp_name']);
                          } else
                                  throw new CHttpException (500, Yii::t('app', "Can't open output stream."));
                  } else
                          throw new CHttpException (500, Yii::t('app', "Can't move uploaded file."));
          } else {
                  // Open temp file
                  $out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
                  if ($out) {
                          // Read binary input stream and append it to temp file
                          $in = fopen("php://input", "rb");

                          if ($in) {
                                  while ($buff = fread($in, 4096))
                                          fwrite($out, $buff);
                          } else
                                  throw new CHttpException (500, Yii::t('app', "Can't open input stream."));

                          fclose($out);
                  } else
                          throw new CHttpException (500, Yii::t('app', "Can't open output stream."));
          }

          // After last chunk is received, process the file
          $ret = array('result' => '1');
          if (intval($chunk) + 1 >= intval($chunks)) {

              $originalname = $fileName;
              if (isset($_SERVER['HTTP_CONTENT_DISPOSITION'])) {
                  $arr = array();
                  preg_match('@^attachment; filename="([^"]+)"@',$_SERVER['HTTP_CONTENT_DISPOSITION'],$arr);
                  if (isset($arr[1]))
                      $originalname = $arr[1];
              }

              // **********************************************************************************************
              // Do whatever you need with the uploaded file, which has $originalname as the original file name
              // and is located at $targetDir . DIRECTORY_SEPARATOR . $fileName
              // **********************************************************************************************
          }

          // Return response
          die(json_encode($ret));
      }
