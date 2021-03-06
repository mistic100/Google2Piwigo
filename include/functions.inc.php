<?php
defined('PICASA_WA_PATH') or die('Hacking attempt!');

/**
 * test if a download method is available
 * @return: bool
 */
if (!function_exists('test_remote_download'))
{
  function test_remote_download()
  {
    return function_exists('curl_init') || ini_get('allow_url_fopen');
  }
}

/**
 * download a remote file (special version for Google2Piwigo)
 *  - needs cURL or allow_url_fopen
 *  - take care of SSL urls
 *
 * @param: string source url
 * @param: mixed destination file (if true, file content is returned)
 */
function picasa_wa_download_remote_file($src, $dest, $headers=array())
{
  if (empty($src))
  {
    return false;
  }
  
  $return = ($dest === true) ? true : false;
  
  array_push($headers, 'Accept-language: en');
  
  /* curl */
  if (function_exists('curl_init'))
  {
    if (!$return)
    {
      $newf = fopen($dest, "wb");
    }
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $src);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    if (!ini_get('safe_mode'))
    {
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
    }
    if (strpos($src, 'https://') !== false)
    {
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    }
    if (!$return)
    {
      curl_setopt($ch, CURLOPT_FILE, $newf);
    }
    else
    {
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    }
    
    $out = curl_exec($ch);
    curl_close($ch);
    
    if ($out === false)
    {
      return 'file_error';
    }
    else if (!$return)
    {
      fclose($newf);
      return true;
    }
    else
    {
      return $out;
    }
  }
  /* file get content */
  else if (ini_get('allow_url_fopen'))
  {
    $src = preg_replace('#^https#', 'http', $src);
    
    $opts = array(
      'http' => array(
        'method' => "GET",
        'user_agent' => 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)',
        'header' => implode("\r\n", $headers),
      )
    );

    $context = stream_context_create($opts);
    
    if (($file = file_get_contents($src, false, $context)) === false)
    {
      return 'file_error';
    }
    
    if (!$return)
    {
      file_put_contents($dest, $file);
      return true;
    }
    else
    {
      return $file;
    }
  }
  
  return false;
}
