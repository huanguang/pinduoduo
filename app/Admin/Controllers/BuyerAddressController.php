<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/13
 * Time: 16:03
 */

namespace App\Admin\Controllers;
use App\Admin\Actions\Buyer\BuyerAddressAdd;
use App\Models\BuyerAddress;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use GuzzleHttp\Client;
use App\Models\Goodss;
use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;

class BuyerAddressController
{
    use HasResourceActions,Helpers;

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
        $grid = new Grid(new BuyerAddress);
        $grid->model()->orderBy('updated_at', 'desc');
        $grid->id('ID');
        $grid->user_id('拼多多ID');
        $grid->name('收货人');
        $grid->mobile('手机号码');
        $grid->district_name('省份');
        $grid->city_name('城市');
        $grid->province_name('县区');
        $grid->address('详细地址');
        $grid->is_default('是否默认')->display(function ($is_default){
            return $is_default == 1 ? '是' : '否';
        });
        $grid->created_at('Created at')->sortable();
        $grid->updated_at('Updated at')->sortable();
        $grid->disableExport();
        $grid->disableRowSelector();
        $grid->disableColumnSelector();
        $grid->actions(function ($actions) {
            $actions->disableView();
            // 获取当前行主键值
            $actions->getKey();
        });
        $grid->filter(function($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->like('mobile','手机号码');
            $filter->like('user_id','拼多多ID');
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
        $show = new Show(Buyer::findOrFail($id));

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
        $form = new Form(new BuyerAddress);
        $form->tools(function (Form\Tools $tools) {
            // 去掉`列表`按钮
            //$tools->disableList();
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
        $form->text('district_name','省份名称');
        $form->text('city_name','城市名称');
        $form->text('province_name','县区名称');
        $form->text('address','详细地址');
        $form->text('name','收货人姓名');
        $form->text('mobile','收货手机号码');
        $form->radio('is_default','默认')->options(['0' => '否', '1'=> '是'])->default('0');
        $form->saved(function (Form $form) {
            $id = $form->model()->id;

        });
        return $form;
    }
}