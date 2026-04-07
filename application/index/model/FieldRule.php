<?php

namespace app\index\model;

use think\Model;

class FieldRule extends Model
{
    public function index($where='')
    {
        $data = $this->alias('a')->field('a.*,b.fieldname,b.explains,c.title')->join('qrcode_fields b','a.field_id=b.id')
                ->join('gx_line c','a.gxline_id=c.id')
                ->where($where)
                ->paginate();
        return $data;
    }
}
