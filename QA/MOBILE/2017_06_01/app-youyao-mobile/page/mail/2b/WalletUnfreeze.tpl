{extends file="Layout.tpl"}

{block name="style"}
    {include file="2b/2bPublic.css"}
{/block}

{block name="body"}
    <div class="header">
        <span class="icon32 icon32-alert float-left"></span>
        <img class="float-right" src="http://pages.youyao.com/a/img/homepage3/red_logo_small.png">
        <h2 class="mail-subject float-left">{Trans::t('your_wallet_have_amout_become_avaiable')}</h2>
        <div class="clear"></div>
    </div>

    <div class="cell-wrapper">
        <div class="cell-fat pmsg-main clearfix">
            <p class="float-left pmsg-content">
            {Trans::t('wallet_become_avaiable_content', null, ['%p'=>$amount_str])}
            </p>
        </div>
    </div>

    <div class="cell-fat">
        <div class="form-row opera-div">
            <label class="opera-button">
                <a class="click-button dealt-button" href="{$click_link}">{Trans::t("view_now")}</a>
            </label>
        </div>
        <div class="form-row">
            <label class="tips-title">{Trans::t('homestaylianxfs')}：</label>
            <p class="tips-row"> contact@youyao.com </p>
            <p class="tips-row"> 4008-886-232 </p>
        </div>
    </div>
    <div style="clear:both"></div>

{/block}
