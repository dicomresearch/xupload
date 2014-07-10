<?php
/**
 * @author Ruslan Fadeev
 * created: 10.04.14 16:18
 */

Yii::import('xupload.actions.XUploadAction');

/**
 * @property CModel formModel
 */
class XUploadChunkAction extends XUploadAction
{
    /** @var string for CEvents... */
    public $currentFilePath;
    protected $error_messages = [
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk',
        8 => 'A PHP extension stopped the file upload',
        'post_max_size' => 'The uploaded file exceeds the post_max_size directive in php.ini',
        'max_file_size' => 'File is too big',
        'min_file_size' => 'File is too small',
        'accept_file_types' => 'Filetype not allowed',
        'max_number_of_files' => 'Maximum number of files exceeded',
        'max_width' => 'Image exceeds maximum width',
        'min_width' => 'Image requires a minimum width',
        'max_height' => 'Image exceeds maximum height',
        'min_height' => 'Image requires a minimum height',
        'abort' => 'File upload aborted',
        'image_resize' => 'Failed to resize image'
    ];
    protected $options;
    protected $imageObjects = [];

    public $modelName = null;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        #$this->sendHeaders();
        $this->options = [
            #'script_url' => $this->get_full_url().'/',
            'script_url' => $this->getController()->createUrl('/' . $this->getController()->getUniqueId() . '/' . $this->getController()->getAction()->getId()) . '/',
            #'upload_dir' => dirname($this->get_server_var('SCRIPT_FILENAME')).'/files/',
            'upload_dir' => $this->path . DIRECTORY_SEPARATOR,
            #'upload_url' => $this->get_full_url().'/files/',
            'upload_url' => $this->publicPath,
            'user_dirs' => false,
            'mkdir_mode' => 0755,
            #'param_name' => 'files',
            'param_name' => $this->modelName,
            // Set the following option to 'POST', if your server does not support
            // DELETE requests. This is a parameter sent to the client:
            'delete_type' => 'POST',
            'access_control_allow_origin' => '*',
            'access_control_allow_credentials' => false,
            'access_control_allow_methods' => ['OPTIONS', 'HEAD', 'GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
            'access_control_allow_headers' => ['Content-Type', 'Content-Range', 'Content-Disposition'],
            // Enable to provide file downloads via GET requests to the PHP script:
            //     1. Set to 1 to download files via readfile method through PHP
            //     2. Set to 2 to send a X-Sendfile header for lighttpd/Apache
            //     3. Set to 3 to send a X-Accel-Redirect header for nginx
            // If set to 2 or 3, adjust the upload_url option to the base path of
            // the redirect parameter, e.g. '/files/'.
            'download_via_php' => false,
            // Read files in chunks to avoid memory limits when download_via_php
            // is enabled, set to 0 to disable chunked reading of files:
            'readfile_chunk_size' => 10 * 1024 * 1024, // 10 MiB
            // Defines which files can be displayed inline when downloaded:
            'inline_file_types' => '/\.(gif|jpe?g|png)$/i',
            // Defines which files (based on their names) are accepted for upload:
            'accept_file_types' => '/.+$/i',
            // The php.ini settings upload_max_filesize and post_max_size
            // take precedence over the following max_file_size setting:
            'max_file_size' => null,
            'min_file_size' => 1,
            // The maximum number of files for the upload directory:
            'max_number_of_files' => null,
            // Defines which files are handled as image files:
            'image_file_types' => '/\.(gif|jpe?g|png)$/i',
            // Image resolution restrictions:
            'max_width' => null,
            'max_height' => null,
            'min_width' => 1,
            'min_height' => 1,
            // Set the following option to false to enable resumable uploads:
            'discard_aborted_uploads' => true,
            // Set to 0 to use the GD library to scale and orient images,
            // set to 1 to use imagick (if installed, falls back to GD),
            // set to 2 to use the ImageMagick convert binary directly:
            'image_library' => 1,
            // Uncomment the following to define an array of resource limits
            // for imagick:
            /*
            'imagick_resource_limits' => array(
                imagick::RESOURCETYPE_MAP => 32,
                imagick::RESOURCETYPE_MEMORY => 32
            ),
            */
            // Command or path for to the ImageMagick convert binary:
            'convert_bin' => 'convert',
            // Uncomment the following to add parameters in front of each
            // ImageMagick convert call (the limit constraints seem only
            // to have an effect if put in front):
            /*
            'convert_params' => '-limit memory 32MiB -limit map 32MiB',
            */
            // Command or path for to the ImageMagick identify binary:
            'identify_bin' => 'identify',
            'image_versions' => [
                // The empty image version key defines options for the original image:
                '' => [
                    // Automatically rotate images based on EXIF meta data:
                    'auto_orient' => true
                ],
                // Uncomment the following to create medium sized images:
                /*
                'medium' => array(
                    'max_width' => 800,
                    'max_height' => 600
                ),
                */
                'thumbnail' => [
                    // Uncomment the following to use a defined directory for the thumbnails
                    // instead of a subdirectory based on the version identifier.
                    // Make sure that this directory doesn't allow execution of files if you
                    // don't pose any restrictions on the type of uploaded files, e.g. by
                    // copying the .htaccess file from the files directory for Apache:
                    //'upload_dir' => dirname($this->get_server_var('SCRIPT_FILENAME')).'/thumb/',
                    //'upload_url' => $this->get_full_url().'/thumb/',
                    // Uncomment the following to force the max
                    // dimensions and e.g. create square thumbnails:
                    //'crop' => true,
                    'max_width' => 80,
                    'max_height' => 80
                ]
            ]
        ];

        #$this->handleDeleting() or $this->handleBatchUploading();
        switch ($this->getServerVar('REQUEST_METHOD')) {
            case 'OPTIONS':
            case 'HEAD':
                $this->head();
                break;
            case 'GET':
                $this->get();
                break;
            case 'PATCH':
            case 'PUT':
            case 'POST':
                $this->post();
                break;
            case 'DELETE':
                $this->delete();
                break;
            default:
                $this->header('HTTP/1.1 405 Method Not Allowed');
        }
    }

