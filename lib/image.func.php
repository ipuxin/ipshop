<?php
//引入产生随机字符串的函数
require_once 'string.func.php';
//通过GD库做验证码
function verifyImage($type = 1, $length = 1, $pixel = 50, $line = 3, $sess_name = "verify")
{
    session_start();
    //创建画布
    $width = 80;
    $height = 30;
    $image = imagecreatetruecolor($width, $height);
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    //用填充矩形填充画布
    imagefilledrectangle($image, 1, 1, $width - 2, $height - 2, $white);
    /*
     * @param $type 1-3中字符串类型
     * @param $length 自定义长度
     */
    //调用函数生成随机数
    $chars = buildRandomString($type, $length);

    //把验证码放入session中,以便和用户的做对比
    $_SESSION [$sess_name] = $chars;
    $fontfiles = array("micross.ttf", "MSUIGHUB.TTF", "MSUIGHUR.TTF", "ntailu.ttf", "ntailub.ttf", "phagspa.ttf");
//	$fontfiles = array ("SIMYOU.TTF" );
    //循环写出验证码
    for ($i = 0; $i < $length; $i++) {
        //字体大小
        $size = mt_rand(14, 18);
        //角度
        $angle = mt_rand(-15, 15);
        $x = 5 + $i * ($size+3);
        $y = mt_rand(20, 26);
        $fontfile = "../fonts/" . $fontfiles [mt_rand(0, count($fontfiles) - 1)];
        $color = imagecolorallocate($image, mt_rand(50, 90), mt_rand(80, 200), mt_rand(90, 180));
        $text = substr($chars, $i, 1);
        /*参数 size 为字形的尺寸；angle 为字型的角度，顺时针计算，0 度为水平，也就是三点钟的方向 (由左到右)，90 度则为由下到上的文字；x,y 二参数为文字的坐标值 (原点为左上角)；参数 col 为字的颜色；fontfile 为字型文件名称，亦可是远端的文件；text 当然就是字符串内容了。返回值为数组，包括了八个元素，头二个分别为左下的 x、y 坐标，第三、四个为右下角的 x、y 坐标，第五、六及七、八二组分别为右上及左上的 x、y 坐标。注意的是欲使用本函数，系统要装妥 GD 及 Freetype 二个函数库。*/
        imagettftext($image, $size, $angle, $x, $y, $color, $fontfile, $text);
    }
    //添加干扰元素:点
    if ($pixel) {
        for ($i = 0; $i < 50; $i++) {
            imagesetpixel($image, mt_rand(0, $width - 1), mt_rand(0, $height - 1), $black);
        }
    }
    //添加干扰元素:线
    if ($line) {
        for ($i = 1; $i < $line; $i++) {
            $color = imagecolorallocate($image, mt_rand(50, 90), mt_rand(80, 200), mt_rand(90, 180));
            imageline($image, mt_rand(0, $width - 1), mt_rand(0, $height - 1), mt_rand(0, $width - 1), mt_rand(0, $height - 1), $color);
        }
    }
    header("content-type:image/gif");
    imagegif($image);
    imagedestroy($image);
}

/**
 * 生成缩略图
 * @param string $filename
 * @param string $destination
 * @param int $dst_w
 * @param int $dst_h
 * @param bool $isReservedSource
 * @param number $scale
 * @return string
 */
function thumb($filename, $destination = null, $dst_w = null, $dst_h = null, $isReservedSource = true, $scale = 0.5)
{
    list($src_w, $src_h, $imagetype) = getimagesize($filename);
    if (is_null($dst_w) || is_null($dst_h)) {
        $dst_w = ceil($src_w * $scale);
        $dst_h = ceil($src_h * $scale);
    }
    $mime = image_type_to_mime_type($imagetype);
    $createFun = str_replace("/", "createfrom", $mime);
    $outFun = str_replace("/", null, $mime);
    $src_image = $createFun($filename);
    $dst_image = imagecreatetruecolor($dst_w, $dst_h);
    imagecopyresampled($dst_image, $src_image, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
    if ($destination && !file_exists(dirname($destination))) {
        mkdir(dirname($destination), 0777, true);
    }
    $dstFilename = $destination == null ? getUniName() . "." . getExt($filename) : $destination;
    $outFun($dst_image, $dstFilename);
    imagedestroy($src_image);
    imagedestroy($dst_image);
    if (!$isReservedSource) {
        unlink($filename);
    }
    return $dstFilename;
}

/**
 *添加文字水印
 * @param string $filename
 * @param string $text
 * @param string $fontfile
 */
function waterText($filename, $text = "imooc.com", $fontfile = "MSYH.TTF")
{
    $fileInfo = getimagesize($filename);
    $mime = $fileInfo ['mime'];
    $createFun = str_replace("/", "createfrom", $mime);
    $outFun = str_replace("/", null, $mime);
    $image = $createFun ($filename);
    $color = imagecolorallocatealpha($image, 255, 0, 0, 50);
    $fontfile = "../fonts/{$fontfile}";
    imagettftext($image, 14, 0, 0, 14, $color, $fontfile, $text);
    $outFun ($image, $filename);
    imagedestroy($image);
}

/**
 *添加图片水印
 * @param string $dstFile
 * @param string $srcFile
 * @param int $pct
 */
function waterPic($dstFile, $srcFile = "../images/logo.jpg", $pct = 30)
{
    $srcFileInfo = getimagesize($srcFile);
    $src_w = $srcFileInfo [0];
    $src_h = $srcFileInfo [1];
    $dstFileInfo = getimagesize($dstFile);
    $srcMime = $srcFileInfo ['mime'];
    $dstMime = $dstFileInfo ['mime'];
    $createSrcFun = str_replace("/", "createfrom", $srcMime);
    $createDstFun = str_replace("/", "createfrom", $dstMime);
    $outDstFun = str_replace("/", null, $dstMime);
    $dst_im = $createDstFun ($dstFile);
    $src_im = $createSrcFun ($srcFile);
    imagecopymerge($dst_im, $src_im, 0, 0, 0, 0, $src_w, $src_h, $pct);
//	header ( "content-type:" . $dstMime );
    $outDstFun ($dst_im, $dstFile);
    imagedestroy($src_im);
    imagedestroy($dst_im);
}



