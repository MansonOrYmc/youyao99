<!DOCTYPE HTML>
<html>
<head>
</head>
<body>
<div id="wrapper">
    <div id="main">
        {block name="body"}{/block}
        {include file="MainFooter.tpl"}
    </div>
    {include file="Footer.tpl"}
</div>
<style type="text/css">
    {include file="Layout.css"}
    {include file="Public.css"}
    {block name="style"}{/block}
</style>
</body>
</html>
