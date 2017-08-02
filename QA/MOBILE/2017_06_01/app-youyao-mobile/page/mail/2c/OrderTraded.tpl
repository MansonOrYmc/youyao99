{extends file="Layout.tpl"}

{block name="style"}
    {include file="2c/OrderTraded.css"}
{/block}

{block name="body"}
    <div class="header">
        <span class="icon32 icon32-succeed float-left"></span>
        <div class="float-left">
            <h2>{Trans::t("order_traded_info")}</h2>
            <p class="title-desc">{Trans::t("order_traded_tips_%s_%t", null, ['%s'=>$order->guest_name, '%t'=>$order->create_time|date_format:"%Y-%m-%d %H:%M"])}</p>
        </div>
        <img class="float-right" src="http://pages.youyao.com/a/img/homepage3/red_logo_small.png">
        <div class="clear"></div>
    </div>

    <div class="cell-wrapper">
        <div class="cell-thin cell-border">
            <p class="float-left">
                {Trans::t("order_num")}
                #{$order->hash_id}
                {if $order->order_source == 'booking'}
                    <span class="pay-in-hotel">
                        [{Trans::t('face to face payment')}]
                    </span>
                {else}
                    <span class="{$order_status_map[$order->status]['type']}">
                        [{Trans::t($order_status_map[$order->status]['key'])}]
                    </span>
                {/if}
            </p>
            <p class="float-right">
                <b>{Trans::t("total_pay_price")}</b>
                /
                <span class="price">{$pay_price}</span>
            </p>
            <div class="clear"></div>
        </div>
        <div id="homestay_info">
            <div class="form-row float-left cell-fat">
                <div class="form-row form-row-inline" id="room_name">
                    <p class="font-big">
                    {if $lang_id == '13'}
                        {$homestay['e_name']}
                    {else}
                        {$homestay['name']}
                    {/if}
                    </p>
                    <br/>
                    <label>{Trans::t("homestay_tel_num")}</label><p>{$homestay['tel_num']}</p>
                    <label>{Trans::t("homestay_mail")}</label><p>{$homestay['mail']|regex_replace:"/.zzk\.group\.\d+/":""}</p>
                    {if $homestay['field_data_field_weixin']}
                        <label>{Trans::t("homestay_weixin")}</label><p>{$homestay['field_data_field_weixin']}</p>
                    {/if}
                    {if $homestay['field_data_field_line']}
                        <label>{Trans::t("homestay_line")}</label><p>{$homestay['field_data_field_line']}</p>
                    {/if}
                    {if $homestay['field_data_field_skype']}
                        <label>{Trans::t("homestay_skype")}</label><p>{$homestay['field_data_field_skype']}</p>
                    {/if}
                    {if $homestay['field_data_field__qq']}
                        <label>{Trans::t("homestay_qq")}</label><p>{$homestay['field_data_field__qq']}</p>
                    {/if}
                </div>
            </div>
            <div class="form-row float-right">
                <img src="{$room_image}"/>
            </div>
            <div class="clear"></div>
        </div>
        <div id="homestay_addr">
            <div class="form-row float-left cell-fat">
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
                <br/>
                {if $homestay['dest_id'] == 11 && $lang_id != 13}
                    <a target="_blank" href="http://static.zzkcdn.com/email/attachments/日本住宿礼仪.pdf">{Trans::t("jp_checkin_courtesy")}</a>
                {/if}
            </div>
            <div class="form-row float-right">
                {if $lang_id == 13}
                    <img src="https://maps.googleapis.com/maps/api/staticmap?center={$homestay['lat']},{$homestay['lon']}&zoom=11&size=352x200&markers=color:red%7C%7C{$homestay['lat']},{$homestay['lon']}&senor=false"/>
                {else}
                    <img src="http://restapi.amap.com/v3/staticmap?location={$homestay['lon']},{$homestay['lat']}&zoom=11&size=352*200&labels={$homestay['name']|replace:' ':''|truncate:15},2,0,16,0xFFFFFF,0x008000:{$homestay['lon']},{$homestay['lat']}&key=5e3e63251397216d16f298046e9240d8"/>
                {/if}
            </div>
            <div class="clear"></div>
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
            {if $homestay['checktime']['checkin_stop'] != ""}
            <div class="form-row form-row-half">
                <div class="form-row form-row-inline">
                    <label class="latest-checkin">{Trans::t("last_checkin_time")}</label>
                    <p>{$homestay['checktime']['checkin_stop']}</p>
                </div>
            </div>
            {/if}
        </div>
        <div class="cell-fat cell-border">
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

<!--
    <div class="cell-fat cell-border">
        <div class="form-row" id="spec_services">
            <label>{Trans::t("spec_service")}</label>
            <p>{Trans::t("spec_service_explain_now")}</p>
        </div>
    </div>
