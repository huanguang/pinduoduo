<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/14
 * Time: 17:05
 */

namespace App\Admin\Controllers;
use App\Admin\Actions\Buyer\Download;
use App\Admin\Actions\Buyer\Generate;
use App\Admin\Actions\Merchant\MerchantStatus;
use App\Models\Shop;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use App\Models\Merchant;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Encore\Admin\Widgets\Table;

class MerchantController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Merchant);
        $grid->model()->orderBy('updated_at', 'desc');
        $grid->id('ID');
        $grid->name('商户姓名')->expand(function ($model) {
            $comments = $model->merchant_operation_log()->get()->map(function ($merchant_operation_log) {
                return $merchant_operation_log->only(['content','admin_id','created_at']);
            });
            $comments = $comments->toArray();
            if(!empty($comments)){
                $arr = [];
                foreach ($comments as $key=> $value){
                    $data['content'] = $value['content'];
                    $data['admin_id'] = DB::table('admin_users')->where('id',$value['admin_id'])->select('name')->first()->name;
                    $data['created_at'] = $value['created_at'];
                    $arr[] = $data;
                }
                $comments = $arr;
            }
            return new Table(['操作内容','操作人','操作时间'], $comments);
        })->sortable();
        $grid->merchant('商户号');
        $grid->appid('appId');
        $grid->secret_key('secretKey');
        $grid->is_key('是否生成密钥')->display(function ($is_key){
            return $is_key == 1 ? '未生成' : '已生成';
        });
        $grid->key_url('公钥')->display(function ($key_url){
            return $key_url == 1 ? '未生成' : '已生成';
        });
        $grid->is_enable('状态')->switch([
            'on'  => ['value' => 1, 'text' => '正常', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => '禁止', 'color' => 'default'],
        ]);
        $grid->created_at('Created at')->sortable();
        $grid->updated_at('Updated at')->sortable();
        $grid->disableExport();
        //$grid->disableRowSelector();
        $grid->disableColumnSelector();
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->add(new Generate());
            $actions->add(new Download());
        });
        $grid->filter(function($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->like('name','商户姓名');
            $filter->like('merchant','商户号');
        });
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new MerchantStatus());
        });
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Shop::findOrFail($id));

        $show->id('ID');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Merchant);
        $form->text('name','商户姓名');
        $form->text('merchant','商户号');
        $form->text('appid','APPID')->default(Str::random(16));
        $form->switch('is_enable','状态')->options([
            'on'  => ['value' => 1, 'text' => '正常', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => '禁止', 'color' => 'default'],
        ]);
        $form->tools(function (Form\Tools $tools) {
            // 去掉`删除`按钮
            $tools->disableDelete();
            // 去掉`查看`按钮
            $tools->disableView();
        });
        $form->footer(function ($footer) {

            // 去掉`重置`按钮
            //$footer->disableReset();

            // 去掉`提交`按钮
            //$footer->disableSubmit();

            // 去掉`查看`checkbox
            $footer->disableViewCheck();

            // 去掉`继续编辑`checkbox
            $footer->disableEditingCheck();

            // 去掉`继续创建`checkbox
            $footer->disableCreatingCheck();

        });
        return $form;
    }
}