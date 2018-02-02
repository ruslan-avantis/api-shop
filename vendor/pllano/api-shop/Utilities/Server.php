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
/*
*     memory_get_usage Возвращает количество памяти в байтах, которое было выделено PHP скрипту на на данный момент.
*     memory_get_peak_usage() - Возвращает пиковое значение объема памяти, выделенной PHP
*     memory_limit максимальный размер памяти для выполнения скриптов на PHP
*     Информация в примерах
*     
*     http://rus-linux.net/MyLDP/hard/cpu-details.html
*/
 
namespace ApiShop\Utilities;
 
class Server {

    public function meminfo()
    {
        @exec('cat /proc/meminfo', $meminfo);
        if (isset($meminfo['0'])) {
            //print_r($meminfo);
            $arr['MemTotal'] = $this->format_size(str_replace(['MemTotal:', 'kB', ' '], '', $meminfo['0']));
            $arr['MemFree'] = $this->format_size(str_replace(['MemFree:', 'kB', ' '], '', $meminfo['1']));
            $arr['MemAvailable'] = $this->format_size(str_replace(['MemAvailable:', 'kB', ' '], '', $meminfo['2']));
            $arr['Buffers'] = $this->format_size(str_replace(['Buffers:', 'kB', ' '], '', $meminfo['3']));
            $arr['Cached'] = $this->format_size(str_replace(['Cached:', 'kB', ' '], '', $meminfo['4']));
            $arr['SwapTotal'] = $this->format_size(str_replace(['SwapTotal:', 'kB', ' '], '', $meminfo['14']));
            $arr['SwapFree'] = $this->format_size(str_replace(['SwapFree:', 'kB', ' '], '', $meminfo['15']));
            //print_r($arr);
            return $arr;
        } else {
            return null;
        }
    }
 
    public function cpuinfo()
    {
        @exec('cat /proc/cpuinfo', $cpuinfo);
        if (isset($cpuinfo['0'])) {
            return $cpuinfo;
        } else {
            return null;
        }
    }
 
    public function nproc()
    {
        @exec('nproc', $nproc);
        @exec('cat /proc/cpuinfo | grep ^processor |wc -l', $cpuinfo);
        if (isset($nproc['0']) && isset($cpuinfo['0'])) {
            if ($nproc['0'] <= $cpuinfo['0']) {
                return $cpuinfo['0'];
            } else {
                return $nproc['0'];
            }
        } else {
            return null;
        }
    }
 
    public function format_size($size, $type = 'KB', $text = null)
    {
        if ($type == 'bytes') {$metrics = ['bytes', 'KB', 'MB', 'GB', 'TB'];}
        if ($type == 'KB') {$metrics = ['KB', 'MB', 'GB', 'TB'];}
        if ($type == 'MB') {$metrics = ['MB', 'GB', 'TB'];}
        $metric = 0;
        while(floor($size/1024) > 0){
            ++$metric;
            $size /= 1024;
        }
        if ($text == null) {
            $ret = round($size,2);
        } else {
            $ret = round($size,2)." ".(isset($metrics[$metric])?$metrics[$metric]:'??');
        }
        return $ret;
    }
 
}
 