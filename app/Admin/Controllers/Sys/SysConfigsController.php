<?php

namespace App\Admin\Controllers\Sys;

use App\Admin\Repositories\Sys\SysConfigs;
use App\Admin\Actions\Sys\FillConfigValue;
use App\Models\Sys\SysConfigCategories;
use App\Models\Sys\SysConfigs as SysConfigsModel;
use App\Enums\ConfigType;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\Displayers\Actions;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class SysConfigsController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(new SysConfigs(), function (Grid $grid) {
            $grid->column('id', admin_trans_field('id'))->sortable();
            $grid->column('category_id', admin_trans_field('category_id'))->display(function ($v) {
                static $names = null;
                if ($names === null) {
                    $names = SysConfigCategories::query()->pluck('name', 'id');
                }
                return $names[$v] ?? $v;
            });
            $grid->column('key', admin_trans_field('key'));
            $grid->column('name', admin_trans_field('name'));
            $grid->column('type', admin_trans_field('type'))->using(ConfigType::options());
            $grid->column('is_editable', admin_trans_field('is_editable'))->using(__('sys-configs.enums.is_editable'))->label([0 => 'default', 1 => 'success']);
            $grid->column('sort', admin_trans_field('sort'));

            $grid->tools(function (Grid\Tools $tools) {
                // 配置分类管理弹框
                $tools->append(admin_dialog_frame_button(
                    admin_base_path('sys/config-categories'),
                    __('sys-configs.buttons.category_manage')
                ));
            });

            $grid->actions(function (Actions $actions) {
                $actions->append(new FillConfigValue());
            });

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->equal('category_id', admin_trans_field('category_id'))->select(SysConfigCategories::query()->orderBy('sort')->pluck('name', 'id'));
                $filter->like('key', admin_trans_field('key'));
                $filter->like('name', admin_trans_field('name'));
                $filter->equal('type', admin_trans_field('type'))->select(ConfigType::options());
                $filter->equal('is_editable', admin_trans_field('is_editable'))->select(__('sys-configs.enums.is_editable'));
            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id): Show
    {
        return Show::make($id, new SysConfigs(), function (Show $show) {
            $show->field('id', admin_trans_field('id'));
            $show->field('category_id', admin_trans_field('category_id'))->display(function ($v) {
                static $names = null;
                if ($names === null) {
                    $names = SysConfigCategories::query()->pluck('name', 'id');
                }
                return $names[$v] ?? $v;
            });
            $show->field('key', admin_trans_field('key'));
            $show->field('name', admin_trans_field('name'));
            $show->field('type', admin_trans_field('type'))->using(ConfigType::options());
            $show->field('options', admin_trans_field('options'))->display(function ($v) {
                return $v ? json_encode($v, JSON_UNESCAPED_UNICODE) : '';
            });
            $show->field('allowed_extensions', admin_trans_field('allowed_extensions'));
            $show->field('value', admin_trans_field('value'));
            $show->field('is_editable', admin_trans_field('is_editable'))->using(__('sys-configs.enums.is_editable'));
            $show->field('description', admin_trans_field('description'));
            $show->field('sort', admin_trans_field('sort'));
            $show->field('created_at', admin_trans_field('created_at'));
            $show->field('updated_at', admin_trans_field('updated_at'));
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        return Form::make(new SysConfigs(), function (Form $form) {
            $form->display('id');
            $form->select('category_id', admin_trans_field('category_id'))->options(SysConfigCategories::query()->orderBy('sort')->pluck('name', 'id'))->required();
            $form->text('key', admin_trans_field('key'))->required()->rules(function ($form) {
                $id = $form->model()->id ?? 'NULL';
                return "required|unique:sys_configs,key,$id,id";
            });
            $form->text('name', admin_trans_field('name'))->required();

            // when 必须调用在触发字段 type 上
            $form->select('type', admin_trans_field('type'))
                ->options(ConfigType::options())
                ->required()
                ->default('text')
                ->when('in', ['radio', 'checkbox', 'select'], function (Form $form) {
                    $form->table('options', admin_trans_field('options'), function ($table) {
                        $table->text('key', '键');
                        $table->text('value', '值');
                    });
                })
                ->when('file', function (Form $form) {
                    $form->tags('allowed_extensions', admin_trans_field('allowed_extensions'))
                        ->saving(function ($value) {
                            // 提交：tags 数组 → 逗号分隔字符串
                            if (is_array($value)) {
                                return implode(',', array_filter(array_map('trim', $value)));
                            }
                            return $value;
                        })
                        ->customFormat(function ($value) {
                            // 编辑回显：数据库字符串 → tags 数组
                            if (is_array($value)) {
                                return $value;
                            }
                            return $value ? array_filter(array_map('trim', explode(',', (string) $value))) : [];
                        });
                });

            $form->radio('is_editable', admin_trans_field('is_editable'))->options(__('sys-configs.enums.is_editable'))->default(1);
            $form->textarea('description', admin_trans_field('description'))->rows(3);
            admin_form_number_field($form->number('sort', admin_trans_field('sort')), '1')->default(0);
        });
    }

    /**
     * 填写配置值（弹框页面）
     *
     * @param int|string $id
     * @param Content $content
     * @return Content
     */
    public function fillValue($id, Content $content): Content
    {
        $form = Form::make(new SysConfigs(), function (Form $form) use ($id) {
            $config = SysConfigsModel::findOrFail($id);

            // 手动设置 PUT 方法
            $form->hidden('_method')->value('PUT');

            // action 用不带前缀的相对路径，内部自动拼接 admin 前缀
            $form->action('sys/configs/' . $id . '/fill-value');

            $form->display('name', admin_trans_field('name'))->value($config->name);
            $form->display('key', admin_trans_field('key'))->value($config->key);
            $this->renderValueField($form, $config);
        });

        return $content->title(__('sys-configs.buttons.fill_value'))->body($form);
    }

    /**
     * 处理填写值表单的 PUT 提交
     *
     * @param int|string $id
     * @return mixed
     */
    public function saveFillValue($id): mixed
    {
        $config = SysConfigsModel::findOrFail($id);
        $value = request('value');

        if ($config->type === ConfigType::CHECKBOX->value) {
            $value = is_array($value) ? json_encode($value) : ($value ?: '[]');
        }

        $config->update(['value' => $value]);

        return (new \Dcat\Admin\Http\JsonResponse())->success(__('admin.save_succeeded'))->refresh();
    }

    /**
     * 根据配置类型动态渲染 value 字段
     *
     * @param Form $form
     * @param SysConfigsModel $config
     * @return void
     */
    private function renderValueField(Form $form, SysConfigsModel $config): void
    {
        $label = admin_trans_field('value');
        $type = $config->type;
        $value = $config->value;

        switch ($type) {
            case ConfigType::TEXT->value:
                $form->text('value', $label)->value($value);
                break;
            case ConfigType::NUMBER->value:
                admin_form_number_field($form->number('value', $label), '0.01')->value($value);
                break;
            case ConfigType::TEXTAREA->value:
                $form->textarea('value', $label)->rows(5)->value($value);
                break;
            case ConfigType::EDITOR->value:
                $form->richEditor('value', $label)->value($value);
                break;
            case ConfigType::RADIO->value:
                $form->radio('value', $label)->options($this->parseOptions($config->options))->value($value);
                break;
            case ConfigType::CHECKBOX->value:
                $form->checkbox('value', $label)
                    ->options($this->parseOptions($config->options))
                    ->value($value)
                    ->customFormat(function ($v) {
                        return $v ? json_decode($v, true) : [];
                    });
                break;
            case ConfigType::SELECT->value:
                $form->select('value', $label)->options($this->parseOptions($config->options))->value($value);
                break;
            case ConfigType::IMAGE->value:
                $form->adminImage('value', $label)->value($value);
                break;
            case ConfigType::FILE->value:
                admin_form_file_field($form->file('value', $label), $this->parseExtensions($config->allowed_extensions))->value($value);
                break;
            case ConfigType::PASSWORD->value:
                $form->password('value', $label)->value($value);
                break;
            case ConfigType::DATE->value:
                $form->date('value', $label)->value($value);
                break;
            case ConfigType::TIME->value:
                $form->time('value', $label)->value($value);
                break;
            case ConfigType::DATETIME->value:
                $form->datetime('value', $label)->value($value);
                break;
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
