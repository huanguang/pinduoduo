<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/12
 * Time: 20:02
 */

namespace App\Admin\Controllers;
use App\Admin\Actions\Goods\GoodsStatus;
use App\Admin\Actions\Goods\GoodsStatusXia;
use App\Models\Goodss;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use GuzzleHttp\Client;
use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use App\Models\GoodsAdd;
use Encore\Admin\Widgets\Table;

class GoodssController extends Controller
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
        $grid = new Grid(new Goodss);
        $grid->model()->orderBy('updated_at', 'desc');
        $grid->id('ID');
        $grid->goods_id('商品ID');
        $grid->goods_name('商品名称')->expand(function ($model) {
            $comments = $model->goods_sku()->get()->map(function ($wisdom_still_order_plan) {
                return $wisdom_still_order_plan->only(['shop_id','goods_id','sku_id', 'group_id', 'goods_name', 'specs_value', 'url','price','img','add_id','created_at','updated_at']);
            });
            $comments = $comments->toArray();
            if(!empty($comments)){
                $arr = [];
                foreach ($comments as $key=> $value){
                    $data['shop_id'] = $value['shop_id'];
                    $data['sku_id'] = $value['sku_id'];
                    $data['group_id'] = $value['group_id'];
                    $data['goods_name'] = $value['goods_name'];
                    $data['specs_value'] = $value['specs_value'];
                    $data['price'] = $value['price'];
                    $data['img'] = "<a href='{$value['img']}' target='_blank'><img src='{$value['img']}' width='50' height='50'></a>";
                    $data['add_id'] = $value['add_id'];
                    $data['updated_at'] = $value['updated_at'];
                    $arr[] = $data;
                }
                $comments = $arr;
            }
            return new Table(['店铺id','商品skuid','group_id','商品名称','规格','价格','sku图片','添加id','更新时间'], $comments);
        })->sortable();
        $grid->img('商品图片')->lightbox(['width' => 50, 'height' => 50]);
        $grid->sales_volume('销量');
        $grid->url('url')->display(function ($item){
            return '<a target="_blank" href="'.$item.'" class="label label-primary">查看</a>';
        });;
        $grid->is_enable('状态')->switch([
            'on'  => ['value' => 1, 'text' => '正常', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => '禁止', 'color' => 'default'],
        ]);
        $grid->created_at('Created at')->sortable();
        $grid->updated_at('Updated at')->sortable();
        $grid->disableExport();
        //$grid->disableRowSelector();
        $grid->disableColumnSelector();
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableView();
        });
        $grid->filter(function($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->like('goods_name','商品名称');
        });
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new GoodsStatus());
            $tools->append(new GoodsStatusXia());
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
        $show = new Show(Goodss::findOrFail($id));

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
        $form = new Form(new Goodss);
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