<?php

/**
 * rpc 客户端
 * Class RpcClient
 */
class RpcClient {
    protected $urlInfo = array();

    /**
     * 解析url
     * RpcClient constructor.
     * @param $url
     */
    public function __construct($url) {
        $this->urlInfo = parse_url($url);
        if(!$this->urlInfo) {
            exit("{$url} error \n");
        }
    }

    /**
     * 远程调用
     * @param $method
     * @param $params
     * @return string
     */
    public function __call($method, $params) {
        //创建一个客户端
        $client = stream_socket_client("tcp://{$this->urlInfo['host']}:{$this->urlInfo['port']}", $errno, $errstr);
        if (!$client) {
            exit("{$errno} : {$errstr} \n");
        }
        //采用json格式进行通讯
        $proto=array(
            //传递调用的类名
            'class'=>basename($this->urlInfo['path']),
            //传递调用的方法名
            'method'=>$method,
            //传递方法的参数
            'params'=>$params,
        );

        $protoData=json_encode($proto);

        //发送自定义的协议数据
        fwrite($client, $protoData);
        //读取服务端回传数据
        $data = fread($client, 2048);
        //关闭客户端
        fclose($client);
        return $data;
    }
}

$cli = new RpcClient('http://0.0.0.0:8000/User');
$cli->test().PHP_EOL;
$cli->getUserInfo(array('name' => '张三', 'age' => 27));