    protected function getFullUrl()
    {
        $https = !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'on') === 0;
        return
            ($https ? 'https://' : 'http://') .
            (!empty($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'] . '@' : '') .
            (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'] .
                ($https && $_SERVER['SERVER_PORT'] === 443 ||
                $_SERVER['SERVER_PORT'] === 80 ? '' : ':' . $_SERVER['SERVER_PORT']))) .
            substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
    }

    protected function getUserId()
    {
        @session_start();
        return session_id();
    }

    protected function getUserPath()
    {
        if ($this->options['user_dirs']) {
            return $this->getUserId() . '/';
        }
        return '';
    }

    protected function getUploadPath($file_name = null, $version = null)
    {
        $file_name = $file_name ? $file_name : '';
        if (empty($version)) {
            $version_path = '';
        } else {
            $version_dir = @$this->options['image_versions'][$version]['upload_dir'];
            if ($version_dir) {
                return $version_dir . $this->getUserPath() . $file_name;
            }
            $version_path = $version . '/';
        }
        return $this->options['upload_dir'] . $this->getUserPath()
        . $version_path . $file_name;
    }

    protected function getQuerySeparator($url)
    {
        return strpos($url, '?') === false ? '?' : '&';
    }

    protected function getDownloadUrl($file_name, $version = null, $direct = false)
    {
        if (!$direct && $this->options['download_via_php']) {
            $url = $this->options['script_url']
                . $this->getQuerySeparator($this->options['script_url'])
                . $this->getSingularParamName()
                . '=' . rawurlencode($file_name);
            if ($version) {
                $url .= '&version=' . rawurlencode($version);
            }
            return $url . '&download=1';
        }
        if (empty($version)) {
            $version_path = '';
        } else {
            $version_url = @$this->options['image_versions'][$version]['upload_url'];
            if ($version_url) {
                return $version_url . $this->getUserPath() . rawurlencode($file_name);
            }
            $version_path = rawurlencode($version) . '/';
        }
        return $this->options['upload_url'] . $this->getUserPath()
        . $version_path . rawurlencode($file_name);
    }

    protected function setAdditionalFileProperties($file)
    {
        $file->deleteUrl = $this->options['script_url']
            . $this->getQuerySeparator($this->options['script_url'])
            . $this->getSingularParamName()
            . '=' . rawurlencode($file->name);
        $file->deleteType = $this->options['delete_type'];
        if ($file->deleteType !== 'DELETE') {
            $file->deleteUrl .= '&_method=DELETE';
        }
        if ($this->options['access_control_allow_credentials']) {
            $file->deleteWithCredentials = true;
        }
    }

    // Fix for overflowing signed 32 bit integers,
    // works for sizes up to 2^32-1 bytes (4 GiB - 1):
    protected function fixIntegerOverflow($size)
    {
        if ($size < 0) {
            $size += 2.0 * (PHP_INT_MAX + 1);
        }
        return $size;
    }

