<?php

# 自定义返回代码

use Illuminate\Pagination\LengthAwarePaginator;
use Dcat\Admin\Widgets\Metrics\Card;

const SUCCESS_CODE = 1;
const ERROR_CODE = 0;

# 自定义特殊报错码
const NO_LOGIN = "no_login";
const NO_BIND_WX = "no_bind_wx";
const NO_BIND_ALIPAY = "no_bind_alipay";
const NO_BIND_PHONE = "no_bind_phone";

/**
 * 返回异常回调信息
 *
 * @param string $msg 返回信息
 * @param string $error 特殊错误码
 * @return void
 */
function error(string $msg, string $error = ''){
    return return_data(ERROR_CODE, $msg, [], $error);
}

/**
 * 返回成功回调信息
 *
 * @param string $msg 返回信息
 * @param array $data 返回数据
 * @return void
 */
function success(string $msg, null|array|Illuminate\Support\Collection|Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|LengthAwarePaginator $data = []){
    return return_data(SUCCESS_CODE, $msg, $data);
}

/**
 * 手动抛出异常，用于中断程序
 *
 * @param string $msg 返回信息
 * @return void
 */
function throwBusinessException(string $msg, string $error = ''){
    throw new \App\Exceptions\BusinessException($msg, $error ?: null);
}

/**
 * 接口返回json格式的数据
 *
 * @param integer $code 项目自定义错误码
 * @param string $msg 返回信息
 * @param array $data 返回数据
 * @param string $error 特殊错误码
 * @return void
 */
function return_data(int $code, string $msg, null|array|Illuminate\Support\Collection|Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|LengthAwarePaginator $data, string $error = ''){
    $response = ['code'=> $code, 'msg'=> $msg, 'data'=> $data];
    if ($error) {
        $response['error'] = $error;
    }
    return response()->json($response, 200); # 此 200 为真正的 http 状态码
}

/**
 * 生成随机码
 *
 * @param int $number 随机码位数
 * @param string $type 随机码内容类型
 * @return string
 */
function create_captcha(int $number, string $type = 'figure'):string{
    $array_figure = [1, 2, 3, 4, 5, 6, 7, 8, 9, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 0];
    $array_lowercase = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
    $array_uppercase = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
    switch($type){
        case 'lowercase':
            $res_array = $array_lowercase;
            break;
        case 'uppercase':
            $res_array = $array_uppercase;
            break;
        case 'lowercase+figure':
            $res_array = array_merge($array_lowercase, $array_figure);
            break;
        case 'uppercase+figure':
            $res_array = array_merge($array_uppercase, $array_figure);
            break;
        case 'lowercase+uppercase':
            $res_array = array_merge($array_lowercase, $array_uppercase);
            break;
        case 'lowercase+uppercase+figure':
            $res_array = array_merge(array_merge($array_lowercase, $array_uppercase), $array_figure);
            break;
        default:
            $res_array = $array_figure;
            break;
    }
    $resstr = '';
    shuffle($res_array);
    foreach(array_rand($res_array, $number) as $v){
        $resstr .= $res_array[$v];
    }
    return $resstr;
}

/**
 * 将包含英文逗号的字符串(以英文逗号隔开的字符串参数)转换成数组
 *
 * @param string $str 以英文逗号隔开的字符串
 * @return Array
 */
function comma_str_to_array(string $str):Array {
    return array_filter(explode(',', $str));
}


/**
 * 从一个数据列表中获取指定字段
 *
 * @param \Illuminate\Database\Eloquent\Collection $data_list
 * @param string $field
 * @return void
 */
function get_collection_field(\Illuminate\Database\Eloquent\Collection $data_list, string $field){
    $data = [];
    foreach($data_list as $value){
        $data[] = $value->$field;
    }
    return $data;
}

