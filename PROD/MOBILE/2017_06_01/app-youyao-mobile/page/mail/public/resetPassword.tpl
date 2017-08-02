{extends file="Layout.tpl"}

{block name="style"}
    {include file="public/resetPassword.css"}
{/block}

{block name="body"}
    <div class="header">
        <span class="icon32 icon32-info float-left"></span>
        <h2 class="float-left">{Trans::t("reset_password_title", $lang_id)}</h2>
        <img class="float-right" src="http://pages.youyao.com/a/img/homepage3/red_logo_small.png">
        <div class="clear"></div>
    </div>

    <div class="main_header">
        <p class="font-big">{Trans::t("reset_password_tips_line1_%s", $lang_id, ['%s'=>$user_info['name']])}</p>
        <p class="font-big">{Trans::t("reset_password_tips_line2", $lang_id)}</p>
        <a id="reset_btn" href="{$url}">{Trans::t('reset_password', $lang_id)}</a>
        <p class="font-big">{Trans::t("reset_password_tips_line3", $lang_id)}</p>
        <p class="font-big">{Trans::t("reset_password_tips_line4", $lang_id)}</p>
        <a href="{$url}">{$url}</a>
        <p class="font-big">{Trans::t("reset_password_tips_line5", $lang_id)}</p>
    </div>
{/block}
