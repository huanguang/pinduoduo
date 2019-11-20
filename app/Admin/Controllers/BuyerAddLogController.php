<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/13
 * Time: 11:47
 */

namespace App\Admin\Controllers;
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
use App\Models\BuyerAddLog;

class BuyerAddLogController extends Controller
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
        $grid = new Grid(new BuyerAddLog);

        $grid->id('ID');
        $grid->user_id('拼多多ID');
        $grid->mobile('手机号码');
        $grid->type('买家类型');
        $grid->money('消费总金额');
        $grid->today_money('今日消费');
        $grid->today_max_money('每日限额');
        $grid->max_money('账号限额');
        $grid->created_at('Created at');
        $grid->updated_at('Updated at');
        $grid->disableExport();
        $grid->disableRowSelector();
        $grid->disableColumnSelector();
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
        $show = new Show(BuyerAddLog::findOrFail($id));

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
        set_time_limit(0);
        $form = new Form(new BuyerAddLog);
        $form->textarea('content','买家账号信息')->help('格式：拼多多ID-access_token-手机号码-每日限额-账号限额。支持批量添加，多条用“;”隔开');
        $form->saving(function (Form $form) {
        });
        //保存后回调
        $form->saved(function (Form $form) {
            $ip = request()->getClientIp();
            $id = $form->model()->id;
            $newData = [];
            $form->model()->content = trimall($form->model()->content);
            if(strpos($form->model()->content,';') !== false){
                //又多条信息
                $list = explode(';',$form->model()->content);
                if(!empty($list)){
                    $newData = [];
                    foreach ($list as $k=>$v){
                        $info = explode('-',$v);
                        //判断买家在不在

                        if(!empty($info)){
                            $b = DB::table('buyer')->where('user_id',$info[0])->first();
                            if(!$b){
                                $newData[] = [
                                    'add_id'=>$id,
                                    'user_id'=>$info[0],
                                    'access_token'=>$info[1],
                                    'mobile'=>$info[2],
                                    'type'=>5,
                                    'today_max_money'=>$info[3],
                                    'max_money'=>$info[4],
                                    'created_at'=>date('Y-m-d H:i:s'),
                                    'updated_at'=>date('Y-m-d H:i:s'),
                                    'ip'=>$ip,
                                ];
                            }
                        }
                    }
                    if(!empty($newData)){
                        $res = DB::table('buyer')->insert($newData);
                        if(!$res){
                            $error = new MessageBag([
                                'title'   => '提示信息',
                                'message' => '添加失败',
                            ]);
                            return back()->with(compact('error'));
                        }
                    }
                }
            }else{
                //只有一条信息
                $info = explode('-',$form->model()->content);
                if(!empty($info)){
                    $b = DB::table('buyer')->where('user_id',$info[0])->first();
                    if(!$b){
                        $newData[] = [
                            'add_id'=>$id,
                            'user_id'=>$info[0],
                            'access_token'=>$info[1],
                            'mobile'=>$info[2],
                            'type'=>5,
                            'today_max_money'=>$info[3],
                            'max_money'=>$info[4],
                            'created_at'=>date('Y-m-d H:i:s'),
                            'updated_at'=>date('Y-m-d H:i:s'),
                            'ip'=>$ip,
                        ];
                    }

                    if(!empty($newData)){
                        $res = DB::table('buyer')->insert($newData);
                        if(!$res){
                            $error = new MessageBag([
                                'title'   => '提示信息',
                                'message' => '添加失败',
                            ]);
                            return back()->with(compact('error'));
                        }
                    }
                }
            }
            return redirect('/admin/buyer');
        });
        $form->tools(function (Form\Tools $tools) {
            // 去掉`列表`按钮
            $tools->disableList();
            // 去掉`删除`按钮
            $tools->disableDelete();
            // 去掉`查看`按钮
            $tools->disableView();
            // 添加一个按钮, 参数可以是字符串, 或者实现了Renderable或Htmlable接口的对象实例
            $tools->add('<div class="box-tools"><div class="btn-group pull-right" style="margin-right:5px"><a href="/admin/buyer" class="btn btn-sm btn-default" title="列表"><i class="fa fa-list"></i><span class="hidden-xs">&nbsp;列表</span></a></div></div>');
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