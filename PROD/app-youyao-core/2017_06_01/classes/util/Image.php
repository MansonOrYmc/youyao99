<?php

define('Const_Host_Domain', 'http://taiwan.youyao.com');

class Util_Image {

    public function __construct() {
        $apf = APF::get_instance();
        $this->zzkcdn_img1 = $apf->get_config('zzkcdn_img1');
    }

  //$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster"); // LKYou master
  //$pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave"); // LKYou slave
  //$pdo = APF_DB_Factory::get_instance()->get_pdo("master"); // one_db master
  //$pdo = APF_DB_Factory::get_instance()->get_pdo("slave"); // one_db slave
    
     /** add by Leon 14-01-15
          **  pic style
          **  qiniu style 
          **     homepic800x600.jpg 
          **     homestay640x480.jpg
          **     room210x130.jpg
          **     roompic.jpg          # 420x300
          **     roomthumb.jpg          # 120x75
          **     userphoto.jpg          # 190x200
          **     userphotomedium.jpg     # 100x100
          **     userphotothumb.jpg     # 50x50
          **     homepagebigimg.jpg     # 1280x600
          **     original.jpg             # ԭͼ 
     */ 
     public static  function imglink($name,$style){
          if(strpos($name,'public')!==false){
            
              $name = strtr($name, array(
                   'public://field/image[current-date:raw]/' => '',
                   'public/field/image[current-date:raw]/' => '',
                   'public://' => '',
                   'public/' => '',
              ));
               return 'http://img1.zzkcdn.com/'.$name."-".$style;
//               return 'http://7lrxoz.com5.z0.glb.qiniucdn.com'.str_replace('public','',$name)."-".$style;
          }else{
               return 'http://img1.zzkcdn.com/'.$name.'/2000x1500.jpg-'.$style;
//               return 'http://7lrxoz.com5.z0.glb.qiniucdn.com/'.$name.'/2000x1500.jpg-'.$style;
          }
     }

    /**
     * generate img src from given name
     * @param $name
     * @param $style
     * @return string $src
     * @author genyiwang <genyiwang@youyao.com>
     */
    public function get_imgsrc_by_name($name, $style, $version=null) {
        $name = strtr($name, array(
           'public://field/image[current-date:raw]/' => '',
           'public/field/image[current-date:raw]/' => '',
           'public://' => '',
           'public/' => '',
        ));
        $match = preg_match('/zzk_([0-9]+)/', $name, $matches);
        $version = $match ? 0 : 1;
        // 版本通过文件名判断
        //$version = $version!==null ? intval($version) : $inter_version;
        $apf = APF::get_instance();
        $zzkcdn_img1 = $apf->get_config('zzkcdn_img1');
        $user = Util_Signin::get_user();
        if($user->roles[3] == 'administrator' && $_REQUEST['origin']) {
             $apf = APF::get_instance();
             $request = $apf->get_request();
             $client_ip = $request->get_client_ip();
             $allow_patterns = @$apf->get_config("debug_allow_patterns");
             if (is_array($allow_patterns)) {
                 foreach ($allow_patterns as $pattern) {
                     if (preg_match($pattern, $client_ip)) {
                        $is_allow = true;
                        break;
                     }
                 }
             }
            if($is_allow) {
                return "http://img.youyao.com" . "/$name/2000x1500.jpg";
            }
        }
        if($version == 1) {
            return $zzkcdn_img1 . "/$name/2000x1500.jpg-$style";
        } else {
            return $zzkcdn_img1 . "/$name-$style";
        }
    }

     public static function img_version($fid) {
          $oldnum = 200000;
          if($fid>=$oldnum){
                  return 1;
          }else{
                  return 0;
          }
     }


    public static function img_default_inlist() {
        return "http://pages.youyao.com/a/pic/584687.jpg";
    }  

    public static function photo_default() {
        return "http://bbs.youyao.com/uc_server/images/noavatar_middle.gif";
    }  