    protected function getFileSize($file_path, $clear_stat_cache = false)
    {
        if ($clear_stat_cache) {
            if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
                clearstatcache(true, $file_path);
            } else {
                clearstatcache();
            }
        }
        return $this->fixIntegerOverflow(filesize($file_path));
    }

    protected function isValidFileObject($file_name)
    {
        $file_path = $this->getUploadPath($file_name);
        if (is_file($file_path) && $file_name[0] !== '.') {
            return true;
        }
        return false;
    }

    protected function getFileObject($file_name)
    {
        if ($this->isValidFileObject($file_name)) {
            $file = new \stdClass();
            $file->name = $file_name;
            $file->size = $this->getFileSize($this->getUploadPath($file_name));
            $file->url = $this->getDownloadUrl($file->name);
            foreach ($this->options['image_versions'] as $version => $options) {
                if (!empty($version)) {
                    if (is_file($this->getUploadPath($file_name, $version))) {
                        $file->{$version . 'Url'} = $this->getDownloadUrl($file->name, $version);
                    }
                }
            }
            $this->setAdditionalFileProperties($file);
            return $file;
        }
        return null;
    }

    protected function getFileObjects($iteration_method = 'get_file_object')
    {
        $upload_dir = $this->getUploadPath();
        if (!is_dir($upload_dir)) {
            return [];
        }
        return array_values(array_filter(array_map([$this, $iteration_method], scandir($upload_dir))));
    }

    protected function countFileObjects()
    {
        return count($this->getFileObjects('is_valid_file_object'));
    }

    protected function getErrorMessage($error)
    {
        return array_key_exists($error, $this->error_messages) ? $this->error_messages[$error] : $error;
    }

    public function getConfigBytes($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        switch ($last) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'g':
                $val *= 1024;
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $this->fixIntegerOverflow($val);
    }

    protected function validate($uploaded_file, $file, $error, $index)
    {
        if ($error) {
            $file->error = $this->getErrorMessage($error);
            return false;
        }
        $content_length = $this->fixIntegerOverflow(intval($this->getServerVar('CONTENT_LENGTH')));
        $post_max_size = $this->getConfigBytes(ini_get('post_max_size'));
        if ($post_max_size && ($content_length > $post_max_size)) {
            $file->error = $this->getErrorMessage('post_max_size');
            return false;
        }
        if (!preg_match($this->options['accept_file_types'], $file->name)) {
            $file->error = $this->getErrorMessage('accept_file_types');
            return false;
        }
        if ($uploaded_file && is_uploaded_file($uploaded_file)) {
            $file_size = $this->getFileSize($uploaded_file);
        } else {
            $file_size = $content_length;
        }
        if ($this->options['max_file_size'] && ($file_size > $this->options['max_file_size'] || $file->size > $this->options['max_file_size'])) {
            $file->error = $this->getErrorMessage('max_file_size');
            return false;
        }
        if ($this->options['min_file_size'] && $file_size < $this->options['min_file_size']) {
            $file->error = $this->getErrorMessage('min_file_size');
            return false;
        }
        if (is_int($this->options['max_number_of_files']) &&
            ($this->countFileObjects() >= $this->options['max_number_of_files']) &&
            // Ignore additional chunks of existing files:
            !is_file($this->getUploadPath($file->name))
        ) {
            $file->error = $this->getErrorMessage('max_number_of_files');
            return false;
        }
        /*$max_width = @$this->options['max_width'];
        $max_height = @$this->options['max_height'];
        $min_width = @$this->options['min_width'];
        $min_height = @$this->options['min_height'];
        if (($max_width || $max_height || $min_width || $min_height) && preg_match($this->options['image_file_types'], $file->name)) {
            list($img_width, $img_height) = $this->get_image_size($uploaded_file);
        }
        if (!empty($img_width)) {
            if ($max_width && $img_width > $max_width) {
                $file->error = $this->getErrorMessage('max_width');
                return false;
            }
            if ($max_height && $img_height > $max_height) {
                $file->error = $this->getErrorMessage('max_height');
                return false;
            }
            if ($min_width && $img_width < $min_width) {
                $file->error = $this->getErrorMessage('min_width');
                return false;
            }
            if ($min_height && $img_height < $min_height) {
                $file->error = $this->getErrorMessage('min_height');
                return false;
            }
        }*/
        return true;
    }

    protected function upCountNameCallback($matches)
    {
        $index = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
        $ext = isset($matches[2]) ? $matches[2] : '';
        return ' (' . $index . ')' . $ext;
    }

    protected function upCountName($name)
    {
        return preg_replace_callback('/(?:(?: \(([\d]+)\))?(\.[^.]+))?$/', [$this, 'upCountNameCallback'], $name, 1);
    }

    protected function getUniqueFilename($filePath, $name, $size, $type, $error, $index, $content_range)
    {
        while (is_dir($this->getUploadPath($name))) {
            $name = $this->upCountName($name);
        }
        // Keep an existing filename if this is part of a chunked upload:
        $uploaded_bytes = $this->fixIntegerOverflow(intval($content_range[1]));
        while (is_file($this->getUploadPath($name))) {
            if ($uploaded_bytes === $this->getFileSize($this->getUploadPath($name))) {
                break;
            }
            $name = $this->upCountName($name);
        }
        return $name;
    }

    protected function trimFileName($file_path, $name, $size, $type, $error, $index, $contentRange)
    {
        // Remove path information and dots around the filename, to prevent uploading
        // into different directories or replacing hidden system files.
        // Also remove control characters and spaces (\x00..\x20) around the filename:
        $name = trim(basename(stripslashes($name)), ".\x00..\x20");
        // Use a timestamp for empty filenames:
        if (!$name) {
            $name = str_replace('.', '-', microtime(true));
        }
        // Add missing file extension for known image types:
        if (strpos($name, '.') === false && preg_match('/^image\/(gif|jpe?g|png)/', $type, $matches)) {
            $name .= '.' . $matches[1];
        }
        if (function_exists('exif_imagetype')) {
            switch (@exif_imagetype($file_path)) {
                case IMAGETYPE_JPEG:
                    $extensions = ['jpg', 'jpeg'];
                    break;
                case IMAGETYPE_PNG:
                    $extensions = ['png'];
                    break;
                case IMAGETYPE_GIF:
                    $extensions = ['gif'];
                    break;
            }
            // Adjust incorrect image file extensions:
            if (!empty($extensions)) {
                $parts = explode('.', $name);
                $extIndex = count($parts) - 1;
                $ext = strtolower(@$parts[$extIndex]);
                if (!in_array($ext, $extensions)) {
                    $parts[$extIndex] = $extensions[0];
                    $name = implode('.', $parts);
                }
            }
        }
        return $name;
    }

    protected function getFileName($filePath, $name, $size, $type, $error, $index, $contentRange)
    {
        return $this->getUniqueFilename(
            $filePath,
            $this->trimFileName($filePath, $name, $size, $type, $error, $index, $contentRange),
            $size,
            $type,
            $error,
            $index,
            $contentRange
        );
    }

    protected function handleFormData($file, $index)
    {
        // Handle form data, e.g. $_REQUEST['description'][$index]
    }

    /*protected function getScaledImageFilePaths($fileName, $version)
    {
        $file_path = $this->getUploadPath($fileName);
        if (!empty($version)) {
            $version_dir = $this->getUploadPath(null, $version);
            if (!is_dir($version_dir)) {
                mkdir($version_dir, $this->options['mkdir_mode'], true);
            }
            $newFilePath = $version_dir . '/' . $fileName;
        } else {
            $newFilePath = $file_path;
        }
        return [$file_path, $newFilePath];
    }

    protected function gdGetImageObject($file_path, $func, $no_cache = false)
    {
        if (empty($this->imageObjects[$file_path]) || $no_cache) {
            $this->gdDestroyImageObject($file_path);
            $this->imageObjects[$file_path] = $func($file_path);
        }
        return $this->imageObjects[$file_path];
    }

    protected function gdSetImageObject($file_path, $image)
    {
        $this->gdDestroyImageObject($file_path);
        $this->imageObjects[$file_path] = $image;
    }

    protected function gdDestroyImageObject($filePath)
    {
        $image = @$this->imageObjects[$filePath];
        return $image && imagedestroy($image);
    }

    protected function gdImageFlip($image, $mode)
    {
        if (function_exists('imageflip')) {
            return imageflip($image, $mode);
        }
        $newWidth = $srcWidth = imagesx($image);
        $newHeight = $srcHeight = imagesy($image);
        $newImg = imagecreatetruecolor($newWidth, $newHeight);
        $srcX = 0;
        $srcY = 0;
        switch ($mode) {
            case '1': // flip on the horizontal axis
                $srcY = $newHeight - 1;
                $srcHeight = -$newHeight;
                break;
            case '2': // flip on the vertical axis
                $srcX = $newWidth - 1;
                $srcWidth = -$newWidth;
                break;
            case '3': // flip on both axes
                $srcY = $newHeight - 1;
                $srcHeight = -$newHeight;
                $srcX = $newWidth - 1;
                $srcWidth = -$newWidth;
                break;
            default:
                return $image;
        }
        imagecopyresampled($newImg, $image, 0, 0, $srcX, $srcY, $newWidth, $newHeight, $srcWidth, $srcHeight);
        return $newImg;
    }

    protected function gdOrientImage($filePath, $srcImg)
    {
        if (!function_exists('exif_read_data')) {
            return false;
        }
        $exif = @exif_read_data($filePath);
        if ($exif === false) {
            return false;
        }
        $orientation = intval(@$exif['Orientation']);
        if ($orientation < 2 || $orientation > 8) {
            return false;
        }
        switch ($orientation) {
            case 2:
                $new_img = $this->gdImageFlip($srcImg, defined('IMG_FLIP_VERTICAL') ? IMG_FLIP_VERTICAL : 2);
                break;
            case 3:
                $new_img = imagerotate($srcImg, 180, 0);
                break;
            case 4:
                $new_img = $this->gdImageFlip($srcImg, defined('IMG_FLIP_HORIZONTAL') ? IMG_FLIP_HORIZONTAL : 1);
                break;
            case 5:
                $tmp_img = $this->gdImageFlip($srcImg, defined('IMG_FLIP_HORIZONTAL') ? IMG_FLIP_HORIZONTAL : 1);
                $new_img = imagerotate($tmp_img, 270, 0);
                imagedestroy($tmp_img);
                break;
            case 6:
                $new_img = imagerotate($srcImg, 270, 0);
                break;
            case 7:
                $tmp_img = $this->gdImageFlip($srcImg, defined('IMG_FLIP_VERTICAL') ? IMG_FLIP_VERTICAL : 2);
                $new_img = imagerotate($tmp_img, 270, 0);
                imagedestroy($tmp_img);
                break;
            case 8:
                $new_img = imagerotate($srcImg, 90, 0);
                break;
            default:
                return false;
        }
        $this->gdSetImageObject($filePath, $new_img);
        return true;
    }

    protected function gdCreateScaledImage($fileName, $version, $options)
    {
        if (!function_exists('imagecreatetruecolor')) {
            error_log('Function not found: imagecreatetruecolor');
            return false;
        }
        list($filePath, $newFilePath) = $this->getScaledImageFilePaths($fileName, $version);
        $type = strtolower(substr(strrchr($fileName, '.'), 1));
        switch ($type) {
            case 'jpg':
            case 'jpeg':
                $src_func = 'imagecreatefromjpeg';
                $write_func = 'imagejpeg';
                $imageQuality = isset($options['jpeg_quality']) ?
                    $options['jpeg_quality'] : 75;
                break;
            case 'gif':
                $src_func = 'imagecreatefromgif';
                $write_func = 'imagegif';
                $imageQuality = null;
                break;
            case 'png':
                $src_func = 'imagecreatefrompng';
                $write_func = 'imagepng';
                $imageQuality = isset($options['png_quality']) ?
                    $options['png_quality'] : 9;
                break;
            default:
                return false;
        }
        $srcImg = $this->gdGetImageObject($filePath, $src_func, !empty($options['no_cache']));
        $image_oriented = false;
        if (!empty($options['auto_orient']) && $this->gdOrientImage($filePath, $srcImg)) {
            $image_oriented = true;
            $srcImg = $this->gdGetImageObject($filePath, $src_func);
        }
        $maxWidth = $imgWidth = imagesx($srcImg);
        $maxHeight = $imgHeight = imagesy($srcImg);
        if (!empty($options['max_width'])) {
            $maxWidth = $options['max_width'];
        }
        if (!empty($options['max_height'])) {
            $maxHeight = $options['max_height'];
        }
        $scale = min($maxWidth / $imgWidth, $maxHeight / $imgHeight);
        if ($scale >= 1) {
            if ($image_oriented) {
                return $write_func($srcImg, $newFilePath, $imageQuality);
            }
            if ($filePath !== $newFilePath) {
                return copy($filePath, $newFilePath);
            }
            return true;
        }
        if (empty($options['crop'])) {
            $newWidth = $imgWidth * $scale;
            $newHeight = $imgHeight * $scale;
            $dstX = 0;
            $dstY = 0;
            $newImg = imagecreatetruecolor($newWidth, $newHeight);
        } else {
            if (($imgWidth / $imgHeight) >= ($maxWidth / $maxHeight)) {
                $newWidth = $imgWidth / ($imgHeight / $maxHeight);
                $newHeight = $maxHeight;
            } else {
                $newWidth = $maxWidth;
                $newHeight = $imgHeight / ($imgWidth / $maxWidth);
            }
            $dstX = 0 - ($newWidth - $maxWidth) / 2;
            $dstY = 0 - ($newHeight - $maxHeight) / 2;
            $newImg = imagecreatetruecolor($maxWidth, $maxHeight);
        }
        // Handle transparency in GIF and PNG images:
        switch ($type) {
            case 'gif':
            case 'png':
                imagecolortransparent($newImg, imagecolorallocate($newImg, 0, 0, 0));
            case 'png':
                imagealphablending($newImg, false);
                imagesavealpha($newImg, true);
                break;
        }
        $success = imagecopyresampled($newImg, $srcImg, $dstX, $dstY, 0, 0, $newWidth, $newHeight, $imgWidth, $imgHeight) && $write_func($newImg, $newFilePath, $imageQuality);
        $this->gdSetImageObject($filePath, $newImg);
        return $success;
    }

    protected function imagick_get_image_object($file_path, $no_cache = false)
    {
        if (empty($this->imageObjects[$file_path]) || $no_cache) {
            $this->imagick_destroy_image_object($file_path);
            $image = new \Imagick();
            if (!empty($this->options['imagick_resource_limits'])) {
                foreach ($this->options['imagick_resource_limits'] as $type => $limit) {
                    $image->setResourceLimit($type, $limit);
                }
            }
            $image->readImage($file_path);
            $this->imageObjects[$file_path] = $image;
        }
        return $this->imageObjects[$file_path];
    }

    protected function imagick_set_image_object($file_path, $image) {
        $this->imagick_destroy_image_object($file_path);
        $this->imageObjects[$file_path] = $image;
    }

    protected function imagick_destroy_image_object($file_path) {
        $image = @$this->imageObjects[$file_path];
        return $image && $image->destroy();
    }

    protected function imagick_orient_image($image) {
        $orientation = $image->getImageOrientation();
        $background = new \ImagickPixel('none');
        switch ($orientation) {
            case \imagick::ORIENTATION_TOPRIGHT: // 2
                $image->flopImage(); // horizontal flop around y-axis
                break;
            case \imagick::ORIENTATION_BOTTOMRIGHT: // 3
                $image->rotateImage($background, 180);
                break;
            case \imagick::ORIENTATION_BOTTOMLEFT: // 4
                $image->flipImage(); // vertical flip around x-axis
                break;
            case \imagick::ORIENTATION_LEFTTOP: // 5
                $image->flopImage(); // horizontal flop around y-axis
                $image->rotateImage($background, 270);
                break;
            case \imagick::ORIENTATION_RIGHTTOP: // 6
                $image->rotateImage($background, 90);
                break;
            case \imagick::ORIENTATION_RIGHTBOTTOM: // 7
                $image->flipImage(); // vertical flip around x-axis
                $image->rotateImage($background, 270);
                break;
            case \imagick::ORIENTATION_LEFTBOTTOM: // 8
                $image->rotateImage($background, 270);
                break;
            default:
                return false;
        }
        $image->setImageOrientation(\imagick::ORIENTATION_TOPLEFT); // 1
        return true;
    }

    protected function imagick_create_scaled_image($file_name, $version, $options) {
        list($file_path, $new_file_path) =
            $this->getScaledImageFilePaths($file_name, $version);
        $image = $this->imagick_get_image_object(
            $file_path,
            !empty($options['no_cache'])
        );
        if ($image->getImageFormat() === 'GIF') {
            // Handle animated GIFs:
            $images = $image->coalesceImages();
            foreach ($images as $frame) {
                $image = $frame;
                $this->imagick_set_image_object($file_name, $image);
                break;
            }
        }
        $image_oriented = false;
        if (!empty($options['auto_orient'])) {
            $image_oriented = $this->imagick_orient_image($image);
        }
        $new_width = $max_width = $img_width = $image->getImageWidth();
        $new_height = $max_height = $img_height = $image->getImageHeight();
        if (!empty($options['max_width'])) {
            $new_width = $max_width = $options['max_width'];
        }
        if (!empty($options['max_height'])) {
            $new_height = $max_height = $options['max_height'];
        }
        if (!($image_oriented || $max_width < $img_width || $max_height < $img_height)) {
            if ($file_path !== $new_file_path) {
                return copy($file_path, $new_file_path);
            }
            return true;
        }
        $crop = !empty($options['crop']);
        if ($crop) {
            $x = 0;
            $y = 0;
            if (($img_width / $img_height) >= ($max_width / $max_height)) {
                $new_width = 0; // Enables proportional scaling based on max_height
                $x = ($img_width / ($img_height / $max_height) - $max_width) / 2;
            } else {
                $new_height = 0; // Enables proportional scaling based on max_width
                $y = ($img_height / ($img_width / $max_width) - $max_height) / 2;
            }
        }
        $success = $image->resizeImage(
            $new_width,
            $new_height,
            isset($options['filter']) ? $options['filter'] : \imagick::FILTER_LANCZOS,
            isset($options['blur']) ? $options['blur'] : 1,
            $new_width && $new_height // fit image into constraints if not to be cropped
        );
        if ($success && $crop) {
            $success = $image->cropImage(
                $max_width,
                $max_height,
                $x,
                $y
            );
            if ($success) {
                $success = $image->setImagePage($max_width, $max_height, 0, 0);
            }
        }
        $type = strtolower(substr(strrchr($file_name, '.'), 1));
        switch ($type) {
            case 'jpg':
            case 'jpeg':
                if (!empty($options['jpeg_quality'])) {
                    $image->setImageCompression(\imagick::COMPRESSION_JPEG);
                    $image->setImageCompressionQuality($options['jpeg_quality']);
                }
                break;
        }
        if (!empty($options['strip'])) {
            $image->stripImage();
        }
        return $success && $image->writeImage($new_file_path);
    }

    protected function imagemagick_create_scaled_image($file_name, $version, $options) {
        list($file_path, $new_file_path) =
            $this->getScaledImageFilePaths($file_name, $version);
        $resize = @$options['max_width']
            .(empty($options['max_height']) ? '' : 'X'.$options['max_height']);
        if (!$resize && empty($options['auto_orient'])) {
            if ($file_path !== $new_file_path) {
                return copy($file_path, $new_file_path);
            }
            return true;
        }
        $cmd = $this->options['convert_bin'];
        if (!empty($this->options['convert_params'])) {
            $cmd .= ' '.$this->options['convert_params'];
        }
        $cmd .= ' '.escapeshellarg($file_path);
        if (!empty($options['auto_orient'])) {
            $cmd .= ' -auto-orient';
        }
        if ($resize) {
            // Handle animated GIFs:
            $cmd .= ' -coalesce';
            if (empty($options['crop'])) {
                $cmd .= ' -resize '.escapeshellarg($resize.'>');
            } else {
                $cmd .= ' -resize '.escapeshellarg($resize.'^');
                $cmd .= ' -gravity center';
                $cmd .= ' -crop '.escapeshellarg($resize.'+0+0');
            }
            // Make sure the page dimensions are correct (fixes offsets of animated GIFs):
            $cmd .= ' +repage';
        }
        if (!empty($options['convert_params'])) {
            $cmd .= ' '.$options['convert_params'];
        }
        $cmd .= ' '.escapeshellarg($new_file_path);
        exec($cmd, $output, $error);
        if ($error) {
            error_log(implode('\n', $output));
            return false;
        }
        return true;
    }

    protected function get_image_size($file_path) {
        if ($this->options['image_library']) {
            if (extension_loaded('imagick')) {
                $image = new \Imagick();
                try {
                    if (@$image->pingImage($file_path)) {
                        $dimensions = [$image->getImageWidth(), $image->getImageHeight()];
                        $image->destroy();
                        return $dimensions;
                    }
                    return false;
                } catch (Exception $e) {
                    error_log($e->getMessage());
                }
            }
            if ($this->options['image_library'] === 2) {
                $cmd = $this->options['identify_bin'];
                $cmd .= ' -ping '.escapeshellarg($file_path);
                exec($cmd, $output, $error);
                if (!$error && !empty($output)) {
                    // image.jpg JPEG 1920x1080 1920x1080+0+0 8-bit sRGB 465KB 0.000u 0:00.000
                    $infos = preg_split('/\s+/', $output[0]);
                    $dimensions = preg_split('/x/', $infos[2]);
                    return $dimensions;
                }
                return false;
            }
        }
        if (!function_exists('getimagesize')) {
            error_log('Function not found: getimagesize');
            return false;
        }
        return @getimagesize($file_path);
    }

    protected function create_scaled_image($file_name, $version, $options) {
        if ($this->options['image_library'] === 2) {
            return $this->imagemagick_create_scaled_image($file_name, $version, $options);
        }
        if ($this->options['image_library'] && extension_loaded('imagick')) {
            return $this->imagick_create_scaled_image($file_name, $version, $options);
        }
        return $this->gdCreateScaledImage($file_name, $version, $options);
    }

    protected function destroy_image_object($file_path) {
        if ($this->options['image_library'] && extension_loaded('imagick')) {
            return $this->imagick_destroy_image_object($file_path);
        }
    }

    protected function is_valid_image_file($file_path) {
        if (!preg_match($this->options['image_file_types'], $file_path)) {
            return false;
        }
        if (function_exists('exif_imagetype')) {
            return @exif_imagetype($file_path);
        }
        $image_info = $this->get_image_size($file_path);
        return $image_info && $image_info[0] && $image_info[1];
    }

    protected function handle_image_file($file_path, $file) {
        $failed_versions = [];
        foreach($this->options['image_versions'] as $version => $options) {
            if ($this->create_scaled_image($file->name, $version, $options)) {
                if (!empty($version)) {
                    $file->{$version.'Url'} = $this->getDownloadUrl(
                        $file->name,
                        $version
                    );
                } else {
                    $file->size = $this->getFileSize($file_path, true);
                }
            } else {
                $failed_versions[] = $version ? $version : 'original';
            }
        }
        if (count($failed_versions)) {
            $file->error = $this->getErrorMessage('image_resize')
                .' ('.implode($failed_versions,', ').')';
        }
        // Free memory:
        $this->destroy_image_object($file_path);
    }*/

    public function onFirstChunkUploaded(CEvent $event)
    {
        $this->raiseEvent('onFirstChunkUploaded', $event);
    }

    public function onAllChunkUploaded(CEvent $event)
    {
        $this->raiseEvent('onAllChunkUploaded', $event);
    }

    public function onFileUploaded(CEvent $event)
    {
        $this->raiseEvent('onFileUploaded', $event);
    }

    protected function handleBatchUploading($uploadedFile, $name, $size, $type, $error, $index = null, $contentRange = null)
    {
        $file = new \stdClass();
        $file->name = $this->getFileName($uploadedFile, $name, $size, $type, $error, $index, $contentRange);
        $file->size = $this->fixIntegerOverflow(intval($size));
        $file->type = $type;
        #if ($this->validate($uploadedFile, $file, $error, $index)) {
        if ($this->validate($uploadedFile, $file, $error, $index)) {
            $this->handleFormData($file, $index);
            $upload_dir = $this->getUploadPath();
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, $this->options['mkdir_mode'], true);
            }
            $this->currentFilePath = $file_path = $this->getUploadPath($file->name);
            $append_file = $contentRange && is_file($file_path) && $file->size > $this->getFileSize($file_path);
            if ($uploadedFile && is_uploaded_file($uploadedFile)) {
                // multipart/formdata uploads (POST method uploads)
                if ($append_file) {
                    file_put_contents($file_path, fopen($uploadedFile, 'r'), FILE_APPEND);
                } else {
                    move_uploaded_file($uploadedFile, $file_path);
                    $event = new CModelEvent($this);
                    $contentRange ? $this->onFirstChunkUploaded($event) : $this->onFileUploaded($event);
                }
            } else {
                // Non-multipart uploads (PUT method support)
                file_put_contents($file_path, fopen('php://input', 'r'), $append_file ? FILE_APPEND : 0);
            }
            $file_size = $this->getFileSize($file_path, $append_file);
            if ($file_size === $file->size) {
                $file->url = $this->getDownloadUrl($file->name);
                if ($contentRange) {
                    $this->onAllChunkUploaded(new CModelEvent($this));
                }
                /*if ($this->is_valid_image_file($file_path)) {
                    $this->handle_image_file($file_path, $file);
                }*/
            } else {
                $file->size = $file_size;
                if (!$contentRange && $this->options['discard_aborted_uploads']) {
                    unlink($file_path);
                    $file->error = $this->getErrorMessage('abort');
                }
            }
            $this->setAdditionalFileProperties($file);
        }
        return $file;
    }

    protected function readFile($filePath)
    {
        $file_size = $this->getFileSize($filePath);
        $chunk_size = $this->options['readfile_chunk_size'];
        if ($chunk_size && $file_size > $chunk_size) {
            $handle = fopen($filePath, 'rb');
            while (!feof($handle)) {
                echo fread($handle, $chunk_size);
                @ob_flush();
                @flush();
            }
            fclose($handle);
            return $file_size;
        }
        return readfile($filePath);
    }

    protected function body($str)
    {
        echo $str;
    }

    protected function header($str)
    {
        header($str);
        return true;
    }

    protected function getServerVar($id)
    {
        return isset($_SERVER[$id]) ? $_SERVER[$id] : '';
    }

    protected function generateResponse($content, $printResponse = true)
    {
        if ($printResponse) {
            $json = json_encode($content);
            $redirect = isset($_REQUEST['redirect']) ? stripslashes($_REQUEST['redirect']) : null;
            if ($redirect) {
                $this->header('Location: ' . sprintf($redirect, rawurlencode($json)));
                return true;
            }
            $this->head();
            if ($this->getServerVar('HTTP_CONTENT_RANGE')) {
                $files = isset($content[$this->options['param_name']]) ?
                    $content[$this->options['param_name']] : null;
                if ($files && is_array($files) && is_object($files[0]) && $files[0]->size) {
                    $this->header('Range: 0-' . ($this->fixIntegerOverflow(intval($files[0]->size)) - 1));
                }
            }
            $this->body($json);
        }
        return $content;
    }

    protected function getVersionParam()
    {
        return isset($_GET['version']) ? basename(stripslashes($_GET['version'])) : null;
    }

    protected function getSingularParamName()
    {
        return substr($this->options['param_name'], 0, -1);
    }

    protected function getFileNameParam()
    {
        $name = $this->getSingularParamName();
        return isset($_REQUEST[$name]) ? basename(stripslashes($_REQUEST[$name])) : null;
    }

    protected function getFileNamesParams()
    {
        $params = isset($_REQUEST[$this->options['param_name']]) ? $_REQUEST[$this->options['param_name']] : [];
        foreach ($params as $key => $value) {
            $params[$key] = basename(stripslashes($value));
        }
        return $params;
    }

    protected function getFileType($filePath)
    {
        switch (strtolower(pathinfo($filePath, PATHINFO_EXTENSION))) {
            case 'jpeg':
            case 'jpg':
                return 'image/jpeg';
            case 'png':
                return 'image/png';
            case 'gif':
                return 'image/gif';
            default:
                return '';
        }
    }

    protected function download()
    {
        switch ($this->options['download_via_php']) {
            case 1:
                $redirectHeader = null;
                break;
            case 2:
                $redirectHeader = 'X-Sendfile';
                break;
            case 3:
                $redirectHeader = 'X-Accel-Redirect';
                break;
            default:
                return $this->header('HTTP/1.1 403 Forbidden');
        }
        $fileName = $this->getFileNameParam();
        if (!$this->isValidFileObject($fileName)) {
            return $this->header('HTTP/1.1 404 Not Found');
        }
        if ($redirectHeader) {
            return $this->header($redirectHeader.': '.$this->getDownloadUrl($fileName, $this->getVersionParam(), true));
        }
        $file_path = $this->getUploadPath($fileName, $this->getVersionParam());
        // Prevent browsers from MIME-sniffing the content-type:
        $this->header('X-Content-Type-Options: nosniff');
        if (!preg_match($this->options['inline_file_types'], $fileName)) {
            $this->header('Content-Type: application/octet-stream');
            $this->header('Content-Disposition: attachment; filename="'.$fileName.'"');
        } else {
            $this->header('Content-Type: '.$this->getFileType($file_path));
            $this->header('Content-Disposition: inline; filename="'.$fileName.'"');
        }
        $this->header('Content-Length: '.$this->getFileSize($file_path));
        $this->header('Last-Modified: '.gmdate('D, d M Y H:i:s T', filemtime($file_path)));
        $this->readFile($file_path);
        return true;#
    }

    protected function sendContentTypeHeader()
    {
        $this->header('Vary: Accept');
        if (strpos($this->getServerVar('HTTP_ACCEPT'), 'application/json') !== false) {
            $this->header('Content-type: application/json');
        } else {
            $this->header('Content-type: text/plain');
        }
    }

    protected function sendAccessControlHeaders()
    {
        $this->header('Access-Control-Allow-Origin: ' . $this->options['access_control_allow_origin']);
        $this->header('Access-Control-Allow-Credentials: ' . ($this->options['access_control_allow_credentials'] ? 'true' : 'false'));
        $this->header('Access-Control-Allow-Methods: ' . implode(', ', $this->options['access_control_allow_methods']));
        $this->header('Access-Control-Allow-Headers: ' . implode(', ', $this->options['access_control_allow_headers']));
    }

    public function head()
    {
        $this->header('Pragma: no-cache');
        $this->header('Cache-Control: no-store, no-cache, must-revalidate');
        $this->header('Content-Disposition: inline; filename="files.json"');
        // Prevent Internet Explorer from MIME-sniffing the content-type:
        $this->header('X-Content-Type-Options: nosniff');
        if ($this->options['access_control_allow_origin']) {
            $this->sendAccessControlHeaders();
        }
        $this->sendContentTypeHeader();
    }

    public function get($printResponse = true)
    {
        if ($printResponse && isset($_GET['download'])) {
            return $this->download();
        }
        $file_name = $this->getFileNameParam();
        if ($file_name) {
            $response = [$this->getSingularParamName() => $this->getFileObject($file_name)];
        } else {
            $response = [$this->options['param_name'] => $this->getFileObjects()];
        }
        return $this->generateResponse($response, $printResponse);
    }

    public function post($printResponse = true)
    {
        if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
            return $this->delete($printResponse);
        }
        $upload = isset($_FILES[$this->options['param_name']]) ? $_FILES[$this->options['param_name']] : null;
        // Parse the Content-Disposition header, if available:
        $file_name = $this->getServerVar('HTTP_CONTENT_DISPOSITION') ? rawurldecode(preg_replace('/(^[^"]+")|("$)/', '', $this->getServerVar('HTTP_CONTENT_DISPOSITION'))) : null;
        // Parse the Content-Range header, which has the following form:
        // Content-Range: bytes 0-524287/2000000
        $content_range = $this->getServerVar('HTTP_CONTENT_RANGE') ? preg_split('/[^0-9]+/', $this->getServerVar('HTTP_CONTENT_RANGE')) : null;
        $size = $content_range ? $content_range[3] : null;
        $files = [];
        if ($upload && is_array($upload['tmp_name'])) {
            // param_name is an array identifier like "files[]",
            // $_FILES is a multi-dimensional array:
            foreach ($upload['tmp_name'] as $index => $value) {
                $files[] = $this->handleBatchUploading(
                    $upload['tmp_name'][$index],
                    $file_name ? $file_name : $upload['name'][$index],
                    $size ? $size : $upload['size'][$index],
                    $upload['type'][$index],
                    $upload['error'][$index],
                    $index,
                    $content_range
                );
            }
        } else {
            // param_name is a single object identifier like "file",
            // $_FILES is a one-dimensional array:
            $files[] = $this->handleBatchUploading(
                isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
                $file_name ? $file_name : (isset($upload['name']) ? $upload['name'] : null),
                $size ? $size : (isset($upload['size']) ? $upload['size'] : $this->getServerVar('CONTENT_LENGTH')),
                isset($upload['type']) ? $upload['type'] : $this->getServerVar('CONTENT_TYPE'),
                isset($upload['error']) ? $upload['error'] : null,
                null,
                $content_range
            );
        }
        #return $this->generateResponse([$this->options['param_name'] => $files], $printResponse);
        // @todo why use $this->options if fileupload JS append response by {files} key?
        return $this->generateResponse(['files' => $files], $printResponse);
    }

    public function delete($printResponse = true)
    {
        $fileNames = $this->getFileNamesParams();
        if (empty($fileNames)) {
            $fileNames = [$this->getFileNameParam()];
        }
        $response = [];
        foreach ($fileNames as $fileName) {
            $file_path = $this->getUploadPath($fileName);
            $success = is_file($file_path) && $fileName[0] !== '.' && unlink($file_path);
            if ($success) {
                foreach ($this->options['image_versions'] as $version => $options) {
                    if (!empty($version)) {
                        $file = $this->getUploadPath($fileName, $version);
                        if (is_file($file)) {
                            unlink($file);
                        }
                    }
                }
            }
            $response[$fileName] = $success;
        }
        return $this->generateResponse($response, $printResponse);
    }
}
