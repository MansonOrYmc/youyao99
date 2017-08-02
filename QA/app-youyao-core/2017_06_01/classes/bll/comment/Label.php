<?php
class Bll_Comment_Label {

    public function get_label_list_byrid($id, $type='room'){

        if($type=='room') {
            $type_id = 0;
        }elseif($type=='homestay'){
            $type_id = 1;
        }
        $path = "/showLabel";
        $params = array(
            'id'   => $id,
            'type' => $type_id,
        );

        $labels = self::set_request($path, $params, "GET");
        $result = array();
        foreach($labels as $row) {
            $result[] = array(
                'lid'    => $row['id'],
                'name'   => $row['name'],
                'number' => $row['number'],
            );
        }
        return $result ? $result : array();

    }

    public function all_label($uid) {
    }

    public function set_comment_label($comment_id, $rid, $labels, $new_labels = array()) {

        $path = "/add";
        $params = array(
            'rid' => $rid,
            'commentId' => $comment_id,
            'labelNames' => array_merge(
                $labels,
                $new_labels
            ),
        );

        return self::set_request($path, $params, "POST");
    }

    public function get_comment_label_bycoomentid($ids) {

        if(empty($ids)) return array();
        $path = "/showCommentLabel";
        $params = array(
            'commentId' => json_encode($ids),
        );

        $data = self::set_request($path, $params, "GET");
        $result = array();
        foreach($data as $row) {
            $result[$row['commentId']] = array_map(function($v){
                return array(
                    'lid'  => $v['id'],
                    'name' => $v['name'],
                );
            }, $row['labels']);
        }
        return $result;
    }

    private function set_request($path, $data, $type) {

        $java_host = APF::get_instance()->get_config("comment_label_api") . "/label";
        $url = $java_host . $path;

//        Logger::info(__FILE__, __CLASS__, __LINE__, var_export($url, true));
//        Logger::info(__FILE__, __CLASS__, __LINE__, var_export($data, true));
        if($type == 'POST') {
            $response = Util_Curl::post($url, json_encode($data), array("Content-Type"=>"application/json;"));
        }
        elseif($type == 'GET') {
            $response = Util_Curl::get($url, $data);
        }
        if($response['code'] != 200) {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($response, true));
        }
        $result = json_decode($response['content'], true);
        if($result['code'] != 200) {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($result, true));
        }

        return $result['info'];
    }

}
