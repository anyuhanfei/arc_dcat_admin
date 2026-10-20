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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SysConfigsController extends AdminController
{
    /**
     * 图片上传默认允许的格式白名单（小写扩展名）
     */
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];

    /**
     * 文件上传默认允许的格式白名单（小写扩展名）
     */
    private const FILE_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar'];

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(new SysConfigs(), function (Grid $grid) {
            // 新增/编辑保持全局弹窗模式（enableDialogCreate + DialogEdit），仅工具栏入口做跳转。
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
                // 返回配置项填写（整页跳转到配置编辑页）
                $tools->append(
                    '<a href="'.admin_base_path('sys/config-editor').'" class="btn btn-default">'
                    .'<i class="feather icon-edit"></i> '.__('sys-configs.buttons.back_to_fill')
                    .'</a>'
                );
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
                ->when(['radio', 'checkbox', 'select'], function (Form $form) {
                    $form->table('options', admin_trans_field('options'), function ($table) {
                        $table->text('key', '键');
                        $table->text('value', '值');
                    });
                })
                ->when(['image', 'file'], function (Form $form) {
                    // 允许扩展名：纯关键词自由填写，以实际提交为准，系统不自动补充；
                    // 仅新增页预填图片常见格式作为便利，编辑页用数据库原值，删除即清空、不回补
                    $form->tags('allowed_extensions', admin_trans_field('allowed_extensions'))
                        ->default(self::IMAGE_EXTENSIONS)
                        ->help('输入允许的扩展名（不带点，如 jpg、png），空格/逗号分隔，可自由填写；以实际提交为准，系统不自动补充。')
                        ->saving(function ($value) {
                            $type = request('type');
                            // 非图片/文件类型不保存白名单
                            if (! in_array($type, [ConfigType::IMAGE->value, ConfigType::FILE->value], true)) {
                                return null;
                            }
                            // 纯关键词清洗：trim、小写、去空、去重，不做候选白名单限制，也不自动补默认
                            $raw = is_array($value)
                                ? $value
                                : preg_split('/[,;，；\s]+/', (string) $value);
                            $list = array_values(array_unique(array_filter(
                                array_map(fn ($x) => strtolower(trim((string) $x)), $raw)
                            )));
                            return $list ? implode(',', $list) : null;
                        })
                        ->customFormat(function ($value) {
                            // 编辑回显：数据库逗号字符串 → tags 数组，纯按库值回显，不叠加默认
                            if (is_array($value)) {
                                return $value;
                            }
                            return $value ? array_values(array_filter(array_map('trim', explode(',', (string) $value)))) : [];
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

        // autoUpload 独立上传 XHR：先存盘并返回 Dcat 上传成功格式，主提交再落库
        if ($file = request()->file('_file_')) {
            return $this->handleAutoUpload($file, $config);
        }

        $value = request('value');

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
                return (new \Dcat\Admin\Http\JsonResponse())->error($e->getMessage());
            }
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
                $imageExts = $this->parseExtensions($config->allowed_extensions, ConfigType::IMAGE->value);
                $form->adminImage('value', $label)
                    ->accept(implode(',', $imageExts), 'image/*')
                    ->value($value);
                break;
            case ConfigType::FILE->value:
                admin_form_file_field(
                    $form->file('value', $label),
                    $this->parseExtensions($config->allowed_extensions, ConfigType::FILE->value)
                )->value($value);
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
     * 处理 autoUpload 独立上传 XHR：按配置白名单校验后存盘，返回 Dcat webuploader
     * 要求的成功格式，前端据此把相对路径写入隐藏域，主提交时再落库。
     *
     * @param UploadedFile $file 上传文件
     * @param SysConfigsModel $config 配置项
     * @return \Illuminate\Http\JsonResponse
     */
    private function handleAutoUpload(UploadedFile $file, SysConfigsModel $config)
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $allowed = $this->parseExtensions($config->allowed_extensions, $config->type);

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
     * @param string|null $type 配置类型（ConfigType value），用于决定空值时的默认白名单
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