-->

<!--
    <div class="cell-fat">
        <div class="form-row">
            <label>{Trans::t("total_pay_price")}</label>
            <p>
                <span class="price">{$pay_price}</span>
            </p>
        </div>
    </div>
-->

    <!--
    <div id="payment_info_wrapper" class="cell-border">
        <div id="payment_info">
            <div class="text">
                <p>{$payment_info}</p>
            </div>
            <img class="headimg" src="{$homestay['headimg']}"/>
            <div class="clear"></div>
        </div>
    </div>
    -->

    <div id="refund_policy" class="cell-fat cell-border">
        <div class="form-row">
            <label>{Trans::t("refund_policy")}</label>
        </div>
        {if $refund_data}
            {if $refund_data['refund_list'][1]['day'] == $refund_data['refund_list'][2]['day']}
            <div class="refund-view">
                <div id="refund_label" class="refund-view-list clearfix">
                    <p style="list-style:none;" class="full-refund two-section">
                        <span class="refund-view-bubble">{Trans::t('%d_prior', false, ['%d' => $refund_data['refund_list'][1]['day']])}</span>
                    </p>
                    <div class="clear"></div>
                </div>
                <div class="refund-view-list clearfix">
                    <p style="list-style:none;" class="refund-view-row full-refund half-row">
                        <label class="refund-view-label">{Trans::t('full_refund')}</label>
                    </p>
                    <p style="list-style:none;" class="refund-view-row none-refund half-row">
                        <label class="refund-view-label">{Trans::t('non_refundable')}</label>
                    </p>
                </div>
            </div>
            <div class="clear"></div>
            <div class="refund-date">
                <p class="refund-date-row two-date1">{($checkin_unix - $refund_data['refund_list'][1]['day'] * 24 * 60 * 60)|date_format:'%Y-%m-%d'}</p>
                <p class="refund-date-row two-date2">{$order->guest_date}</p>
            </div>
            <div class="clear"></div>
            {else}
                <div id="refund_label" class="refund-view-list clearfix">
                    <p style="list-style:none;" class="full-refund two-section">
                        <span class="refund-view-bubble">{Trans::t('%d_prior', false, ['%d' => $refund_data['refund_list'][1]['day']])}</span>
                    </p>
                    <p style="list-style:none;" class="full-refund">
                        <span class="refund-view-bubble">{Trans::t('%d_prior', false, ['%d' => $refund_data['refund_list'][1]['day']])}</span>
                    </p>
                    <p style="list-style:none;" class="partial-refund">
                        <span class="refund-view-bubble">{Trans::t('%d_prior', false, ['%d' => $refund_data['refund_list'][2]['day']])}</span>
                    </p>
                    <div class="clear"></div>
                </div>
                <div class="refund-view-list clearfix">
                    <p style="list-style:none;" class="refund-view-row full-refund">
                        <label class="refund-view-label">{Trans::t('full_refund')}</label>
                    </p>
                    <p style="list-style:none;" class="refund-view-row partial-refund">
                        <label class="refund-view-label">{Trans::t('%p%_refund', false, ['%p' => $refund_data['refund_list'][2]['per']])}</label>
                    </p>
                    <p style="list-style:none;" class="refund-view-row none-refund">
                        <label class="refund-view-label">{Trans::t('non_refundable')}</label>
                    </p>
                </div>
            </div>
            <div class="clear"></div>
            <div class="refund-date">
                <p class="refund-date-row three-date1">{($checkin_unix - $refund_data['refund_list'][1]['day'] * 24 * 60 * 60)|date_format:'%Y-%m-%d'}</p>
                <p class="refund-date-row three-date2">{($checkin_unix - $refund_data['refund_list'][2]['day'] * 24 * 60 * 60)|date_format:'%Y-%m-%d'}</p>
                <p class="refund-date-row three-date3">{$order->guest_date}</p>
            </div>
            <div class="clear"></div>
            {/if}
        {else}
        <div class="form-row">
            <p>{str_replace("\n", "<br/>", $homestay['field_data_field__dingfangshuoming'])}</p>
        </div>
        {/if}
    </div>

    <div id="refund_policy" class="cell-fat">
        <div class="form-row">
            <label>{Trans::t("zhuyishixiang")}</label>
        </div>
        <div class="form-row">
            <p>{str_replace("\n", "<br/>", $homestay['field_data_field_zhuyishixiang'])}</p>
        </div>
    </div>


    {if $homestay['dest_id'] == 11 && $lang_id != 13}
    <div class="cell-fat">
        <img src="http://static.zzkcdn.com/mail/japanactivitive.jpg"/>
        <br/>
        <img width="100%" src="http://static.zzkcdn.com/japan_foodie.png"/>
    </div>
    {/if}
{/block}
