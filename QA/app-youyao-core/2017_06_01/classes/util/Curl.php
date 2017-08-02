<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 15/8/18
 * Time: 下午4:13
 */
define('UPLOAD_IMG','http://up.youyao.com/upload');
class Util_Curl {
    const JSON_CONTENT_TYPE = "application/json;charset=\"utf-8\"";
    const JSON_PATTERN = '/^(?:application|text)\/(?:[a-z]+(?:[\.-][0-9a-z]+){0,}[\+\.]|x-)?json(?:-[a-z]+)?/i';
    const XML_PATTERN = '~^(?:text/|application/(?:atom\+|rss\+)?)xml~i';

   static function upload_curl_pic($file)
    {
        $url  = UPLOAD_IMG;
        $fields['my_field'] = '@'.$file;
        if (function_exists('curl_file_create')) {
            $cfile = curl_file_create($file);
            $fields['my_field']=$cfile;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url );
        curl_setopt($ch, CURLOPT_POST, 1 );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields );
        ob_start();
        curl_exec($ch);
        $result = ob_get_contents();
        ob_end_clean();
        curl_close($ch);
        return $result;
    }


    static function http_get_data($url) {

        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt ( $ch, CURLOPT_URL, $url );
        ob_start ();
        curl_exec ( $ch );
        $return_content = ob_get_contents ();
        ob_end_clean ();

        $return_code = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
        return $return_content;
    }

    public static function post($url, $data = array(), $headers = array(),$auth=array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POST, true);
        if ($curlHeader = self::build_curl_header($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeader);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, self::build_post_data($data, $headers));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if(is_array($auth) && !empty($auth)){
            curl_setopt($ch, CURLOPT_USERPWD,$auth['user'].":".$auth['pass']);
        }
        $output = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        return array('code' => $responseCode, 'content' => $output);
    }

    public static function get($url, $data = array(), $headers = array())
    {
        $ch = curl_init();
//        echo self::build_get_url($url, $data).PHP_EOL;
        curl_setopt($ch, CURLOPT_URL, self::build_get_url($url, $data));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setOpt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // 尝试连接时间
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); // 总时间
        if(strpos($url, 'https') !== false){
            //  不验证 ca
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            //curl_setopt($ch, CURLOPT_SSLVERSION, 3); //  php 文档 你最好别设置这个值，让它使用默认值。 设置为 2 或 3 比较危险，在 SSLv2 和 SSLv3 中有弱点存在。
//    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
//    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
//    curl_setopt($ch, CURLOPT_CAINFO, CORE_PATH. "classes/includes/httpscacert/cacert.pem");//证书地址
        }
        if ($curlHeader = self::build_curl_header($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeader);
        }
        $output = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($responseCode != 200) {
            $error = curl_error($ch);
        }

        curl_close($ch);
        return array('code' => $responseCode, 'content' => $output, 'error'=> $error );
    }

    public static function build_curl_header($headers = array())
    {
        if (empty($headers)) return false;

        $output = array();
        foreach ($headers as $key => $value) {
            $output[] = $key . ': ' . $value;
        }
        return $output;
    }

    //////////////////////////////////////////////////////////////////////
    //
    // following borrow from php-curl-class
    // https://github.com/php-curl-class/php-curl-class
    //
    //////////////////////////////////////////////////////////////////////

    public static function build_get_url($url, $data = array())
    {
        return $url . (empty($data) ? '' : '?' . http_build_query($data));
    }

    public static function build_post_data($data, $headers)
    {
        if (is_array($data)) {
            if (self::is_array_multidim($data)) {
                if (isset($headers['Content-Type']) &&
                    preg_match(self::JSON_PATTERN, $headers['Content-Type'])) {
                    $json_str = json_encode($data);
                    if (!($json_str === false)) {
                        $data = $json_str;
                    }
                } else {
                    $data = self::http_build_multi_query($data);
                }
            } else {
                $binary_data = false;
                foreach ($data as $key => $value) {
                    // Fix "Notice: Array to string conversion" when $value in curl_setopt($ch, CURLOPT_POSTFIELDS,
                    // $value) is an array that contains an empty array.
                    if (is_array($value) && empty($value)) {
                        $data[$key] = '';
                        // Fix "curl_setopt(): The usage of the @filename API for file uploading is deprecated. Please use
                        // the CURLFile class instead". Ignore non-file values prefixed with the @ character.
                    } elseif (is_string($value) && strpos($value, '@') === 0 && is_file(substr($value, 1))) {
                        $binary_data = true;
                        if (class_exists('CURLFile')) {
                            $data[$key] = new \CURLFile(substr($value, 1));
                        }
                    } elseif ($value instanceof \CURLFile) {
                        $binary_data = true;
                    }
                }

                if (!$binary_data) {
                    if (isset($headers['Content-Type']) &&
                        preg_match(self::JSON_PATTERN, $headers['Content-Type'])) {
                        $json_str = json_encode($data);
                        if (!($json_str === false)) {
                            $data = $json_str;
                        }
                    } else {
                        $data = http_build_query($data, '', '&');
                    }
                }
            }
        }
        return $data;
    }

    public static function is_array_multidim($array)
    {
        if (!is_array($array)) {
            return false;
        }

        return (bool)count(array_filter($array, 'is_array'));
    }

    public static function is_array_assoc($array)
    {
        return (bool)count(array_filter(array_keys($array), 'is_string'));
    }

    public static function http_build_multi_query($data, $key = null)
    {
        $query = array();

        if (empty($data)) {
            return $key . '=';
        }

        $is_array_assoc = self::is_array_assoc($data);

        foreach ($data as $k => $value) {
            if (is_string($value) || is_numeric($value)) {
                $brackets = $is_array_assoc ? '[' . $k . ']' : '[]';
                $query[] = urlencode($key === null ? $k : $key . $brackets) . '=' . rawurlencode($value);
            } elseif (is_array($value)) {
                $nested = $key === null ? $k : $key . '[' . $k . ']';
                $query[] = self::http_build_multi_query($value, $nested);
            }
        }

        return implode('&', $query);
    }
}

