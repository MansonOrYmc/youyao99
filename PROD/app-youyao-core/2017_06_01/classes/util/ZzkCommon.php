<?php

// zzk common util functions
// add by andrew 2015-04-27

class Util_ZzkCommon
{

  public static function unit_test_phone_text()
  {
    $phoneTexts = array("+886-49-2855311   886-49-2856888", "049-2850116/0492850323(这个)",
                      "049-2919743（成交订单要确认）0920080630", "049-2850116/(886)0492850323(这个)",
                      " 電　話：049-2918 862  手　機：0912-431 655", "0933-993654 / 03-8522225 / 03-8513032 ",
                      "(03) 9954800", "（02）2893 - 9922");

    foreach ($phoneTexts as $phoneText) {
      $results = Util_ZzkCommon::phone_extract_numbers($phoneText, 10);
      echo "matched: $phoneText => ".implode(" | ", $results)."\n";
    }
  }
  
  public static function phone_combine_numbers($dest_id, $phoneText1)
  {
    $args = func_get_args();
    $dest_id = array_shift($args);
    $results = array();
    $hsCallNumber = array();
    foreach ($args as $phoneText) {
      $phoneText = trim($phoneText);
      if (!empty($phoneText)) {
        $hsCallNumber[] = $phoneText;
      }
    }
    return $hsCallNumber;
  }

  /**
   *   传入可变长度参数的电话号码，返回格式化后的电话号码数组
   */
  public static function phone_format_numbers($dest_id, $phoneText1)
  {
    $args = func_get_args();
    $dest_id = array_shift($args);
    $results = array();
    foreach ($args as $phoneText) {
      $phoneNums = Util_ZzkCommon::phone_extract_numbers($phoneText, $dest_id);
      //echo "matched: $phoneText => ".implode(" | ", $phonNums)."\n";
      $results = array_merge($results, $phoneNums);
    }

    return array_unique($results);
  }

  //
  // add by andrew 2015-04-27
  // format taiwan phone number
  // 手机号码 09xxxxxxxx  8位
  // 02xxxxxxxx  8位 台北、新北、基隆
  // 04xxxxxxxx  8位 台中
  // 049xxxxxxx  8位 南投
  // 03xxxxxxx   7位 桃园、新竹、花莲、宜兰、苗栗
  // 输入包含电话号码的字符串，输出去除国际区号，以及开头0，和其他连接字符的纯数字号码()
  public static function phone_extract_numbers($phoneText, $dest_id)
  {
    if (preg_match_all("/((\(|（)[0-9\-\+]+(\)|）))?[0-9\-\+\s]+/", trim($phoneText), $matches, PREG_SET_ORDER)) {
      $phoneNums = array();
      foreach ($matches as $val) {
        $phoneText = trim($val[0]);
        if (strlen($phoneText) < 7) continue;

        if (preg_match("/\s+/", $phoneText)) {
          $phoneTexts = Util_ZzkCommon::phone_split_by_space($phoneText, $dest_id);
        } else {
          $phoneTexts = array($phoneText);
        }

        foreach ($phoneTexts as $ptext) {
          $phoneNum = Util_ZzkCommon::phone_remove_area_code($ptext, $dest_id);
          //echo "for: $ptext => $phoneNum \n";
          $phoneNums[] = Util_ZzkCommon::phone_prefix_area_code($phoneNum, $dest_id);
        }
      }
      return $phoneNums;
    }

    return array();
  }

  //
  // private functions
  //

  private static function phone_split_by_space($phoneText, $deist_id)
  {
    $pts = preg_split("/\s+/", $phoneText);
    if (empty($pts)) return array();

    $phoneTexts = array();
    $pt = trim($pts[0]);
    for ($i = 1; $i < count($pts); $i++) {
      if (preg_match("/^(0|\+?886|\(\d+\))/", trim($pts[$i]))) {
        $phoneTexts[] = $pt;
        $pt = trim($pts[$i]);
      } else {
        $pt .= trim($pts[$i]);
      }
    }
    $phoneTexts[] = $pt;

    return $phoneTexts;
  }

