<?php
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
 
namespace ApiShop\Database;
 
use ApiShop\Database\PdoDb;
use PDO;
 
class MysqlDb
{
    protected $db;
    private $sort = "id";
    private $order = "DESC";
    private $offset = 0;
    private $limit = 10;
    private $relations = null;
    private $resource = null;
 
    function __construct()
    {
        $this->db = PdoDb::getInstance();
    }
 
    public function get($resource = null, array $arr = array(), $id = null)
    {
        $this->resource = $resource;
        $i=0;
        if ($id >= 1) {
            if (count($arr) >= 1) {
                foreach($arr as $key => $value)
                {
                    if ($key == "relations") {
                        $this->relations = $value;
                    }
                }
            }
            // Формируем запрос к базе данных
            $sql = "
                SELECT * 
                FROM  `".$resource."` 
                WHERE  `".$resource."_id` ='".$id."' 
                LIMIT 1
            ";
        } else {
            $query = "";
            if (count($arr) >= 1) {
                foreach($arr as $key => $value)
                {
                    if ($key == ""){$key = null;}
                    if (isset($key) && isset($value)) {
                        $i=+1;
                        if ($key == "sort") {
                            $this->sort = $value; $i-=1;
                        } if ($key == "order") {
                            $this->order = $value; $i-=1;
                        } if ($key == "offset") {
                            $this->offset = $value; $i-=1;
                        } if ($key == "limit") {
                            $this->limit = $value; $i-=1;
                        } if ($key == "relations") {
                            $this->relations = $value; $i-=1;
                        } else {
                            if ($i == 1) {
                            $query .= "WHERE `".$key."` ='".$value."' ";
                            } else {
                                if (is_int($value)) {
                                    $query .= "AND `".$key."` ='".$value."' ";
                                } else {
                                    $query .= "AND `".$key."` LIKE '%".$value."%' ";
                                }
                            }
                        }
                    }
                }
            }
            // Формируем запрос к базе данных
            $sql = "
                SELECT * 
                FROM `".$resource."` 
                ".$query." 
                ORDER BY `".$this->sort."` ".$this->order." 
                LIMIT ".$this->offset." , ".$this->limit." 
            ";
        }
        // Отправляем запрос в базу
        $stmt = $this->db->dbh->prepare($sql);
        if ($stmt->execute()) {
            // Ответ будет массивом
            $response = array();
            // Получаем ответ в виде массива
            $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // Если ничего не нашли отдаем null
            $response = null;
        }
        // Получаем связи
        if ($this->relations != null) {
            if (count($response) >= 1) {
                foreach($response as $value)
                {
                    $relations = $this->get_relations($value[$resource."_id"]);
                }
            }
            return array_merge($response, $relations);
        } else {
            return $response;
        }
    }
 
    // Создаем одну запись
    public function post($resource = null, array $arr = array())
    {
        // Задаем пустые значения чтобы не выдавало ошибок
        $insert = "";
        $values = "";
        if (count($insert) >= 1) {
            foreach($insert as $key => $unit)
            {
                if ($key == ""){$key = null;}
                if (isset($key) && isset($unit)) {
                    $insert .= ", `".$key."`";
                    $values .= ", '".$unit."'";
                }
            }
        }
        if ($resource != null) {
            // Формируем запрос к базе данных
            $sql = "INSERT INTO `".$resource."` (`id`".$insert.") VALUES ('NULL'".$values.");";
            // Отправляем запрос в базу
            $stmt = $this->db->dbh->prepare($sql);
            if ($stmt->execute()) {
                // Если все ок отдаем id
                $response = $this->db->dbh->lastInsertId();
            } else {
                // Если ничего не нашли отдаем 0
                $response = null;
            }
        } else {
            // Неуказан ресурс
            $response = null;
        }
        // Возвращаем ответ на запрос
        return $response;
    }
 
    // Обновляем
    public function put($resource = null, array $arr = array(), $id = null)
    {
        $this->resource = $resource;
        // Задаем пустое значение $query чтобы не выдавало ошибок
        $query = '';
        // если есть id, тогда в массиве $arr данные для одной записи
        if ($id >= 1) {
            if (count($arr) >= 1) {
                foreach($arr as $key => $value)
                {
                    if ($key == ''){$key = null;}
                    if (isset($key) && isset($value)) {
                        $query .= "`".$key."` ='".$value."' ";
                    }
                }
            }
            // Формируем запрос к базе данных
            $sql = "
                UPDATE `".$resource."` 
                SET ".$query." 
                WHERE `".$resource."_id` =".$id."
            ";
            // Отправляем запрос в базу
            $stmt = $this->db->dbh->prepare($sql);

            if ($stmt->execute()) {
                // Если все ок отдаем 1
                $response = 1;
            } else {
                // Если нет отдаем 0
                $response = null;
            }
        } else {
            $i=0;
            if (count($arr) >= 1) {
                foreach($arr as $item)
                {
                    foreach($item as $key => $value)
                    {
                        if ($key == ""){$key = null;}
                        if (isset($key) && isset($value)) {
                            if ($key == $resource."_id"){
                                $key_id = $key;
                                $id = $value;
                            } else {
                                $query .= "`".$key."` ='".$value."' ";
                            }
                        }
                    }
                    // Формируем запрос к базе данных
                    $sql = "
                        UPDATE `".$resource."` 
                        SET ".$query." 
                        WHERE `".$key_id."` =".$id."
                    ";
                    // Отправляем запрос в базу
                    $stmt = $this->db->dbh->prepare($sql);
                    if ($stmt->execute()) {
                        // Если все ок +1
                        $i+=1;
                    } else {
                        // Если нет +0
                        $i+=0;
                    }
                }
            }
            $response = $i;
        }
        // Возвращаем колличество обновленных записей
        return $response;
    }
 
