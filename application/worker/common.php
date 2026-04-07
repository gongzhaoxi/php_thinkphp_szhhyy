<?phpuse think\Db;
function create_sn($table, $field='sn', $prefix = '', $rand_suffix_length = 4, $pool = []){    $suffix = '';    for ($i = 0; $i < $rand_suffix_length; $i++) {        if (empty($pool)) {            $suffix .= rand(0, 9);        } else {            $suffix .= $pool[array_rand($pool)];        }    }    $sn = $prefix . date('Ymd') . $suffix;
		if($table == 'order_material' || $table == 'order_price'){
			if (Db::name('order_material')->where($field, $sn)->find() || Db::name('order_price')->where($field, $sn)->find()) {
			    return create_sn($table, $field, $prefix, $rand_suffix_length, $pool);
			}
		}else{
			if (Db::name($table)->where($field, $sn)->find()) {
			    return create_sn($table, $field, $prefix, $rand_suffix_length, $pool);
			}
		}
		        return $sn;}