     public static function imglink_new($name,$style,$dest_pic=0){
          if(strpos($name,'youyao.com')){
               return Util_Image::imglink_old($name,$style);
          }else{
               /** add by Leon 14-01-15
               **  pic style
               **  qiniu style 
               **     homepic800x600.jpg 
               **     homestay640x480.jpg
               **     room210x130.jpg
               **     roompic.jpg          # 420x300
               **     roomthumb.jpg          # 120x75
               **     userphoto.jpg          # 190x200
               **     userphotomedium.jpg     # 100x100
               **     userphotothumb.jpg     # 50x50
               **     homepagebigimg.jpg     # 1280x600
               **     original.jpg             # 原图 
               */ 
               $use_cdn = 1 ;
               if($use_cdn == 1 && $dest_pic==0){ // use qiniu cdn
                    $style = $style ? "-".$style : '';
                    return 'http://img1.zzkcdn.com/'.$name.'/2000x1500.jpg'.$style;
//                    return 'http://7lrxoz.com5.z0.glb.qiniucdn.com/'.$name.'/2000x1500.jpg'.$style;
               }else{
                    $style = Util_Image::img_style_config($style);
                    return 'http://image.youyao.com/'.$name.'/'.$style;
               }
          }
     }

     public static function img_style_config($style,$v=0){
          $conf = array(
               'homepic800x600.jpg ' => '800x600.jpg',
               'homestay640x480.jpg' => '640x480.jpg',
               'room210x130.jpg' => '210x130.jpg',
               'roompic.jpg' => '420x300.jpg',
               'roomthumb.jpg' => '120x75.jpg',
               'userphoto.jpg' => '190x200.jpg',
               'userphotomedium.jpg' => '100x100.jpg',
               'userphotothumb.jpg' => '50x50.jpg',
               'homepagebigimg.jpg' => '1280x600.jpg',
               'original.jpg' => '2000x1500.jpg',
          );     
          if($v==0){
               return($conf[$style]);
          }else{
               $flip = array_flip($conf);
               return($flip[$style]);
          }
     }

