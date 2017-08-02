{extends file="Layout.tpl"}

{block name="style"}
    {include file="2c/OrderRoomNotNull.css"}
{/block}

{block name="body"}
    <div class="header">
        <span class="icon32 icon32-alert float-left"></span>
        <div class="float-left">
            <h2>{Trans::t("order_room_not_null_info")}</h2>
            <p>{Trans::t("order_room_not_null_tips_%s", null, ['%s'=>$payment_info])}</p>
        </div>
        <img class="float-right" src="http://pages.youyao.com/a/img/homepage3/red_logo_small.png">
        <div class="clear"></div>
    </div>

    <div class="main_header">
        <p class="order_new_msg font-big">{Trans::t("order_room_not_null_msg_%gname_%checkindate_%homestayname", null, ['%gname'=>"<b>`$order->guest_name`</b>", '%checkindate'=>$order->guest_date, '%homestayname'=>$homestay['name']])}</p>
        <p class="font-big">{Trans::t("order_room_not_null_h_info_%s", null, ['%s'=>$payment_info])}</p>
    </div>
    <div class="cell-fat cell-border" id="recommond_rooms">
        <div class="form-row">
            <label>{Trans::t("view_other_rooms")}</label>
        </div>
        {foreach $recommond_rooms as $room}
            <div class="form-row form-row-half">
                <a target="_blank" href="http://www{$apf->get_config('base_domain')}/r/{$room['id']}"><img src="{Util_Image::imglink($room['first_pic'], "roompic.jpg")}"/></a>
                <a target="_blank" href="http://www{$apf->get_config('base_domain')}/r/{$room['id']}" class="font-big">{$room['title']}</a>
                <p class="price-small">
                    {if $lang_id == 10}
                        NT${$room['low_price']}
                    {else}
                        ¥{$room['low_price']}
                    {/if}
                </p>
            </div>
        {/foreach}
    </div>

    <div class="main_header">
        <a target="_blank" href="http://www{$apf->get_config('base_domain')}/user/feedback/" id="pay_btn">{Trans::t("i_need_to_complain")}</a>
        <p class="pay_link_tips">{Trans::t("room_not_null_complain_tips")}</p>
    </div>

    <div class="cell-wrapper">
        <div class="cell-thin cell-border">
            <p class="float-left">
                {Trans::t("order_num")}
                #{$order->hash_id}
                <span class="{$order_status_map[$order->status]['type']}">[{Trans::t($order_status_map[$order->status]['key'])}]</span>
            </p>
            <p class="float-right">
                <b>{Trans::t("total_pay_price")}</b>
                /
                <span class="price-mute">{$pay_price}</span>
            </p>
            <div class="clear"></div>
        </div>
        <div class="cell-fat cell-border">
            <div class="form-row" id="room_name">
                <p class="font-big">{$homestay['name']}</p>
            </div>
            <div class="form-row">
                <label>{Trans::t("homestay_address")}</label>
                <p class="font-big">
                    {foreach $area_array as $loc}
                        {$loc['type_name']}
                    {/foreach}
                </p>
                <p class="font-big">{$homestay['address']}</p>
            </div>
        </div>
        <div class="cell-fat cell-border">
            <div class="form-row">
                <label>{Trans::t("check_in_room")} {Trans::t("check_in_room_num_and_nights_%n_%d", null, ['%n'=>$order->room_num, '%d'=>$order->guest_days])}</label>
                <p class="font-big">{$order->room_name}</p>
            </div>
        </div>
        <div class="cell-fat cell-border">
            <div class="form-row form-row-half">
                <label>{Trans::t("check_in_date")}</label>
                <p class="font-big">{$order->guest_date}</p>
            </div>
            <div class="form-row form-row-half">
                <label>{Trans::t("check_out_date")}</label>
                <p class="font-big">{$order->guest_checkout_date}</p>
            </div>
        </div>
        <div class="cell-fat">
            <div class="form-row form-row-half">
                <label>{Trans::t("check_in_user")}</label>
                <p class="font-big">{$order->guest_name}</p>
                <p>{$order->guest_number}{Trans::t("adult")} {$order->guest_child_number}{Trans::t("children")}</p>
                {if $order->guest_child_age > 0}
                    <p>{Trans::t("guest_child_info")} / {Trans::t('age')} {$order->guest_child_age}</p>
                {/if}
            </div>
            <div class="form-row form-row-half">
                <div class="form-row form-row-inline">
                    <label>{Trans::t("contact_phone")}</label>
                    <p>{$order->guest_telnum}</p>
                </div>
                <div class="form-row form-row-inline">
                    <label>{Trans::t("email")}</label>
                    <p>{$order->guest_mail}</p>
                </div>
                {if $order->guest_wechat}
                <div class="form-row form-row-inline">
                    <label>{Trans::t("webchat")}</label>
                    <p>{$order->guest_wechat}</p>
                </div>
                {/if}
                {if $order->guest_line_id}
                <div class="form-row form-row-inline">
                    <label>{Trans::t("guest_line_id")}</label>
                    <p>{$order->guest_line_id}</p>
                </div>
                {/if}
            </div>
        </div>
    </div>

    <div class="cell-fat cell-border">
        <div class="form-row" id="spec_services">
            <label>{Trans::t("spec_service")}</label>
            <p>{Trans::t("spec_service_explain_now")}</p>
        </div>
    </div>

    <div class="cell-fat">
        <div class="form-row">
            <label>{Trans::t("total_pay_price")}</label>
            <p>
                <span class="price-mute">{$pay_price}</span>
            </p>
        </div>
    </div>
{/block}
