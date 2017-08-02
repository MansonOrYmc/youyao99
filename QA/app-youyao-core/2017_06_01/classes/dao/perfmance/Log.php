<?php
class Dao_Perfmance_Log {
	private $pdo;
    private $slave_pdo;

    public function __construct() {
        $this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
    }

    public function create_table($time) {
        $date = date("Ym", $time);
        $sql = "
CREATE TABLE IF NOT EXISTS stats_db.log_perf_$date (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `type` varchar(32) DEFAULT NULL,
  `site` varchar(10) DEFAULT NULL,
  `guid` varchar(50) DEFAULT NULL,
  `page_name` varchar(50) DEFAULT NULL,
  `url` varchar(1024) DEFAULT NULL,
  `ip` varchar(15) DEFAULT NULL,
  `country` varchar(6) DEFAULT NULL,
  `region` varchar(32) DEFAULT NULL,
  `city` varchar(32) DEFAULT NULL,
  `as_num` varchar(64) DEFAULT NULL,
  `user_agent` varchar(1024) DEFAULT NULL,
  `navigation_start` bigint(13) DEFAULT NULL,
  `redirect_start` bigint(13) DEFAULT NULL, 
  `redirect_end` bigint(13) DEFAULT NULL,
  `fetch_start` bigint(13) DEFAULT NULL,
  `domain_lookup_start` bigint(13) DEFAULT NULL,
  `domain_lookup_end` bigint(13) DEFAULT NULL,
  `connect_start` bigint(13) DEFAULT NULL,
  `secure_connection_start` bigint(13) DEFAULT NULL,
  `connect_end` bigint(13) DEFAULT NULL,
  `request_start` bigint(13) DEFAULT NULL,
  `response_start` bigint(13) DEFAULT NULL,
  `response_end` bigint(13) DEFAULT NULL,
  `dom_loading` bigint(13) DEFAULT NULL,
  `dom_interactive` bigint(13) DEFAULT NULL,
  `dom_content_loaded_event_start` bigint(13) DEFAULT NULL,
  `dom_content_loaded_event_end` bigint(13) DEFAULT NULL,
  `dom_complete` bigint(13) DEFAULT NULL,
  `load_event_start` bigint(13) DEFAULT NULL,
  `load_event_end` bigint(13) DEFAULT NULL,
  `unload_event_start` bigint(13) DEFAULT NULL,
  `unload_event_end` bigint(13) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `site` (`site`),
  KEY `guid` (`guid`),
  KEY `page_name` (`page_name`),
  KEY `country` (`country`),
  KEY `region` (`region`),
  KEY `city` (`city`),
  KEY `as_num` (`as_num`),
  KEY `created` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
    }

    public function drop_table($time) {
        $date = date("Ym", $time);
        $sql = "DROP TABLE IF EXISTS stats_db.log_perf_$date";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
    }

    public function insert_record(
        $parent_id,
        $type,
        $site,
        $guid,
        $page_name,
        $url,
        $ip,
        $country,
        $region,
        $city,
        $as_num,
        $user_agent,
        $perf_data
    ) {
        $date = date("Ym", time());
        $sql = "INSERT INTO stats_db.log_perf_$date (
                    parent_id, type, site, guid, page_name, url, ip, country, region, city, as_num, user_agent,
                    navigation_start, redirect_start, redirect_end, fetch_start, domain_lookup_start, domain_lookup_end,
                    connect_start, secure_connection_start, connect_end, request_start, response_start, response_end,
                    dom_loading, dom_interactive, dom_content_loaded_event_start, dom_content_loaded_event_end, dom_complete,
                    load_event_start, load_event_end, unload_event_start, unload_event_end
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?
                )";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(
            $parent_id,
            $type,
            $site,
            $guid,
            $page_name,
            $url,
            $ip,
            $country,
            $region,
            $city,
            $as_num,
            $user_agent,
            $perf_data['navigationStart'],
            $perf_data['redirectStart'], 
            $perf_data['redirectEnd'],
            $perf_data['fetchStart'],
            $perf_data['domainLookupStart'],
            $perf_data['domainLookupEnd'],
            $perf_data['connectStart'],
            $perf_data['secureConnectionStart'],
            $perf_data['connectEnd'],
            $perf_data['requestStart'],
            $perf_data['responseStart'],
            $perf_data['responseEnd'],
            $perf_data['domLoading'],
            $perf_data['domInteractive'],
            $perf_data['domContentLoadedEventStart'],
            $perf_data['domContentLoadedEventEnd'],
            $perf_data['domComplete'],
            $perf_data['loadEventStart'],
            $perf_data['loadEventEnd'],
            $perf_data['unloadEventStart'],
            $perf_data['unloadEventEnd']
        ));
        return $this->pdo->lastInsertId();
    }
}
