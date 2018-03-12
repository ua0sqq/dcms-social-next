<?php
class captcha
{
    public $str;
    public $x = 100;
    public $y = 40;
    public $img;
    public $gif=false;
    public $png=false;
    public $jpg=false;
    public function __construct($str)
    {
        if (!function_exists('gd_info')) {
            header('Location: /style/errors/gd_err.gif');
            exit;
        }
        if (imagetypes() & IMG_PNG) {
            $this->png=true;
        }
        if (imagetypes() & IMG_GIF) {
            $this->gif=true;
        }
        if (imagetypes() & IMG_JPG) {
            $this->jpg=true;
        }
        $this->str=$str;
        $this->img=imagecreatetruecolor($this->x, $this->y);
        imagefill($this->img, 0, 0, imagecolorallocate($this->img, 255, 255, 255));
    }
    public function create()
    {
        for ($i=0; $i<5 ;$i++) {
            $n = $this->str{$i};
            if ($this->png) {
                $num[$n]=imagecreatefrompng(H.'/style/captcha/'.$n.'.png');
            } elseif ($this->gif) {
                $num[$n]=imagecreatefromgif(H.'/style/captcha/'.$n.'.gif');
            } elseif ($this->jpg) {
                $num[$n]=imagecreatefromjpeg(H.'/style/captcha/'.$n.'.jpg');
            }
            imagecopy($this->img, $num[$n], $i*15+10, 8, 0, 0, 15, 20);
        }
    }
    
    public function MultiWave()
    {
        include_once H.'sys/inc/MultiWave.php';
        $this->img=MultiWave($this->img);
    }
    public function colorize($value=90)
    {
        if (function_exists('imagefilter')) {
            imagefilter($this->img, IMG_FILTER_COLORIZE, mt_rand(0, $value), mt_rand(0, $value), mt_rand(0, $value));
        }
    }
    public function output($q=50)
    {
        @ob_end_clean();
        if ($this->jpg) {
            header("Content-type: image/jpeg");
            imagejpeg($this->img, null, $q);
        } elseif ($this->png) {
            header("Content-type: image/png");
            imagepng($this->img);
        } elseif ($this->gif) {
            header("Content-type: image/gif");
            imagegif($this->img);
        }
        exit;
    }
}
