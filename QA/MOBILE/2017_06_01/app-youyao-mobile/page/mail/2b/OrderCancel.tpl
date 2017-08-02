{extends file="Layout.tpl"}

{block name="style"}
    {include file="2b/2bPublic.css"}
{/block}

{block name="body"}
    <div class="header clearfix">
        <span class="icon32 icon32-alert float-left"></span>
        <img class="float-right" src="http://pages.youyao.com/a/img/homepage3/red_logo_small.png">
        <h2 class="mail-subject float-left">【{Trans::t("guest")}】{$order->guest_name}{Trans::t("bookingcancel")}</h2>
        {if $cancel_reason}
        <span class="title-content float-left">{Trans::t("reasonofcancel")}：{$cancel_reason}</span>
        {/if}
        </span>
        <div class="clear"></div>
    </div>

    <div class="cell-wrapper">
        <div class="cell-thin cell-border">
            <p class="float-left">
                {Trans::t("order_num")}
                #{$order->hash_id}
                <span class="{$order_status_map_for_homestay[$order->status]['type']}">[{Trans::t($order_status_map_for_homestay[$order->status]['key'], $lang_id)}]</span>
            </p>
            <p class="float-right">
                <b>{Trans::t("total_pay_price")}</b>
                /
                <span class="price">{Trans::t("key_price_unit")}{$order->total_price_tw}</span>
            </p>
            <div class="clear"></div>
        </div>
        <div class="cell-border clearfix booking-info">
            <div class="cell-fat float-left booking-msg">
                <div class="form-row">
                    <label>{$order->uname}</label>
                    <p class="font-big">{$order->room_name}</p>
                </div>
                <div class="form-row">
                    <label>{Trans::t("roomnum")}</label>
                    <p class="font-big">{$order->room_num}{Trans::t("rooms")}x{$order->guest_days}{Trans::t("days")}</p>
                </div>
                <div class="form-row">
                    <label>{Trans::t("guest number")}</label>
                    <p class="font-big">{$order->guest_number}{Trans::t("adult")} {$order->guest_child_number}{Trans::t("children")}</p>
                    {if $order->guest_child_age != ""}
                        <p class="clearfix">
                        <label class="float-left">{Trans::t("guest_child_info")} /</label>
                        <label class="float-left child-info">
                        {foreach $format_child as $child}
                            {Trans::t('age')} {$child['age']}
                            {Trans::t('height')} {$child['height']}
                            <br/>
                        {/foreach}
                        </label>
                        </p>
                    {/if}
                </div>
            </div>
            <div class="pic-layer float-right">
                <img src="{$room_image}" />
            </div>
            <div class="clear"></div>
        </div>
        <div class="cell-fat cell-border clearfix">
            <div class="form-row form-row-half calendar-div">
                <div class="calendar-title">
                    <span class="calendar-circle"></span>
                    <label class="calendar-label">{Trans::t("Check in date")}</label>
                    <span class="calendar-circle"></span>
                </div>
                <div class="calendar-pannel">
                    <span class="calendar-week calendar-pannel-label">{Trans::t($order->guest_date|date_format:"%A")}</span>
                    <span class="calendar-day calendar-pannel-label">{Trans::t($order->guest_date|date_format:"%d")}</span>
                    <span class="calendar-date calendar-pannel-label">{$order->guest_date|date_format:"%Y-%m"}</span>
                </div>
            </div>
            <img class="float-left calendar-arrow" src="http://static.zzkcdn.com/mail/icon_jiantou.png" />
            <div class="form-row form-row-half calendar-div">
                <div class="calendar-title">
                    <span class="calendar-circle"></span>
                    <label class="calendar-label">{Trans::t("Chack out date")}</label>
                    <span class="calendar-circle"></span>
                </div>
                <div class="calendar-pannel float-left">
                    <span class="calendar-week calendar-pannel-label">{Trans::t($order->guest_checkout_date|date_format:"%A")}</span>
                    <span class="calendar-day calendar-pannel-label">{Trans::t($order->guest_checkout_date|date_format:"%d")}</span>
                    <span class="calendar-date calendar-pannel-label">{$order->guest_checkout_date|date_format:"%Y-%m"}</span>
                </div>
            </div>
            <p class="calendar-explain">{Trans::t("total_stay_%d_days_please_keep_room", false, ["%d"=>$order->guest_days])}</p>
            <div class="clear"></div>
        </div>
        <div class="cell-fat">
            <div class="form-row form-row-half contact-div">
                <label>{Trans::t("check_in_user")}</label>
                <p class="font-big">{$order->guest_name}</p>
            </div>
        </div>
    </div>

    <div class="cell-fat cell-border">
        <div class="form-row" id="spec_services">
            <label>{Trans::t("spec_service")}</label>
            {if not $order_addition_services}
                <p>{Trans::t("no_spec_service")}</p>
            {/if}
            {foreach $order_addition_services as $s}
                <p>
                    {if $s['category'] == "baoche"}
                        <span class="icon24 icon24-car"></span>
                    {else}
                        <span class="icon24 icon24-other"></span>
                    {/if}
                    <span class="service_name">
                        {if $s['category'] == "baoche"}
                            {Trans::t('baoche')}
                        {else}
                            {$s['service_name']}
                        {/if}
                    </span>
                    <span class="service_price">
                        {if $s['price'] > 0}
                            {Trans::t('key_price_unit', $lang_id)} {$s['price']}
                        {else}
                            {Trans::t('free', $lang_id)}
                        {/if}
                    </span>
                    <span class="service_description">
                        {$s['description']}
                    </span>
                </p>
            {/foreach}
        </div>
    </div>

    <div style="clear:both"></div>

{/block}
