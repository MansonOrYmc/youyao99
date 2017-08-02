<?php

apf_require_class("APF_Controller");

class Theme_DiscoveryController extends APF_Controller
{
    public function handle_request()
    {    Util_Language::set_locale_id($_REQUEST['multilang']);
        $req = APF::get_instance()->get_request();
        $param_arr = $req->get_parameters();
        $page = $param_arr['page'];
        if ($page > 1) {
            Util_Json::render(200,array(),null, null);
            return;
        }
        $list = array();

        if ($param_arr['multilang'] == 12) {

//            if (($param_arr['os'] == 'android' && $param_arr['version'] > 91 )||($param_arr['os'] == 'ios' && version_compare($param_arr['version'], '5.0.8', '>'))){
//                $list[] = $this->service_format(
//                    'http://static.zzkcdn.com/tianzhenleyuanjian.jpg',
//                    '【众筹】自在客注册会员独享（Beta）',
//                    '自在客注册会员限时享受“提前3个月预售民宿特色服务，1000元即可享受民宿1200元优质服务',
//                    array(
//                        'service_id' => 233,
//                    )
//                );
//            }else{
//                $list[] = $this->item_format(
//                    'http://static.zzkcdn.com/tianzhenleyuanjian.jpg',
//                    '【众筹】自在客注册会员独享（Beta）',
//                    '自在客注册会员限时享受“提前3个月预售民宿特色服务，1000元即可享受民宿1200元优质服务',
//                    null,
//                    array(
//                        'homestay_uid' => 357375,
//                        'homestay_name' => '天真乐元',
//                    )
//                );
//            }


            $list[] = $this->item_format(
                'http://static.zzkcdn.com/minsugushi.jpg',
                '翠川民宿',
                '翠川住的很舒适，环境很优雅宁静，非常典型的日式风格，古香古色，房子有一个很美的院子，可以闲来无事坐在外边，喝茶。我最喜欢睡榻榻米的感觉，一楼的空间很大，适合一家人或者五、六个朋友住。这个住地方交通也很便利，在京都最好的出行就是坐公车，人不多，而且沿途还能看风景。是特别美的一次住宿经历！！',
                null,
                array(
                    'homestay_uid' => 296497,
                    'homestay_name' => '翠川民宿
',
                )
            );


            $list[] = $this->item_format(
                'http://static.zzkcdn.com/activity/faxian160727_1.jpg',
                '夏琳旅馆',
                '老板非常用心，被照顾的非常好。 房间干净舒适，接待人员非常友善， 帮我们非常多的小忙，
               例如忘记带豆腐头充电器 离逢甲夜市一分钟就到， 半夜我们还走下去看U2电影。 这是很棒的住宿体验！',
                null,
                array(
                    'homestay_uid' => 380753,
                    'homestay_name' => '夏琳旅馆
',
                )
            );
           /* $list[] = $this->item_format(
                'http://static.zzkcdn.com/activity/faxian160727_2.jpg',
                '垦丁生态民宿懒人屋',
                '我们的民宿老板阿智超好！带我们去潮间带生态之旅,老板会为你找最实惠的游玩项目,还有免费的烤鱼、夜间追沙马,观看海生物等活动!',
                null,
                array(
                    'homestay_uid' =>1181,
                    'homestay_name' => '垦丁生态民宿懒人屋',
                )
            );*/

            
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/kuailexiaoyu.jpg',
                '体验潜水，融入大海',
                '下午最后一潜，下水前烈日当头，钻出水面居然变成大雨倾盆，和阿玮教练合影一张之后恋恋不舍地离开小琉球。',
                null,
                array(
                    'homestay_uid' => 273374,
                    'homestay_name' => '体验潜水，融入大海',
                )
            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/lanhai.jpg',
                '岚海欧风民宿',
                '台北第3天，9份，淡水，士林夜市，天茗喝茶。谢谢我的朋友们，有你们在真好',
                null,
                array(
                    'homestay_uid' => 1220,
                    'homestay_name' => '岚海欧风民宿',
                )
            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/sanyuesan.jpg',
                '宜兰三月三民宿',
                '来台湾之前因为杂事过多导致失眠许久，入台后的两晚失眠越发严重，在我离开夏尔民宿的时候，老板小兰特意给我马上要入住的【三月三民宿】的黄老板打去电话，告知我的失眠问题，细心的两位老板电话沟通后，特意为我换了一间最安静的房间。希望我好好休息一下，实在太感谢他们的照顾。黄老板还特意推荐我晚上去礁溪泡个温泉，谁我的失眠很有帮助，正如他所说我美美睡了一觉，直到中午才起床吃午饭，临行前黄先生送侩木精油作为离别礼物，还驱车送我去火车站，
                在路上他临时下车，回来时将手上热腾腾的夹肉烧饼递给我，说担心我去九份的火车上饿肚子，简直贴心的让我又惊又喜，每一个小小的举动都暖在心里。',
                null,
                array(
                    'homestay_uid' => 4280,
                    'homestay_name' => '宜兰三月三民宿',
                )
            );

            $list[] = $this->item_format(
                'http://static.zzkcdn.com/shenghuyuanzhuang.jpg',
                '圣护院民宿',
                '首先感谢自在客小妍女士的热心服务。说一下圣护院庄这家民宿吧，之前对民宿很有期待，实际体验过之后觉得真心物超所值！',
                null,
                array(
                    'homestay_uid' => 106539,
                    'homestay_name' => '圣护院民宿',
                )
            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/daimenglaoban.jpg',
                '夏尔丽精品会馆',
                '这是和爱尔丽民宿老板的合影，很呆萌的老板，风景很好，民宿也很棒！',
                null,
                array(
                    'homestay_uid' => 21275,
                    'homestay_name' => '夏尔丽精品会馆',
                )
            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/wandaohaibao.jpg',
                '垦丁生态懒人屋',
                '玩到high爆，还有可爱滴小姑娘生生被吓哭，于是她撇在快艇上和船长一起开着快艇拉着我们的香蕉船飞驰，迎风破浪，超爽的体验！',
                null,
                array(
                    'homestay_uid' => 1181,
                    'homestay_name' => '垦丁生态懒人屋',
                )
            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/pinbanzhou.jpg',
                '兰屿Yama的地瓜园',
                '每一个达悟男子自出生那一天起，族人就会为他种下一棵树，到他成年后，这棵树就被砍下来做成只属于这个男子的拼板舟，达悟男人驾着拼板舟出海捕捞飞鱼。飞鱼季期间，大概是每年三月到六月，女人是禁止接近拼板舟的。过了飞鱼季之后，禁忌也就失效了。所以我们今天才能体验一回原住民的拼板舟[得意]',
                null,
                array(
                    'homestay_uid' => 33825,
                    'homestay_name' => '兰屿Yama的地瓜园',
                )
            );

            $list[] = $this->item_format(
                'http://static.zzkcdn.com/moganshanyunjing.jpg',
                '莫干山云镜',
                '第一次住这么有意境的民宿，吃这么香醇的饭菜。云里竹，节节高升不畏风雨；雾中月，浩然当空代代相传；今生得一云境之宿，宁与世无争长留。',
                null,
                array(
                    'homestay_uid' => 275362,
                    'homestay_name' => '莫干山云镜',
                )
            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/zailushang.jpg',
                '从作家到投资人，文吉儿一直在路上',
                '我不记得时间走了多久，不记得我走了多远的路来到这里。在很多次旅行中我明白了一个道理，有的风景满身伤痕也要去看，有的人千辛万苦也要去拥抱。最美的风景不是眼睛里的像素而是心底的影像。',
                null,
                array(
                    'homestay_uid' => 198878,
                    'homestay_name' => '太平洋海边渡假民宿',
                )
            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/ribengushi.JPG',
                '带着宝宝们过一天日本的生活',
                '土路人第一次穿和服很比较兴奋啊！在这里还发现了可以直接预订和服，很方便！',
                null,
                array(
                    'homestay_uid' => 296497,
                    'homestay_name' => '翠川民宿',
                )
            );

            $list[] = $this->item_format(
                'http://static.zzkcdn.com/110508349D021689F95A41ABE0242098.jpg',
                '香榭城背包客民宿',
                '这是在花莲的民宿，管家非常热心且负责，下次必会再去的地方。这是在屋内楼梯上的一张合影，很有纪念意义。',
                null,
                array(
                    'homestay_uid' => 33025,
                    'homestay_name' => '香榭城背包客民宿',
                )
            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/65366E5217F8651E5BA0FAEACF1AB206.jpg',
                '途中.九份 国际青年旅舍',
                '那天刚好碰到老板的朋友安迪哥，带了全瑞芳最好吃的小吃，还带我们去九份山上看夜景，第二天还为我们做早餐……当时真是舍不得走。',
                null,
                array(
                    'homestay_uid' => 13964,
                    'homestay_name' => '途中.九份 国际青年旅舍',
                )
            );

            $list[] = $this->item_format(
                'http://static.zzkcdn.com/3AF53790@C8F95D44.53BC8857.jpg',
                '花莲美那多民宿',
                '我们是两个人，老板娘免费给我们升级了六人套房，房间很大',
                null,
                array(
                    'homestay_uid' => 23,
                    'homestay_name' => '花莲美那多民宿',
                )
            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/9494A030@331DCC53.53BC8857.jpg',
                '欣欣度假旅馆',
                '强烈安利台湾的胖胖车队！垦丁一日游幸福爆炸。师傅阿志超幽默，每到一个景点都下来陪我们玩，讲解拍照，还请客爆好吃的柠檬鸡胖胖车外形都萌萌哒，每个车还有自己的主题。我们是青蛙王子。重点是你们看他拍照多拼命啊！！还会教我们摆pose！环岛包车就找他！价格还便宜！',
                null,
                array(
                    'homestay_uid' => 4068,
                    'homestay_name' => '欣欣度假旅馆',
                )
            );





            $list[] = $this->item_format(
                'http://static.zzkcdn.com/app/discovery/7.jpg?imageView2/1/interlace/1/q/50',
                '垦丁最受网友欢迎的民宿',
                '台湾人以“家”的形态，将个人生活美学用民宿表达得淋漓尽致，在敞开家门接纳客人的同时，也在输出着台湾特有的文化。让我们看看最受网友最欢迎的民宿。',
                'http://mp.weixin.qq.com/s?__biz=MzA5OTMwODkyOA==&mid=505433481&idx=1&sn=4068e08d259233c3dafdeb63a0109df2#rd'
            );
//            $list[] = $this->item_format(
//                'http://static.zzkcdn.com/mobile/app/img/%E6%97%A5%E6%9C%AC%E8%B5%8F%E6%A8%B1.jpg?imageView2/1/interlace/1/q/50',
//                '2016日本赏樱指南',
//                '当我们还在看着嫩芽抽新的时候，和我们一海相隔的霓虹人民已经张罗着去赏樱花啦，那么日本赏樱哪里好？',
//                'http://mp.weixin.qq.com/s?__biz=MzA5OTMwODkyOA==&mid=976620662&idx=1&sn=e3966bf05532832037fda0f768b32bac#rd'
//            );
         
//            $list[] = $this->item_format(
//                'http://static.zzkcdn.com/mobile/app/img/%E5%9E%A6%E4%B8%81%E9%9F%B3%E4%B9%90%E8%8A%82.jpg?imageView2/1/interlace/1/q/50',
//                '垦丁音乐节，带你嗨翻四月天',
//                '狂欢了一天后，你最需要的当然是一家好民宿，一张温暖舒适的床，带上好心情入眠。这些民宿不住就亏了！',
//                'http://mp.weixin.qq.com/s?__biz=MzA5OTMwODkyOA==&mid=999217294&idx=1&sn=152629e98a32422e31fd707ba4fd9456#rd'
//            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/huanliankulaodie.jpg',
                '民宿之行，竟遇到我的酷老爹',
                '怎么会遇到“老爹”这样的人，以为是一司机，后来跪着被他传奇人生征服。很淳朴开朗热情的有个性与理性的但绝对有钱又十分低调的台湾花莲人。',
                null,
                array(
                    'homestay_uid' => 13964,
                    'homestay_name' => '途中.九份 国际青年旅舍',
                )
            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/mobile/app/img/%E5%8E%A6%E9%97%A8.jpg?imageView2/1/interlace/1/q/50',
                '住进鼓浪屿最有温度的民宿',
                '大潘夫妇赋予了民宿放松、简单、清新的慢步调风格，午后的鼓浪屿，我的阳光小屋，等你来。',
                'http://mp.weixin.qq.com/s?__biz=MzA5OTMwODkyOA==&mid=976635606&idx=1&sn=e52a4939ed6b84c1da115e854e68211a#rd'
            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/app/discovery/1-2.jpg?imageView2/1/interlace/1/q/50',
                '峇里情人渡假别墅',
                '来自客人“vicky的天气”的评价：我超级喜欢这里！太美啦～环境超级好！早餐特别好吃～ 老闆娘超级热情 适合发呆～',
                null,
                array(
                    'homestay_uid' => 67,
                    'homestay_name' => '峇里情人渡假别墅',
                )
            );

