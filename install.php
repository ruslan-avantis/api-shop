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
 
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
 
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
    echo 'Все ок ! <a href="/">Перейти на главную</a>';
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
    $errors = array();

    $destination = str_replace(array("/", "\\"), DIRECTORY_SEPARATOR, $destination);
    $directory = str_replace(array("/", "\\"), "/", $directory);

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
        $relativePath = str_replace(array("/", "\\"), DIRECTORY_SEPARATOR, $relativePath);        
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
 
/*
if (!file_exists(__DIR__ . "/index.php")) {
 
    file_put_contents(__DIR__ . "/api-shop.zip", file_get_contents("https://github.com/pllano/api-shop/releases/download/1.0.1/api-shop.zip"));
 
    $zip = new \ZipArchive;
    $res = $zip->open(__DIR__ . "/api-shop.zip");
 
    if ($res === TRUE) {
 
        $zip->extractTo(__DIR__ . "/");
        $zip->close();
 
        echo 'Все ок ! <a href="/">Перейти на главную</a>';
 
    } else {
        echo 'failed';
    }
 
	unlink(__DIR__ . '/api-shop.zip');
 
}
*/