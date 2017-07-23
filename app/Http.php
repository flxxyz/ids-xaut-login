<?php

namespace App;

class Http
{
    public $url;
    public $cookie = '';
    protected $user;
    protected $pwd;
    protected $params = [];
    const UserAgent = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36';

    public function __construct($url, $user = '', $pwd = '')
    {
        $this->url = $url;
        $this->user = $user;
        $this->pwd = $pwd;
        //$this->cookie = $cookie;
    }

    public function get()
    {
        $url = $this->url;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        //curl_setopt($ch, CURLOPT_NOBODY, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, self::UserAgent);

        $ob = self::ob($ch);

        $content = self::split($ob['result']);

        //return $content;

        if ($ob['code'] != '404' && $ob['result'])
            $this->params = self::handleParams($content);
        else
            return false;
    }

    protected function handleParams($html)
    {
        $user = $this->user;
        $pwd = $this->pwd;

        preg_match('<input type="hidden" name="lt" value="(\S+)" />', $html, $match);
        $lt = $match[1];
        preg_match('<input type="hidden" name="execution" value="(\S+)" />', $html, $match);
        $execution = $match[1];
        preg_match('<input type="hidden" name="_eventId" value="(\S+)" />', $html, $match);
        $_eventId = $match[1];
        return [
            'username' => $user,
            'password' => $pwd,
            'lt' => $lt,
            'execution' => $execution,
            '_eventId' => $_eventId
        ];
    }

    public function login()
    {
        //$url = 'http://my.xaut.edu.cn/index.portal';
        $url = $this->url;
        $cookie = $this->cookie;
        $params = $this->params;

        $arr = [
            'Host: ids.xaut.edu.cn',
            "User-Agent: " . self::UserAgent,
            //'Referer: ' . $url,
            'Upgrade-Insecure-Requests: 1',
            'Origin: http://ids.xaut.edu.cn',
            'Cache-Control: max-age=0',
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'DNT: 1',
            'Referer: http://ids.xaut.edu.cn/authserver/login',
            'Accept-Encoding: gzip, deflate',
            'Accept-Language: zh-CN,zh;q=0.8,zh-TW;q=0.6,ko;q=0.4,ja;q=0.2,en;q=0.2,en-US;q=0.2',
            'Cookie: ' . $cookie,
            'Connection: keep-alive',
        ];

        //return $arr;

        $account = 'username=' . $params['username'] . '&password=' . $params['password'];
        $param = '&lt=' . $params['lt'] . '&execution=' . $params['execution'];
        $post = $account . $param . '&_eventId=' . $params['_eventId'];

        //return $account;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $arr);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $ob = self::ob($ch);

        list($header, $body) = explode("\r\n\r\n", $ob['result'], 2);
        preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
        $this->cookie = substr($matches[1][1], 1);
        preg_match_all('/Location:([^;]*)\nC/', $header, $match);
        $this->url = trim($match[1][0]);
        //$this->url = 'http://ids.xaut.edu.cn/authserver/login';

        return $this->url;
    }

    public function go($url = '')
    {
        $url = $this->url;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $ob = self::ob($ch);

        list($header, $body) = explode("\r\n\r\n", $ob['result'], 2);
        preg_match_all("/Location:([^;]*)\nC/", $header, $matches);
        $this->url = trim($matches[1][0]);
        preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
        $this->cookie = substr($matches[1][0], 1);

        // 进入系统首页
        $arr = [
            'Host: my.xaut.edu.cn',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
            'User-Agent: ' . self::UserAgent,
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'DNT: 1',
            'Accept-Encoding: gzip, deflate',
            'Accept-Language: zh-CN,zh;q=0.8,zh-TW;q=0.6,ko;q=0.4,ja;q=0.2,en;q=0.2,en-US;q=0.2',
            'Cookie: ' . $this->cookie,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $arr);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $ob = self::ob($ch);

        list($header, $body) = explode("\r\n\r\n", $ob['result'], 2);
        list($header, $body) = explode("\r\n\r\n", $body, 2);

        return $body;
    }

    protected function split($data)
    {
        list($header, $body) = explode("\r\n\r\n", $data, 2);
        preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
        $this->cookie = substr($matches[1][0], 1);
        return $body;
    }

    protected function ob($ch)
    {
        ob_start();
        $result['info'] = curl_getinfo($ch);
        $result['code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result['result'] = curl_exec($ch);
        ob_end_clean();
        curl_close($ch);

        return $result;
    }
}