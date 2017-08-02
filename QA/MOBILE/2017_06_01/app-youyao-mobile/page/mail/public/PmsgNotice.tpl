{extends file="Layout.tpl"}

{block name="style"}
    {include file="public/Public.css"}
{/block}

{block name="body"}
    <div class="header">
        <span class="icon32 icon32-alert float-left"></span>
        <img class="float-right" src="http://pages.youyao.com/a/img/homepage3/red_logo_small.png">
        <h2 class="mail-subject float-left">{Trans::t("yougotanewprivatemsgat")}</h2>
        <span class="title-content float-left">{Trans::t("you_got_a_pmsg_from_%n_on_%d", false, ["%n"=>$from_data['nickname'], "%d"=>$msg['timestamp']|date_format:"%Y-%m-%d %k:%M" ])}</span>
        <div class="clear"></div>
    </div>

    <div class="cell-wrapper">
        <div class="cell-fat pmsg-main clearfix">
            <label class="float-left pmsg-to">【{Trans::t("homestay_master")}】：</label>
            <p class="float-left pmsg-content">
            {$msg['body']}
            </p>
            <label class="float-right pmsg-sign">【{Trans::t("youyao")}】：{$from_data['nickname']}</label>
            <div class="clear"></div>
        </div>
    </div>

    <div class="cell-fat">
        <div class="form-row opera-div">
            <label class="opera-button">
                <a class="click-button dealt-button" href="{$click_link}">{Trans::t("reply_now")}</a>
            </label>
        </div>
        <div class="form-row">
            <label class="tips-title">{Trans::t("tips-title")}</label>
            <p class="tips-row"> {Trans::t("pmsg_tips_1")} </p>
            <p class="tips-row"> {Trans::t("pmsg_tips_2")} </p>
        </div>
    </div>
    <div style="clear:both"></div>

{/block}
