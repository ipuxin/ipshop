<?php
//登录处理
require_once '../include.php';
$username = $_POST['username'];
$username = addslashes($username);
$password = md5($_POST['password']);
//获取提交过来的验证码
$verify = $_POST['verify'];
//获取session中的验证码
$verify1 = $_SESSION['verify'];
//是否自动登录
$autoFlag = $_POST['autoFlag'];

//判断验证码
if ($verify == $verify1) {
    //判断是否登录成功
    $sql = "select * from imooc_admin where username='{$username}' and password='{$password}'";
    $row = checkAdmin($sql);

    //如果有记录,保存到session
    if ($row) {
        //如果选了一周内自动登陆
        if ($autoFlag) {
            setcookie("adminId", $row['id'], time() + 7 * 24 * 3600);
            setcookie("adminName", $row['username'], time() + 7 * 24 * 3600);
        }
        $_SESSION['adminName'] = $row['username'];
        $_SESSION['adminId'] = $row['id'];

        //登录成功,并跳转
        alertMes("登陆成功", "index.php");
    } else {
        //登录失败并跳转
        alertMes("登陆失败，重新登陆", "login.php");
    }
} else {
    alertMes("验证码错误", "login.php");
}