    // Удаляем
    public function delete($resource = null, array $arr = array(), $id = null)
    {  
        if ($resource != null) {
            if ($id >= 1) {
                // Формируем запрос к базе данных
                $sql = "
                    DELETE 
                    FROM `".$resource."` 
                    WHERE `".$resource."_id` ='".$id."'
                    ";
                // Отправляем запрос в базу
                $stmt = $this->db->dbh->prepare($sql);
                if ($stmt->execute()) {
                    // Если все ок отдаем 1
                    $response = 1;
                } else {
                    // Если нет отдаем null
                    $response = null;
                }
            } else {
                $i=0;
                if (count($arr) >= 1) {
                    foreach($arr as $item)
                    {
                        foreach($item as $key => $value)
                        {
                            if ($key == ""){$key = null;}
                            if (isset($key) && isset($value)) {
                                $key_id = $key;
                                $id = $value;
                            }
                        }
                        // Формируем запрос к базе данных
                        $sql = "
                            DELETE
                            FROM `".$resource."` 
                            WHERE `".$key_id."` =".$id."
                        ";
                        // Отправляем запрос в базу
                        $stmt = $this->db->dbh->prepare($sql);
                        if ($stmt->execute()) {
                            // Если все ок +1
                            $i+=1;
                        } else {
                            // Если нет +0
                            $i+=0;
                        }
                    }
                    $response = $i;
                } else {
                    $response = null;
                }
            }
        } else {
            // Неуказан ресурс
            $response = null;
        }
        // Возвращаем ответ
        return $response;
    }
 
    // count для пагинатора
    public function count($resource = null, array $arr = array(), $id = null)
    {
        $this->resource = $resource;
        $i=0;
        // Приходится делать запрос и при наличии id, так как может отдать null
        if ($id >= 1) {
            // Формируем запрос к базе данных
            $sql = "
                SELECT COUNT(*) 
                FROM  `".$resource."` 
                WHERE  `".$resource."_id` ='".$id."' 
                LIMIT 1
            ";
        } else {
            $query = "";
            if (count($arr) >= 1) {
                foreach($arr as $key => $value)
                {
                    if ($key == ""){$key = null;}
                    if (isset($key) && isset($value)) {
                        $i=+1;
                        if ($key == "sort") {
                            $this->sort = $value; $i-=1;
                        } if ($key == "order") {
                            $this->order = $value; $i-=1;
                        } if ($key == "offset") {
                            $this->offset = $value; $i-=1;
                        } if ($key == "limit") {
                            $this->limit = $value; $i-=1;
                        } if ($key == "relations") {
                            $i-=1;
                        } else {
                            if ($i == 1) {
                            $query .= "WHERE `".$key."` ='".$value."' ";
                            } else {
                                if (is_int($value)) {
                                    $query .= "AND `".$key."` ='".$value."' ";
                                } else {
                                    $query .= "AND `".$key."` LIKE '%".$value."%' ";
                                }
                            }
                        }
                    }
                }
            }
            // Формируем запрос к базе данных
            $sql = "
                SELECT COUNT(*)  
                FROM `".$resource."` 
                ".$query." 
                ORDER BY `".$this->sort."` ".$this->order." 
                LIMIT ".$this->offset." , ".$this->limit." 
            ";
        }
        // Отправляем запрос в базу
        $stmt = $this->db->dbh->prepare($sql);
        if ($stmt->execute()) {
            // Ответ будет массивом
            $response = array();
            // Получаем ответ в виде массива
            $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // Если ничего не нашли отдаем null
            $response = null;
        }
        return $response;
    }
 
    public function get_relations($id)
    {
        if ($this->relations != null) {
            // Декодируем relations в массив
            $relations = json_decode(base64_decode($this->relations), true);
            foreach($relations as $key => $value)
            {
                $arr = $this->get($key, [$this->resource."_id" => $id]);
                if ($value == "all") {
                    $response[$key] = $arr;
                } else {
                    foreach($arr as $keys => $values)
                    {
                        if(in_array($keys, $value, true)){
                            $array[$keys] = $values;
                        }
                    }
                    $response[$key] = $array;
                }
            }
            return $response;
        
        } else {
            return null;
        }
        
    }
 
}
 