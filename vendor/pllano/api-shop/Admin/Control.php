<?php
 
namespace ApiShop\Admin;
 
use ApiShop\Config\Settings;
 
class Control {
    // Проверяем разрешен ли этот тип запроса для данного ресурса
    public function test($resource) {
        $config = (new Settings())->get();
        // Если ресурс активен
		if (isset($config["settings"]["admin"]["resource"][$resource])) {
            if ($config["settings"]["admin"]["resource"][$resource] == true) {
                return true;
            } else {
                return false;
            }
		} else {
            return false;
        }
    }
 
    public function delete($dir)
    {
	   $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delete("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
}
 