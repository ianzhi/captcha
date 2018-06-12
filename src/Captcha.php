<?php
namespace IanZhi\Captcha;
/**
 * 验证码类
 */
class Captcha
{
    /**
     * 验证码
     */
    private $code = '';

    /**
     * 配置项
     */
    private $config = [
        'type' => 'png', // jpeg|png|gif|bmp
        'path' => false, // 路径或者false
        'code_type' => 0, // 0=数字
        'code_size' => 4, // 验证码长度
        'font_size' => 15, // 文字大小，磅
        'width' => '100', // 生成图片宽度
        'height' => '50', // 生成图片高度
    ];

    /**
     * 字典
     */
    private $disc = [
        'number' => '0123456789',
        'lartin' => 'abcdefghijklmnopqrstuvwxyz',
    ];

    /**
     * 构造方法，传入配置或者保持空
     */
    public function __construct($config=[])
    {
        if ($config) {
            foreach ($config as $k=>$v) {
                if (array_key_exists($k, $this->config)) {
                    $this->config[$k] = $v;
                }
            }
        }
    }

    /**
     * 生成验证码内容
     * @return string 写入图片的验证码内容
     */
    private function generateCode($code_type=0, $code_size=0)
    {
        if ($code_type) {
            $this->config['code_type'] = $code_type;
        }
        if ($code_size) {
            $this->config['code_size'] = $code_size;
        }

        /**
         * 确定生成类型
         */
        switch ($this->config['code_type']) {
            case 0:
                $disc = $this->disc['number'];
                for ($i = 0; $i < $this->config['code_size']; $i ++) {
                    $this->code .= mt_rand(0, 9);
                }
                return $this->code;
        }
    }

    /**
     * 配置
     * @param string|array $key
     * @param mixed $value
     * @return FirstWordAvatar 对象
     */
    public function set($key, $value)
    {
        if (array_key_exists($key, $this->config)) {
            $this->config[$key] = $value;
        }
        return $this;
    }

    /**
     * 生成图片资源
     * @return resource 图片资源
     */
    public function generate()
    {
        // 准备画布资源
        $img_res = imagecreate($this->config['width'], $this->config['height']);

        // 背景
        $bg_red = mt_rand(0, 255);
        $bg_green = mt_rand(0, 255);
        $bg_blue = mt_rand(0, 255);
        $bg_color = imagecolorallocate($img_res, $bg_red, $bg_green, $bg_blue);
        imagefill($img_res, 0, 0, $bg_color);

        // 文字
        $code = $this->generateCode();
        $per_letter_width = $this->config['width'] / strlen($code);
        for($i = 0; $i < strlen($code); $i++) {
            // 第一个字母
            $letter = $code{$i};
            // 文字角度
            if (mt_rand(0, 1) === 0) {
                $angle = mt_rand(-80, 0);
            } else {
                $angle = mt_rand(0, 80);
            }
            // 文字大小
            $font_pos = imagettfbbox($this->config['font_size'], $angle, __DIR__.'/fonts/msyh.ttf', $letter);
            $font_width = $font_pos[2] - $font_pos[0] + 0.0763 * $this->config['font_size'];
            $font_height = $font_pos[1] - $font_pos[5] + 0.0763 * $this->config['font_size'];
            // 文字位置
            if ($angle >= 0) {
                $letter_x = mt_rand($per_letter_width * $i + $font_width, $per_letter_width * ($i + 1));
            } else {
                $letter_x = mt_rand($per_letter_width * $i, $per_letter_width * ($i + 1) - $font_width);
            }
            $letter_y = mt_rand($font_height, $this->config['height']);
            // 文字颜色
            $font_color_red = mt_rand(0, 255);
            $font_color_green = mt_rand(0, 255);
            $font_color_blue = mt_rand(0, 255);
            $font_color = imagecolorallocate($img_res, $font_color_red, $font_color_green, $font_color_blue);
            // 写入文字
            imagettftext($img_res, $this->config['font_size'], $angle, $letter_x, $letter_y, $font_color, __DIR__.'/fonts/msyh.ttf', $letter);
        }
        
        return $img_res;
    }
    
    /**
     * 输出图片（默认输出到浏览器，给定输出文件位置则输出到文件）
     * @param string|false $path 保存路径
     */
    public function output($path = false)
    {
        // 保存路径
        if (isset($path) && $path) {
            $this->config['path'] = $path;
        }

        $img_res = $this->generate();

        // 确定输出类型和生成用的方法名
        $content_type = 'image/' . $this->config['type'];
        $generateMethodName = 'image' . $this->config['type'];

        // 确定是否输出到浏览器
        if (!$this->config['path']) {
            header("Content-type: " . $content_type);
            $generateMethodName($img_res);
        } else {
            $generateMethodName($img_res, $this->config['path']);
        }

        // 销毁资源
        imagedestroy($img_res);
    }

    /**
     * 获取正确的验证码
     */
    public function getCode()
    {
        return $this->code;
    }
}