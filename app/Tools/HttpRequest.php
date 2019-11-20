<?php
namespace App\Tools;
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/12
 * Time: 9:34
 */
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class HttpRequest
{
    /**
     * 发送网络请求
     * @param string $url 请求地址
     * @param array $headers 请求头信息
     * @param array $data 请求数据
     * @param string $type 请求类型
     * @param string $token 请求认证
     * @param string $cookie 请求cookie
     * @param int $proxy 是否设置代理请求
     * @return bool|mixed 返回
     */
    public static function send($url= '', $headers = [], $data = [],$type = 'POST', $token = '', $cookie = '',$proxy = 1){
        if(!$url) return false;
        $client = new Client();
        $info = [];
        /**
         * 设置请求头
         */
        $info['headers'] = ['Content-type'=> 'application/json;charset=UTF-8'];
        if(!empty($headers)){
            $info['headers'] = array_merge($headers,$info['headers']);
        }
        /**
         * 设置请求参数
         */
        if(!empty($data)){
            $info['json'] = $data;
        }
        /**
         * 设置cookie
         */
        if($cookie){
            $jar = new \GuzzleHttp\Cookie\CookieJar();
            $info['cookies'] = $jar;
        }
        /**
         * 设置认证
         */
        if($token){
            $info['headers'] = array_merge($info['headers'],['AccessToken'=>$token]);
        }
        /**
         * 设置代理
         */
        if($proxy == 1){
            $info['proxy'] = [
                'http'  => 'http://liuhuanguang:123456@54.153.106.16:3128', // http请求方式
                'https' => 'https://liuhuanguang:123456@54.153.106.16:3128', // http请求方式
                'no' => ['.mit.edu', 'foo.com']    // 不需要使用代理的域名
            ];
        }
        $response = $client->request($type,$url,$info);
        if($response->getStatusCode() == 200){
            //请求成功
            return json_decode($response->getBody()->getContents(),true);
        }
        return false;
    }

    /**
     * 发送网络请求2(获取商品详情)
     * @param string $url 请求地址
     * @param array $headers 请求头信息
     * @param array $data 请求数据
     * @param string $type 请求类型
     * @param string $token 请求认证
     * @param string $cookie 请求cookie
     * @param int $proxy 是否设置代理请求
     * @return bool|mixed 返回
     */
    public static function send2($url= '', $headers = [], $data = [],$type = 'POST', $token = '', $cookie = '',$proxy = 1){
        if(!$url) return false;
        $client = new Client();
        $info = [];
        /**
         * 设置请求头
         */
        $info['headers'] = ['Content-type'=> 'application/json;charset=UTF-8'];
        if(!empty($headers)){
            $info['headers'] = array_merge($headers,$info['headers']);
        }
        /**
         * 设置请求参数
         */
        if(!empty($data)){
            $info['json'] = $data;
        }
        /**
         * 设置cookie
         */
        if($cookie){
            $jar = new \GuzzleHttp\Cookie\CookieJar();
            $info['cookies'] = $jar;
        }
        /**
         * 设置认证
         */
        if($token){
            $info['headers']['Cookie'] = "PDDAccessToken={$token}";
        }
        /**
         * 设置代理
         */
        if($proxy == 1){
            $info['proxy'] = [
                'http'  => 'http://liuhuanguang:123456@54.153.106.16:3128', // http请求方式
                'https' => 'https://liuhuanguang:123456@54.153.106.16:3128', // http请求方式
                'no' => ['.mit.edu', 'foo.com']    // 不需要使用代理的域名
            ];
        }
        $response = $client->request($type,$url,$info);
        $html = $response->getBody()->getContents();
        $html = trimall($html);
        $data = get_between($html,"window.rawData=",'}};</script><link');
        $data = $data.'}}';
        $data = json_decode($data,true);
        return $data['store']['initDataObj'];
    }

    /**
     * 原生post请求
     * @param $url //请求链接
     * @param array $params 请求参数
     * @param array $headers 请求头
     * @param text $cookieJar cookie文件
     * @return mixed
     */
    public static function post($url, $params=[], $headers=[], $cookieJar='')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
//        curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC); //代理认证模式
//        curl_setopt($ch, CURLOPT_PROXY, "113.65.232.163"); //代理服务器地址
//        curl_setopt($ch, CURLOPT_PROXYPORT, 12463); //代理服务器端口
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        if ($cookieJar) curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * 原生get请求
     * @param $url //请求链接
     * @param array $headers 请求头
     * @param $cookieJar cookie文件
     * @return mixed
     */
    public static function get($url, $headers=[], $cookieJar='')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        if ($cookieJar) curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public static function postXml($url,$data){

        $header[] = "Content-type: text/xml";      //定义content-type为xml,注意是数组
        $ch = curl_init ($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.186 Safari/537.36');
        curl_setopt($ch,CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);
        if(curl_errno($ch)){
            return false;
        }
        $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        Log::error($httpCode);
        curl_close($ch);
        return $httpCode;
    }
}