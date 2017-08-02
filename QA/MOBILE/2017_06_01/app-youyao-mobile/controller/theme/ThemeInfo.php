<?php
apf_require_class("APF_Controller");

class Theme_ThemeInfoController extends APF_Controller {

	public function handle_request() {

		header("Content-type: application/json");

		$req = APF::get_instance()->get_request();
		$params = $req->get_parameters();

		$data = Theme_RecommController::warpInitData();
		$action = $params['action'];
		if ($action == 'home') {
			$results = array();
			$banner2 = array('image'=>'http://zzkimg1.qiniudn.com/daijinjuan-ios-app-176-1.jpg-homepic800x600.jpg',
								'title'=>'注册会员即送代金券',
								'type'=>'1',
								'url'=>'http://taiwan.youyao.com/weixin/promotion.php');
			//array_push($results, $banner2);
			$banner3=array('image'=>'http://zzkimg1.qiniudn.com/mobile-highlight-service800x220.jpg-homepic800x600.jpg',

				'title'=>'自在客',

				'type'=>'1',

				'url'=>'');

			array_push($results,$banner3);
            /**
             * 拼房
             */

            if ($params['os'] == 'android') {
                $pingfangImageUrl = 'http://img1.zzkcdn.com/pingfangandroid.jpg';

            } else {
                $pingfangImageUrl = 'http://img1.zzkcdn.com/pingfangios1.jpg';
            }

            $banner4 = array('image' => $pingfangImageUrl,
                'title' => '台湾拼客',
                'type' => '1',
                'url' => 'http://taiwan.youyao.com/v2/a/pinfang/list');
            array_push($results,$banner4);


			foreach ($data[0] as $value) {
				$theme = array('themeId'=>$value['themeId'],
									'themeName'=>$value['themeName'],
									'themePic'=>$value['themePic'],
									'homestayNum'=>count($data[1][$value['themeId']]));
				array_push($results, $theme);
			}

			$returnResult = array('code'=>1, 'codeMsg'=>'', 'body'=>$results);
            zzk_echo(json_encode($returnResult));
		}else if ($action == 'item') {
			$theme_id = isset($params['theme_id']) ? $params['theme_id'] : '';
			$returnJSON = array('code'=>0, 'codeMsg'=>'');
			if (strlen($theme_id) <= 0) {
				$returnJSON['codeMsg'] = 'theme_id为必填项!';
				zzk_echo(json_encode($returnJSON));
				return false;
			}

			self::handleItemTheme($theme_id, $data);
		}

		return false;
	}

	private function handleItemTheme($theme_id, $data) {

		$itemTheme = $data[1][$theme_id];

		$bll_themeInfo = new Bll_Theme_ThemeInfo();
		$itemInfo = $bll_themeInfo->acquire_theme_info_by_data($itemTheme);
		if ($itemInfo) {
			$returnJSON['code'] = 1;
			$returnJSON['codeMsg'] = '操作成功!';
		}else {
			$returnJSON['code'] = 0;
			$returnJSON['codeMsg'] = '操作数据库失败!';
		}
		$returnJSON['body'] = $itemInfo;
        zzk_echo(json_encode($returnJSON));
		return true;
	}

