<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/13
 * Time: 14:09
 */

namespace App\Admin\Controllers;
use App\Admin\Actions\Order\ConfirmOrder;
use App\Admin\Actions\Order\OrderQr;
use App\Admin\Actions\Order\Reason;
use App\Admin\Actions\Order\TongZhi;
use App\Admin\Actions\Order\TongZhiAll;
use App\Models\Order;
use App\Http\Controllers\Controller;
use App\Tools\AppPath;
use App\Tools\Rsa;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use App\Tools\PayTypeCode;
use App\Tools\OrderCode;
use App\Tools\OrderType;
use Illuminate\Support\MessageBag;
use Encore\Admin\Widgets\Table;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
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
        $grid = new Grid(new Order);
        $grid->model()->orderBy('updated_at', 'desc');
        $grid->id('ID');
        $grid->money('金额');
        $grid->order_sn('订单号')->expand(function ($model) {
            $comments = $model->order_operation_log()->get()->map(function ($order_operation_log) {
                return $order_operation_log->only(['content','admin_id','created_at']);
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
        $grid->request_order_sn('外部订单号');

        $grid->goods_name('商品')->limit(10);
        $grid->shop_name('店铺');
        $grid->buyer_name('买家');
        $grid->pdd_order_sn('PDD订单号');
        $grid->pay_type('支付')->display(function ($pay_type){
            return PayTypeCode::$status[$pay_type];
        });
        $grid->api_status_str('状态');
        //$grid->type('类型')->display(function ($type){
            //return OrderType::$status[$type];
        //});
        $grid->pay_at('支付时间')->sortable();
        $grid->is_notice('通知')->display(function ($is_notice){
            $data = [
                '1'=>'否',
                '2'=>'是',
                '3'=>'失败',
            ];
            return $data[$is_notice];
        });
        $grid->notice_num('异步')->sortable();
        $grid->pay_url('支付')->display(function ($pay_url){
            return '<a target="_blank" href="'.$pay_url.'" class="label label-primary">支付</a>';
        });
        $grid->updated_at('Updated at')->sortable();
        $grid->disableExport();
        //$grid->disableRowSelector();
        $grid->disableColumnSelector();
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
            $actions->disableEdit();
            //$actions->append(new Reason());
        });
        $grid->filter(function($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->like('order_sn','订单号');
            $filter->like('request_order_sn','来源(订单号)');
            $filter->like('pdd_order_sn','PDD订单号');
        });
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new Reason());
            $tools->append(new TongZhi());
            $tools->append(new TongZhiAll());
            $tools->append(new ConfirmOrder());
        });
        $grid->disableActions();
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
        $show = new Show(Order::findOrFail($id));

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
        $form = new Form(new Order);
        $form->text('request_order_sn','订单号')->required();
        $form->currency('money','支付金额')->symbol('￥')->required();
        $form->radio('pay_type','支付方式')->options(PayTypeCode::$status)->default(1)->required();
        $form->radio('type','出码方式')->options(OrderType::$status)->default(2)->readonly()->required();
        $form->tools(function (Form\Tools $tools) {
            // 去掉`删除`按钮
            $tools->disableDelete();
            // 去掉`查看`按钮
            $tools->disableView();
            //去掉列表按钮
            $tools->disableList();
            // 添加一个按钮, 参数可以是字符串, 或者实现了Renderable或Htmlable接口的对象实例
            $tools->add('<div class="box-tools"><div class="btn-group pull-right" style="margin-right:5px"><a href="/admin/order" class="btn btn-sm btn-default" title="列表"><i class="fa fa-list"></i><span class="hidden-xs">&nbsp;订单列表</span></a></div></div>');
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
        /**
         * 保存前操作
         */
        $form->saving(function (Form $form){
            $data['inside'] = 100;
            $data['timestamp'] = time();
            $data['pay_type'] = $form->pay_type;
            $data['request_order_sn'] = $form->request_order_sn;
            $data['money'] = $form->money;
            $data['type'] = $form->type;
            $data['appid'] = 'W4dCuK3OfztXFTiq';
            $data['notify_url'] = 'http://www.baidu.com';
            $dispatcher = app('Dingo\Api\Dispatcher');
            $data['sign'] = Rsa::encode($data,AppPath::APP_RSA_PASS_PATH,AppPath::APP_RSA_PFX_PATH);
            $res = $dispatcher->json($data)->post('order/create');
            if($res['status_code'] != 200){
                $error = new MessageBag([
                    'title'   => '提示信息',
                    'message' => '添加失败',
                ]);
                return back()->with(compact('error'));
            }
            return redirect('/admin/order');
        });
        return $form;
    }
}