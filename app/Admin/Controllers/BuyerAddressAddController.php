<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/13
 * Time: 17:25
 */

namespace App\Admin\Controllers;
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
class BuyerAddressAddController extends Controller
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

        $grid->id('ID');
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
        $show = new Show(BuyerAddress::findOrFail($id));

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
        $form = new Form(new BuyerAddress);
        $form->textarea('url','地址')->help('格式：PDD会员id-收货地址id-省份id-城市id-县区id-详细地址-收货人-收货手机号码-是否默认（0，不是，1，是）。支持批量添加，多条用“;”隔开');
        $form->saving(function (Form $form) {
            $data = trimall($form->url);
            if(strpos($data,';') !== false){
                //多条
                $list = explode(';',$data);
                if(!empty($list)){
                    $newData = [];
                    foreach ($list as $k=>$v){
                        $info = explode('-',$v);
                        if(count($info) == 9) {
                            $newData[] = [
                                'user_id' => $info[0],
                                'address_id' => $info[1],
                                'district_id' => $info[2],
                                'city_id' => $info[3],
                                'province_id' => $info[4],
                                'address' => $info[5],
                                'name' => $info[6],
                                'mobile' => $info[7],
                                'is_default' => $info[8],
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            ];
                        }
                    }
                    if(!empty($newData)){
                        $res = DB::table('buyer_address')->insert($newData);
                        if(!$res){
                            if(!$res){
                                //判断店铺在不在，不在的话添加店铺
                                $error = new MessageBag([
                                    'title'   => '提示信息',
                                    'message' => '添加失败',
                                ]);
                                return back()->with(compact('error'));
                            }
                        }
                    }
                }
            }else{
                $info = explode('-',$data);
                if(count($info) == 9){
                    $newData[] = [
                        'user_id'=>$info[0],
                        'address_id'=>$info[1],
                        'district_id'=>$info[2],
                        'city_id'=>$info[3],
                        'province_id'=>$info[4],
                        'address'=>$info[5],
                        'name'=>$info[6],
                        'mobile'=>$info[7],
                        'is_default'=>$info[8],
                        'created_at'=>date('Y-m-d H:i:s'),
                        'updated_at'=>date('Y-m-d H:i:s'),
                    ];
                    if(!empty($newData)){
                        $res = DB::table('buyer_address')->insert($newData);
                        if(!$res){
                            if(!$res){
                                //判断店铺在不在，不在的话添加店铺
                                $error = new MessageBag([
                                    'title'   => '提示信息',
                                    'message' => '添加失败',
                                ]);
                                return back()->with(compact('error'));
                            }
                        }
                    }
                }
            }
            return redirect('/admin/address');
        });
        //保存后回调
        $form->tools(function (Form\Tools $tools) {
            // 去掉`列表`按钮
            $tools->disableList();
            // 去掉`删除`按钮
            $tools->disableDelete();
            // 去掉`查看`按钮
            $tools->disableView();
            // 添加一个按钮, 参数可以是字符串, 或者实现了Renderable或Htmlable接口的对象实例
            $tools->add('<div class="box-tools"><div class="btn-group pull-right" style="margin-right:5px"><a href="/admin/address" class="btn btn-sm btn-default" title="列表"><i class="fa fa-list"></i><span class="hidden-xs">&nbsp;列表</span></a></div></div>');
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