	private function warpInitData() {
		$theme0 = array('themeId'=>'0', 'themeName'=>'亲子', 'themePic'=>'http://img1.zzkcdn.com/theme_qinzi.jpg-homepic800x600.jpg');
		$theme1 = array('themeId'=>'1', 'themeName'=>'好口碑', 'themePic'=>'http://img1.zzkcdn.com/theme_haokoubei.jpg-homepic800x600.jpg');
		$theme2 = array('themeId'=>'2', 'themeName'=>'海边', 'themePic'=>'http://img1.zzkcdn.com/theme_haibian.jpg-homepic800x600.jpg');
//		$theme3 = array('themeId'=>'3', 'themeName'=>'温泉民宿', 'themePic'=>'http://img1.zzkcdn.com/theme_wenquan.jpg-homepic800x600.jpg');
		$theme3 = array('themeId'=>'3', 'themeName'=>'童玩节·high翻你的假期', 'themePic'=>'http://img1.zzkcdn.com/版面背景.jpg-homepic800x600.jpg');

		$theme4 = array('themeId'=>'4', 'themeName'=>'背包客栈', 'themePic'=>'http://img1.zzkcdn.com/16312930469_1a9d8ca070_k-2222.jpg-homepic800x600.jpg');
		$theme5 = array('themeId'=>'5', 'themeName'=>'花园', 'themePic'=>'http://img1.zzkcdn.com/theme_huayuan.jpg-homepic800x600.jpg');
		$theme6 = array('themeId'=>'6', 'themeName'=>'蜜月', 'themePic'=>'http://img1.zzkcdn.com/theme_miyue.jpg-homepic800x600.jpg');

		$theme7 = array('themeId'=>'7', 'themeName'=>'每周推荐','themePic'=>'http://img1.zzkcdn.com/518旅店.桃园机场驿站.jpg-homepic800x600.jpg');

		$themes = array($theme0, $theme1, $theme2, $theme3, $theme4, $theme5, $theme6,$theme7);

		// 0->亲子.
		$item000 = array('homeId'=>'1022', 'name'=>'垦丁牧场旅栈', 'itemPic'=>'http://img1.zzkcdn.com/theme_qinzi_muchang.jpg-homepic800x600.jpg');
		$item001 = array('homeId'=>'1023', 'name'=>'垦丁二手童话民宿', 'itemPic'=>'http://img1.zzkcdn.com/zzk_5198.jpg-homepic800x600.jpg');
		$item002 = array('homeId'=>'37086', 'name'=>'爱旅行亲子屋', 'itemPic'=>'http://img1.zzkcdn.com/theme_qinzi_ailvxing.jpg-homepic800x600.jpg');
		$item003 = array('homeId'=>'2522', 'name'=>'垦丁恋恋莎堡民宿', 'itemPic'=>'http://img1.zzkcdn.com/theme_qinzi_lianlianshabao.jpg-homepic800x600.jpg');
		$item004 = array('homeId'=>'2781', 'name'=>'乐森活民宿', 'itemPic'=>'http://img1.zzkcdn.com/theme_qinzi_leshenghuo.jpg-homepic800x600.jpg');
		$item005 = array('homeId'=>'4825', 'name'=>'迪利小屋', 'itemPic'=>'http://img1.zzkcdn.com/theme_qinzi_dilixiaowo.jpg-homepic800x600.jpg');
		$item006 = array('homeId'=>'9123', 'name'=>'榄人生态民宿', 'itemPic'=>'http://img1.zzkcdn.com/theme_qinzi_lanrenshengtai.jpg-homepic800x600.jpg');
		$item007 = array('homeId'=>'8696', 'name'=>'圣荷缇渡假城堡', 'itemPic'=>'http://img1.zzkcdn.com/theme_qinzi_shenghetidu.png-homepic800x600.jpg');
		$item016 = array('homeId'=>'14183', 'name'=>'胖达部屋', 'itemPic'=>'http://img1.zzkcdn.com/zzk_171146.jpg-homepic800x600.jpg');
		$item008 = array('homeId'=>'19730', 'name'=>'凉夏', 'itemPic'=>'http://img1.zzkcdn.com/theme_qinzi_liangxia.jpg-homepic800x600.jpg');
		$item009 = array('homeId'=>'17624', 'name'=>'依比鸭鸭水岸会馆', 'itemPic'=>'http://img1.zzkcdn.com/theme_qinzi_yibiyaya.jpg-homepic800x600.jpg');
		$item010 = array('homeId'=>'41708', 'name'=>'菜爷爷亲子蔬宿', 'itemPic'=>'http://img1.zzkcdn.com/theme_qinzi_caiyeye.png-homepic800x600.jpg');
		$item011 = array('homeId'=>'15474', 'name'=>'熙缇欧风民宿', 'itemPic'=>'http://img1.zzkcdn.com/theme_qinzi_xitiou.jpg-homepic800x600.jpg');
		$item012 = array('homeId'=>'38859', 'name'=>'童划民宿', 'itemPic'=>'http://img1.zzkcdn.com/theme_qinzi_tonghua.png-homepic800x600.jpg');
		$item013 = array('homeId'=>'7607', 'name'=>'小熊森林民宿', 'itemPic'=>'http://img1.zzkcdn.com/theme_qinzi_xiaoxiongshenlin.jpg-homepic800x600.jpg');
		$item014 = array('homeId'=>'54671', 'name'=>'花莲卡乐弗民宿', 'itemPic'=>'http://img1.zzkcdn.com/theme_qinzi_kalefu.jpg-homepic800x600.jpg');
		$item015 = array('homeId'=>'14595', 'name'=>'花莲橘子糖', 'itemPic'=>'http://img1.zzkcdn.com/theme_qinzi_juzitang.jpg-homepic800x600.jpg');

		$item0 = array($item000, $item001, $item002, $item003, $item004, $item005, $item006, $item007,$item016,$item008, $item009, $item010, $item011, $item012, $item013, $item014, $item015);

		// 1->好口碑
		$item100 = array('homeId'=>'31', 'name'=>'台湾垦丁民宿-海之恋', 'itemPic'=>'http://img1.zzkcdn.com/theme_haokoubei_haizilian.jpg-homepic800x600.jpg');
		$item101 = array('homeId'=>'271', 'name'=>'近月旭海民宿', 'itemPic'=>'http://img1.zzkcdn.com/theme_haokoubei_jinyuexuhai.jpg-homepic800x600.jpg');
		$item102 = array('homeId'=>'293', 'name'=>'花莲菁华河畔民宿', 'itemPic'=>'http://img1.zzkcdn.com/theme_haokoubei_qinghuahepan.png-homepic800x600.jpg');
		$item103 = array('homeId'=>'308', 'name'=>'花莲阿里巴巴民宿', 'itemPic'=>'http://img1.zzkcdn.com/theme_haokoubei_alibaba.png-homepic800x600.jpg');
		$item104 = array('homeId'=>'326', 'name'=>'岩手旅店', 'itemPic'=>'http://img1.zzkcdn.com/theme_haokoubei_yanshou.jpg-homepic800x600.jpg');
		$item105 = array('homeId'=>'328', 'name'=>'花莲望远境民宿', 'itemPic'=>'http://img1.zzkcdn.com/theme_haokoubei_wangyuanjin.jpg-homepic800x600.jpg');
		$item106 = array('homeId'=>'504', 'name'=>'恋恋枫情民宿', 'itemPic'=>'http://img1.zzkcdn.com/theme_haokoubei_lianlianfengqing.jpg-homepic800x600.jpg');
		$item107 = array('homeId'=>'876', 'name'=>'清境柏克莱花园民宿', 'itemPic'=>'http://img1.zzkcdn.com/theme_haokoubei_bokelai.jpg-homepic800x600.jpg');
		$item108 = array('homeId'=>'942', 'name'=>'九份爱的物语民宿', 'itemPic'=>'http://img1.zzkcdn.com/theme_haokoubei_aidwuyu.jpg-homepic800x600.jpg');

		$item1 = array($item100, $item101, $item102, $item103, $item104, $item105, $item106, $item107, $item108);

		// 2->海边
		$item200 = array('homeId'=>'192', 'name'=>'台湾花莲听海民宿', 'itemPic'=>'http://img1.zzkcdn.com/theme_haibian_hualiantinghai.jpg-homepic800x600.jpg');
		$item201 = array('homeId'=>'38383', 'name'=>'海明蔚民宿', 'itemPic'=>'http://img1.zzkcdn.com/theme_haibian_haimingwei.jpg-homepic800x600.jpg');
		$item202 = array('homeId'=>'1004', 'name'=>'海边Beach House', 'itemPic'=>'http://img1.zzkcdn.com/theme_haibian_BeachHouse.jpg-homepic800x600.jpg');
		$item203 = array('homeId'=>'6034', 'name'=>'斯图亚特海洋庄园', 'itemPic'=>'http://img1.zzkcdn.com/theme_haibian_situyate.jpg-homepic800x600.jpg');
		// $item204 = array('homeId'=>'1323', 'name'=>'海阁旅店 南湾海景民宿', 'itemPic'=>'');
		$item204 = array('homeId'=>'7445', 'name'=>'垦丁滨海之家民宿', 'itemPic'=>'http://img1.zzkcdn.com/theme_haibian_binghaizhijia.jpg-homepic800x600.jpg');
		$item205 = array('homeId'=>'4613', 'name'=>'乐水Hotel de Plus', 'itemPic'=>'http://img1.zzkcdn.com/theme_haibian_HoteldePlus.jpg-homepic800x600.jpg');
		$item206 = array('homeId'=>'8984', 'name'=>'蓝海晴天海景民宿', 'itemPic'=>'http://img1.zzkcdn.com/theme_haibian_lanhaiqingtian.jpg-homepic800x600.jpg');
		$item207 = array('homeId'=>'1682', 'name'=>'垦丁山海恋', 'itemPic'=>'http://img1.zzkcdn.com/theme_haibian_shanhailian.jpg');

		$item2 = array($item200, $item201, $item202, $item203, $item204, $item205, $item206, $item207);

		// 3->童玩节·high翻你的假期
		$item300 = array('homeId'=>'61809', 'name'=>'老公老婆友善民宿', 'itemPic'=>'http://img1.zzkcdn.com/husband.jpg-homepic800x600.jpg');
		$item301 = array('homeId'=>'9312', 'name'=>'恋恋小栈英式民宿', 'itemPic'=>'http://img1.zzkcdn.com/lian.jpg-homepic800x600.jpg');
		$item302 = array('homeId'=>'5061', 'name'=>'罗东夏尔民宿', 'itemPic'=>'http://img1.zzkcdn.com/xiaer.png-homepic800x600.jpg');
		$item303 = array('homeId'=>'7811', 'name'=>'罗东夜市幸福yes', 'itemPic'=>'http://img1.zzkcdn.com/yes.png-homepic800x600.jpg');
		$item304 = array('homeId'=>'4280', 'name'=>'三月三民宿', 'itemPic'=>'http://img1.zzkcdn.com/san.jpg-homepic800x600.jpg');
		$item305 = array('homeId'=>'14685', 'name'=>'忘记回家', 'itemPic'=>'http://img1.zzkcdn.com/forget.jpg-homepic800x600.jpg');
		$item306 = array('homeId'=>'32100', 'name'=>'我的秘密基地', 'itemPic'=>'http://img1.zzkcdn.com/secret.png-homepic800x600.jpg');
		$item307 = array('homeId'=>'508', 'name'=>'芯园民宿', 'itemPic'=>'http://img1.zzkcdn.com/xinyuan.jpg-homepic800x600.jpg');
		$item308 = array('homeId'=>'4252', 'name'=>'宜兰水岸森林会馆', 'itemPic'=>'http://img1.zzkcdn.com/yilanshuian.jpg-homepic800x600.jpg');
		$item309 = array('homeId'=>'17231', 'name'=>'宜然风乡村民宿', 'itemPic'=>'http://img1.zzkcdn.com/yiranfengxiang.png-homepic800x600.jpg');
		$item310 = array('homeId'=>'14975', 'name'=>'宜人生活民宿', 'itemPic'=>'http://img1.zzkcdn.com/yirenlife.jpg-homepic800x600.jpg');


		$item3 = array($item300, $item301, $item302, $item303, $item304, $item305, $item306, $item307, $item308, $item309, $item310);







		// 4->背包客栈
		$item400 = array('homeId'=>'66668', 'name'=>'33背包客栈', 'itemPic'=>'http://img1.zzkcdn.com/theme_beibaoke_33beibao.jpg-homepic800x600.jpg');
		$item401 = array('homeId'=>'31068', 'name'=>'红米国际青年旅馆', 'itemPic'=>'http://img1.zzkcdn.com/theme_beibaoke_hongmiguoji.jpg-homepic800x600.jpg');
		$item402 = array('homeId'=>'22', 'name'=>'花莲Sleeping Boot背包客栈', 'itemPic'=>'http://img1.zzkcdn.com/theme_beibaoke_SleepingBoot.jpg-homepic800x600.jpg');
		$item403 = array('homeId'=>'23366', 'name'=>'津津音乐客栈', 'itemPic'=>'http://img1.zzkcdn.com/theme_beibaoke_jinjinyinyue.jpg-homepic800x600.jpg');
		$item404 = array('homeId'=>'50264', 'name'=>'垦丁沃客背包客栈', 'itemPic'=>'http://img1.zzkcdn.com/theme_beibaoke_aoke.jpg-homepic800x600.jpg');
		$item405 = array('homeId'=>'39158', 'name'=>'女巫国际背包客栈', 'itemPic'=>'http://img1.zzkcdn.com/theme_beibaoke_nvwuguoji.jpg-homepic800x600.jpg');
		$item406 = array('homeId'=>'11343', 'name'=>'西门町漫步旅店', 'itemPic'=>'http://img1.zzkcdn.com/theme_beibaoke_ximending.jpg-homepic800x600.jpg');
		$item407 = array('homeId'=>'21015', 'name'=>'小艾人文工坊背包客栈', 'itemPic'=>'http://img1.zzkcdn.com/theme_beibaoke_xiaoairenwen.jpg-homepic800x600.jpg');
		$item408 = array('homeId'=>'24199', 'name'=>'近来背包客栈', 'itemPic'=>'http://img1.zzkcdn.com/theme_beibaoke_jinlai.jpg-homepic800x600.jpg');
		$item409 = array('homeId'=>'114114', 'name'=>'Banana Hostel-香蕉客人', 'itemPic'=>'http://img1.zzkcdn.com/84b0a1c1ae35fe18bc2b5dee5cfd2678/2000x1500.jpg-homepic800x600.jpg');
		$item410 = array('homeId'=>'116009', 'name'=>'Luckytree House-幸运树舍', 'itemPic'=>'http://img1.zzkcdn.com/85b2a147992467495adddc7b133b1a24/2000x1500.jpg-homepic800x600.jpg');
		$item411 = array('homeId'=>'108122', 'name'=>'大可居青年旅馆', 'itemPic'=>'http://img1.zzkcdn.com/8f376abb2f82ba5195dd6b48060dd443/2000x1500.jpg-homepic800x600.jpg');
		$item412 = array('homeId'=>'121104', 'name'=>'台北卧客-Homewalk Guesthouse', 'itemPic'=>'http://img1.zzkcdn.com/68c9c868cc4ea25be444fe636a2ba1ee/2000x1500.jpg-homepic800x600.jpg');		
        $item413 = array('homeId'=>'83222',  'name'=>'Here-There Hostel 这里那里青年旅店站前馆','itemPic'=>'http://img1.zzkcdn.com/16312930469_1a9d8ca070_k-2222.jpg-homepic800x600.jpg');
		$item4 = array($item400, $item401, $item402, $item403, $item404, $item405, $item406, $item407, $item408,$item409,$item410,$item411,$item412,$item413);

		// 5->花园
		$item500 = array('homeId'=>'325', 'name'=>'花莲温蒂花园民宿 速订', 'itemPic'=>'http://img1.zzkcdn.com/theme_huayuan_wendihuayuan.png-homepic800x600.jpg');
		$item501 = array('homeId'=>'15481', 'name'=>'花莲吉琍林园民宿 速订', 'itemPic'=>'http://img1.zzkcdn.com/theme_huayuan_jilili.jpg-homepic800x600.jpg');
		$item502 = array('homeId'=>'586', 'name'=>'花莲好所在庭园休闲民宿 速订', 'itemPic'=>'http://img1.zzkcdn.com/theme_huayuan_haosuozai.jpg-homepic800x600.jpg');
		$item503 = array('homeId'=>'7045', 'name'=>'花莲紫禾园渡假 速订', 'itemPic'=>'http://img1.zzkcdn.com/zzk_76736.jpg-homepic800x600.jpg');
		$item504 = array('homeId'=>'596', 'name'=>'花莲贝拉利亚民宿 速订', 'itemPic'=>'http://img1.zzkcdn.com/theme_huayuan_beilaliya.jpg-homepic800x600.jpg');
		$item505 = array('homeId'=>'271', 'name'=>'花莲近月旭海民宿 速订', 'itemPic'=>'http://img1.zzkcdn.com/theme_huayuan_jinyuexuhai.jpg-homepic800x600.jpg');
		// $item506 = array('homeId'=>'876', 'name'=>'清境柏克莱花园民宿', 'itemPic'=>'');
		$item506 = array('homeId'=>'9540', 'name'=>'苗栗卓也小屋', 'itemPic'=>'http://img1.zzkcdn.com/theme_huayuan_miaolizhuo.jpg-homepic800x600.jpg');
		$item5 = array($item500, $item501, $item502, $item503, $item504, $item505, $item506);

		// 6->蜜月
		$item600 = array('homeId'=>'18968', 'name'=>'垦丁圣都villa', 'itemPic'=>'http://img1.zzkcdn.com/theme_miyue_shengduvilla.jpg-homepic800x600.jpg');
		$item601 = array('homeId'=>'204', 'name'=>'垦丁卡米克特色民宿', 'itemPic'=>'http://img1.zzkcdn.com/theme_miyue_kamike.jpg-homepic800x600.jpg');
		$item602 = array('homeId'=>'1855', 'name'=>'仲夏-垦丁Kenting民宿速订', 'itemPic'=>'http://img1.zzkcdn.com/theme_miyue_Kenting.jpg-homepic800x600.jpg');
		$item603 = array('homeId'=>'1816', 'name'=>'垦丁加利利民宿速订', 'itemPic'=>'http://img1.zzkcdn.com/theme_miyue_jialili.jpg-homepic800x600.jpg');
		$item604 = array('homeId'=>'4320', 'name'=>'日月潭山季花园民宿', 'itemPic'=>'http://img1.zzkcdn.com/theme_miyue_riyuetan.jpg-homepic800x600.jpg');
		$item605 = array('homeId'=>'67', 'name'=>'花莲巴厘情人度假别墅', 'itemPic'=>'http://img1.zzkcdn.com/theme_miyue_baliqingren.jpg-homepic800x600.jpg');
		$item606 = array('homeId'=>'3327', 'name'=>'九份金瓜石 我们的家187', 'itemPic'=>'http://img1.zzkcdn.com/theme_miyue_jinguashi.jpg-homepic800x600.jpg');
		$item607 = array('homeId'=>'10850', 'name'=>'澎湖希腊边境', 'itemPic'=>'http://img1.zzkcdn.com/theme_miyue_penghuxila.jpg-homepic800x600.jpg');
		$item6 = array($item600, $item601, $item602, $item603, $item604, $item605, $item606, $item607);

		//每周推荐
		$item700 = array('homeId'=>'143271','name'=>'Kitskazshostel 台北背包公寓','itemPic'=>'http://img1.zzkcdn.com/Kitskazshostel.jpg-homepic800x600.jpg');
		$item701 = array('homeId'=>'138391','name'=>'518旅店桃園機場驛棧','itemPic'=>'http://img1.zzkcdn.com/518.jpg-homepic800x600.jpg');
		$item702 = array('homeId'=>'140704','name'=>'九份見晴民宿','itemPic'=>'http://img1.zzkcdn.com/jiufenjianqing.jpg-homepic800x600.jpg');
		$item703 = array('homeId'=>'8375','name'=>'九份惠風民宿','itemPic'=>'http://img1.zzkcdn.com/jiufenhuifeng.jpg-homepic800x600.jpg');
		$item704 = array('homeId'=>'134611','name'=>'帝尔民宿','itemPic'=>'http://img1.zzkcdn.com/dier.jpg-homepic800x600.jpg');
		$item705 = array('homeId'=>'96581','name'=>'岛国熊猫民宿','itemPic'=>'http://img1.zzkcdn.com/daoguo.jpg-homepic800x600.jpg');
		$item706 = array('homeId'=>'138318','name'=>'墾丁house','itemPic'=>'http://img1.zzkcdn.com/house.jpg-homepic800x600.jpg');
		$item707 = array('homeId'=>'141455','name'=>'垦丁海明威旅店','itemPic'=>'http://img1.zzkcdn.com/haimingwei.jpg-homepic800x600.jpg');
		$item708 = array('homeId'=>'142296','name'=>'福憩背包客栈-和平舘','itemPic'=>'http://img1.zzkcdn.com/peace.jpg-homepic800x600.jpg');
		$item709 = array('homeId'=>'126677','name'=>'宮賞藝術大飯店','itemPic'=>'http://img1.zzkcdn.com/gong.jpg-homepic800x600.jpg');

		$item7 = array($item700, $item701, $item702, $item703, $item704, $item705, $item706, $item707,$item708,$item709);

		return array($themes, array('0'=>$item0, '1'=>$item1, '2'=>$item2, '3'=>$item3, '4'=>$item4, '5'=>$item5, '6'=>$item6,'7'=>$item7));
	}

}

?>
