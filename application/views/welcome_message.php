<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<? $this->load->view('header'); ?>
    <div class="container">
        <div class="wrapper">
            <?php if ($this->session->flashdata('errorMessage')): ?>
                <div class="alert alert-danger">
                    <?= $this->session->flashdata('errorMessage') ?>
                </div>
            <? endif; ?>
            <form action="/welcome/connect" method="post" name="Login_Form" class="form-signin">
                <h3 class="form-signin-heading">Validate web-server schema</h3>
                <hr class="colorgraph">
                <br>

                <input type="text" class="form-control" name="ipAddress" placeholder="IP address" required=""
                       autofocus=""/>
                <input type="text" class="form-control" name="login" placeholder="Login" required=""
                       autofocus=""/>
                <input type="password" class="form-control" name="password" placeholder="Password" required=""/>

                <?= form_dropdown('type', $countries, [], 'class="form-control"') ?>

                <button class="btn btn-lg btn-primary btn-block" name="Submit" value="Connect" type="Submit">Connect
                </button>
            </form>
        </div>
    </div>
<? $this->load->view('footer'); ?>