<?php
/**
 * Created by IntelliJ IDEA.
 * User: ilanyu
 * Blog: http://www.lanyus.com/
 * E-mail: lanyu19950316@gmail.com
 * Date: 2015/8/20
 * Time: 3:30
 */
set_time_limit(0);
ignore_user_abort(true);

function getDNSFromRemote($content)
{
//    $fp = fsockopen("udp://8.8.8.8",53,$errno,$errstr,5); //for udp
    $fp = fsockopen("tcp://8.8.8.8",53,$errno,$errstr,5);
//    fwrite($fp,$content); //for udp
    fwrite($fp,pack('n*',strlen($content)) . $content);
    $dns = stream_get_contents($fp,4096,2);
    fclose($fp);
    return $dns;
}

function getDomain($str,$type = "0001")
{
    $res = unpack("H*",$str)[1];
    $resArray = str_split($res,2);
    $domain = "";
    $i = 12;
    while ($resArray[$i] != "00")
    {
        $len = hexdec($resArray[$i]);
        for ($j = $i + 1; $j <= $i + $len; $j++)
        {
            $domain .= chr('0x' . $resArray[$j]);
        }
        $domain .= ".";
        $i = $i + $len + 1;
    }
    if ($resArray[$i+1] . $resArray[$i+2] == $type)
    {
        return substr($domain,0,strlen($domain)-1);
    }
    return false;
}

$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP); //TODO 这里需要if判断是否成功
socket_bind($socket,"0.0.0.0",53); //TODO 这里需要if判断是否成功
while (true)
{
    socket_recvfrom($socket,$str,1024,0,$address,$port);
//    $domain = getDomain($str);
    $dns = getDNSFromRemote($str);
    socket_sendto($socket,$dns,strlen($dns),0,$address,$port);

    if (file_exists("./stop"))
    {
        socket_close($socket);
        exit;
    }
}