            $list[] = $this->item_format(
                'http://static.zzkcdn.com/app/discovery/8.jpg?imageView2/1/interlace/1/q/50',
                '带孩子去台湾必住的十大亲子民宿',
                '去花莲追鲸鱼，去清境喂羊咩咩， 去阿里山坐坐小火车，去垦丁玩沙…… 台湾，有太多可以让宝贝们去体验的玩法。',
                'http://mp.weixin.qq.com/s?__biz=MzA5OTMwODkyOA==&mid=690324148&idx=1&sn=fbb43a410ca4fa8a9577badb88b1e669#rd'
            );

            $list[] = $this->item_format(
                'http://static.zzkcdn.com/app/discovery/jiaochanwei.jpg?imageView2/1/interlace/1/q/50',
                '较场尾的温暖海景',
                '它并不似大小梅沙那般闻名，也因此得以保留其较为原汁原味的风貌。它是纯真而文艺的海边村落，而最引人注目的是岛上相与坐落的民宿景观。',
                'http://mp.weixin.qq.com/s?__biz=MzA5OTMwODkyOA==&mid=904970569&idx=1&sn=a2230307080d26e061faafcbfec029ed#rd/?campaign_code=edm_zh'
            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/app/discovery/3.jpg?imageView2/1/interlace/1/q/50',
                '85猪宝宝的窝',
                '来自客人“xuwanhua”的评价：冲着别人评论的海景房去的！下午到了后进房就萌生出拍剪影照片的想法！',
                null,
                array(
                    'homestay_uid' => 10919,
                    'homestay_name' => '85猪宝宝的窝',
                )
            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/app/discovery/4.jpg?imageView2/1/interlace/1/q/50',
                '利未庄园民宿',
                '我们的客人说： 临海，安静，舒适，干净，温暖，品质， 是我梦想中的房子。',
                null,
                array(
                    'homestay_uid' => 1182,
                    'homestay_name' => '利未庄园民宿',
                )
            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/app/discovery/5-1.jpg?imageView2/1/interlace/1/q/50',
                '夜宿海生馆',
                '专门的导览人员带你去夜宿区域讲解海洋 生物知识，还可以去工作区域喂食鱼类， 重要的是晚上和鱼一起睡。',
                null,
                array(
                    'homestay_uid' => 14215,
                    'homestay_name' => '海生馆夜宿',
                )
            );

