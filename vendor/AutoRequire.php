<?php
/**
 * This file is part of the AutoRequire
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/pllano/auto-require
 * @version 1.0.1
 * @package pllano/auto-require
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * An example of a general-purpose implementation that includes the optional
 * functionality of allowing multiple base directories for a single namespace
 * prefix.
 *
 * Given a foo-bar package of classes in the file system at the following
 * paths ...
 *
 *     /path/to/packages/foo-bar/
 *         src/
 *             Baz.php             # Foo\Bar\Baz
 *             Qux/
 *                 Quux.php        # Foo\Bar\Qux\Quux
 *         tests/
 *             BazTest.php         # Foo\Bar\BazTest
 *             Qux/
 *                 QuuxTest.php    # Foo\Bar\Qux\QuuxTest
 *
 * ... add the path to the class files for the \Foo\Bar\ namespace prefix
 * as follows:
 *
 *      <?php
 *      // instantiate the loader
 *      $loader = new \Example\Psr4AutoloaderClass;
 *
 *      // register the autoloader
 *      $loader->register();
 *
 *      // register the base directories for the namespace prefix
 *      $loader->addNamespace('Foo\Bar', '/path/to/packages/foo-bar/src');
 *      $loader->addNamespace('Foo\Bar', '/path/to/packages/foo-bar/tests');
 *
 * The following line would cause the autoloader to attempt to load the
 * \Foo\Bar\Qux\Quux class from /path/to/packages/foo-bar/src/Qux/Quux.php:
 *
 *      <?php
 *      new \Foo\Bar\Qux\Quux;
 *
 * The following line would cause the autoloader to attempt to load the
 * \Foo\Bar\Qux\QuuxTest class from /path/to/packages/foo-bar/tests/Qux/QuuxTest.php:
 *
 *      <?php
 *      new \Foo\Bar\Qux\QuuxTest;
 */
 
namespace Pllano\AutoRequire;
 
class Autoloader
{
    /**
     * An associative array where the key is a namespace prefix and the value
     * is an array of base directories for classes in that namespace.
     *
     * @var array
     */
    protected $prefixes = [];
    protected $replace_name = null;
    protected $base_dir = null;
    // Ссылка на резервный файл auto_require.json
    protected $json_get = "https://raw.githubusercontent.com/pllano/auto-require/master/auto_require.json";
    protected $repository = "https://raw.githubusercontent.com/pllano/auto-require/master/repository";
    private $dir = null;
    private $json = null;
 
    public function run($dir = null, $json = null, $json_get = null)
    {
        if (isset($dir) && isset($json)) {
 
            $this->dir = $dir;
            $this->json = $json;
 
            if (!file_exists($this->dir)) {
                mkdir($this->dir, 0777, true);
            }
            if (isset($json_get)) {
                $this->json_get = $json_get;
            }
            if (!file_exists($this->json) && isset($this->json_get)) {
                file_put_contents($this->json, file_get_contents($this->json_get));
            }
 
            $require = [];
            // Открываем файл json с параметрами класов
            $data = $this->get();
 
            // Обновляем пакеты
            if (isset($data["update"])) {
                if (count($data["update"]) >= 1) {
                    // Перебираем массив
                    foreach($data["update"] as $value)
                    {
                        if (isset($value["vendor"]) && isset($value["name"])) {
                            // Если папки пакета нет необходимо скачать файлы
                            if (!file_exists($this->dir."/".$value["vendor"].'/'.$value["name"])) {
                                // Если есть ссылка скачиваем архив
                                if (isset($value["link"])) {
                                    $this->load($value["link"], $this->dir, $value["name"], $value["vendor"], $value["version"]);
                                }
                            } else {
                                // Если папка пакета есть - перезаписываем
                                if (isset($value["link"])) {
                                     // Получаем данные существующего пакета
                                     $one = getOne($name);
                                     // Сравниваем версию, если у нас версия выше, удаляем старые файлы и скачиваем новые
                                     if (version_compare($one["version"], $value["version"], '<')) {
                                        $this->delete($this->dir."/".$value["vendor"].'/'.$value["name"]);
                                        $this->load($value["link"], $this->dir, $value["name"], $value["vendor"], $value["version"]);
                                     }
                                 }
                            }
                        }
                    }
                }
            }
 
            // Устанавливаем пакеты
            if (isset($data["require"])) {
                if (count($data["require"]) >= 1) {
                    // Перебираем массив
                    foreach($data["require"] as $key => $value)
                    {
                        if (isset($value["vendor"]) && isset($value["name"])) {
                            if ($value["state"] != '0' && $value["state"] != '') {
                                // Если папки класса нет необходимо скачать файлы
                                if (!file_exists($this->dir."/".$value["vendor"].'/'.$value["name"])) {
                                // Если есть ссылка скачиваем архив
                                    if (isset($value["link"])) {
                                        $this->load($value["link"], $this->dir, $value["name"], $value["vendor"], $value["version"]);
                                    }
                                }
                            }
                        }
                        $require[] = $value;
                    }
                }
            }
 
            // register the autoloader
            $this->register();
 
            if (count($require) >= 1) {
                foreach($require as $value)
                {
                    if (isset($value["state"])) {
                    if ($value["state"] != '0' && $value["state"] != '') {
                        if (isset($value["files"]) && isset($value["dir"])) {
                            // Подключаем файл если этого требует пакет
                            require $this->dir.''.$value["dir"].'/'.$value["files"];
                        }
                        if (isset($value["autoloading"]) && isset($value["replace_name"]) && isset($value["dir"])) {
                            if ($value["autoloading"] == "psr-0") {
                                // Регистрируем базовый каталог и префикс пространства имен PSR-0
                                $this->setAutoloading($value["replace_name"], $this->dir.''.$value["dir"]);
                            }
                        } elseif (isset($value["namespace"]) && isset($value["dir"])) {
                            // Регистрируем базовый каталог и префикс пространства имен PSR-4
                            // register the base directories for the namespace prefix
                            $this->addNamespace($value["namespace"], $this->dir.''.$value["dir"]);
                        }
                    }
                    }
                }
            }
 
        } else {
            return null;
        }
    }
 
