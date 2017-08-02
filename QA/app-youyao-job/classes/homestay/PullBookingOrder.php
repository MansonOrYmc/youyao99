<?php
class Homestay_PullBookingOrder {

    function run() {

        $response = Util_Curl::get("http://open.api.zizaike.com/booking/saveReservationssummary");
        if($response['code'] != 200) {
            Logger::info(__FILE__, __CLASS__, __LINE__, "NETWORK FAILED", var_export($response, true));
        }
        $data = json_decode($response['content'], true);
        if($data['code'] != 200) {
            Logger::info(__FILE__, __CLASS__, __LINE__, "API FAILED", var_export($response, true));
        }
    }
}