/**
 * 后台用户展示优化
 *  使用方式：在 grid() 方法中的字段列表对象中使用 display() 方法，然后使用此方法
*
* @param [type] $user_data
* @return void
*/
function admin_grid_user_field($user_data, $default = '已注销'){
    try {
        $avatar = $user_data->avatar == '' ? config("app.url") . "/static/avatar.jpeg" : full_url($user_data->avatar);
        $str = '<div style="width:200px">';
        $str .= '<img data-action="preview-img" src="' . $avatar . '" style="margin-top: 1px;margin-right: 1px;width:60px;height:60px;cursor:pointer;float:left;" class="img img-thumbnail">';
        $str .= 'ID:&nbsp;<span class="label" style="padding:0em 0.3em 0em;background:#586cb1">' . $user_data->id . '</span>';
        $str .= "<br/>";
        $str .= '<span style="padding-top:2px;"><i class="feather icon-user"></i>:&nbsp;' . $user_data->nickname . '&nbsp;';
        $str .= "<br/>";
        $str .= '<span style="padding-top:22px;"><i class="feather icon-smartphone"></i>:&nbsp;' . $user_data->phone . '</span>';
        $str .= '</div>';
        return $str;
    } catch (\Exception $e) {
        return $default;
    }
}

/**
 * 后台富文本展示
 *  使用方法，将 grid() 中的富文本字段列表对象传入此方法
 *
 * @param [type] $content_obj
 * @return void
 */
function admin_grid_content($content_obj){
    return $content_obj->width('15%')->display('')->modal(function ($modal) {
        $modal->title($this->title);
        $this->content == null ? $modal->icon('feather ') : $modal->icon('feather icon-eye');
        $card = (new Card(null, ''))->header($this->content);
        return "<div style='padding:10px 10px 0'>$card</div>";
    });
}

/**
 * 后台图片表单上传处理
 *  使用方法，将 form() 中的图片表单对象传入此方法
 *
 * @param [type] $image_obj
 * @return void
 */
function admin_form_image_field($image_obj){
    return $image_obj->autoUpload()->uniqueName()->removable(false)->retainable();
}

/**
 * 后台媒体选择表单上传处理
 *  使用方法，将 form() 中的媒体选择表单对象传入此方法
 *
 * @param [type] $media_selector_obj
 * @param int $limit
 * @param array $types image-图片选择,video-视频选择,audio-音频选择,powerpoint-文稿选择,code-代码文件选择,zip-压缩包选择,text-文本选择,other-其他选择
 * @return void
 */
function admin_form_media_selector_field($media_selector_obj, int $limit = 1, array $types = ['image']){
    return $media_selector_obj->options([
        'area'     => ['60%', '98%'], // 弹框大小
        'limit'    => $limit, // 媒体数量
        'types'    => $types, // 媒体选择类型
        'sortable' => true, // 排序
        'move'     => json_encode(['dir' => 'media', 'fileNameIsEncrypt' => true]),// 第一个参数，媒体上传路径。默认media。第二个参数，媒体名称是否加密。默认true
    ]);
}

/**
 * 后台数字表单输入限制
 *  使用方法，将 form() 中的数字表单对象传入此方法
 *
 * @param [type] $number_obj
 * @param string $step
 * @return void
 */
function admin_form_number_field($number_obj, $step = '0.01'){
    return $number_obj->attribute('type', 'number')->attribute('step', $step);
}

/**
 * 整合分页的统计数据
 *
 * @param LengthAwarePaginator $datas
 * @return void
 */
function format_paginated_datas(LengthAwarePaginator $datas, array $visible = ['*'], ?callable $func = null){
    $data = null;
    if($func){
        $data = $datas->map(function($item) use ($func){
            return $func($item);
        });
    }
    if(count($visible) > 0 && $visible[0] != '*'){
        $data = $datas->setVisible($visible);
    }
    return [
        'total'=> $datas->total(),  // 总条数
        'count'=> $datas->count(),  // 当页总数
        'perPage'=> $datas->perPage(),  // 每页应展示数量
        'currentPage'=> $datas->currentPage(),  // 当前页码
        'lastPage'=> $datas->lastPage(),  // 最后一页页码
        'firstItem'=> $datas->firstItem(),  // 当页第一条数据的编号(id)
        'lastItem'=> $datas->lastItem(),  // 当页最后一条数据的编号(id)
        'datas'=> $data ?? $datas->setVisible($visible),
    ];
}

