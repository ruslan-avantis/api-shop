<?php
// {API}$hop
/**
 * This file is part of the API SHOP
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/pllano/api-shop
 * @version 1.0.1
 * @package pllano.api-shop
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); */

$site = '
<!DOCTYPE html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"><title>API Shop - Installation</title><link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700,300italic,400italic,700italic" /><link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"><style>body,html{width:100%;height:100%}body{font-family:"Source Sans Pro"}.btn-xl{font-size:1ю5rem; padding:1.25rem 2.3rem}.content-section{padding-top:7.5rem;padding-bottom:7.5rem}h1,h2,h3,h4,h5,h6{font-weight:700}.masthead{min-height:30rem;position:relative;display:table;width:100%;height:auto;padding-top:8rem;padding-bottom:8rem;background:linear-gradient(90deg,rgba(255,255,255,.1) 0,rgba(255,255,255,.1) 100%),url(https://blackrockdigital.github.io/startbootstrap-stylish-portfolio/img/bg-masthead.jpg);background-position:center center;background-repeat:no-repeat;background-size:cover}.masthead h1{font-size:4rem;margin:0;padding:0}@media (min-width:992px){.masthead{height:100vh}.masthead h1{font-size:5.5rem}}a{color:#1d809f}a:active,a:focus,a:hover{color:#155d74}.btn-primary{background-color:#1d809f!important;border-color:#1d809f!important;color:#fff!important}.btn-primary:active,.btn-primary:focus,.btn-primary:hover{background-color:#155d74!important;border-color:#155d74!important}.btn{box-shadow:0 3px 3px 0 rgba(0,0,0,.1);font-weight:700}.bg-primary{background-color:#1d809f!important}.text-primary{color:#1d809f!important}.loader-fon {position: fixed;left: 0;top: 0;right: 0;z-index: 9999999999;width: 100%;height: 100%;margin: 0;padding: 0;background-color: rgba(233, 235, 238, 0.50);}.loader {position: fixed;z-index: 99999999999;left: 50%;top: 50%;margin: -50px 0 0 -50px;border: 5px solid #E9EBEE;border-radius: 50%;border-top: 5px solid #7FB800;border-bottom: 5px solid #00A6ED;border-left: 5px solid #FFB400;border-right: 5px solid #F6511D;width: 80px;height: 80px;-webkit-animation: spin 800ms linear infinite;animation: spin 800ms linear infinite;}@-webkit-keyframes spin {0% {-webkit-transform: rotate(0deg);}100% {-webkit-transform: rotate(360deg);}}@keyframes spin {0% {transform: rotate(0deg);}100% {transform: rotate(360deg);}}.load {font-size:2rem;}</style></head><body><header class="masthead d-flex">
<div class="container text-center my-auto">
<h1 class="mb-1">Установка - API Shop</h1>
<h3 class="mb-5"><em>Для загрузки необходимого ПО, нажмите: <strong>Продолжить</strong></em></h3>
<div id="loader-link"><a class="btn btn-primary btn-xl" href="/" onclick="Load();">Продолжить</a></div>
</div>
<div id="loader-div"></div></header></body></html><script src="https://code.jquery.com/jquery-3.2.1.min.js"></script><script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js"></script><script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js"></script><script>function Load(){$("#loader-div").html("<div class=\'loader-fon\'><div class=\'loader-wrapper\'><div class=\'loader\'></div></div></div>");$("#loader-link").html("<div class=\'load\'>Загружаю файлы ...</div>");}</script>
';
 
$file = __DIR__ . "/api-shop.zip";
 
if (!file_exists($file)) {
    file_put_contents($file, file_get_contents("https://github.com/pllano/api-shop/archive/master.zip"));
}
 
// Директория в zip архиве, подлежащая извлечению
$dir = 'api-shop-master';
 
// Место назначения для сохранения извлечённых элементов
$dest = __DIR__;

$zip = new ZipArchiveExtended;
if ($zip->open($file) === true) {
  $res = $zip->extractDirTo($dest, $dir);
  $zip->close();
  if ($res === true) {
    echo $site;
  } else {
    echo 'При извлечении возникли ошибки';
	}
}
 
if (file_exists(__DIR__ . '/install.php') && file_exists(__DIR__ . '/index.php')) {
    unlink(__DIR__ . '/install.php');
}
 
if (file_exists($file)) {
    unlink($file);
}

class ZipArchiveExtended extends ZipArchive
{
  /**
   * Извлекает содержимое директории из zip архива
   *
   * @param string $destination Место назначения для сохранения извлечённых элементов
   * @param string $directory Директория в zip архиве, подлежащая извлечению
   * @return boolean|array Возвращает значение true в случае успешного выполнения операции, либо array, содержащий ошибки извлечения
   */
  public function extractDirTo($destination, $directory)
  {
    $errors = [];
    $destination = str_replace(["/", "\\"], DIRECTORY_SEPARATOR, $destination);
    $directory = str_replace(["/", "\\"], "/", $directory);
    if (substr($destination, mb_strlen(DIRECTORY_SEPARATOR, "UTF-8") * -1) != DIRECTORY_SEPARATOR) {
      $destination .= DIRECTORY_SEPARATOR;
    }
    if (substr($directory, -1) != "/") {
      $directory .= "/";
    }
    for ($i = 0; $i < $this->numFiles; $i++) {
      $filename = $this->getNameIndex($i);
      if (substr($filename, 0, mb_strlen($directory, "UTF-8")) == $directory) {
        $relativePath = substr($filename, mb_strlen($directory, "UTF-8"));
        $relativePath = str_replace(["/", "\\"], DIRECTORY_SEPARATOR, $relativePath);        
        if (mb_strlen($relativePath, "UTF-8") > 0) {
          if (substr($filename, -1) == "/") {
            if (!is_dir($destination . $relativePath))
              if (!@mkdir($destination . $relativePath, 0755, true)) {
                $errors[$i] = $filename;
              }
          } else {
            if (dirname($relativePath) != ".") {
              if (!is_dir($destination . dirname($relativePath))) {
                @mkdir($destination . dirname($relativePath), 0755, true);
              }
            }            
            if (@file_put_contents($destination . $relativePath, $this->getFromIndex($i)) === false) {
              $errors[$i] = $filename;
            }
          }
        }
      }
    }
    return count($errors) > 0 ? $errors : true;
  }
}
 