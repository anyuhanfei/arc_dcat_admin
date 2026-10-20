<?php

namespace App\Admin\Controllers\Sys;

use App\Admin\Repositories\Sys\SysConfigs;
use App\Models\Sys\SysConfigs as SysConfigsModel;
use App\Models\Sys\SysConfigCategories;
use App\Enums\ConfigType;
use Dcat\Admin\Form;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SysConfigEditorController extends AdminController
{
    /**
     * 配置编辑页（按分类 tab 展示可编辑配置项）
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content): Content
    {
        return $content
            ->title(__('sys-config-editor.title'))
            ->body($this->buildForm());
    }

    /**
     * 构建配置编辑表单
     *
     * @return Form
     */
    private function buildForm(): Form
    {
        return Form::make(new SysConfigs(), function (Form $form) {
            // action 用不带前缀的相对路径，内部自动拼接 admin 前缀
            $form->action('sys/config-editor');

            // 去掉右上角列表按钮
            $form->disableListButton();
            $form->disableCreatingCheck();
            $form->disableDeleteButton();
            $form->disableViewButton();
            $form->disableViewCheck();
            $form->disableEditingCheck();

            // 右上角工具区：配置项管理（整页跳转，不再弹框）
            $form->tools(function (\Dcat\Admin\Form\Tools $tools) {
                $tools->append(
                    '<a href="'.admin_base_path('sys/configs').'" class="btn btn-primary">'
                    .'<i class="feather icon-folder"></i>&nbsp;'.__('sys-config-editor.buttons.config_manage')
                    .'</a>'
                );
            });

            // 查询所有可编辑配置项
            $configs = SysConfigsModel::query()
                ->where('is_editable', 1)
                ->with('category')
                ->orderBy('sort')
                ->get();

            if ($configs->isEmpty()) {
                $form->html('<div class="alert alert-info">' . __('sys-config-editor.messages.no_editable_config') . '</div>');
                return;
            }

            // 按分类分组
            $grouped = $configs->groupBy('category_id');

            // 查询有可编辑配置项的分类，按 sort 排序
            $categories = SysConfigCategories::query()
                ->whereIn('id', $grouped->keys())
                ->orderBy('sort')
                ->get();

            foreach ($categories as $category) {
                $form->tab($category->name, function (Form $form) use ($grouped, $category) {
                    $categoryConfigs = $grouped->get($category->id, collect());
                    foreach ($categoryConfigs as $config) {
                        $fieldName = 'config_' . $config->id;
                        $this->renderEditorField($form, $config, $fieldName);
                    }
                });
            }
        });
    }

    /**
     * 保存配置编辑页的 POST 提交
     *
     * @return mixed
     */
    public function store(): mixed
    {
        // autoUpload 独立上传 XHR：_file_ 为 webuploader 字段，upload_column 为 config_{id}
        if ($file = request()->file('_file_')) {
            return $this->handleAutoUpload($file, request('upload_column'));
        }

        $data = request()->all();

        $configs = SysConfigsModel::query()->where('is_editable', 1)->get();

        $errors = [];

        foreach ($configs as $config) {
            $fieldName = 'config_' . $config->id;
            if (! array_key_exists($fieldName, $data)) {
                continue;
            }

            $value = $data[$fieldName];

            if ($config->type === ConfigType::CHECKBOX->value) {
                // 过滤 null/空项，避免存出 [null] 这类坏值
                $items = is_array($value)
                    ? array_values(array_filter($value, fn ($v) => $v !== null && $v !== ''))
                    : [];
                $value = json_encode($items, JSON_UNESCAPED_UNICODE);
            }

            // 图片/文件：按配置的扩展名白名单校验提交路径，非法格式拒绝保存
            if (in_array($config->type, [ConfigType::IMAGE->value, ConfigType::FILE->value], true)) {
                try {
                    $value = $this->filterUploadValue($config, $value);
                } catch (\RuntimeException $e) {
                    $errors[] = $config->name . '：' . $e->getMessage();
                    continue;
                }
            }

            $config->update(['value' => $value]);
        }

        if ($errors) {
            return (new \Dcat\Admin\Http\JsonResponse())->error(implode('；', $errors));
        }

        return (new \Dcat\Admin\Http\JsonResponse())->success(__('sys-config-editor.messages.save_success'))->refresh();
    }

    /**
     * 根据配置类型渲染编辑页字段（字段名为 config_{id}）
     *
     * @param Form $form
     * @param SysConfigsModel $config
     * @param string $fieldName
     * @return void
     */
    private function renderEditorField(Form $form, SysConfigsModel $config, string $fieldName): void
    {
        $label = $config->name;
        $type = $config->type;
        $field = null;

        switch ($type) {
            case ConfigType::TEXT->value:
                $field = $form->text($fieldName, $label);
                break;
            case ConfigType::NUMBER->value:
                $field = admin_form_number_field($form->number($fieldName, $label), '0.01');
                break;
            case ConfigType::TEXTAREA->value:
                $field = $form->textarea($fieldName, $label)->rows(5);
                break;
            case ConfigType::EDITOR->value:
                $field = $form->richEditor($fieldName, $label);
                break;
            case ConfigType::RADIO->value:
                $field = $form->radio($fieldName, $label)->options($this->parseOptions($config->options));
                break;
            case ConfigType::CHECKBOX->value:
                $field = $form->checkbox($fieldName, $label)
                    ->options($this->parseOptions($config->options))
                    ->customFormat(function ($v) use ($config) {
                        return $config->value ? json_decode($config->value, true) : [];
                    });
                break;
            case ConfigType::SELECT->value:
                $field = $form->select($fieldName, $label)->options($this->parseOptions($config->options));
                break;
            case ConfigType::IMAGE->value:
                $imageExts = $this->parseExtensions($config->allowed_extensions, ConfigType::IMAGE->value);
                $field = $form->adminImage($fieldName, $label)
                    ->accept(implode(',', $imageExts), 'image/*');
                break;
            case ConfigType::FILE->value:
                $field = admin_form_file_field(
                    $form->file($fieldName, $label),
                    $this->parseExtensions($config->allowed_extensions, ConfigType::FILE->value)
                );
                break;
            case ConfigType::PASSWORD->value:
                $field = $form->password($fieldName, $label);
                break;
            case ConfigType::DATE->value:
                $field = $form->date($fieldName, $label);
                break;
            case ConfigType::TIME->value:
                $field = $form->time($fieldName, $label);
                break;
            case ConfigType::DATETIME->value:
                $field = $form->datetime($fieldName, $label);
                break;
        }

        // 仅当配置值非空时回填默认值。
        // 注意：Dcat 的 value(null) 是“读取”语义，会返回标量（Number 还会强转成 int），
        // 不能把其返回值赋给字段对象，否则 $field 变成 int，下面 ->help() 会报
        // “Call to a member function help() on int”。多选框回显走 customFormat，不走 value()。
        if ($field !== null && $type !== ConfigType::CHECKBOX->value && $config->value !== null && $config->value !== '') {
            $field->value($config->value);
        }

        if ($field !== null && $config->description) {
            $field->help($config->description);
        }
    }

    /**
     * 将 options 数组（[{"key":"显示文本","value":"提交值"}]）转为 [提交值=>显示文本] 关联数组。
     * 即页面展示 key，表单提交/保存 value。
     *
     * @param mixed $options
     * @return array<string, string>
     */
    private function parseOptions(mixed $options): array
    {
        if (! is_array($options) || empty($options)) {
            return [];
        }
        $result = [];
        foreach ($options as $item) {
            if (is_array($item) && isset($item['key'], $item['value'])) {
                $result[$item['value']] = $item['key'];
            }
        }
        return $result;
    }

    /**
     * 处理 autoUpload 独立上传 XHR：存盘并返回 Dcat webuploader 要求的成功格式，
     * 前端据此把相对路径写入隐藏域，主提交时再落库。
     *
     * @param UploadedFile $file 上传文件
     * @param mixed $column upload_column，形如 config_{id}
     * @return \Illuminate\Http\JsonResponse
     */
    private function handleAutoUpload(UploadedFile $file, mixed $column)
    {
        // 由 upload_column=config_{id} 解析对应配置项，取其白名单
        $config = null;
        if (is_string($column) && preg_match('/^config_(\d+)$/', $column, $m)) {
            $config = SysConfigsModel::find((int) $m[1]);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $allowed = $config
            ? $this->parseExtensions($config->allowed_extensions, $config->type)
            : [];

        if (! empty($allowed) && ! in_array($ext, $allowed, true)) {
            return response()->json([
                'status'  => false,
                'message' => '仅支持 ' . implode('/', $allowed) . ' 格式，当前为 ' . ($ext ?: '未知'),
            ]);
        }

        $dir = date('Ymd');
        $name = date('Ymd_His') . '_' . Str::random(8) . '.' . $ext;
        $path = $dir . '/' . $name;

        Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));

        return response()->json([
            'status' => true,
            'data'   => [
                'id'   => $path,
                'name' => $name,
                'path' => $name,
                'url'  => Storage::disk('public')->url($path),
            ],
        ]);
    }

    /**
     * 校验图片/文件提交路径的扩展名是否在配置白名单内；空值（清空）直接放行。
     *
     * @param SysConfigsModel $config 配置项
     * @param mixed $value 提交的文件路径
     * @return string|null 清洗后的路径，空值返回 null
     * @throws \RuntimeException 扩展名不在白名单时抛出
     */
    private function filterUploadValue(SysConfigsModel $config, mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : '';
        if ($value === '') {
            return null;
        }

        $allowed = $this->parseExtensions($config->allowed_extensions, $config->type);
        if (empty($allowed)) {
            // 未配置白名单即不限制
            return $value;
        }
        $path = (string) (parse_url($value, PHP_URL_PATH) ?: $value);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (! in_array($ext, $allowed, true)) {
            throw new \RuntimeException('仅支持 ' . implode('/', $allowed) . ' 格式，当前为 ' . ($ext ?: '未知'));
        }

        return $value;
    }

    /**
     * 将逗号分隔的扩展名字符串转为数组；为空时按类型返回默认白名单
     *
     * @param mixed $extensions
     * @param string|null $type 配置类型（ConfigType value）
     * @return array<int, string>
     */
    private function parseExtensions(mixed $extensions, ?string $type = null): array
    {
        $list = is_array($extensions)
            ? $extensions
            : ($extensions ? explode(',', (string) $extensions) : []);
        $list = array_values(array_filter(array_map('trim', $list)));

        // 填什么存什么：配置为空即不限制，不自动补默认扩展名
        return $list;
    }
}
