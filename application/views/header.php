<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="language" content="en"/>
    <link href="<?php echo base_url(); ?>assets/50ed00da/css/bootstrap.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/3e14fee7/solarized_light.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/a57ddca8/style.css" rel="stylesheet">
    <script src="<?php echo base_url(); ?>assets/4d48157f/jquery.js"></script>
    <script src="<?php echo base_url(); ?>assets/50ed00da/js/bootstrap.js"></script>
    <script src="<?php echo base_url(); ?>assets/306e634b/jssearch.js"></script>
    <title>Cash register validator</title>
</head>
<body>
<div class="wrap">
    <nav id="w34" class="navbar-inverse navbar-fixed-top navbar" role="navigation">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#w34-collapse"><span
                    class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span></button>
            <a class="navbar-brand" href="#">Cash register validator</a>
            <a class="navbar-brand" href="<?php echo base_url(); ?>Manual.docx">Manual</a>
        </div>
        <? if (!empty($this->session->userdata('credentials'))): ?>
            <a class="pull-right header-logout" href="/welcome/logout">Logout</a>
        <? endif; ?>
        <div id="w34-collapse" class="collapse navbar-collapse"></div>
    </nav>