  private static function phone_remove_area_code($phoneText, $dest_id)
  {
    $phoneNum = trim($phoneText);
    if ($dest_id == 10) {
      $phoneNum = preg_replace("/\+886/", "", $phoneNum);
    }
    $phoneNum = preg_replace("/[\-\+\s\(\)（）]/", "", $phoneNum);
    $phoneNum = preg_replace("/^0/", "", $phoneNum);
    if (strlen($phoneNum) > 8) {
      if ($dest_id == 10) {
        $phoneNum = preg_replace("/^886/", "", $phoneNum);
      }
      $phoneNum = preg_replace("/^0/", "", $phoneNum);
    }
    return $phoneNum;
  }

  private static function phone_prefix_area_code($phoneText, $dest_id)
  {
    switch ($dest_id) {
      case 10:
        $phoneNumber = "+886-".$phoneText;
        break;
      
      default:
        $phoneNumber = $phoneText;
        break;
    }

    return $phoneNumber;
  }

    private static $sd="皑蔼碍爱翱袄奥坝罢摆败颁办绊帮绑镑谤剥饱宝报鲍辈贝钡狈备惫绷笔毕毙币闭边编贬变辩辫标鳖别瘪濒滨宾摈饼并拨钵铂驳卜补财参蚕残惭惨灿苍舱仓沧厕侧册测层诧搀掺蝉馋谗缠铲产阐颤场尝长偿肠厂畅钞车彻尘陈衬撑称惩诚骋痴迟驰耻齿炽冲虫宠畴踌筹绸丑橱厨锄雏础储触处传疮闯创锤纯绰辞词赐聪葱囱从丛凑蹿窜错达带贷担单郸掸胆惮诞弹当挡党荡档捣岛祷导盗灯邓敌涤递缔颠点垫电淀钓调迭谍叠钉顶锭订丢东动栋冻斗犊独读赌镀锻断缎兑队对吨顿钝夺堕鹅额讹恶饿儿尔饵贰发罚阀珐矾钒烦范贩饭访纺飞诽废费纷坟奋愤粪丰枫锋风疯冯缝讽凤肤辐抚辅赋复负讣妇缚该钙盖干赶秆赣冈刚钢纲岗皋镐搁鸽阁铬个给龚宫巩贡钩沟构购够蛊顾剐关观馆惯贯广规硅归龟闺轨诡柜贵刽辊滚锅国过骇韩汉号阂鹤贺横轰鸿红后壶护沪户哗华画划话怀坏欢环还缓换唤痪焕涣黄谎挥辉毁贿秽会烩汇讳诲绘荤浑伙获货祸击机积饥讥鸡绩缉极辑级挤几蓟剂济计记际继纪夹荚颊贾钾价驾歼监坚笺间艰缄茧检碱硷拣捡简俭减荐槛鉴践贱见键舰剑饯渐溅涧将浆蒋桨奖讲酱胶浇骄娇搅铰矫侥脚饺缴绞轿较秸阶节茎鲸惊经颈静镜径痉竞净纠厩旧驹举据锯惧剧鹃绢杰洁结诫届紧锦仅谨进晋烬尽劲荆觉决诀绝钧军骏开凯颗壳课垦恳抠库裤夸块侩宽矿旷况亏岿窥馈溃扩阔蜡腊莱来赖蓝栏拦篮阑兰澜谰揽览懒缆烂滥捞劳涝乐镭垒类泪篱离里鲤礼丽厉励砾历沥隶俩联莲连镰怜涟帘敛脸链恋炼练粮凉两辆谅疗辽镣猎临邻鳞凛赁龄铃凌灵岭领馏刘龙聋咙笼垄拢陇楼娄搂篓芦卢颅庐炉掳卤虏鲁赂禄录陆驴吕铝侣屡缕虑滤绿峦挛孪滦乱抡轮伦仑沦纶论萝罗逻锣箩骡骆络妈玛码蚂马骂吗买麦卖迈脉瞒馒蛮满谩猫锚铆贸么霉没镁门闷们锰梦谜弥觅幂绵缅庙灭悯闽鸣铭谬谋亩钠纳难挠脑恼闹馁内拟腻撵捻酿鸟聂啮镊镍柠狞宁拧泞钮纽脓浓农疟诺欧鸥殴呕沤盘庞赔喷鹏骗飘频贫苹凭评泼颇扑铺朴谱栖凄脐齐骑岂启气弃讫牵扦钎铅迁签谦钱钳潜浅谴堑枪呛墙蔷强抢锹桥乔侨翘窍窃钦亲寝轻氢倾顷请庆琼穷趋区躯驱龋颧权劝却鹊确让饶扰绕热韧认纫荣绒软锐闰润洒萨鳃赛三伞丧骚扫涩杀纱筛晒删闪陕赡缮伤赏烧绍赊摄慑设绅审婶肾渗声绳胜圣师狮湿诗尸时蚀实识驶势适释饰视试寿兽枢输书赎属术树竖数帅双谁税顺说硕烁丝饲耸怂颂讼诵擞苏诉肃虽随绥岁孙损笋缩琐锁獭挞抬态摊贪瘫滩坛谭谈叹汤烫涛绦讨腾誊锑题体屉条贴铁厅听烃铜统头秃图涂团颓蜕脱鸵驮驼椭洼袜弯湾顽万网韦违围为潍维苇伟伪纬谓卫温闻纹稳问瓮挝蜗涡窝卧呜钨乌污诬无芜吴坞雾务误锡牺袭习铣戏细虾辖峡侠狭厦吓锨鲜纤咸贤衔闲显险现献县馅羡宪线厢镶乡详响项萧嚣销晓啸蝎协挟携胁谐写泻谢锌衅兴汹锈绣虚嘘须许叙绪续轩悬选癣绚学勋询寻驯训讯逊压鸦鸭哑亚讶阉烟盐严颜阎艳厌砚彦谚验鸯杨扬疡阳痒养样瑶摇尧遥窑谣药爷页业叶医铱颐遗仪彝蚁艺亿忆义诣议谊译异绎荫阴银饮隐樱婴鹰应缨莹萤营荧蝇赢颖哟拥佣痈踊咏涌优忧邮铀犹游诱舆鱼渔娱与屿语吁御狱誉预驭鸳渊辕园员圆缘远愿约跃钥岳粤悦阅云郧匀陨运蕴酝晕韵杂灾载攒暂赞赃脏凿枣灶责择则泽贼赠扎札轧铡闸栅诈斋债毡盏斩辗崭栈战绽张涨帐账胀赵蛰辙锗这贞针侦诊镇阵挣睁狰争帧郑证织职执纸挚掷帜质滞钟终种肿众诌轴皱昼骤猪诸诛烛瞩嘱贮铸筑驻专砖转赚桩庄装妆壮状锥赘坠缀谆着浊兹资渍踪综总纵邹诅组钻台";
    private static $td="皚藹礙愛翺襖奧壩罷擺敗頒辦絆幫綁鎊謗剝飽寶報鮑輩貝鋇狽備憊繃筆畢斃幣閉邊編貶變辯辮標鼈別癟瀕濱賓擯餅並撥缽鉑駁蔔補財參蠶殘慚慘燦蒼艙倉滄廁側冊測層詫攙摻蟬饞讒纏鏟産闡顫場嘗長償腸廠暢鈔車徹塵陳襯撐稱懲誠騁癡遲馳恥齒熾沖蟲寵疇躊籌綢醜櫥廚鋤雛礎儲觸處傳瘡闖創錘純綽辭詞賜聰蔥囪從叢湊躥竄錯達帶貸擔單鄲撣膽憚誕彈當擋黨蕩檔搗島禱導盜燈鄧敵滌遞締顛點墊電澱釣調叠諜疊釘頂錠訂丟東動棟凍鬥犢獨讀賭鍍鍛斷緞兌隊對噸頓鈍奪墮鵝額訛惡餓兒爾餌貳發罰閥琺礬釩煩範販飯訪紡飛誹廢費紛墳奮憤糞豐楓鋒風瘋馮縫諷鳳膚輻撫輔賦複負訃婦縛該鈣蓋幹趕稈贛岡剛鋼綱崗臯鎬擱鴿閣鉻個給龔宮鞏貢鈎溝構購夠蠱顧剮關觀館慣貫廣規矽歸龜閨軌詭櫃貴劊輥滾鍋國過駭韓漢號閡鶴賀橫轟鴻紅後壺護滬戶嘩華畫劃話懷壞歡環還緩換喚瘓煥渙黃謊揮輝毀賄穢會燴匯諱誨繪葷渾夥獲貨禍擊機積饑譏雞績緝極輯級擠幾薊劑濟計記際繼紀夾莢頰賈鉀價駕殲監堅箋間艱緘繭檢堿鹼揀撿簡儉減薦檻鑒踐賤見鍵艦劍餞漸濺澗將漿蔣槳獎講醬膠澆驕嬌攪鉸矯僥腳餃繳絞轎較稭階節莖鯨驚經頸靜鏡徑痙競淨糾廄舊駒舉據鋸懼劇鵑絹傑潔結誡屆緊錦僅謹進晉燼盡勁荊覺決訣絕鈞軍駿開凱顆殼課墾懇摳庫褲誇塊儈寬礦曠況虧巋窺饋潰擴闊蠟臘萊來賴藍欄攔籃闌蘭瀾讕攬覽懶纜爛濫撈勞澇樂鐳壘類淚籬離裏鯉禮麗厲勵礫曆瀝隸倆聯蓮連鐮憐漣簾斂臉鏈戀煉練糧涼兩輛諒療遼鐐獵臨鄰鱗凜賃齡鈴淩靈嶺領餾劉龍聾嚨籠壟攏隴樓婁摟簍蘆盧顱廬爐擄鹵虜魯賂祿錄陸驢呂鋁侶屢縷慮濾綠巒攣孿灤亂掄輪倫侖淪綸論蘿羅邏鑼籮騾駱絡媽瑪碼螞馬罵嗎買麥賣邁脈瞞饅蠻滿謾貓錨鉚貿麽黴沒鎂門悶們錳夢謎彌覓冪綿緬廟滅憫閩鳴銘謬謀畝鈉納難撓腦惱鬧餒內擬膩攆撚釀鳥聶齧鑷鎳檸獰甯擰濘鈕紐膿濃農瘧諾歐鷗毆嘔漚盤龐賠噴鵬騙飄頻貧蘋憑評潑頗撲鋪樸譜棲淒臍齊騎豈啓氣棄訖牽扡釺鉛遷簽謙錢鉗潛淺譴塹槍嗆牆薔強搶鍬橋喬僑翹竅竊欽親寢輕氫傾頃請慶瓊窮趨區軀驅齲顴權勸卻鵲確讓饒擾繞熱韌認紉榮絨軟銳閏潤灑薩鰓賽叁傘喪騷掃澀殺紗篩曬刪閃陝贍繕傷賞燒紹賒攝懾設紳審嬸腎滲聲繩勝聖師獅濕詩屍時蝕實識駛勢適釋飾視試壽獸樞輸書贖屬術樹豎數帥雙誰稅順說碩爍絲飼聳慫頌訟誦擻蘇訴肅雖隨綏歲孫損筍縮瑣鎖獺撻擡態攤貪癱灘壇譚談歎湯燙濤縧討騰謄銻題體屜條貼鐵廳聽烴銅統頭禿圖塗團頹蛻脫鴕馱駝橢窪襪彎灣頑萬網韋違圍爲濰維葦偉僞緯謂衛溫聞紋穩問甕撾蝸渦窩臥嗚鎢烏汙誣無蕪吳塢霧務誤錫犧襲習銑戲細蝦轄峽俠狹廈嚇鍁鮮纖鹹賢銜閑顯險現獻縣餡羨憲線廂鑲鄉詳響項蕭囂銷曉嘯蠍協挾攜脅諧寫瀉謝鋅釁興洶鏽繡虛噓須許敘緒續軒懸選癬絢學勳詢尋馴訓訊遜壓鴉鴨啞亞訝閹煙鹽嚴顔閻豔厭硯彥諺驗鴦楊揚瘍陽癢養樣瑤搖堯遙窯謠藥爺頁業葉醫銥頤遺儀彜蟻藝億憶義詣議誼譯異繹蔭陰銀飲隱櫻嬰鷹應纓瑩螢營熒蠅贏穎喲擁傭癰踴詠湧優憂郵鈾猶遊誘輿魚漁娛與嶼語籲禦獄譽預馭鴛淵轅園員圓緣遠願約躍鑰嶽粵悅閱雲鄖勻隕運蘊醞暈韻雜災載攢暫贊贓髒鑿棗竈責擇則澤賊贈紮劄軋鍘閘柵詐齋債氈盞斬輾嶄棧戰綻張漲帳賬脹趙蟄轍鍺這貞針偵診鎮陣掙睜猙爭幀鄭證織職執紙摯擲幟質滯鍾終種腫衆謅軸皺晝驟豬諸誅燭矚囑貯鑄築駐專磚轉賺樁莊裝妝壯狀錐贅墜綴諄著濁茲資漬蹤綜總縱鄒詛組鑽臺";
    public static function tradition2simple($sContent)
    {
        if(is_array($sContent)){
            $arr=array();
            foreach($sContent as $k=>$v){
                $arr[Util_ZzkCommon::tradition2simple($k)]=Util_ZzkCommon::tradition2simple($v);
            }
            return $arr;
        }else if(is_object($sContent)){
            $arr=array();
            foreach($sContent as $k=>$v){
                $arr[Util_ZzkCommon::tradition2simple($k)]=Util_ZzkCommon::tradition2simple($v);
            }
            return (object)$arr;
        }else {
            $iContent = mb_strlen($sContent, 'UTF-8');
            for ($i = 0; $i < $iContent; $i++) {
                $str = mb_substr($sContent, $i, 1, 'UTF-8');
                $match = mb_strpos(Util_ZzkCommon::$td, $str, null, 'UTF-8');
                $simpleCN .= ($match !== false) ? mb_substr(Util_ZzkCommon::$sd, $match, 1, 'UTF-8') : $str;
            }
            if($simpleCN) {
                $simpleCN = str_replace('坂', '阪', $simpleCN);
            }
            return $simpleCN;
        }
    }
    public static function simple2tradition($sContent)
    {
        if(is_array($sContent)){
            $arr=array();
            foreach($sContent as $k=>$v){
                $arr[Util_ZzkCommon::simple2tradition($k)]=Util_ZzkCommon::simple2tradition($v);
            }
            return $arr;
        }else if(is_object($sContent)){
            $arr=array();
            foreach($sContent as $k=>$v){
                $arr[Util_ZzkCommon::simple2tradition($k)]=Util_ZzkCommon::simple2tradition($v);
            }
            return (object)$arr;
        }
        else
        {
        $iContent=mb_strlen($sContent,'UTF-8');
        for($i=0;$i<$iContent;$i++){
            $str=mb_substr($sContent,$i,1,'UTF-8');
//            $str=Util_ZzkCommon::unicode_decode($str);
            $match=mb_strpos(Util_ZzkCommon::$sd,$str,null,'UTF-8');
            $traditionalCN.=($match!==false )?mb_substr(Util_ZzkCommon::$td,$match,1,'UTF-8'):$str;
        }

            if($traditionalCN){
            $traditionalCN = str_replace('阿裏山','阿里山',$traditionalCN);
            $traditionalCN = str_replace('莫幹山','莫干山',$traditionalCN);
            $traditionalCN = str_replace('劄幌','札幌',$traditionalCN);
            $traditionalCN = str_replace('坂','阪',$traditionalCN);
            }
            return $traditionalCN;
        }
    }
    public function unicode_decode($name)
    {
        // 转换编码，将Unicode编码转换成可以浏览的utf-8编码
        $pattern = '/([\w]+)|(\\\u([\w]{4}))/i';
        preg_match_all($pattern, $name, $matches);
        if (!empty($matches))
        {
            $name = '';
            for ($j = 0; $j < count($matches[0]); $j++)
            {
                $str = $matches[0][$j];
                if (strpos($str, '\\u') === 0)
                {
                    $code = base_convert(substr($str, 2, 2), 16, 10);
                    $code2 = base_convert(substr($str, 4), 16, 10);
                    $c = chr($code).chr($code2);
                    $c = iconv('UCS-2', 'UTF-8', $c);
                    $name .= $c;
                }
                else
                {
                    $name .= $str;
                }
            }
        }
        return $name;
    }

