<?php
//提示消息公用方法
function alertMes($mes,$url){
	echo "<script>alert('{$mes}');</script>";
	echo "<script>window.location='{$url}';</script>";
}
