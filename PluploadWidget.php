<?php
/**
 * Copyright (c) 2010, Gareth Bond, http://www.gazbond.co.uk
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided
 * that the following conditions are met:
 *
 *   * Redistributions of source code must retain the above copyright notice, this list of conditions and the
 *     following disclaimer.
 *   * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and
 *     the following disclaimer in the documentation and/or other materials provided with the distribution.
 *   * Neither the name of Yii Software LLC nor the names of its contributors may be used to endorse or
 *     promote products derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A
 * PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF
 * THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * Yii widget wrapper for Pupload: http://www.plupload.com/
 * Allows you to upload files using HTML5 Gears, Silverlight, Flash, BrowserPlus or normal forms,
 * providing some unique features such as upload progress, image resizing and chunked uploads.
 *
 * Config options: http://www.plupload.com/documentation.php
 *
 * Usage:
 * <pre>
 * <?php $this->widget('application.extensions.Plupload.PluploadWidget', array(
 *   'config' => array(
 *       'runtimes' => 'flash',
 *       'url' => '/image/upload/',
 *   ),
 *   'id' => 'uploader'
 * )); ?>
 * </pre>
 *
 * @author gazbond
 */ 
class PluploadWidget extends CWidget {

    const ASSETS_DIR_NAME       = 'assets';
    const PLUPLOAD_FILE_NAME    = 'plupload.full.min.js';
    const JQUERYQUEUE_FILE_NAME = 'jquery.plupload.queue.min.js';
    const JQUERYUI_FILE_NAME    = 'jquery.ui.plupload.min.js';
    const GEARS_FILE_NAME       = 'gears_init.js';
    const BROWSER_PLUS_URL      = 'http://bp.yahooapis.com/2.4.21/browserplus-min.js';
    const FLASH_FILE_NAME       = 'plupload.flash.swf';
    const SILVERLIGHT_FILE_NAME = 'plupload.silverlight.xap';
    const DEFAULT_RUNTIMES      = 'gears,flash,silverlight,browserplus,html5';
    const PUPLOAD_CSS_PATH      = 'css/plupload.queue.css';
    const JQUERYUI_CSS_PATH     = 'css/jquery.ui.plupload.css';
    const I18N_DIR_NAME         = 'i18n';

    public $config = array();

    public $callbacks = array();

