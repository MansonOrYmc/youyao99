{extends file="Layout.tpl"}

{block name="style"}
    {include file="2c/OrderCanceled.css"}
{/block}

{block name="body"}
    <div class="header">
        <span class="icon32 icon32-alert float-left"></span>
        <div class="float-left">
            <h2>{Trans::t("order_canceled_info")}</h2>
        </div>
        <img class="float-right" src="http://pages.youyao.com/a/img/homepage3/red_logo_small.png">
        <div class="clear"></div>
    </div>

    <div class="main_header">
        <p class="order_new_msg font-big">
        {if $lang_id == 13}
            {Trans::t("order_canceled_msg_%gname_%checkindate_%homestayname", null, ['%gname'=>$order->guest_name, '%checkindate'=>$order->guest_date, '%homestayname'=>$homestay['e_name']])}
        {else}
            {Trans::t("order_canceled_msg_%gname_%checkindate_%homestayname", null, ['%gname'=>$order->guest_name, '%checkindate'=>$order->guest_date, '%homestayname'=>$homestay['name']])}
        {/if}
        </p>
        <a target="_blank" href="http://www{$apf->get_config('base_domain')}/user/feedback/" id="pay_btn">{Trans::t("i_need_to_complain")}</a>
        {if $lang_id != 13}
        <p class="pay_link_tips">{Trans::t("order_canceled_complain_tips")}</p>
        {/if}
    </div>

    <div class="cell-wrapper">
        <div class="cell-thin cell-border">
            <p class="float-left">
                {Trans::t("order_num")}
                #{$order->hash_id}
                <span class="{$order_status_map[$order->status]['type']}">[{Trans::t($order_status_map[$order->status]['key'])}]</span>
            </p>
            <p class="float-right">
                {if $order->order_source == 'booking'}
                    <b>{Trans::t("price")}</b>
                {else}
                    <b>{Trans::t("refer_price")}</b>
                {/if}
                /
                <span class="price-mute">{$pay_price}</span>
            </p>
            <div class="clear"></div>
        </div>
        <div class="cell-fat cell-border">
            <div class="form-row" id="room_name">
                <p class="font-big">
                {if $lang_id == 13}
                    {$homestay['e_name']}
                {else}
                    {$homestay['name']}
                {/if}
                </p>
            </div>
            <div class="form-row">
                <label>{Trans::t("homestay_address")}</label>
                <p class="font-big">
                    {foreach $area_array as $loc}
                        {if $lang_id == 13}
                            {Trans::t($loc['name_code'])}
                        {else}
                            {$loc['type_name']}
                        {/if}
                    {/foreach}
                </p>
                <p class="font-big">
                {if $lang_id == 13}
                    {$homestay['e_address']}
                {else}
                    {$homestay['address']}
                {/if}
                </p>
            </div>
        </div>
        <div class="cell-fat cell-border">
            <div class="form-row">
                <label>{Trans::t("check_in_room")}
                    {$check_in_room_night = Trans::t("check_in_room_num_and_nights_%n_%d", null, ['%n'=>$order->room_num, '%d'=>$order->guest_days])}
                    {if $order->room_num == 1}
                        {$check_in_room_night = str_replace("Rooms", "Room", $check_in_room_night)}
                    {/if}
                    {if $order->guest_days == 1}
                        {$check_in_room_night = str_replace("Nights", "Night", $check_in_room_night)}
                    {/if}
                    {$check_in_room_night}
                </label>
                <p class="font-big">
                {if $lang_id == 13}
                    {$homestay['e_name']}
                {else}
                    {$order->room_name}
                {/if}
                </p>
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
                <p>{Trans::t("adult")}：{$order->guest_number}
                   {if $order->guest_child_number} {Trans::t("children")}：{$order->guest_child_number} {/if}</p>
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

<!--
    <div class="cell-fat cell-border">
        <div class="form-row" id="spec_services">
            <label>{Trans::t("spec_service")}</label>
            <p>{Trans::t("spec_service_explain_now")}</p>
        </div>
    </div>

    <div class="cell-fat">
        <div class="form-row">
            <label>{Trans::t("refer_price")}</label>
            <p>
                <span class="price-mute">{$pay_price}</span>
            </p>
        </div>
    </div>
-->
{/block}
