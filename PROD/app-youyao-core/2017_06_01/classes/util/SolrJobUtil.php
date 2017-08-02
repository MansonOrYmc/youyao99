<?php
require_once  dirname(__FILE__).'/SolrCenter.php';
class Util_SolrJobUtil {

       
  public static function build_xml_delete_by_yyid($yyid)
  {
    $xw = new \xmlWriter();
    $xw->openMemory();
    $xw->startDocument('1.0', 'UTF-8');
    $xw->startElement('delete');
    $xw->startElement('yyid');
    $xw->writeCdata($yyid);
    $xw->endElement();
    $xw->endElement();
    $xw->endDocument();
    $xml = $xw->outputMemory(true);
    return $xml;
  }

  //"delete" documents by ID and by Query
  public static function build_xml_delete_by_query($q="*:*")
  {
    $xw = new \xmlWriter();
    $xw->openMemory();
    $xw->startDocument('1.0', 'UTF-8');
    $xw->startElement('delete');
    $xw->startElement('query');
    $xw->writeCdata($q);
    $xw->endElement();
    $xw->endElement();
    $xw->endDocument();
    $xml = $xw->outputMemory(true);
    return $xml;
  }

  public static function build_xml_commit()
  {
    $xw = new \xmlWriter();
    $xw->openMemory();
    $xw->startDocument('1.0', 'UTF-8');
    $xw->startElement('commit');
      $xw->startAttribute('waitSearcher');
        $xw->text('false');
      $xw->endAttribute();
    $xw->endElement();
    $xw->endDocument();
    $xml = $xw->outputMemory(true);
    return $xml;
  }

  public static function build_xml_optimize()
  {
    $xw = new \xmlWriter();
    $xw->openMemory();
    $xw->startDocument('1.0', 'UTF-8');
    $xw->startElement('optimize');
      $xw->startAttribute('waitSearcher');
        $xw->text('false');
      $xw->endAttribute();
    $xw->endElement();
    $xw->endDocument();
    $xml = $xw->outputMemory(true);
    return $xml;
  }

  public static function build_hospital_xml($row) {
    $xw = new xmlWriter();
    $xw->openMemory();
    $xw->startDocument('1.0', 'UTF-8');
    $xw->startElement('add');
    $xw->startElement('doc');

    foreach ($row as $key=>$value) {
      if($key == "id" || $key == "yyid"){
        $xw->startElement('field');
        $xw->writeAttribute('name', $key);
        $xw->writeCdata($value);
        $xw->endElement();
      }
      else{
        $xw->startElement('field');
        $xw->writeAttribute('name', $key);
        $xw->writeCdata(preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $value));
        $xw->endElement();
      }
    }

    $xw->endElement();
    $xw->endElement();
    $xw->endDocument();
    $xml = $xw->outputMemory(true);
    return $xml;
  }
  
  // $solrRet = SolrJobUtil::post_to_solr($post_url, $xml);
  public static function post_to_solr($updateURL, $update, $format='xml')
  {
    $ch = curl_init($updateURL);

    // Set Login/Password auth (if required)
    //curl_setopt($ch, CURLOPT_USERPWD, SOLR_LOGIN.':'.SOLR_PASSWORD);

    // Set POST fields
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $update);

    // Return transfert
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // Set type of data sent
    if ($format == 'xml') {
      curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type:text/xml; charset=utf-8"));
    } else {
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    }

    $output = curl_exec($ch);

    // if ($format == 'xml') {
    //   $output = json_encode($output);
    // }
    // // Get response result
    // $output = json_decode($output);

    // Get response code
   $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Close Curl resource
    curl_close($ch);

    return array('responseCode' => $responseCode, 'output' => $output);
  }
  
  public static function post2solr($postUrl, $xml)
  {
    //$curl = curl_init("http://127.0.0.1:8984/zzk-search/update");
    //http://192.168.1.17:8983/search/hospital/update
   
    $curl = curl_init($postUrl);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type:text/xml; charset=utf-8"));
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
    curl_exec($curl);
    curl_close($curl);
  }

  public static function get_post_url($ins="")
  {
    //$curl = curl_init("http://127.0.0.1:8984/zzk-search/update");
    //$post_url = "http://$searchHost:$searchPort/search/room/update";
    $host = APF::get_instance()->get_config('solr_host');
    $port = APF::get_instance()->get_config('solr_port');
    if(empty($ins)){
       $ins = '/search/hospital/update';
    }
    return "http://". $host . ":" . $port . $ins;
  }
}
