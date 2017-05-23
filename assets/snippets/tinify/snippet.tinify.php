<?php
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}

$key = isset($key) ? $key: ''; //get from tinypng.com
if ($key == '') {
    return $input;  
    $modx->logEvent(0, 3, "set KEY from tinypng.com", 'tinify');
}

$keys = explode(',', $key);
$keys = array_rand($keys, 1);
$key =  $keys[$keys[0]];


$newfolderaccessmode = $modx->config['new_folder_permissions'] ? octdec($modx->config['new_folder_permissions']) : 0777;

$cacheFolder=isset($cacheFolder) ? $cacheFolder : "assets/cache/images";
if(!is_dir(MODX_BASE_PATH.$cacheFolder)) {
    mkdir(MODX_BASE_PATH.$cacheFolder);
    chmod(MODX_BASE_PATH.$cacheFolder, $newfolderaccessmode);
}

$tmpFolder = 'assets/cache/tmp';
if (!empty($input)) $input = rawurldecode($input);

if(empty($input) || !file_exists(MODX_BASE_PATH . $input)){
    $input = isset($noImage) ? $noImage : 'assets/snippets/tinify/noimage.png';
}

// allow read in tinify cache folder
if (strpos($cacheFolder, 'assets/cache/') === 0 && $cacheFolder != 'assets/cache/' && !is_file(MODX_BASE_PATH . $cacheFolder . '/.htaccess')) {
	file_put_contents(MODX_BASE_PATH . $cacheFolder . '/.htaccess', "order deny,allow\nallow from all\n");
}

if(!is_dir(MODX_BASE_PATH.$tmpFolder)) {
    mkdir(MODX_BASE_PATH.$tmpFolder);
    chmod(MODX_BASE_PATH.$tmpFolder, $newfolderaccessmode);
}

$path_parts=pathinfo($input);
$tmpImagesFolder=str_replace(MODX_BASE_PATH . "assets/images","",$path_parts['dirname']);
$tmpImagesFolder=str_replace("assets/images","",$tmpImagesFolder);
$tmpImagesFolder=explode("/",$tmpImagesFolder);
$ext=strtolower($path_parts['extension']);

$options = 'f='.(in_array($ext,explode(",","png,gif,jpeg"))?$ext:"jpg&q=96").'&'.strtr($options, Array("," => "&", "_" => "=", '{' => '[', '}' => ']'));
parse_str($options, $params);
 
foreach ($tmpImagesFolder as $folder) {
    if (!empty($folder)) {
        $cacheFolder.="/".$folder;
        if(!is_dir(MODX_BASE_PATH.$cacheFolder)) {
            mkdir(MODX_BASE_PATH.$cacheFolder);
            chmod(MODX_BASE_PATH.$cacheFolder, $newfolderaccessmode);
        }
    }
}

$fname_preffix = "$cacheFolder/";
$fname = $path_parts['filename'];
$fname_suffix = "-{$params['w']}x{$params['h']}-".substr(md5(serialize($params).filemtime(MODX_BASE_PATH . $input)),0,3).".{$params['f']}";
$outputFilename = MODX_BASE_PATH.$fname_preffix.$fname.$fname_suffix;
if (!file_exists($outputFilename)) {
    require_once(MODX_BASE_PATH."assets/snippets/tinify/lib/Tinify/Exception.php");
    require_once(MODX_BASE_PATH."assets/snippets/tinify/lib/Tinify/ResultMeta.php");
    require_once(MODX_BASE_PATH."assets/snippets/tinify/lib/Tinify/Result.php");
    require_once(MODX_BASE_PATH."assets/snippets/tinify/lib/Tinify/Source.php");
    require_once(MODX_BASE_PATH."assets/snippets/tinify/lib/Tinify/Client.php");
    require_once(MODX_BASE_PATH."assets/snippets/tinify/lib/Tinify.php");

    try {
        // Use the Tinify API client.
        \Tinify\setKey($key);
        $source = \Tinify\fromFile(MODX_BASE_PATH . $input);
        
        if ($params['w'] > 0 || $params['h'] > 0){
            //добавить проверку параметров от исходной картинки что б если что только оптимизировать 
            $params['m'] = isset($params['m']) ? $params['m']: 'cover';//scale / fit / cover 
            $resized = $source->resize(array(
                "method" => $params['m'],
                "width" => intval($params['w']),
                "height" => intval($params['h'])
            ));
            $resized->toFile($outputFilename);
        
        }else{

            $source->toFile($outputFilename);
        
        }     

    } catch(\Tinify\AccountException $e) {
        // Verify your API key and account limit.
        $modx->logEvent(0, 3, "The error message is: " . $e.getMessage(), 'tinify');
        return $input;
        
    } catch(\Tinify\ClientException $e) { 
        // Check your source image and request options.
        $modx->logEvent(0, 3, "Check your source image and request options.", 'tinify');
        return $input;

    } catch(\Tinify\ServerException $e) {
        // Temporary issue with the Tinify API.
        $modx->logEvent(0, 3, "Temporary issue with the Tinify API.", 'tinify');
        return $input;

    } catch(\Tinify\ConnectionException $e) {
        // A network connection error occurred.
        $modx->logEvent(0, 3, "A network connection error occurred.", 'tinify');
        return $input;

    } catch(Exception $e) {
        // Something else went wrong, unrelated to the Tinify API.
        $modx->logEvent(0, 3, "Something else went wrong, unrelated to the Tinify API.", 'tinify');
        return $input;
    }
 
}
return $fname_preffix.rawurlencode($fname).$fname_suffix;
?>