     public static function imglink_old($url,$style){
     if(!empty($style)){
      $imgurl = "http://img1.zzkcdn.com/";
//      $imgurl = "http://7lrxoz.com5.z0.glb.qiniucdn.com/";
      $history = 199999;
     /*
      if(file_exists('/data2/tonycai/one.youyao.com/picupload/nowfid.txt')){
        $history = trim(file_get_contents('/data2/tonycai/one.youyao.com/picupload/nowfid.txt'));
      }
     */
      if(preg_match("/current-date/",$url)){
        $url = str_replace("field/image[current-date:raw]/","",$url);
      }
        switch($style)
        {
        case 'room':
          $fid =  str_replace(array(Const_Host_Domain."/sites/default/files/styles/galleryformatter_slide/public/zzk_",".jpg",".png",".gif"),"",$url);
          $fidn = preg_replace("/_[0-9]/","",$fid);
          if((int)$fidn < $history && preg_match("/galleryformatter_slide/",$url) && is_numeric($fidn) ){
     //-roompic.jpg
            return $imgurl.str_replace(Const_Host_Domain."/sites/default/files/styles/galleryformatter_slide/public/","",$url)."-homepic800x600.jpg";
          }else{
            return $url;
          }
        case 'userphoto':
          $fid =  str_replace(array(Const_Host_Domain."/sites/default/files/styles/userphoto/public/zzk_",".jpg",".png",".gif"),"",$url);
          $fidn = preg_replace("/_[0-9]/","",$fid);
          if((int)$fidn < $history && preg_match("/userphoto/",$url) && is_numeric($fidn)){
            return $imgurl.str_replace(Const_Host_Domain."/sites/default/files/styles/userphoto/public/","",$url)."-userphoto.jpg";
          }else{
            return $url;
          }
        case 'room210x130':
          $fid =  str_replace(array(Const_Host_Domain."/sites/default/files/styles/galleryformatter_slide/public/zzk_",".jpg",".png",".gif"),"",$url);
          $fidn = preg_replace("/_[0-9]/","",$fid);
          if((int)$fidn < $history && preg_match('/galleryformatter_slide/', $url) && is_numeric($fidn)){
            return $imgurl.str_replace(Const_Host_Domain."/sites/default/files/styles/galleryformatter_slide/public/","",$url)."-room210x130.jpg";
          }else{
            return $url;
          }
        case 'homestay640x480':
          $fid =  str_replace(array(Const_Host_Domain."/sites/default/files/styles/galleryformatter_slide/public/zzk_",".jpg",".png",".gif"),"",$url);
          $fidn = preg_replace("/_[0-9]/","",$fid);
          if((int)$fidn < $history && preg_match('/galleryformatter_slide/',$url) && is_numeric($fidn)){
            return $imgurl.str_replace(Const_Host_Domain."/sites/default/files/styles/galleryformatter_slide/public/","",$url)."-homestay640x480.jpg";
          }else{
            return $url;
          }
        case 'userphotothumb':
          $fid =  str_replace(array(Const_Host_Domain."/sites/default/files/styles/userphoto/public/zzk_",".jpg",".png",".gif"),"",$url);
          $fidn = preg_replace("/_[0-9]/","",$fid);
          if((int)$fidn < $history && preg_match('/userphoto/',$url) && is_numeric($fidn)){
            return $imgurl.str_replace(Const_Host_Domain."/sites/default/files/styles/userphoto/public/","",$url)."-userphotothumb.jpg";
          }else{
            return $url;
          }
        case 'roomthumb':
          $fid =  str_replace(array(Const_Host_Domain."/sites/default/files/styles/galleryformatter_thumb/public/zzk_",".jpg",".png",".gif"),"",$url);
          $fidn = preg_replace("/_[0-9]/","",$fid);
          if((int)$fidn < $history && preg_match('/galleryformatter_thumb/',$url) && is_numeric($fidn)){
            return $imgurl.str_replace(Const_Host_Domain."/sites/default/files/styles/galleryformatter_thumb/public/","",$url)."-roomthumb.jpg";
          }else{
            return $url;
          }
        case 'userphotomedium':
          $fid =  str_replace(array(Const_Host_Domain."/sites/default/files/styles/userphoto/public/zzk_",".jpg",".png",".gif"),"",$url);
          $fidn = preg_replace("/_[0-9]/","",$fid);
          if((int)$fidn < $history && preg_match('/userphoto/',$url) && is_numeric($fidn)){
            return $imgurl.str_replace(Const_Host_Domain."/sites/default/files/styles/userphoto/public/","",$url)."-userphotomedium.jpg";
          }else{
            return $url;
          }
        case 'original':
          $fid = str_replace(array(Const_Host_Domain."/sites/default/files/styles/large/public/zzk_",".jpg",".png",".gif"),"",$url);
          $fidn = preg_replace("/_[0-9]/","",$fid);
          if((int)$fidn < $history && preg_match('/large/',$url) && is_numeric($fidn)){
            return $imgurl.str_replace(Const_Host_Domain."/sites/default/files/styles/large/public/","",$url)."-original.jpg";
          }else{
            return $url;
          }
        case 'homepic800x600.jpg':
          $fid =  str_replace(array(Const_Host_Domain."/sites/default/files/styles/galleryformatter_slide/public/zzk_",".jpg",".png",".gif"),"",$url);
          $fidn = preg_replace("/_[0-9]/","",$fid);
          if((int)$fidn < $history && preg_match("/galleryformatter_slide/",$url) && is_numeric($fidn) ){
     //-roompic.jpg
            return $imgurl.str_replace(Const_Host_Domain."/sites/default/files/styles/galleryformatter_slide/public/","",$url)."-homepic800x600.jpg";
          }else{
            return $url;
          }
        case 'userphoto.jpg':
          $fid =  str_replace(array(Const_Host_Domain."/sites/default/files/styles/userphoto/public/zzk_",".jpg",".png",".gif"),"",$url);
          $fidn = preg_replace("/_[0-9]/","",$fid);
          if((int)$fidn < $history && preg_match("/userphoto/",$url) && is_numeric($fidn)){
            return $imgurl.str_replace(Const_Host_Domain."/sites/default/files/styles/userphoto/public/","",$url)."-userphoto.jpg";
          }else{
            return $url;
          }
        case 'room210x130.jpg':
          $fid =  str_replace(array(Const_Host_Domain."/sites/default/files/styles/galleryformatter_slide/public/zzk_",".jpg",".png",".gif"),"",$url);
          $fidn = preg_replace("/_[0-9]/","",$fid);
          if((int)$fidn < $history && preg_match('/galleryformatter_slide/', $url) && is_numeric($fidn)){
            return $imgurl.str_replace(Const_Host_Domain."/sites/default/files/styles/galleryformatter_slide/public/","",$url)."-room210x130.jpg";
          }else{
            return $url;
          }
        case 'homestay640x480.jpg':
          $fid =  str_replace(array(Const_Host_Domain."/sites/default/files/styles/galleryformatter_slide/public/zzk_",".jpg",".png",".gif"),"",$url);
          $fidn = preg_replace("/_[0-9]/","",$fid);
          if((int)$fidn < $history && preg_match('/galleryformatter_slide/',$url) && is_numeric($fidn)){
            return $imgurl.str_replace(Const_Host_Domain."/sites/default/files/styles/galleryformatter_slide/public/","",$url)."-homestay640x480.jpg";
          }else{
            return $url;
          }
        case 'userphotothumb.jpg':
          $fid =  str_replace(array(Const_Host_Domain."/sites/default/files/styles/userphoto/public/zzk_",".jpg",".png",".gif"),"",$url);
          $fidn = preg_replace("/_[0-9]/","",$fid);
          if((int)$fidn < $history && preg_match('/userphoto/',$url) && is_numeric($fidn)){
            return $imgurl.str_replace(Const_Host_Domain."/sites/default/files/styles/userphoto/public/","",$url)."-userphotothumb.jpg";
          }else{
            return $url;
          }
        case 'roomthumb.jpg':
          $fid =  str_replace(array(Const_Host_Domain."/sites/default/files/styles/galleryformatter_thumb/public/zzk_",".jpg",".png",".gif"),"",$url);
          $fidn = preg_replace("/_[0-9]/","",$fid);
          if((int)$fidn < $history && preg_match('/galleryformatter_thumb/',$url) && is_numeric($fidn)){
            return $imgurl.str_replace(Const_Host_Domain."/sites/default/files/styles/galleryformatter_thumb/public/","",$url)."-roomthumb.jpg";
          }else{
            return $url;
          }
        case 'userphotomedium.jpg':
          $fid =  str_replace(array(Const_Host_Domain."/sites/default/files/styles/userphoto/public/zzk_",".jpg",".png",".gif"),"",$url);
          $fidn = preg_replace("/_[0-9]/","",$fid);
          if((int)$fidn < $history && preg_match('/userphoto/',$url) && is_numeric($fidn)){
            return $imgurl.str_replace(Const_Host_Domain."/sites/default/files/styles/userphoto/public/","",$url)."-userphotomedium.jpg";
          }else{
            return $url;
          }
        case 'original.jpg':
          $fid = str_replace(array(Const_Host_Domain."/sites/default/files/styles/large/public/zzk_",".jpg",".png",".gif"),"",$url);
          $fidn = preg_replace("/_[0-9]/","",$fid);
          if((int)$fidn < $history && preg_match('/large/',$url) && is_numeric($fidn)){
            return $imgurl.str_replace(Const_Host_Domain."/sites/default/files/styles/large/public/","",$url)."-original.jpg";
          }else{
            return $url;
          }
        case 'roompic.jpg':
          $fid =  str_replace(array(Const_Host_Domain."/sites/default/files/styles/galleryformatter_slide/public/zzk_",".jpg",".png",".gif"),"",$url);
          $fidn = preg_replace("/_[0-9]/","",$fid);
          if((int)$fidn < $history && preg_match("/galleryformatter_slide/",$url) && is_numeric($fidn) ){
     //-roompic.jpg
            return $imgurl.str_replace(Const_Host_Domain."/sites/default/files/styles/galleryformatter_slide/public/","",$url)."-homepic800x600.jpg";
          }else{
            return $url;
          }
        case 'sys_theme':
          if(strpos($url,"galleryformatter_slide")){
            return Util_Image::imglink_new($url,"room");
          }elseif(strpos($url,"galleryformatter_thumb")){
            return Util_Image::imglink_new($url,"roomthumb");
          }elseif(strpos($url,"thumbnail")){
            $fid = str_replace(array(Const_Host_Domain."/sites/default/files/styles/thumbnail/public/zzk_",".jpg",".png",".gif"),"",$url);
            $fidn = preg_replace("/_[0-9]/","",$fid);
            if((int)$fidn < $history && is_numeric($fidn)){
              return $imgurl.str_replace(Const_Host_Domain."/sites/default/files/styles/thumbnail/public/","",$url)."-userphotomedium.jpg";
            }else{
              return $url;
            }
          }elseif(strpos($url,"medium")){
            $fid = str_replace(array(Const_Host_Domain."/sites/default/files/styles/medium/public/zzk_",".jpg",".png",".gif"),"",$url);
            $fidn = preg_replace("/_[0-9]/","",$fid);
            if((int)$fidn < $history && is_numeric($fidn)){
              return $imgurl.str_replace(Const_Host_Domain."/sites/default/files/styles/medium/public/","",$url)."-userphoto.jpg";
            }else{
              return $url;
            }
          }elseif(strpos($url,"large")){
            $fid = str_replace(array(Const_Host_Domain."/sites/default/files/styles/medium/large/zzk_",".jpg",".png",".gif"),"",$url);
            $fidn = preg_replace("/_[0-9]/","",$fid);
            if((int)$fidn < $history && is_numeric($fidn)){
              return $imgurl.str_replace(Const_Host_Domain."/sites/default/files/styles/large/public/","",$url)."-homestay640x480.jpg";
            }else{
              return $url;
            }
          }elseif(strpos($url,"userphoto")){
            return Util_Image::imglink_new($url,"userphoto");
          }else{
            return $url;
          }
        }
     }else{
      return $url;
     }
     }

