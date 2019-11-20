<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/13
 * Time: 11:32
 */

namespace App\Admin\Controllers;
use App\Admin\Actions\Buyer\BuyerAddressAdd;
use App\Admin\Actions\Buyer\BuyerStatus;
use App\Models\Buyer;
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
use Encore\Admin\Widgets\Table;

class BuyerController extends Controller
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
        $grid = new Grid(new Buyer);
        $grid->model()->orderBy('updated_at', 'desc');
        $grid->id('ID');
        $grid->user_id('拼多多ID')->expand(function ($model) {
            $comments = $model->buyer_operation_log()->get()->map(function ($buyer_operation_log) {
                return $buyer_operation_log->only(['content','admin_id','created_at']);
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
        $grid->mobile('手机号码');
        $grid->type('买家类型');
        $grid->money('消费总金额');
        $grid->today_money('今日消费');
        $grid->today_max_money('每日限额')->editable();
        $grid->max_money('账号限额')->editable();
        $grid->is_enable('状态')->switch([
            'on'  => ['value' => 1, 'text' => '正常', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => '禁止', 'color' => 'default'],
        ]);
        $grid->created_at('Created at')->sortable();
        $grid->updated_at('Updated at')->sortable();
        $grid->disableExport();
        $grid->disableCreateButton();
        //$grid->disableRowSelector();
        $grid->disableColumnSelector();
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->add(new BuyerAddressAdd);
        });
        $grid->filter(function($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->like('mobile','手机号码');
            $filter->like('user_id','拼多多ID');
        });
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new BuyerStatus());
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
        $form = new Form(new Buyer);
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
        $form->currency('max_money','账号限额')->symbol('￥');
        $form->currency('today_max_money','每日限额')->symbol('￥');
        $form->switch('is_enable','状态')->options([
            'on'  => ['value' => 1, 'text' => '正常', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => '禁止', 'color' => 'default'],
        ]);
        return $form;
    }

}