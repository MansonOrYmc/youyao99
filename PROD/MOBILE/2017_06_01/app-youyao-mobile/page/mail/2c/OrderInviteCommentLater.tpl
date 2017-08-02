{extends file="Layout.tpl"}

{block name="style"}
    {include file="2c/OrderInviteCommentLater.css"}
{/block}

{block name="body"}
    <div class="header">
        <span class="icon32 icon32-info float-left"></span>
        <div class="float-left">
            <h2>{Trans::t("order_invite_comment_later_info", $lang_id)}</h2>
        </div>
        <img class="float-right" src="http://pages.youyao.com/a/img/homepage3/red_logo_small.png">
        <div class="clear"></div>
    </div>

    <div class="main_header">
        <p class="order_new_msg font-big">{Trans::t("order_invite_comment_later_msg_%gname_%hname", $lang_id, ['%gname'=>$order->guest_name, '%hname'=>$homestay['name']])}</p>
        <a target="_blank" href="http://www{$apf->get_config('base_domain')}/user/{$order->guest_uid}/mycomment" id="pay_btn">{Trans::t("i_need_to_comment", $lang_id)}</a>
        <p class="pay_link">{Trans::t("order_invite_comment_later_tips", $lang_id)}</p>
    </div>
{/block}
