<?php
class Util_NetWorkAddress {

	public static function get_client_ip() {
		if(!empty($_SERVER["HTTP_CLIENT_IP"])){
			$client_ip = $_SERVER["HTTP_CLIENT_IP"];
		}
		elseif(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
			$client_ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}
		elseif(!empty($_SERVER["REMOTE_ADDR"])){
			$client_ip = $_SERVER["REMOTE_ADDR"];
		}
		else{
			$client_ip = "127.0.0.1";
		}
		return $client_ip;
	}

	public static function obtain_cityname_by_ip($onlineip) {

	    $socket = @socket_create(AF_UNIX, SOCK_STREAM, 0);
	    if (!$socket) {
	        return '';
	    }
	    $ret = @socket_connect($socket, '/tmp/qqwry.sock');
	    if (!$ret) {
	        return '';
	    }
	    $ret = @socket_write($socket, "$onlineip\n");
	    if (!$ret) {
	        @socket_close($socket);
	        return '';
	    }
	    $ret = @socket_read($socket, 256);
	    @socket_close($socket);
	    if (!$ret) {
	        return '';
	    }

	    if (!$ret) {
	        return '';
	    }
	    list($location, $isp) = split('/',$ret);
	    if($location == "局域网") {
	       $location = "";
	    }

	    return $location;
	}

}
?>