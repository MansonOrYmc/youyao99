<?php

class Bll_Comment_CommentInfo {
	private $commentInfoDao;

	public function __construct() {
		$this->commentInfoDao = new Dao_Comment_CommentInfo();
        $this->commentInfoMemcache = new Dao_Comment_CommentMemcache();
	}

	public function save_comment_info_by_data($info) {
		$roomID = isset($info['roomID']) ? $info['roomID'] : '';
		$homeID = isset($info['homeID']) ? $info['homeID'] : '';
		$userID = isset($info['userID']) ? $info['userID'] : '';
		$parentCommentID = isset($info['parentCommentID']) ? $info['parentCommentID'] : '';
        $orderID = isset($info['orderID']) ? $info['orderID'] : '';
		$content = isset($info['content']) ? $info['content'] : '';
		$source = isset($info['source']) ? $info['source'] : 0;
		$images = isset($info['images']) ? $info['images'] : "";
		$wholeExpScore = isset($info['wholeExpScore']) ? $info['wholeExpScore'] : 0;
		$conformDescScore = isset($info['conformDescScore']) ? $info['conformDescScore'] : 0;
		$cleanlinessScore = isset($info['cleanlinessScore']) ? $info['cleanlinessScore'] : 0;
		$arrivedScore = isset($info['arrivedScore']) ? $info['arrivedScore'] : 0;
		$communicationScore = isset($info['communicationScore']) ? $info['communicationScore'] : 0;
		$locationScore = isset($info['locationScore']) ? $info['locationScore'] : 0;
		$worthScore = isset($info['worthScore']) ? $info['worthScore'] : 0;
		$isRecommend = isset($info['isRecommend']) ? $info['isRecommend'] : 0;
		$siteRecommendScore = isset($info['siteRecommendScore']) ? $info['siteRecommendScore'] : 0;

		if ($wholeExpScore == 0) {
			$wholeExpScore = 5;
		}

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

		$imagesArray = explode(",", $images);
		$validate_images = 0;
/*
		if (count($imagesArray) > 0) {
			$validate_images = 1;
		}
*/
		if (trim($images)) {
			$validate_images = 1;
		}

		if ($parentCommentID.length <= 0) {
			$dbInfo = array($homeID, $roomID, $userID, $orderID, $content, $source, $wholeExpScore, $conformDescScore, $cleanlinessScore, $arrivedScore, $communicationScore, $locationScore, $worthScore, $isRecommend, $siteRecommendScore, $validate_images, $client_ip);
			$result = $this->commentInfoDao->save_part_comment_info_by_data($dbInfo);
			if($validate_images){
				self::save_comment_images($imagesArray, $result);
            }
			if ($result) {
				return $result; //true;
			}
			return false;
		}else {
			$dbInfo = array($parentCommentID, $homeID, $roomID, $userID, $orderID, $content, $source, $wholeExpScore, $conformDescScore, $cleanlinessScore, $arrivedScore, $communicationScore, $locationScore, $worthScore, $isRecommend, $siteRecommendScore, $validate_images, $client_ip);
			$result = $this->commentInfoDao->save_comment_info_by_data($dbInfo);
			if ($validate_images) {
				self::save_comment_images($imagesArray, $result);
            }
			if ($result) {
				return $result; //true;
			}
			return false;
		}
		
	}

	public function save_comment_images($images, $commentId) {
		foreach ($images as $value) {
			$dbInfo = array($commentId, $value);
			$this->commentInfoDao->save_comment_images($dbInfo);
		}
		return true;
	}

	public function acquire_comment_info_by_data($info) {
		$homeID = isset($info['homeID']) ? (int)$info['homeID'] : 0;
		$real_homeID = isset($info['real_homeID']) ? (int)$info['real_homeID'] : 0;
		$roomID = isset($info['roomID']) ? (int)$info['roomID'] : 0;
		$type = isset($info['type']) ? $info['type'] : 0;
		$pageIndex = isset($info['pageIndex']) ? $info['pageIndex'] : 1;
		$pageSize = isset($info['pageSize']) ? $info['pageSize'] : 20;
		$info['type'] = $type;
		$info['pageIndex'] = $pageIndex;
		$info['pageSize'] = $pageSize;

		if ($homeID > 0 || $real_homeID>0) {
			$commentInfo = $this->commentInfoDao->acquire_comment_info_by_homeID($info);
		}else if ($roomID > 0) {
			$commentInfo = $this->commentInfoDao->acquire_comment_info_by_roomID($info);
		}

        return $commentInfo;
	}

    public function acquire_comment_summary_by_homeid($home_id) {
	    return $this->commentInfoMemcache->acquire_comment_summary_by_homeID($home_id);
    }

    public function acquire_comment_by_orderid($oid)
    {
        return $this->commentInfoDao->get_comment_by_orderid($oid);

    }

    public function acquire_comment_by_orderids($oids) {
        if(!is_array($oids)) $oids = array($oids);
        if(empty($oids)) return ;
        return $this->commentInfoDao->get_comment_by_orderids($oids);
    }

    public function acquire_rooms_comments_statistics_by_hid($hid) {
        $rooms_comments_statistics = $this->commentInfoDao->acquire_rooms_comments_statistics_by_hid($hid);
        foreach($rooms_comments_statistics as $row) {
            $row['whole_exp_avg_formated'] = number_format((float)$row['whole_exp_avg'], 1, '.', '');
            $ret[(string)$row['rid']] = $row;
        }
        return $ret;
    }
}
