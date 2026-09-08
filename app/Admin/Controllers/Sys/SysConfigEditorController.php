<?php

namespace App\Admin\Controllers\Sys;

use App\Admin\Repositories\Sys\SysConfigs;
use App\Models\Sys\SysConfigs as SysConfigsModel;
use App\Models\Sys\SysConfigCategories;
use App\Enums\ConfigType;
use Dcat\Admin\Form;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Http\Controllers\AdminController;

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

            // 右上角工具区：配置项管理弹框按钮
            $form->tools(function (\Dcat\Admin\Form\Tools $tools) {
                $tools->append(admin_dialog_frame_button(
                    admin_base_path('sys/configs'),
                    __('sys-config-editor.buttons.config_manage')
                ));
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
        $data = request()->all();

        $configs = SysConfigsModel::query()->where('is_editable', 1)->get();

        foreach ($configs as $config) {
            $fieldName = 'config_' . $config->id;
            if (! array_key_exists($fieldName, $data)) {
                continue;
            }

            $value = $data[$fieldName];

            if ($config->type === ConfigType::CHECKBOX->value) {
                $value = is_array($value) ? json_encode($value) : ($value ?: '[]');
            }

            $config->update(['value' => $value]);
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
                $field = $form->text($fieldName, $label)->value($config->value);
                break;
            case ConfigType::NUMBER->value:
                $field = admin_form_number_field($form->number($fieldName, $label), '0.01')->value($config->value);
                break;
            case ConfigType::TEXTAREA->value:
                $field = $form->textarea($fieldName, $label)->rows(5)->value($config->value);
                break;
            case ConfigType::EDITOR->value:
                $field = $form->ueditor($fieldName, $label)->value($config->value);
                break;
            case ConfigType::RADIO->value:
                $field = $form->radio($fieldName, $label)->options($this->parseOptions($config->options))->value($config->value);
                break;
            case ConfigType::CHECKBOX->value:
                $field = $form->checkbox($fieldName, $label)
                    ->options($this->parseOptions($config->options))
                    ->customFormat(function ($v) use ($config) {
                        return $config->value ? json_decode($config->value, true) : [];
                    });
                break;
            case ConfigType::SELECT->value:
                $field = $form->select($fieldName, $label)->options($this->parseOptions($config->options))->value($config->value);
                break;
            case ConfigType::IMAGE->value:
                $field = admin_form_media_selector_field($form->mediaSelector($fieldName, $label))->value($config->value);
                break;
            case ConfigType::FILE->value:
                $field = admin_form_file_field($form->file($fieldName, $label), $this->parseExtensions($config->allowed_extensions))->value($config->value);
                break;
            case ConfigType::PASSWORD->value:
                $field = $form->password($fieldName, $label)->value($config->value);
                break;
            case ConfigType::DATE->value:
                $field = $form->date($fieldName, $label)->value($config->value);
                break;
            case ConfigType::TIME->value:
                $field = $form->time($fieldName, $label)->value($config->value);
                break;
            case ConfigType::DATETIME->value:
                $field = $form->datetime($fieldName, $label)->value($config->value);
                break;
        }

        if ($field !== null && $config->description) {
            $field->help($config->description);
        }
    }

    /**
     * 将 options 数组（[{"key":"1","value":"选项1"}]）转为 ['1'=>'选项1'] 关联数组
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
                $result[$item['key']] = $item['value'];
            }
        }
        return $result;
    }

    /**
     * 将逗号分隔的扩展名字符串转为数组；为空时返回默认扩展名列表
     *
     * @param mixed $extensions
     * @return array<int, string>
     */
    private function parseExtensions(mixed $extensions): array
    {
        if (empty($extensions)) {
            return ['jpg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip'];
        }
        if (is_array($extensions)) {
            return array_values(array_filter(array_map('trim', $extensions)));
        }
        return array_values(array_filter(array_map('trim', explode(',', (string) $extensions))));
    }
}
