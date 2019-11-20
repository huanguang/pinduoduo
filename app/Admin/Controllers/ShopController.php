<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/12
 * Time: 15:42
 */

namespace App\Admin\Controllers;
use App\Models\Shop;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
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
        $grid = new Grid(new Shop);
        $grid->model()->orderBy('updated_at', 'desc');
        $grid->id('ID');
        $grid->shop_name('店铺名称')->expand(function ($model) {
            $comments = $model->shop_operation_log()->get()->map(function ($shop_operation_log) {
                return $shop_operation_log->only(['content','admin_id','created_at']);
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
        $grid->shop_id('店铺ID');
        $grid->salesTip('销量');
        $grid->today_money('今日收入');
        $grid->money('总收入');
        $grid->max_money('账号限额')->editable();;
        $grid->today_max_money('每日限额')->editable();;
        $grid->url('店铺地址')->display(function ($url){
            $url =  'http://mobile.yangkeduo.com/'.$url;
            return '<a target="_blank" href="'.$url.'" class="label label-primary">查看</a>';
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
        });
        $grid->filter(function($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->like('shop_name','店铺名称');
        });
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new \App\Admin\Actions\Shop\ShopStatus());
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
        $form = new Form(new Shop);
        $form->text('shop_name','店铺名称');
        $form->text('shop_id','店铺ID');
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