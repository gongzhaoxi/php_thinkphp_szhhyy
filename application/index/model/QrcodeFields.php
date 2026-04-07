<?php

namespace app\index\model;

use think\Model;

class QrcodeFields extends Model
{
    public function gxlines()
    {
        return $this->belongsToMany('GxLine','FieldRule','gxline_id','field_id');
    }
}
