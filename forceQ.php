<?php
/* 
Author: Albert Lin(灰尘疾客,qq2736550029)
Blog: https://www.gkcoll.xyz
Introduction: 实现各端 QQ 跳转的方案，可一定概率上实现『强制聊天』
Reference: 
- https://github.com/Truimo/php-qq-api
- https://github.com/zhangsheng377/qqchat
- Android APP: Alua手册
- https://w.lho.cc/2449.html
- https://www.jianshu.com/p/fa77c3a9f2ce
- http://www.manongjc.com/detail/61-qydnmbftgwrcoew.html
- https://blog.csdn.net/weixin_43272781/article/details/104380379
Thanks:
- Tester: Lanyu(蓝宇,qq2747155774), ZA(qq2745653202)
*/

function ua()
{
    header('Content-Type:application/json; charset=utf-8');
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']); // 获取小写的用户代理字段

    if (strpos($agent, 'windows nt')) {
        return "win";
    } elseif (strpos($agent, 'iphone')) {
        return "ios";
    } elseif (strpos($agent, 'android')) {
        return "and";
    } else {
        return "win";
    }
}

$visitor = ua();

function jump($url)
{
    // 302 重定向函数
    header("Location:$url");
}

function chat($qq)
{
    // 跳转聊天窗口函数
    global $visitor;
    // 三端跳转 QQ 聊天界面的接口
    $Android_api = "mqqwpa://im/chat?chat_type=wpa&uin=";
    $PC_api = "tencent://message/?uin=";
    $iOS_api = "mqq://im/chat?chat_type=wpa&uin=";

    if ($visitor == "win") {
        jump($PC_api . $qq);
    } elseif ($visitor == "ios") {
        jump($iOS_api . $qq);
    } elseif ($visitor == "and") {
        jump($Android_api . $qq);
    }
}

function card($qq)
{
    // 跳转资料卡函数
    global $visitor;
    // 三端跳转 QQ 资料卡的接口
    $Android_api = "mqqwpa://card/show_pslcard?uin=";
    $PC_api = "tencent://ContactInfo/?subcmd=ViewInfo&uin=";
    $iOS_api = "mqq://card/show_pslcard?uin=";

    if ($visitor == "win") {
        jump($PC_api . $qq);
    } elseif ($visitor == "ios") {
        jump($iOS_api . $qq);
    } elseif ($visitor == "and") {
        jump($Android_api . $qq);
    }

}

function group($qq)
{
    // 关于下面代码有个故事，它们原本是 Python 代码，被我写出来后再用火爆如今的 AI 界巨作 —— ChatGPT 翻译/重构成 PHP 代码。
    // 无论什么语言版本，它们都是用于免 idkey （机器爬虫查询）加 QQ 群的
    $query_api = "https://qun.qq.com/proxy/domain/shang.qq.com/wpa/g_wpa_get";
    $t = strval(time()).strval(intval(microtime()*1000)); // 要求的时间戳为包括小数点后三位的整数

    $headers = ["referer" => "https://qun.qq.com/proxy.html?callback=1&id=1"]; // 头部信息（溯源为必要）
    $query_url = $query_api . "?guin=" . $qq . "&t=" . $t; // 拼接查询地址

    // echo $query_url;
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $query_url,
        CURLOPT_REjumpTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => $headers,
        // CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'], // 此行为灰尘疾客所写
    ]);
    $response = curl_exec($curl);
    curl_close($curl);

    $data = json_decode($response, true); // 获取到返回数据
    $group_url = "http://shang.qq.com/wpa/qunwpa?idkey=" . $data['result']['data'][0]['key']; // 拼接要跳转的链接

    jump($group_url);
}

function main()
{
    $qq = $_GET["qq"]; // 获取QQ
    $type = $_GET["type"]; // 获取跳转方式

    if (empty($qq) and empty($type)) {
        echo "<script>alert('未传入任何参数')</script>";
        echo "<div align = center><h2>Document</h2></div>";
    } else {
        if (empty($qq)) {
            echo json_encode(array('code' => 201, 'CNmsg' => '必要参数 (qq) 不能为空！', 'ENmsg' => 'The required parameter (qq) cannot be null!'), 480);
            echo "<script>alert('Warning!!! 请传入参数 qq 的值!!! 示例值: 2736550029')</script>";
        } elseif (!preg_match("/^[1-9]\d{4,10}$/", $qq)) {
            echo json_encode(array('code' => 202, 'CNmsg' => 'QQ格式不正确', 'ENmsg' => 'format of QQ is not invalid!'), 480);
            echo "<script>alert('Warning!!! QQ 号格式不正确!!!')</script>";
        } else {
            if ($type == "card") {
                // 跳转 QQ 资料卡
                card($qq);
            } elseif ($type == "chat") {
                // 跳转 QQ 聊天窗口，必须已加好友或开启临时会话
                chat($qq);
            } elseif ($type == "group") {
                // 跳转到 QQ 群资料卡（未加）或聊天界面（已加），支持免 idkey
                group($qq);
            } else {
                echo json_encode(array('code' => 203, 'CNmsg' => '没有指定跳转类型', 'ENmsg' => 'No jump type specified!'), 480);
                echo "<script>alert('Warning!!! 请传入参数 type 的值!!! 可用有效值：card;chat;group')</script>";
            }
        }
    }
}
main();

?>