{extends file="Layout.tpl"}

{block name="style"}
    {include file="2c/OrderRefunded.css"}
{/block}

{block name="body"}
    {if $lang_id == "10"}
        {if $order->pay_price}
            {assign var="pay_price_num" value=$order->pay_price['total_fee']}
            {assign var="pay_price_unit" value="NT$"}
        {else}
            {assign var="pay_price_num" value=$order->total_price_tw}
            {assign var="pay_price_unit" value="NT$"}
        {/if}
    {else}
        {if $order->pay_price}
            {assign var="pay_price_num" value=$order->pay_price['total_fee']}
            {assign var="pay_price_unit" value="¥"}
        {else}
            {assign var="pay_price_num" value=$order->total_price}
            {assign var="pay_price_unit" value="¥"}
        {/if}
    {/if}

    <div class="header">
        <span class="icon32 icon32-succeed float-left"></span>
        <div class="float-left">
            <h2>{Trans::t("order_refunded_info", $lang_id)}</h2>
            <p>{Trans::t("order_refunded_tips_%s", $lang_id, ['%s'=>$order->guest_name])}</p>
        </div>
        <img class="float-right" src="http://pages.youyao.com/a/img/homepage3/red_logo_small.png">
        <div class="clear"></div>
    </div>

    <div class="main_header">
        <p class="order_new_msg font-big">{Trans::t("order_refunded_msg_%days_%price", $lang_id, ['%days'=>$datediff, '%price'=>"`$pay_price_unit``$pay_price_num`"])}</p>
        <a target="_blank" href="http://www{$apf->get_config('base_domain')}/user/feedback/" id="pay_btn">{Trans::t("i_need_to_complain", $lang_id)}</a>
        <p class="pay_link_tips">{Trans::t("order_refund_complain_tips", $lang_id)}</p>
    </div>

    <div class="cell-wrapper">
        <div class="cell-thin cell-border">
            <p class="float-left">
                {Trans::t("order_num", $lang_id)}
                #{$order->hash_id}
                <span class="{$order_status_map[$order->status]['type']}">[{Trans::t($order_status_map[$order->status]['key'], $lang_id)}]</span>
            </p>
            <p class="float-right">
                <b>{Trans::t("total_pay_price", $lang_id)}</b>
                /
                {if $lang_id == "10"}
                    <span class="price">NT$ {if $order->pay_price}{$order->pay_price['total_fee']}{else}{$order->total_price_tw}{/if}</span>
                {elseif $lang_id == "12"}
                    <span class="price">¥ {if $order->pay_price}{$order->pay_price['total_fee']}{else}{$order->total_price}{/if}</span>
                {else}
                    <span class="price">{if $order->pay_price}{$order->pay_price['total_fee']}{else}{$order->total_price}{/if}</span>
                {/if}
            </p>
            <div class="clear"></div>
        </div>
        <div class="cell-fat cell-border">
            <div class="form-row" id="room_name">
                <p class="font-big">{$homestay['name']}</p>
            </div>
            <div class="form-row">
                <label>{Trans::t("homestay_address", $lang_id)}</label>
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
                <label>{Trans::t("check_in_room", $lang_id)} {Trans::t("check_in_room_num_and_nights_%n_%d", $lang_id, ['%n'=>$order->room_num, '%d'=>$order->guest_days])}</label>
                <p class="font-big">{$order->room_name}</p>
            </div>
        </div>
        <div class="cell-fat cell-border">
            <div class="form-row form-row-half">
                <label>{Trans::t("check_in_date", $lang_id)}</label>
                <p class="font-big">{$order->guest_date}</p>
            </div>
            <div class="form-row form-row-half">
                <label>{Trans::t("check_out_date", $lang_id)}</label>
                <p class="font-big">{$order->guest_checkout_date}</p>
            </div>
        </div>
        <div class="cell-fat">
            <div class="form-row form-row-half">
                <label>{Trans::t("check_in_user", $lang_id)}</label>
                <p class="font-big">{$order->guest_name}</p>
                <p>{$order->guest_number}{Trans::t("adult", $lang_id)} {$order->guest_child_number}{Trans::t("children", $lang_id)}</p>
                {if $order->guest_child_age > 0}
                    <p>{Trans::t("guest_child_info", $lang_id)} / {Trans::t('age', $lang_id)} {$order->guest_child_age}</p>
                {/if}
            </div>
            <div class="form-row form-row-half">
                <div class="form-row form-row-inline">
                    <label>{Trans::t("contact_phone", $lang_id)}</label>
                    <p>{$order->guest_telnum}</p>
                </div>
                <div class="form-row form-row-inline">
                    <label>{Trans::t("email", $lang_id)}</label>
                    <p>{$order->guest_mail}</p>
                </div>
                {if $order->guest_wechat}
                <div class="form-row form-row-inline">
                    <label>{Trans::t("webchat", $lang_id)}</label>
                    <p>{$order->guest_wechat}</p>
                </div>
                {/if}
                {if $order->guest_line_id}
                <div class="form-row form-row-inline">
                    <label>{Trans::t("guest_line_id", $lang_id)}</label>
                    <p>{$order->guest_line_id}</p>
                </div>
                {/if}
            </div>
        </div>
    </div>

    <div class="cell-fat cell-border">
        <div class="form-row" id="spec_services">
            <label>{Trans::t("spec_service", $lang_id)}</label>
            {if not $order_addition_services}
                <p>{Trans::t("no_spec_service", $lang_id)}</p>
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
                            {Trans::t('baoche', $lang_id)}
                        {else}
                            {$s['service_name']}
                        {/if}
                    </span>
                    <span class="service_price">
                        {if !$s['free']}
                            {Trans::t('free', $lang_id)}
                        {else}
                            {if $lang_id == "10"}
                                NT${if $homestay['dest_id'] == 10}{$s['price']}{else}{ceil($s['price']*$order->exchange_rate)}{/if}
                            {else}
                                ¥{if $homestay['dest_id'] == 10}{ceil($s['price']/$order->exchange_rate)}{else}{$s['price']}{/if}
                            {/if}
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
            <label>{Trans::t("total_pay_price", $lang_id)}</label>
            <p>
                {if $lang_id == "10"}
                    <span class="price">NT$ {if $order->pay_price}{$order->pay_price['total_fee']}{else}{$order->total_price_tw}{/if}</span>
                {elseif $lang_id == "12"}
                    <span class="price">¥ {if $order->pay_price}{$order->pay_price['total_fee']}{else}{$order->total_price}{/if}</span>
                {else}
                    <span class="price">{if $order->pay_price}{$order->pay_price['total_fee']}{else}{$order->total_price}{/if}</span>
                {/if}
            </p>
        </div>
    </div>

    <div id="refund_policy" class="cell-fat">
        <div class="form-row">
            <label>{Trans::t("refund_policy", $lang_id)}</label>
        </div>
        {if $refund_data}
        <div class="refund-view">
            <div id="refund_label" class="refund-view-list clearfix">
                <p class="full-refund">
                    <span class="refund-view-bubble">{Trans::t('%d_prior', false, ['%d' => $refund_data['refund_list'][1]['day']])}</span>
                </p>
                <p class="partial-refund">
                    <span class="refund-view-bubble">{Trans::t('%d_prior', false, ['%d' => $refund_data['refund_list'][2]['day']])}</span>
                </p>
                <div class="clear"></div>
            </div>
            <div class="refund-view-list clearfix">
                <p class="refund-view-row full-refund">
                    <label class="refund-view-label">{Trans::t('full_refund')}</label>
                </p>
                <p class="refund-view-row partial-refund">
                    <label class="refund-view-label">{Trans::t('%p%_refund', false, ['%p' => $refund_data['refund_list'][2]['per']])}</label>
                </p>
                <p class="refund-view-row none-refund">
                    <label class="refund-view-label">{Trans::t('non_refundable')}</label>
                </p>
            </div>
        </div>
        <p class="no_refund_date">{$order->guest_date}</p>
        <div class="clear"></div>
        {else}
        <div class="form-row">
            <p>{str_replace("\n", "<br/>", $homestay['field_data_field__dingfangshuoming'])}</p>
        </div>
        {/if}
    </div>
{/block}