     //列表缩略图
     public static function getroomsmallimage($images)
     {
         $imageurl = $images[0];
         if ($imageurl) {
             return $imageurl;
         } else {
             return 'http://pages.youyao.com/a/taiwan/default_room.jpg';
         }
     }

     //图片
     public static function getroomimages($rid)
     {
     //    $result = db_query("SELECT file.uri FROM {field_data_field_image} image, {file_managed} file  WHERE image.entity_id = $rid and image.field_image_fid = file.fid and image.entity_type='node' and image.bundle='article' AND file.status = 1");
         $result = Util_Image::zzk_db_file_image($rid);
         if (empty($result)) {
             $images[] = 'http://pages.youyao.com/a/taiwan/default_room.jpg';

         } else {
             foreach ($result as $row) {
                 $image = Util_Image::zzk_db_file_managed($row);
                 if (Util_Image::img_version($row) == 1) {
                     $image = Util_Image::imglink_new($image, 'homestay640x480.jpg');
                 } else {
                     $image = str_replace('public://', Const_Host_Domain.'/sites/default/files/styles/galleryformatter_slide/public/', $image);
                     $image = Util_Image::imglink_new($image, 'homestay640x480');
                 }

                 $images[] = $image;
             }
         }
         return $images;
     }


     public static function zzk_db_file_image($entity_id, $entity_type='node', $bundle='article', $version = 0){
       $results = array('0'=>'');
       $matches = array();
       if(empty($entity_id)){
         return $results;
       }
       // $query = db_select('field_data_field_image', 't');
       // $query->fields('t', array('field_image_fid','field_image_version'));
       // $query->condition('t.entity_id', $entity_id, '=');
       // $query->condition('t.entity_type', $entity_type, '=');
       // $query->condition('t.bundle', $bundle, '=');
       // $results = $query->range(0,36)->orderBy('delta', 'asc')->execute();
    $pdo = APF_DB_Factory::get_instance()->get_pdo("slave"); // one_db slave
    $sql = "select field_image_fid, field_image_version from one_db.drupal_field_data_field_image where entity_id = ? and entity_type = ? and bundle = ? order by delta asc limit 0, 36";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($entity_id, $entity_type, $bundle));
    $results = $stmt->fetchAll(PDO::FETCH_OBJ);

