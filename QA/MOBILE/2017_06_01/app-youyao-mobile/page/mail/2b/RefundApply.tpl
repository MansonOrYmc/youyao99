{extends file="Layout.tpl"}

{block name="style"}
    {include file="2b/2bPublic.css"}
{/block}

{block name="body"}
    <div class="header">
        <span class="icon32 icon32-alert float-left"></span>
        <img class="float-right" src="http://pages.youyao.com/a/img/homepage3/red_logo_small.png">
        <h2 class="mail-subject float-left">{Trans::t("you_got_a_refund_apply")}</h2>
        <span class="title-content float-left">{Trans::t("customer_%n_refund_in%d_on_youyao", false, ["%n"=>$order->guest_name, "%d"=>$order->update_date|date_format:"%Y-%m-%d %k:%M" ])}</span>
        <div class="clear"></div>
    </div>

    <div class="cell-wrapper">
        <div class="cell-thin cell-border">
            <p class="float-left">
                {Trans::t("order_num")}
                #{$order->hash_id}
                <span class="">[{Trans::t("refunding")}]</span>
            </p>
            <div class="clear"></div>
        </div>
        <div class="cell-fat cell-border">
            <div class="form-row" id="room_name">
                <label class="font-big">{$order->uname}</label>
                <p class="font-big">{$order->room_name}</p>
            </div>
        </div>
        <div class="cell-fat cell-border">
            <div class="form-row form-row-half">
                <label>{Trans::t("Check in date")}</label>
                <p class="font-big">{$order->guest_date}</p>
            </div>
            <div class="form-row form-row-half">
                <label>{Trans::t("Chack out date")}</label>
                <p class="font-big">{$order->guest_checkout_date}</p>
            </div>
        </div>
        <div class="cell-fat cell-border">
            <div class="form-row">
                <label>{Trans::t("room_num_days")} </label>
                <p class="font-big">{$order->room_num}{Trans::t("rooms")}x{$order->guest_days}{Trans::t("days")}</p>
            </div>
        </div>
        <div class="cell-fat">
            <div class="form-row form-row-half">
                <label>{Trans::t("guest number")}</label>
                <p class="font-big">{$order->guest_name}</p>
                <p>{$order->guest_number}{Trans::t("adult")} {$order->guest_child_number}{Trans::t("children")}</p>
                {if $order->guest_child_age > 0}
                    <p>{Trans::t("guest_child_info")} / {Trans::t('age')} {$order->guest_child_age}</p>
                {/if}
            </div>
            <div class="form-row form-row-half contact-div">
                <div class="form-row form-row-inline">
                    <label>{Trans::t("lianxidianhua")}</label>
                    <p>{$order->guest_telnum}</p>
                </div>
                <div class="form-row form-row-inline">
                    <label>{Trans::t("email")}</label>
                    <p>{$order->guest_mail}</p>
                </div>
                {if $order->guest_wechat}
                <div class="form-row form-row-inline">
                    <label>{Trans::t("homestayweixin")}</label>
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
        <div class="guest-remark">
            <img class="guest-remark-arrow" src="http://static.zzkcdn.com/mail/jiao.png">
            <p class="guest-remark-content">{Trans::t("reason_of_refund")}<br/>
            <label class="font-big">{$refund['refund_content']}</label></p>
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

    <div class="cell-fat cell-border">
        <div class="form-row">
            <label>{Trans::t("total_pay_price")}</label>
            <p>
                {if $lang_id == "10"}
                    <span>NT$ {if $order->pay_price}{$order->pay_price['total_fee']}{else}{$order->total_price_tw}{/if}</span>
                {elseif $lang_id == "12"}
                    <span>¥ {if $order->pay_price}{$order->pay_price['total_fee']}{else}{$order->total_price}{/if}</span>
                {else}
                    <span>{if $order->pay_price}{$order->pay_price['total_fee']}{else}{$order->total_price}{/if}</span>
                {/if}
            </p>
            <p>{Trans::t("zzk_will_check_by_your_rule")}</p>
            <p>{Trans::t("if_you_have_no_rule")}（{Trans::t("need_help_%e", false, ['%e'=>$contact_mail])}）</p>
        </div>
    </div>

    <div class="cell-fat">
        <div class="form-row">
            <p class="refund-notice font-big">
            {Trans::t("refund_tips")}
            </p>
        </div>
    </div>
    <div style="clear:both"></div>
{/block}
