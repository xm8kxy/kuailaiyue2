<?php


namespace Qwadmin\Controller;

class LogoutController extends ComController
{
    public function index()
    {
        cookie('auth', null);
        session('uid',null);
        $url = U("login/index");
        header("Location: {$url}");
        exit(0);
    }
}