    public static function zzk_echo($str){
            if($_REQUEST["version"]<'4.9.3'&&$_REQUEST["os"]=='ios'){
                echo $str;
            }else{
                $multilang = 12;
                if(isset($_REQUEST["multilang"])&&!empty($_REQUEST["multilang"]))
                {
                    $multilang = $_REQUEST["multilang"];
                }
                if($multilang==10){
                    if(!is_null(json_decode($str))){
                        $str = json_decode($str);
                        $str = Util_ZzkCommon::simple2tradition($str);
                        header('Content-Type:application/json');
                        echo json_encode($str);
                    }else {
                        $str = Util_ZzkCommon::simple2tradition($str);
                        echo $str;
                    }
                }elseif($multilang==12){
                    if(!is_null(json_decode($str))){
                        $str = json_decode($str);
                        $str = Util_ZzkCommon::tradition2simple($str);
                        header('Content-Type:application/json');
                        echo json_encode($str);
                    }else {
                        $str = Util_ZzkCommon::tradition2simple($str);
                        echo $str;
                    }
                }else{
                    echo $str;
                }
            }
    }

    public static function zzk_pre_echo($str){
        //为了解决 订单详情进去都会闪退 bug
        $str_z = '';
        if($_REQUEST["version"]<'4.9.3'&&$_REQUEST["os"]=='ios'){
            $str_z .= $str;
        }else{
            $multilang = 12;
            if(isset($_REQUEST["multilang"])&&!empty($_REQUEST["multilang"]))
            {
                $multilang = $_REQUEST["multilang"];
            }
            if($multilang==10){
                if(!is_null(json_decode($str))){
                    $str = json_decode($str);
                    $str = Util_ZzkCommon::simple2tradition($str);
                    header('Content-Type:application/json');
                    $str_z .= json_encode($str);
                }else {
                    $str = Util_ZzkCommon::simple2tradition($str);
                    $str_z .= $str;
                }
            }elseif($multilang==12){
                if(!is_null(json_decode($str))){
                    $str = json_decode($str);
                    $str = Util_ZzkCommon::tradition2simple($str);
                    header('Content-Type:application/json');
                    $str_z .= json_encode($str);
                }else {
                    $str = Util_ZzkCommon::tradition2simple($str);
                    $str_z .= $str;
                }
            }else{
                $str_z .= $str;
            }
        }
        return $str_z;
    }