       foreach($results as $r){
           if($version==0){
             $matches[] = $r->field_image_fid; 
           }else{
             $matches[]['fid'] = $r->field_image_fid; 
             $matches[]['version'] = $r->field_image_fid;
           }
       }
       return $matches;
     }

     function zzk_db_file_managed($fid, $version=0) {
       $results = array();
       if(empty($fid) || (int)$fid == 0){
         return $results;
       }

       if($version ==0 ){
         $version = Util_Image::img_version($fid);
       }
       if($version == 0){
         $pdo = APF_DB_Factory::get_instance()->get_pdo("slave"); // one_db slave
         $sql = "select uri from one_db.drupal_file_managed where fid = ?";
         // $query = db_select('file_managed', 't');
         // $query->fields('t', array('uri'));
         // $query->condition('t.fid', $fid, '=');
         // $results = $query->range(0,1)->execute()->fetchField();
       }else{
         // $query = zzkwww_select('t_img_managed','t');
         // $query->fields('t', array('uri'));
         // $query->condition('t.fid', $fid,'=');
         // $results = $query->range(0,1)->execute()->fetchField();
         $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave"); // LKYou slave
         $sql = "select uri from LKYou.t_img_managed where fid = ?";
       }
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($fid));
       $column = $stmt->fetchColumn(0);
       $stmt->closeCursor();
       return $column;
     }
     public static function get_homestay_image($homestay_uid){
          $images=Util_Image::get_homestay_images($homestay_uid);
          if(is_array($images))return $images[0];
          else return $images;
     }

     public  static function get_homestay_images($homestay_uid)
     {
          $homepage_bll = new Bll_Homepage_HomepageInfo();
          $home_stay_images = $homepage_bll->get_home_stay_images($homestay_uid, 100);
          $home_stay_images = array_map(function ($row) {
               return Util_Image::img_url_generate($row['uri'], $row['field_image_version'],$row['new_uri']);
          }, $home_stay_images);

          return $home_stay_images;
     }

     public static function img_url_generate($img, $img_version, $img_new_uri = null)
     {
          if (empty($img_new_uri)) {
               $img_new_uri = $img;
          }

          $img = strtr($img, array(
               'public://field/image[current-date:raw]/' => '',
               'public/field/image[current-date:raw]/' => '',
               'public://' => '',
               'public/' => '',
          ));
          if ($img_version == 1) {
               $img_url = 'http://img1.zzkcdn.com/' . $img_new_uri . '/2000x1500.jpg-roompic.jpg';
          } else {
               $img_url = 'http://img1.zzkcdn.com/' . $img . '-roompic.jpg';
          }

          return $img_url;
     }

}