    public function init() {        
        $css = "";

        $localPath = dirname(__FILE__) . "/" . self::ASSETS_DIR_NAME;
        $publicPath = Yii::app()->getAssetManager()->publish($localPath);

        if(!isset($this->config['flash_swf_url'])) {

            $flashUrl = $publicPath . "/" . self::FLASH_FILE_NAME;
            $this->config['flash_swf_url'] = $flashUrl;
        }

        if(!isset($this->config['silverlight_xap_url'])) {
            
            $silverLightUrl = $publicPath . "/" . self::SILVERLIGHT_FILE_NAME;
            $this->config['silverlight_xap_url'] = $silverLightUrl;
        }

        if(!isset($this->config['runtimes'])) {

            $this->config['runtimes'] = self::DEFAULT_RUNTIMES;
        }

        $runtimes = explode(',', $this->config['runtimes']);
        foreach($runtimes as $key => $value) {

            $value = strtolower(trim($value));
            if($value === 'gears') {

                $gearsPath = $publicPath . "/" . self::GEARS_FILE_NAME;
                Yii::app()->clientScript->registerScriptFile($gearsPath);
            }
            if($value === 'browserplus') {

                Yii::app()->clientScript->registerScriptFile(self::BROWSER_PLUS_URL);
            }
        }

        $pluploadPath = $publicPath . "/" . self::PLUPLOAD_FILE_NAME;
        Yii::app()->clientScript->registerScriptFile($pluploadPath);

        $use_jquery_ui = (isset($this->config['jquery_ui']) && $this->config['jquery_ui']);
        if($use_jquery_ui) {

            $jQueryUIPath = $publicPath . "/" . self::JQUERYUI_FILE_NAME;
            Yii::app()->clientScript->registerScriptFile($jQueryUIPath);

            $jQueryUICssPath = $publicPath . "/" . self::JQUERYUI_CSS_PATH;
            Yii::app()->clientScript->registerCssFile($jQueryUICssPath);
        } else {

            $jQueryQueuePath = $publicPath . "/" . self::JQUERYQUEUE_FILE_NAME;
            Yii::app()->clientScript->registerScriptFile($jQueryQueuePath);

            $cssPath = $publicPath . "/" . self::PUPLOAD_CSS_PATH;
            Yii::app()->clientScript->registerCssFile($cssPath);
        }

        if(isset($this->config['language'])) {

            Yii::app()->clientScript->registerScriptFile($publicPath . "/" . self::I18N_DIR_NAME . "/" . $this->config['language'] . ".js");
            unset($this->config['language']);
        }

        $max_file_number = 0;
        if(isset($this->config['max_file_number'])) {
            $max_file_number = $this->config['max_file_number'];
            unset($this->config['max_file_number']);
        }

        $autostart = false;
        if(isset($this->config['autostart'])) {
            $autostart = $this->config['autostart'];
            unset($this->config['autostart']);
        }

        $reset_after_upload = false;
        if(isset($this->config['reset_after_upload'])) {
            $reset_after_upload = $this->config['reset_after_upload'];
            unset($this->config['reset_after_upload']);
        }

        $callback_total_queued = false;
        if(isset($this->config['callback_total_queued'])) {
            $callback_total_queued = $this->config['callback_total_queued'];
            unset($this->config['callback_total_queued']);
        }

        if(isset($this->config['file_list_height'])) {
            $file_list_height = $this->config['file_list_height'];
            if ($file_list_height < 20)
                $file_list_height = 20;
            if ($use_jquery_ui) {
                $css .= ".plupload_scroll { max-height:".$file_list_height."px; min-height:".$file_list_height."px; }\n".
                        ".plupload_scroll .plupload_filelist table { height:".$file_list_height."px; }\n".
                        ".plupload_droptext {line-height: ".$file_list_height."px;}\n";
            } else {
                $css .= ".plupload_scroll .plupload_filelist { height:".$file_list_height."px; }\n".
                        "li.plupload_droptext {line-height: ".($file_list_height-20)."px;}\n";
            }
            unset($this->config['file_list_height']);
        }

        if(isset($this->config['visible_header'])) {
            if (!$this->config['visible_header'])
                $css .= ".plupload_header { display:none; }\n";
            unset($this->config['visible_header']);
        }

        $fnUniqueId = str_replace(".", "", uniqid("", TRUE));

        $jsConfig = CJavaScript::jsonEncode($this->config);
        $jqueryScript = "function do_plupload_$fnUniqueId() {jQuery('#$this->id').pluploadQueue({$jsConfig}); var uploader = $('#$this->id').pluploadQueue(); ";

        if ($max_file_number > 0 || $autostart) {

            $jqueryScript .= "uploader.bind('FilesAdded', function(up, files) {";
            if ($max_file_number > 0) {
                $jqueryScript .= "if (up.files.length > $max_file_number) up.splice($max_file_number, up.files.length-$max_file_number); ";
            }
            if ($autostart) {
                $jqueryScript .= "if(up.files.length > 0) uploader.start(); ";
            }
            $jqueryScript .= "}); ";
        }

        if (isset($this->callbacks) && is_array($this->callbacks)) {

            foreach ($this->callbacks as $bind => $function) {

                $jqueryScript .= "uploader.bind('$bind', $function); ";
            }
        }

        if ($reset_after_upload || $callback_total_queued !== false) {
            $jqueryScript .= "uploader.bind('FileUploaded', function(up, file, res) { ";
            if ($reset_after_upload) {
                $jqueryScript .= "if(up.total.queued == 0) do_plupload_$fnUniqueId(); ";
            }
            if ($callback_total_queued !== false) {
                $jqueryScript .= "var callback_total_queued = $callback_total_queued; callback_total_queued(up.total.queued); ";
            }
            $jqueryScript .= "}); ";
        }

        if ($callback_total_queued !== false) {
            $jqueryScript .= "uploader.bind('QueueChanged', function(up) { var callback_total_queued = $callback_total_queued; callback_total_queued(up.files.length); }); ";
        }

        if (isset($this->config['add_files_text'])) {
            $jqueryScript .= "setTimeout(\"jQuery('.plupload_add').html('".$this->config['add_files_text']."');\", 1000);";
        }

        if ($autostart) {
            $css .= ".plupload_start { display:none; }\n";
        }

        $jqueryScript .= "} ";

        $uniqueId = 'Yii.' . __CLASS__ . '#' . $this->id;
        Yii::app()->clientScript->registerScript($uniqueId.".end", stripcslashes($jqueryScript), CClientScript::POS_END);
        Yii::app()->clientScript->registerScript($uniqueId.".ready", "do_plupload_$fnUniqueId();", CClientScript::POS_READY);
        if (strlen($css) > 0)
            Yii::app()->clientScript->registerCss($uniqueId.".css", $css);
    }

    public function run()
    {
        echo "<div id=\"$this->id\">";
        echo "<p>".Yii::t('plupload', "Your browser doesn't have Flash, Silverlight, Gears, BrowserPlus or HTML5 support.")."</p>";
	echo "</div>";
    }
}
?>