    public function load($link, $dir, $name, $vendor, $version)
    {
        file_put_contents($dir.'/'.$name.".zip", file_get_contents($link));
        // Подключаем архиватор
        $zip = new \ZipArchive;
        $res = $zip->open($dir.'/'.$name.".zip");
        if ($res === TRUE) {
            $zip->extractTo($dir."/".$vendor);
            $zip->close();
            rename($dir."/".$vendor.'/'.$name."-".$version,
            $dir."/".$vendor.'/'.$name);
            unlink($dir.'/'.$name.".zip");
        }
    }
 
    public function delete($dir)
    {
       $files = array_diff(scandir($dir), ['.','..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delete("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
 
    public function set_dir($dir = null)
    {
        if (isset($dir)) {
            $this->dir = $dir;
        }
    }
    
    public function set_json($json = null)
    {
        if (isset($json)) {
            $this->json = $json;
        }
    }
    
    public function set_json_get($json_get = null)
    {
        if (isset($json_get)) {
            $this->json_get = $json_get;
        }
    }
 
    // Загрузка и преобразование в массив файла auto_require.json
    public function get()
    {
        if (file_exists($this->json)) {
            return json_decode(file_get_contents($this->json), true);
        } else {
            return null;
        }
    }
 
    public function getOne($name = null)
    {
        $return = null;
        if (isset($name)) {
            $data = $this->get();
            $param['require'] = [];
            foreach($data['require'] as $key => $val)
            {
                if (strtolower($name) == strtolower($val['name'])) {
                    $return = $val;
                }
            }
        }
        return $return;
    }
 
    // Проверяем существавание класса в файле
    public function exists($name = null)
    {
        if (isset($name)) {
            $data = $this->get();
            foreach($data['require'] as $key => $val)
            {
                if (strtolower($val['name']) == strtolower($name)) {
                    return $val['state'];
                } else {
                    return null;
                }
            }
        } else {
            return null;
        }
    }
 
    public function state($name = null, $state = null)
    {
        $return = false;
        if (isset($name) && isset($state)) {
            $data = $this->get();
            $param['require'] = [];
            foreach($data['require'] as $key => $val)
            {
                if (strtolower($name) == strtolower($val['name'])) {
                    $val['state'] = $state;
                    $param['require'][$key] = $val;
                    $return = true;
                }
            }
 
            $arr = array_replace_recursive($data, $param);
            $newArr = json_encode($arr);
            file_put_contents($this->json, $newArr);
        }
 
        return $return;
 
    }
 
    /**
     * Register loader with SPL autoloader stack.
     *
     * @return void
    */
    public function register()
    {
        //spl_autoload_register([$this, 'autoload']);
        spl_autoload_register([$this, 'loadClass']);
 
        // Лекарство для Twig который работает с пространсвом имен PSR-0
        spl_autoload_register(function ($class) {
            // project-specific namespace prefix
            $prefix = $this->replace_name;
            // base directory for the namespace prefix
            $base_dir = $this->base_dir;
            // does the class use the namespace prefix?
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                // no, move to the next registered autoloader
                return;
            }
            // get the relative class name
            $relative_class = substr($class, $len);
            // replace the namespace prefix with the base directory, replace namespace
            // separators with directory separators in the relative class name, append
            // with .php
            $file = $base_dir . str_replace('_', '/', $relative_class) . '.php';
            // if the file exists, require it
            if (file_exists($file)) {
                require $file;
            }
        });
    }
    
    public function unregister()
    {
        spl_autoload_unregister([$this, 'loadClass']);
    }
    
    public function setAutoloading($replace_name, $base_dir)
    {
        $this->replace_name = $replace_name;
        $this->base_dir = $base_dir;
    }
 
    /**
     * Adds a base directory for a namespace prefix.
     *
     * @param string $prefix The namespace prefix.
     * @param string $base_dir A base directory for class files in the
     * namespace.
     * @param bool $prepend If true, prepend the base directory to the stack
     * instead of appending it; this causes it to be searched first rather
     * than last.
     * @return void
     */
    public function addNamespace($prefix, $base_dir, $prepend = false)
    {
        // normalize namespace prefix
        $prefix = trim($prefix, '\\') . '\\';

        // normalize the base directory with a trailing separator
        $base_dir = rtrim($base_dir, DIRECTORY_SEPARATOR) . '/';

        // initialize the namespace prefix array
        if (isset($this->prefixes[$prefix]) === false) {
            $this->prefixes[$prefix] = [];
        }

        // retain the base directory for the namespace prefix
        if ($prepend) {
            array_unshift($this->prefixes[$prefix], $base_dir);
        } else {
            array_push($this->prefixes[$prefix], $base_dir);
        }
    }

    public function autoload($className)
    {
        $className = ltrim($className, '\\');
        $fileName  = '';
        $namespace = '';
        if ($lastNsPos = strrpos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
 
        require $fileName;
    }
 
    /**
     * Loads the class file for a given class name.
     *
     * @param string $class The fully-qualified class name.
     * @return mixed The mapped file name on success, or boolean false on
     * failure.
     */
    public function loadClass($class)
    {
        // the current namespace prefix
        $prefix = $class;

        // work backwards through the namespace names of the fully-qualified
        // class name to find a mapped file name
        while (false !== $pos = strrpos($prefix, '\\')) {

            // retain the trailing namespace separator in the prefix
            $prefix = substr($class, 0, $pos + 1);

            // the rest is the relative class name
            $relative_class = substr($class, $pos + 1);

            // try to load a mapped file for the prefix and relative class
            $mapped_file = $this->loadMappedFile($prefix, $relative_class);
            if ($mapped_file) {
                return $mapped_file;
            }

            // remove the trailing namespace separator for the next iteration
            // of strrpos()
            $prefix = rtrim($prefix, '\\');
        }

        // never found a mapped file
        return false;
    }
 
    /**
     * Load the mapped file for a namespace prefix and relative class.
     *
     * @param string $prefix The namespace prefix.
     * @param string $relative_class The relative class name.
     * @return mixed Boolean false if no mapped file can be loaded, or the
     * name of the mapped file that was loaded.
     */
    protected function loadMappedFile($prefix, $relative_class)
    {
        // are there any base directories for this namespace prefix?
        if (isset($this->prefixes[$prefix]) === false) {
            return false;
        }

        // look through base directories for this namespace prefix
        foreach ($this->prefixes[$prefix] as $base_dir) {

            // replace the namespace prefix with the base directory,
            // replace namespace separators with directory separators
            // in the relative class name, append with .php
            $file = $base_dir
                  . str_replace('\\', '/', $relative_class)
                  . '.php';

            // if the mapped file exists, require it
            if ($this->requireFile($file)) {
                // yes, we're done
                return $file;
            }
        }

        // never found it
        return false;
    }

    /**
     * If a file exists, require it from the file system.
     *
     * @param string $file The file to require.
     * @return bool True if the file exists, false if not.
     */
    protected function requireFile($file)
    {
        if (file_exists($file)) {
            require $file;
            return true;
        }
        return false;
    }
}
 