            $list[] = $this->item_format(
                'http://static.zzkcdn.com/app/discovery/zuimeixiaozhen.jpg?imageView2/1/interlace/1/q/50',
                '日本最美の隐世小镇',
                '泡泡冬日的温泉、尝一碗抹茶的清香、听风和流水的声音......把时间都浪费掉，把烦恼都抛却掉，在这段慢时光里，只有治愈和温暖的味道。',
                'http://mp.weixin.qq.com/s?__biz=MzA5OTMwODkyOA==&mid=909572018&idx=1&sn=34bd96803d76709ac96fefb92ad301bd&scene=1&srcid=1223cDGpDeOyPcmlxU6nMbBj#wechat_redirect'
            );

            $list[] = $this->item_format(
                'http://static.zzkcdn.com/app/discovery/6.jpg?imageView2/1/interlace/1/q/50',
                '九份闲情',
                '民宿主人张大哥， 是一位讲求生活趣味的素人设计师， 因为对木质艺术的热爱， 这幢民宿出自他的巧思与巧手， 这可不同于一般的旅店哦。',
                null,
                array(
                    'homestay_uid' => 75189,
                    'homestay_name' => '九份闲情',
                )
            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/app/discovery/shouerzuimeiminsu.png',
                '首尔最美的韩屋民宿',
                '正如来北京要去胡同、四合院，去上海要逛弄堂一样，去首尔，韩屋也是不能错过的地方，它是韩国传统文化的精华所在。',
                'http://mp.weixin.qq.com/s?__biz=MzA5OTMwODkyOA==&mid=897186506&idx=1&sn=d32576f9e1e2cabbd387331719628cb5&scene=1&srcid=1224bAn81EJtzB0yEEPh6v2R&from=singlemessage&isappinstalled=0#wechat_redirect'
            );


