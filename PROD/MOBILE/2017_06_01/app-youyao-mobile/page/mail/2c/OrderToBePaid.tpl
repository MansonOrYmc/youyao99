{extends file="Layout.tpl"}

{block name="style"}
    {include file="2c/OrderToBePaid.css"}
{/block}

{block name="body"}

    <div class="header">
        <span class="icon32 icon32-info float-left"></span>
        <div class="float-left">
            <h2>{Trans::t("order_to_be_paid_info")}</h2>
            <p>{Trans::t("order_to_be_paid_tips_%s", null, ['%s'=>"<b>`$order->guest_name`</b>"])}</p>
        </div>
        <img class="float-right" src="http://pages.youyao.com/a/img/homepage3/red_logo_small.png">
        <div class="clear"></div>
    </div>

    <div class="main_header">
        <p class="order_new_msg font-big">{Trans::t("order_to_be_paid_msg_%s", null, ['%s'=>"<span class='price'>`$pay_price`</span>"])}</p>
        {if $lang_id == 12}
            <a target="_blank" href="http://www{$apf->get_config('base_domain')}/user/payment/{$order->url_code}" id="pay_btn">{Trans::t("pay_now")}</a>
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
                <b>{Trans::t("total_pay_price")}</b>
                /
                <span class="price">{$pay_price}</span>
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
                <label>{Trans::t("check_in_date", null)}</label>
                <p class="font-big">{$order->guest_date}</p>
            </div>
            <div class="form-row form-row-half">
                <label>{Trans::t("check_out_date", null)}</label>
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
                <span class="price">{$pay_price}</span>
            </p>
        </div>
    </div>

    <div id="payment_info">
        <div class="text">
            <p>{$payment_info}</p>
        </div>
        <img class="headimg" src="{$homestay['headimg']}"/>
        <div class="clear"></div>
    </div>
{/block}
