<?php

namespace app\index\model;

use think\Model;

class GxLine extends Model
{
    public function qrcode()
    {
        return $this->belongsToMany('QrcodeFields','FieldRule','qrcode_id','gxline_id');
    }
}