    public static function get_orderstats_string($status){
        $list = array(
                    '0'=>'待处理',
                    '1'=>'处理中',
                    '2'=>'成交',
                    '3'=>'关闭',
                    '4'=>'待支付',
                    '5'=>'关闭',
                    '6'=>'已汇款',
                    '7'=>'申请退款',
                    '8'=>'退款已确认',
                    '11'=>'已退款',
                    '12'=>'退款取消',
            );

        return $list[$status];
    }

    public static function get_multilang_customer_orderstatus($status, $dest_id=12) {
        $list = array(
            '0' => 'Unread',
            '1' => 'Unread', // 取消处理中的状态
            '2' => 'has_dealed', // Trans::t('Not Bank transferred')
            '3' => 'hasbeencancel',
            '4' => 'Waiting to pay',
            '5' => 'hasbeencancel',
            '6' => 'has_dealed', // Trans::t('Bank transferred')
            '7' => 'refund_applying',
            '8' => 'refunding',
            '9' => 'refunded',
        );

        return Trans::t($list[$status], $dest_id);
    }

  public static function extractLocidFromLocTypeCode($locTypeCode) {
    $codes = explode(',', $locTypeCode);
    if (empty($codes)) return "";

    return trim($codes[count($codes) - 1]);
  }

  public static function getLocTypeCodeFromLocid($locid) {
    $codes = explode(',', $locTypeCode);
    if (empty($codes)) return "";

    return trim($codes[count($codes) - 1]);
  }

    public static function zzk_get_dates($in,$out){
        if(strtotime($in) > strtotime($out)){return false;}
        $dates = array();
        $start = strtotime($in);
        $end = strtotime($out);
        while($start<=$end){
            $dates[]=date('Y-m-d',$start);
            $start+=86400;
        }
        return $dates;
    }

}
