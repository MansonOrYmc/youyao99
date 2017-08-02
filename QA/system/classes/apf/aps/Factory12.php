<?php

apf_require_class("APF_APS_Client12");

/**
 * 通过 Factory 获取 APS Client 实例。
 *
 * 使用方法：
 *
 * ```
 *  $client = APF_APS_Factory12::get_instance()->get_client('ip2city');
 *
 *  $handle1 = $client->start_request('ip2city', ['180.166.126.90'], 0,
 *      function($result, $status, $extras) {
 *          if ($status == 200) {
 *              $this->request->set_attribute('city1_name', $result['name']);
 *              $this->request->set_attribute('city1_pinyin', $result['pinyin']);
 *      }
 *  });
 *  $handle2 = $client->start_request('ip2city', ['114.80.230.198']);
 *
 *  $replies = APF_APS_Client12::wait_for_replies();
 *  $reply = @$replies[$handle2];
 *  if ($reply && $reply->status == 200) {
 *      $this->request->set_attribute('city2_name', $reply->result['name']);
 *      $this->request->set_attribute('city2_pinyin', $reply->result['pinyin']);
 *  }
 * ```
 */
class APF_APS_Factory12
{
    /**
     * @return APF_APS_Factory12
    */
    public static function get_instance() {
        if (!self::$instance) {
            self::$instance = new APF_APS_Factory12();
        }
        return self::$instance;
    }

    private static $instance;


    /**
     * 通过读取 APF 的配置文件，获取 APS_Client 实例。返回的 Client 已经链接，可以直接使用。
     *
     * ```
     * config/aps.php
     * config['ip2city'] = array (
     *   'spID' = ...
     *   'spVer' = ...
     *   'endpoints' = array ('...')
     *   'linger' = ...
     *   'sndhwm' = ...
     *   'rcvhwm' = ...
     * );
     * ```
     *
     * @return APF_APS_Client12
     */
    public function get_client($configName)
    {
        $config = APF::get_instance()->get_config($configName, 'aps');
        if (empty($config)) {
            return null;
        }

        if (!isset($this->_clients[$configName])) {
            $spID = $config['spID'];
            $spVer = isset($config['spVer']) ? $config['spVer'] : null;
            $sender = defined('MACHINE_NAME') ? MACHINE_NAME : null;
            $linger = isset($config['linger']) ? $config['linger'] : 100;
            $sndhwm = isset($config['sndhwm']) ? $config['sndhwm'] : 1000;
            $rcvhwm = isset($config['rcvhwm']) ? $config['rcvhwm'] : 1000;
            $client = new APF_APS_Client12($spID, $spVer, $sender,
                                           $linger, $sndhwm, $rcvhwm);

            $endpoints = $config['endpoints'];
            foreach ($endpoints as $endpoint) {
                $client->connect($endpoint);
            }

            $this->_clients[$configName] = $client;
        }

        return $this->_clients[$configName];
    }

    private $_clients = array();
}