            Util_Json::render(200, $list);
        } elseif ($param_arr['multilang'] == 10) {

            $list[] = $this->item_format(
                'http://static.zzkcdn.com/wutonghuarong.jpg',
                '放空看海 藍天與白雲的海島假期 澎湖馬公 語榕花園民宿',
                '窗外白雲悠閒在澎湖的藍色天空中，渴望旅行的心靈蠢蠢動，飛機降落在馬公機場，迎接而來的是美麗的大海與舒服的微風，遠離都市裡的喧嘩，來到澎湖這座可愛的島嶼感受放空的假期。
白色的建築外觀聳立在一片綠色草原中，位於馬公近郊，環境十分悠閒寧靜，民宿前方就是一大片庭院綠油油的草皮，若是在春夏之際，庭院的花花草草更是顯得熱鬧非凡，一踏進民宿立刻直奔頂樓的露天陽台，鄰近的建築遮蔽物少，能依邊欣賞澎湖內灣海景與沒有光害的天空，晚上的星星月亮與海融合美景是澎湖最棒的風景之一。
語榕花園民宿提供了多樣的雙人與四人房型，各屬於不同的風格，非常適合與好友 同事 家庭一同前來，民宿更是獲得了『好客民宿』的優質評價！來到澎湖非常極為推薦～讓妳有個美好的海島回憶。',
                null,
                array(
                    'homestay_uid' => 49090,
                    'homestay_name' => '语榕花园民宿',
                )
            );
            $list[] = $this->item_format(
                'http://img1.zzkcdn.com/n03719992384c21a9085ad32e5a1932f/2000x1500.jpg-homepic800x600.jpg',
                '85美麗灣，俯瞰高雄景色的住宿體驗',
                '高雄85大樓位在新光碼頭旁.高度僅次於台北101大樓.獨特的建築外觀.成為高雄市的景觀地標之一，可將高雄港.漁港,貨輪,遊艇往返美景一覽無遺！
85美麗灣提供多樣的房型，分別有海景，街景，景觀，中庭等二至多人房，以簡潔舒適為基本訴求，打造精緻有品味的空中住宿體驗，透過房間裡的玻璃窗，將整座城市的美景盡收眼底，同時欣賞高雄白天與夜晚的美景，且房間坪數大，寬敞明亮，房價平實優惠，cp值高，非常適合情侶，家庭，好友，出差等旅人的住宿需求唷。
附近鄰近捷運站，百貨商圈，美食夜市，嚴然已成為來到高雄的住宿首選！',
                null,
                array(
                    'homestay_uid' => 4634,
                    'homestay_name' => '85美麗灣',
                )
            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/hanahuasu.jpg',
                '島嶼南端的慵懶峇里島風格，遠眺船帆石 Hana 花宿',
                '前往墾丁路上，讚嘆湛藍的大海、沐浴明豔的陽光，雀躍欣喜的心情享受南國的熱情。越過喧嘩的墾丁大街之後，景色開始轉向遼闊的海景公路，船帆石昂然矗立在眼前，一棟充滿繽紛色彩風格的建築，佇立在前方。
Hana花宿是一對可愛的夫妻花媽與山豬爸的夢想，打造而起的熱情民宿，慵懶的峇里島風格，搭配船帆石的海洋景色，築起一個異國混搭的氛圍，
在民宿裡，想像自己在熱情的島嶼上，從海景房靜靜看出去的藍色大海，溫暖陽光照射進來的剪影，彷彿置身於南洋風情。
一進到房間，坪數大的讓我們覺得興奮又驚喜，空間十分寬敞又明亮，異國色彩繽紛鮮明，且設施設備完整，民宿距離海邊只需要三分鐘，非常適合前來玩水嬉戲或是放空的旅人，可以讓你完全忘記平時的忙碌與煩惱，且價格也是相當實惠，非常推薦給旅人們參考，
在陽台上享受民宿準備的早餐，體驗南國陽光的假期吧！',
                null,
                array(
                    'homestay_uid' => 20048,
                    'homestay_name' => '墾丁 hana花宿',
                )
            );
            $list[] = $this->item_format(
                'http://img1.zzkcdn.com/b5cf78df40185500414cea5bc0213214/2000x1500.jpg-homepic800x600.jpg',
                '住進寧靜巷弄好居所，台北好地方',
                '搭著捷運穿梭這座城市，交織著繁忙與活力的台北，總是有種特別的魅力。隱藏在安靜巷弄內的寧靜公寓，台北好地方，是收容旅人歡喜和疲倦的居所，更讓每個來造訪的妳感受不一樣的住宿體驗，讓妳能在入睡前再次品味台北美好的地方。
以家的溫暖打造的空間，雖然沒有飯店的豪華，但主人用心經營照顧每個來往的旅人，提供各種房型，閱讀單人房，雙人雅房，或是獨立包層，無論是獨自旅行或是與三五好友都非常適合，鄰近捷運站，交通超方便，附近就是小巨蛋，更是許多到台北看演唱會的住宿好選擇！',
                null,
                array(
                    'homestay_uid' => 100878,
                    'homestay_name' => '台北好地方',
                )
            );
            $list[] = $this->item_format(
                'http://img1.zzkcdn.com/e88b9afb18e4a52b2627d9415024966a/2000x1500.jpg-homepic1024x768.jpg',
                '走進法式靈魂住宿空間，愛上花蓮的左岸巴黎風格',
                '午後的花蓮陽光，更顯得慵懶，從火車站出來後不到10分鐘的車程，愛上巴黎民宿就位於熱鬧市區裡，交通相當方便～！
進入獨棟的民宿建築，淨入眼簾的是簡單舒爽的大廳，鵝黃色的法式吊燈讓整個空間更溫暖了起來，美麗大方的女主人與貼心的管家小幫手，熱情招呼著歡迎妳到來。
在愛上巴黎的寓所裡，共有五個不同主題的房型空間，嫩綠色系充滿花朵的普羅旺斯，高貴的凡爾賽宮氣息，南法左岸的亞維儂，木質傢具擺設的蒙馬特大道，艾菲爾鐵塔設計，這裏住著五種法式古典優雅與現代的想像，在每一間房，每一處的角落，都重現巴黎的經典風味。
民宿也提供單車租借服務，特色早餐，內部還有電梯，相當方便，若想體驗不一樣的異國風情，愛上巴黎是個深受女孩們喜愛的首選！',
                null,
                array(
                    'homestay_uid' => 146978,
                    'homestay_name' => '愛上巴黎民宿',
                )
            );
            $list[] = $this->item_format(
                'http://img1.zzkcdn.com/89190fde7fedbbf1258d86f45a33b893/2000x1500.jpg-homepic1024x768.jpg',
                '女孩們嚮往的童話夢幻空間，入住巷弄內超高cp值愛戀屋',
                '白藍色相間的地中海風格，在花蓮市區的巷弄內，顯得特別有特色，從花蓮車站出來後，不到十分鐘的距離，就可馬上到達，對於住宿要求cp值高的旅人絕不可錯過的推薦民宿 花蓮 愛戀屋。
民宿共有2個館別，而有許多主題房型提供選擇，阿里巴巴的冒險趣味，藍色地中海的海洋風格，粉紅可愛的卡通主題，淺水艇之夜，荷蘭風車與童話森林都帶給旅人不一樣的入住體驗，女孩們嚮往的希臘館，則是帶著優雅鄉村風格與希臘秘境的設計，大多的房型都有浴缸的設計，來到這裡，無論情侶，好友，家庭的親子，都非常適合，且房間的價格也是走平價路線，相當划算！這裏的民宿管家服務也非常熱情，貼心地招呼且相當重視客人入住的隱密空間唷～',
                null,
                array(
                    'homestay_uid' => 302674,
                    'homestay_name' => '花蓮 愛戀屋民宿',
                )
            );
            $list[] = $this->item_format(
                'http://img1.zzkcdn.com/z194661b6f06fcfe5d6d5b0898aabf0fzzkcopr/2000x1500.jpg-homepic800x600.jpg',
                '屏東滿州 Small House Villa｜遇見綠海中純白個性泳池小屋',
                '遠離墾丁大街的塵囂，靠近佳樂水的滿州，在綠意草原中的一棟純白色度假泳池小屋，處處充滿溫馨的小設計，還有小管家幫妳準備的特色早餐，讓你感受美好的度假生活體驗！！',
                null,
                array(
                    'homestay_uid' => 286280,
                    'homestay_name' => 'Small House Villa',
                )
            );
            $list[] = $this->item_format(
                'http://img1.zzkcdn.com/k4ab5f5f3804755619a48346979e254b/2000x1500.jpg-homepic1024x768.jpg',
                '彷彿童話故事般歐洲小鎮的森林中',
                '彷彿童話故事般歐洲小鎮的森林中，在遼闊的空間座落一座小城堡，草地與棕櫚樹的陪襯，色彩鮮明的美式工業風格！等妳來體會每個一個舒服的角落。',
                null,
                array(
                    'homestay_uid' => 372624,
                    'homestay_name' => '花蓮 吉安 森林中',
                )
            );
            $list[] = $this->item_format(
                'http://img1.zzkcdn.com/wb4d7eb033adba5c09b1f6776b36692c/2000x1500.jpg-homepic800x600.jpg',
                '擁抱美崙溪畔的寧靜享受，花蓮 日光水岸民宿',
                '嚮往花蓮的好山少水，卻又希望能住在方便的市區，一個寧靜又悠閒，能暫時給妳一個舒適的空間，這樣一個地方，日光水岸民宿就市區寧靜的住宅區轉角，等待與妳的相遇。
全新落成的建築新宅，外觀簡約的設計，一進入大廳就可感受到溫馨的氛圍，少數有電梯的貼心配置，更方便家庭或是年長者的入住體驗，進入房間，寬敞而明亮的空間，蔚藍海岸 薰衣草 風鈴木等五種不同主題的風格呈現，陽光從陽台灑進來的窗邊，能靜靜的體驗花蓮的美好時光。
強調民宿的位置方便性，從火車站出來只需要短短的三分鐘即可到達，民宿還可提供自行單車，讓你暢遊熱鬧與悠閒兼具的市區，來一場悠活的迴瀾之旅。',
                null,
                array(
                    'homestay_uid' => 465931,
                    'homestay_name' => '日光水岸民宿',
                )
            );
            $list[] = $this->item_format(
                'http://img1.zzkcdn.com/3ca68f561d924e2d5b7bd445eb301ac8/2000x1500.jpg-homepic800x600.jpg',
                '普羅旺斯的法式鄉村田野風光，吉安 大花紫薇田園民宿。',
                '車子轉入吉安鄉間小路，連棟的房屋建築錯落在農田間，和湛藍的天空一起繪出一幅幅舒適的美景。打開車窗，混雜著遍地芬芳香氣撲鼻而來，大花紫薇田園民宿就隱藏在這田野之中。
民宿主人一手打造的鄉村風格，經由國外進口的原木傢俱搭配暖色系，空間布滿了藝術氣息與優雅質感，彷彿之身於南法的鄉村，角落的一處，擺放著主人喜愛的皮質感沙發，咖啡豆的香氣適合在這裡放空一個下午，而早晨則舒服的看著窗外美景享受民宿精心準備的手做早餐。
山茶花，七里香，天堂鳥， 大花紫薇四個不同主題房型的風格呈現，各具特色且充滿舒適感，不仿趁著週末在這裡度過一個悠閒森活的愜意假期吧。',
                null,
                array(
                    'homestay_uid' => 125451,
                    'homestay_name' => '大花紫薇田園民宿',
                )
            );
            $list[] = $this->item_format(
                'http://img1.zzkcdn.com/jqda42cf900bff8f0c263686de561d5c/2000x1500.jpg-homepic800x600.jpg',
                '湛藍天空的藍海星辰，來去台東感受悠閒體驗',
                '筆直的馬亨大道上，陽光輕輕灑落，台東的湛藍天空讓人心曠神怡，回頭一看，用藍星點綴的新穎建築正是2016近期開幕的台東藍海星辰民宿，踩著輕快的步伐，一探究竟。
吹著舒服的風，民宿獨棟的建築特別亮眼，民宿內部簡約卻充滿溫馨的氛圍，大廳充滿海洋主題的設計，讓人有種來到地中海度假的悠閒感，民宿附近就是森林公園，午後可以騎著單車，沿著自行車道呼吸新鮮空氣享受森芬多精，藉此洗滌工作繁忙的煩惱，沈澱自己的心靈。
這裏提供了2房型，樂活與忘憂雙人房及四人房型可選擇，
寬敞的房間，使用飯店級的高級備品與設備，讓你的旅行更加放鬆，自在地得到舒適休息。若想遠離城市的喧嘩，歡迎來到台東，感受風和日麗的海洋氣息，體驗藍海星辰的熱情問候。',
                null,
                array(
                    'homestay_uid' => 468194,
                    'homestay_name' => '藍海星辰民宿',
                )
            );
            $list[] = $this->item_format(
                'http://img1.zzkcdn.com/p508ae5eaea813fd6ad102d869066edezzkcopr/2000x1500.jpg-homepic800x600.jpg',
                '木質寧靜的礁溪溫泉透天民宿，綠意盎然的南洋風格體驗',
                '從高速公路穿過雪隧後，放眼望去的蘭陽平原就在眼前，駛入充滿溫泉與稻田的礁溪田野小徑，搖下車窗，一片綠油油的稻田，
松田民宿就佇立在一旁的連接透天獨棟，灰與黑的設計外觀，給人現代化與低調的質感風格。
民宿的門口小院子，種滿了綠意盎然的植物，花花草草的佈滿了四周，讓感覺更貼近大自然，很舒服的氛圍。整潔明亮的客廳，乾淨的程度讓我驚艷，南洋風的傢俱與精心擺設，井然有序的排列著，原木質感的設計搭配，更讓人感覺到沈穩寧靜。
南洋異國風情，遠眺稻田與山景的精緻，在房間的陽台就可以感受蘭陽平原的魅力，房間裡還有溫泉泡澡的池子，在旅途中，無論是情侶，親子，家庭都讓妳可以放鬆的泡個澡，體驗礁溪無色無味的特殊溫泉享受。貼心又熱情的男主人，早晨注重養生自然手作的精緻早餐更是不可錯過！民宿距離火車站及熱鬧大街只需要五分鐘，交通非常方便，位置又非常寧靜，超推薦！',
                null,
                array(
                    'homestay_uid' => 14869,
                    'homestay_name' => '松田民宿',
                )
            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/mobile/app/discovery/apptaiwan.jpg',
                '府城漫遊-台南在地小吃-x-懷舊風格小屋',
                '在台南，「歷史香料，讓小吃更美味」早期日治時代，先將「台南府城」改名為「台南市街」，並劃分為東門、西門、南門、北門和西門外五區，陸續多次調整。到了1907年，開始拆城牆，小西門與大西門之間的城牆首先拆除，城基改為「西門路」....同時隨著西方建築傳入，台南舊城風貌從一個清代城市開始改變了。',
                'https://www.facebook.com/notes/%E8%87%AA%E5%9C%A8%E5%AE%A2-youyao-%E6%97%85%E8%A1%8C%E5%BE%9E%E7%BE%8E%E5%A5%BD%E4%BD%8F%E5%AE%BF%E9%96%8B%E5%A7%8B/%E5%BA%9C%E5%9F%8E%E6%BC%AB%E9%81%8A-%E5%8F%B0%E5%8D%97%E5%9C%A8%E5%9C%B0%E5%B0%8F%E5%90%83-x-%E6%87%B7%E8%88%8A%E9%A2%A8%E6%A0%BC%E5%B0%8F%E5%B1%8B/945010315605197?'
            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/mobile/app/discovery/kamike.jpg',
                '從明天起，做一個幸福的人',
                '卡米克民宿位於墾丁貓鼻頭，2007年開始動土，主人富新漢和惠利隔年二月就住進了新家。富新漢和惠利姐一直以綠色建築的理念來建立自己夢想的家園。',
                'https://www.facebook.com/notes/%E8%87%AA%E5%9C%A8%E5%AE%A2-youyao-%E6%97%85%E8%A1%8C%E5%BE%9E%E7%BE%8E%E5%A5%BD%E4%BD%8F%E5%AE%BF%E9%96%8B%E5%A7%8B/%E5%BE%9E%E6%98%8E%E5%A4%A9%E8%B5%B7%E5%81%9A%E4%B8%80%E5%80%8B%E5%B9%B8%E7%A6%8F%E7%9A%84%E4%BA%BA/785340954905468?'
            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/mobile/app/discovery/%E8%9C%BB%E8%9C%93%E7%9F%B31000x1000.JPG',
                '美的連仙境都不足以形容它',
                '夜裡的蜻蜓石顯得安靜，有時候換個環境總讓自己靜下來的感受， 感受民宿主人營造的氛圍，在外頭看著簡單的外牆， 一進到蜻蜓石才發現這一切都不簡單',
                'http://difeny.pixnet.net/blog/post/43646548'
            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/mobile/app/discovery/jinguashi-space14b%20%E5%89%AF%E6%9C%AC.jpg?imageView2/1/interlace/1/q/50',
                '一趟緩慢的旅行',
                '礦城裡的小天堂，安靜與山對話放下行囊的那一刻，一切都淡定了',
                'https://www.facebook.com/notes/%E8%87%AA%E5%9C%A8%E5%AE%A2-youyao-%E6%97%85%E8%A1%8C%E5%BE%9E%E7%BE%8E%E5%A5%BD%E4%BD%8F%E5%AE%BF%E9%96%8B%E5%A7%8B/%E5%9C%A8%E7%B7%A9%E6%85%A2%E9%87%91%E7%93%9C%E7%9F%B3%E6%99%82%E9%96%93%E7%9A%84%E5%96%AE%E4%BD%8D%E6%98%AF%E4%B8%80%E6%9C%B5%E9%9B%B2%E6%BC%82%E7%A7%BB%E7%9A%84%E8%B7%9D%E9%9B%A2/781202751985955?'
            );


            Util_Json::render(200, $list, null, null, false);

        } elseif ($param_arr['multilang'] == 13) {

            $list[] = $this->item_format(
                'http://static.zzkcdn.com/starHeya.jpg',
                '台北星部屋StarHeya亲子的家',
                '차 한잔 에 행복 하다. 하나 의 인정 이 담겨 있다. Happy cup of tea, a human touch. 타이베이. 안녕. 저는 반드시 다시 올 것이다. Goodbye Taipei, I will come again. PS: Taipei leaves too much regret, but fortunately encountered enthusiastic Bed and Breakfast 아저씨 uncle, were very good, we must live in order to later this oh!',
                null,
                array(
                    'homestay_uid' => 38403,
                    'homestay_name' => '台北星部屋StarHeya亲子的家
',
                )
            );

            $list[] = $this->item_format(
                'http://static.zzkcdn.com/minsugushi.jpg',
                '翠川民宿',
                'Cui Chuan very comfortable living environment is very elegant and quiet, very typical Japanese style, antique, house has a beautiful yard, nothing else can sit outside, drinking tea. I like the feeling of sleeping tatami, floor> plenty of room for a family or a five or six friends live. The traffic is very convenient place to live in Kyoto, the best is to take a bus trip, not many people, but also look at the scenery along the way. One is particularly beautiful stay! !',
                null,
                array(
                    'homestay_uid' => 296497,
                    'homestay_name' => '翠川民宿
',
                )
            );


            $list[] = $this->item_format(
                'http://static.zzkcdn.com/activity/faxian160727_1.jpg',
                '夏琳旅馆',
                'The host very carefully, very well taken care of. The room was clean and comfortable, the reception staff were very friendly and helped us a lot of small favors,Such as forgetting to head with tofu charger one minute away from Feng Chia night market to midnight, we go to see U2 movie. This is a great stay!',
                null,
                array(
                    'homestay_uid' => 380753,
                    'homestay_name' => '夏琳旅馆
',
                )
            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/activity/faxian160727_2.jpg',
                '垦丁生态民宿懒人屋',
                'Our BnB A Zhi very good host! Took us to the intertidal eco-tour, the host will find the most affordable for you to play the project, as well as free fish, catch crabs at night, watch marine life and other activities!',
                null,
                array(
                    'homestay_uid' =>1181,
                    'homestay_name' => '垦丁生态民宿懒人屋',
                )
            );

            
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/kuailexiaoyu.jpg',
                'Experience diving into the sea',
                'Afternoon last dive, before launching the scorching sun, out of the water actually turned into rain-soaked, and Ah Wei coach reluctantly left after a group photo Liouciou.',
                null,
                array(
                    'homestay_uid' => 273374,
                    'homestay_name' => '体验潜水，融入大海',
                )
            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/lanhai.jpg',
                '岚海欧风民宿',
                'Taipei Day 3, 9 parts, fresh water, Shihlin Night Market, Tin Ming tea. Thank you, my friends, nice to have you in',
                null,
                array(
                    'homestay_uid' => 1220,
                    'homestay_name' => '岚海欧风民宿',
                )
            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/sanyuesan.jpg',
                '宜兰三月三民宿',
                "Because too many chores cause insomnia for a long time before coming to Taiwan, to Taiwan after two sleepless nights of more serious, I left the Shire B & B when the boss specifically to Betty I'll be staying in [BnB san yue san] host huang fight go phone to inform my insomnia, attentive bosses phone call communication, specifically as I changed one of the most quiet room. I hope I have a good rest, too grateful for their care. Huang boss also specifically recommend the evening I went to a hot spring soak Chiaohsi, Who helps my insomnia, as he said Mimi I slept until noon, get up for lunch, before leaving to send Wong Kui wood oil as a parting gift, he also drove to send me to the station, On his way off temporary, came back to the hands of hot biscuits Garou gave me, that worried me to train on nine hungry, so I was surprised almost intimate, every little move all warm in my heart",
                null,
                array(
                    'homestay_uid' => 4280,
                    'homestay_name' => '宜兰三月三民宿',
                )
            );

            $list[] = $this->item_format(
                'http://static.zzkcdn.com/shenghuyuanzhuang.jpg',
                '圣护院民宿',
                'Thank youyao Ms. Xiaoyan enthusiastic service. Talk about this village of St.-care hospital bed and breakfast bar, before bed and breakfasts great expectations, after the actual experienced feel really value for money!',
                null,
                array(
                    'homestay_uid' => 106539,
                    'homestay_name' => '圣护院民宿',
                )
            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/daimenglaoban.jpg',
                '夏尔丽精品会馆',
                'This is a Bed and Breakfast and Airlie owner of the photo, it is to naivety host, good scenery, bnb are great!',
                null,
                array(
                    'homestay_uid' => 21275,
                    'homestay_name' => '夏尔丽精品会馆',
                )
            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/wandaohaibao.jpg',
                '垦丁生态懒人屋',
                'To play high explosive, as well as lovely little girl drop life and started crying, so she left in the boat, the captain pulled together driving a speedboat speeding our banana, wind waves, super cool experience!',
                null,
                array(
                    'homestay_uid' => 1181,
                    'homestay_name' => '垦丁生态懒人屋',
                )
            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/pinbanzhou.jpg',
                '兰屿Yama的地瓜园',
                "Each Dawud man from birth that day, the tribe will plant a tree for him, to his adult life, the tree will be cut down to make only part of the man's canoe, a man drove Tao canoe sea fishing flying fish>. During the flying fish season, probably from March to June, a woman is a ban on approaching the canoe. After a flying fish season, taboos also fails. So today we experience a return to Native canoe [proud]",
                null,
                array(
                    'homestay_uid' => 33825,
                    'homestay_name' => '兰屿Yama的地瓜园',
                )
            );

            $list[] = $this->item_format(
                'http://static.zzkcdn.com/moganshanyunjing.jpg',
                '莫干山云镜',
                'First live such a mood BnB, eat so mellow meals. Clouds bamboo, have been rising undeterred by the rain; fog month, awe-inspiring when the air from generation to generation; this life places a cloud environment, the long stay rather aloof.',
                null,
                array(
                    'homestay_uid' => 275362,
                    'homestay_name' => '莫干山云镜',
                )
            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/zailushang.jpg',
                'From writer to investors, Wen Jir has been on the road',
                'I do not remember how long time to go, I do not remember how far away the road here. In many trips, I understand the truth, but also to see some scenery covered with scars, some people are going to embrace hardships. The most beautiful scenery is not the eyes but the heart of the image pixels',
                null,
                array(
                    'homestay_uid' => 198878,
                    'homestay_name' => '太平洋海边渡假民宿',
                )
            );
            $list[] = $this->item_format(
                'http://static.zzkcdn.com/ribengushi.JPG',
                'A day with a baby who lives in Japan',
                'Soil passers first to wear a kimono is more excited ah! Here you can also find a direct booking kimono, very convenient!',
                null,
                array(
                    'homestay_uid' => 296497,
                    'homestay_name' => '翠川民宿',
                )
            );

            Util_Json::render(200, $list, null, null, false);
        }

    }

    public  function  service_format($img, $title, $content,$data){

        return array(
            'image' => $img,
            'title' => $title,
            'content' => $content,
            'type' => 'homestay',
            'topic_type' => Trans::t('service'), //'特色服务',
            'android' => array(
                'target' => 'com.youyao.taiwanlodge.zzkservice.ServiceItemDetailActivity',
                'bundle' => array(
                    'SERVICE_ID' => $data['service_id'],
                )

            ),
            'ios' => array(
                'target' => 'ServiceDetailViewController',
                'storyboard' => 0,
                'bundle' => array(
                    'serviceId' => (string) $data['service_id'],

                )
            )
        );
//        return  Push_Pusher::service_recommend_push($data['serviceId']);
    }


    public function item_format($img, $title, $content, $url, $type = 'webview',$data)
    {
        if (is_array($type)) {
            return array(
                'image' => $img,
                'title' => $title,
                'content' => $content,
                'type' => 'homestay',
                'topic_type' => Trans::t('Characteristic_bnb'),
                'android' => array(
                    'target' => 'com.youyao.taiwanlodge.room.HomestayDetailNew_Activity',
                    'bundle' => array(
                        'homestayUid' => strval($type['homestay_uid']),
                        'homestayName' => $type['homestay_name'],
                    ),
                ),
                'ios' => array(
                    'target' => 'RoomListViewController',
                    'storyboard' => 0,
                    'bundle' => array(
                        'homestayUid' => strval($type['homestay_uid']),
                        'homeName' => $type['homestay_name'],
                    ),
                ),
            );
        } else {
            return array(
                'image' => $img,
                'title' => $title,
                'content' => $content,
                'type' => $type,
                'topic_type' => Trans::t('Original_articles')
,
                'android' => array(
                    'target' => 'com.youyao.taiwanlodge.WebView_Activity',
                    'bundle' => array(
                        'url' => $url,
                    ),
                ),
                'ios' => array(
                    'target' => 'WebViewController',
                    'bundle' => array(
                        'url' => $url,
                    ),
                    'storyboard' => 1,
                ),
            );
        }

    }

}