/**
* 获取文件的完整路径
*  一般情况下，查询出来的图片、文件数据直接通过模型中的访问器修改为了完整路径。
*  但可能有一些特殊情况不是由模型获取到的数据，需要手动拼接完整路径。
*
* @param string $file_path 文件路径
* @param string $catelogue 文件目录
* @return void
*/
function full_url($file_path, $catelogue = '/uploads/'){
    if($file_path == '' || $file_path == null){
        return '';
    }
    if(strpos($file_path, 'http') !== false){
        return $file_path;
    }
    if(strpos($file_path, 'https') !== false){
        return $file_path;
    }
    if(strpos($file_path, 'data:') !== false){
        return $file_path;
    }
    return url($catelogue . $file_path);
}

/**
 * 生成打开 iframe 弹框的统一按钮（弹框走全局 window.openDcatFrame：mini 布局无侧边栏、80% 尺寸）
 *  使用方式：后台 Grid tools 中 $tools->append(admin_dialog_frame_button(admin_base_path('sys/xxx'), __('xxx.buttons.xxx')))
 *
 * @param string $url   弹框页 URL（admin 路径，如 admin_base_path('sys/xxx')）
 * @param string $title 弹框标题/按钮文案
 * @param string $icon  图标类，默认 feather icon-folder
 * @param string $class 按钮类，默认 btn btn-primary
 * @return string
 */
function admin_dialog_frame_button($url, $title, $icon = 'feather icon-folder', $class = 'btn btn-primary'){
    $onclick = 'window.openDcatFrame('.json_encode($url).', '.json_encode($title).')';

    return '<a href="javascript:void(0)" class="'.e($class).'" onclick="'.e($onclick).'"><i class="'.$icon.'"></i>&nbsp;'.e($title).'</a>';
}

/**
 * 获取币种名称（按当前语言）
 *
 * @param  string      $currency 币种标识（config('business.wallet.*') 的 key）
 * @param  string|null $locale   语言，默认当前 app locale
 * @return string 名称；币种或语言未配置时返回币种标识本身
 */
function wallet_currency_name($currency, $locale = null)
{
    $locale = $locale ?: app()->getLocale();

    return config('business.wallet.currencies.'.$currency.'.name.'.$locale, $currency);
}

/**
 * 用户信息名片（左头像 + 右昵称/ID/手机号上下分布）
 * 用于所有与用户关联的后台列表的用户信息展示
 *
 * @param mixed       $user    用户模型（需有 id/nickname/avatar/phone；null 或异常返回默认）
 * @param string      $default 用户不存在/异常时的文案
 */
function admin_user_card($user, $default = '已注销')
{
    try {
        if (! $user || ! isset($user->id)) {
            return $default;
        }

        $avatar = ! empty($user->avatar) ? full_url($user->avatar) : config('app.url').'/static/avatar.jpeg';

        return '<div class="d-flex align-items-center" style="min-width:150px">'
            .'<img src="'.e($avatar).'" data-action="preview-img" class="img img-thumbnail rounded-circle" style="width:50px;height:50px;object-fit:cover;cursor:pointer;flex-shrink:0">'
            .'<div style="line-height:1.7">'
            .'<div><i class="feather icon-user"></i>: '.e((string) ($user->nickname ?? ''))
            .'&nbsp;&nbsp;<span class="label" style="background:#586cb1; font-size:80%; padding: 1px 5px;">'.e((string) $user->id).'</span></div>'
            .'<div><i class="feather icon-smartphone"></i>: '.e((string) ($user->phone ?? '')).'</div>'
            .'</div></div>';
    } catch (\Exception $e) {
        return $default;
    }
}

/**
 * 获取钱包流水类型名称（按当前语言）
 *
 * @param  string      $type   流水类型标识（config('business.wallet.*') 的 key）
 * @param  string|null $locale 语言，默认当前 app locale
 * @return string 名称；类型或语言未配置时返回类型标识本身
 */
function wallet_record_type_name($type, $locale = null)
{
    $locale = $locale ?: app()->getLocale();

    return config('business.wallet.types.'.$type.'.name.'.$locale, $type);
}
