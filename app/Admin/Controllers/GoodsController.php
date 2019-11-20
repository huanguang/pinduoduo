<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/12
 * Time: 15:41
 */

namespace App\Admin\Controllers;
use App\Models\Goods;
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
use App\Models\GoodsAdd;
use App\Tools\AppPath;
use App\Tools\Rsa;

class GoodsController extends Controller
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
        $grid = new Grid(new GoodsAdd);

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
        $show = new Show(GoodsAdd::findOrFail($id));

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
        $form = new Form(new GoodsAdd);
        $form->textarea('url','商品Url')->help('支持批量添加，多条用“-”隔开');
        $form->saving(function (Form $form) {
        });
        //保存后回调
        $form->saved(function (Form $form) {
            $id = $form->model()->id;
            $newData = [];
            $form->model()->url = trimall($form->model()->url);
            if(strpos($form->model()->url,'-') !== false){
                $arr = explode('-',$form->url);
                if(!empty($arr)){
                    foreach ($arr as $key=>$value){
                        if($value){
                            //内部调用获取商品详情接口
                            $data = [];
                            $data['inside'] = 100;
                            $data['timestamp'] = time();
                            $data['url'] = $value;
                            $data['sign'] = Rsa::encode($data,AppPath::APP_RSA_PASS_PATH,AppPath::APP_RSA_PFX_PATH);
                            $res = $this->api->json($data)->post('goods/details');
                            if($res['status_code'] == 200){
                                //判断店铺是否存在，不存在添加
                                $mall = $res['data']['content']['mall'];
                                $shop = DB::table('shop')->where('shop_id',$mall['mallID'])->select('shop_id')->first();
                                if(!$shop){
                                    DB::table('shop')->insert(['shop_name'=>$mall['mallName'],'shop_id'=>$mall['mallID'],'salesTip'=>$mall['salesTip'],'url'=>$mall['pddRoute'],'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
                                }
                                $res['data']['content'] = $res['data']['content']['goods'];
                                //插入商品，判断当前商品是否存在后台，不存在添加
                                $info = DB::table('goodss')->where('goods_id',$res['data']['content']['goodsID'])->select('goods_id')->first();
                                if(!$info){
                                    $goodsData = [
                                        'created_at'=>date('Y-m-d H:i:s'),
                                        'updated_at'=>date('Y-m-d H:i:s'),
                                        'url'=>$value,
                                        'sales_volume'=>$res['data']['content']['sideSalesTip'],
                                        'img'=>$res['data']['content']['thumbUrl'],
                                        'add_id'=>$id,
                                        'json_data'=>json_encode($res['data']['content']),
                                        'goods_name'=>$res['data']['content']['goodsName'],
                                        'goods_id'=>$res['data']['content']['goodsID'],
                                    ];
                                    $goodsDataId = Goodss::insertGetId($goodsData);
                                    $newData = [];
                                    if($res['data']['content']['skus']){
                                        foreach ($res['data']['content']['skus'] as $k=>$v){
                                            //处理规格
                                            $specsStr = '';
                                            $spec = $v['specs'];
                                            foreach ($spec as $kkk=>$vvv){
                                                $specsStr.= "{$vvv['spec_key']}:{$vvv['spec_value']},";
                                            }
                                            $newData[] = [
                                                'url'=>$value,
                                                'shop_id'=>$res['data']['content']['mallID'],
                                                'goods_id'=>$res['data']['content']['goodsID'],
                                                'sku_id'=>$v['skuID'],
                                                'price'=>$v['normalPrice'],
                                                'specs_value'=>$specsStr,
                                                'goodss_id'=>$goodsDataId,
                                                'add_id'=>$id,
                                                'group_id'=>$res['data']['content']['groupTypes'][0]['groupID'],
                                                'goods_name'=>$res['data']['content']['goodsName'],
                                                'created_at'=>date('Y-m-d H:i:s'),
                                                'updated_at'=>date('Y-m-d H:i:s'),
                                                'img'=>$v['thumbUrl'],
                                            ];
                                        }
                                    }
                                    if(!empty($newData)){
                                        $res = DB::table('goods')->insert($newData);
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
                                //调用成功
                            }
                        }
                    }
                }
            }else{
                //单个链接
                if($form->model()->url){
                    //内部调用获取商品详情接口
                    $data = [];
                    $data['inside'] = 100;
                    $data['timestamp'] = time();
                    $data['url'] = $form->model()->url;
                    $data['sign'] = Rsa::encode($data,AppPath::APP_RSA_PASS_PATH,AppPath::APP_RSA_PFX_PATH);
                    $res = $this->api->json($data)->post('goods/details');
                    if($res['status_code'] == 200){
                        $mall = $res['data']['content']['mall'];
                        $shop = DB::table('shop')->where('shop_id',$mall['mallID'])->select('shop_id')->first();
                        if(!$shop){
                            DB::table('shop')->insert(['shop_name'=>$mall['mallName'],'shop_id'=>$mall['mallID'],'salesTip'=>$mall['salesTip'],'url'=>$mall['pddRoute'],'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
                        }
                        $res['data']['content'] = $res['data']['content']['goods'];
                        $info = DB::table('goodss')->where('goods_id',$res['data']['content']['goodsID'])->select('goods_id')->first();
                        if(!$info){
                            //插入商品
                            $goodsData = [
                                'created_at'=>date('Y-m-d H:i:s'),
                                'updated_at'=>date('Y-m-d H:i:s'),
                                'url'=>$form->model()->url,
                                'sales_volume'=>$res['data']['content']['sideSalesTip'],
                                'img'=>$res['data']['content']['thumbUrl'],
                                'add_id'=>$id,
                                'json_data'=>json_encode($res['data']['content']),
                                'goods_name'=>$res['data']['content']['goodsName'],
                                'goods_id'=>$res['data']['content']['goodsID'],
                            ];
                            $goodsDataId = Goodss::insertGetId($goodsData);
                            if($res['data']['content']['skus']){
                                foreach ($res['data']['content']['skus'] as $k=>$v){
                                    //处理规格
                                    $specsStr = '';
                                    $spec = $v['specs'];
                                    foreach ($spec as $kkk=>$vvv){
                                        $specsStr.= "{$vvv['spec_key']}:{$vvv['spec_value']},";
                                    }
                                    $newData[] = [
                                        'url'=>$form->url,
                                        'shop_id'=>$res['data']['content']['mallID'],
                                        'goods_id'=>$res['data']['content']['goodsID'],
                                        'sku_id'=>$v['skuID'],
                                        'price'=>$v['normalPrice'],
                                        'specs_value'=>$specsStr,
                                        'goodss_id'=>$goodsDataId,
                                        'add_id'=>$id,
                                        'group_id'=>$res['data']['content']['groupTypes'][0]['groupID'],
                                        'goods_name'=>$res['data']['content']['goodsName'],
                                        'created_at'=>date('Y-m-d H:i:s'),
                                        'updated_at'=>date('Y-m-d H:i:s'),
                                        'img'=>$v['thumbUrl'],
                                    ];
                                }
                            }
                        }
                        if(!empty($newData)){
                            $res = DB::table('goods')->insert($newData);
                            if(!$res){
                                $error = new MessageBag([
                                    'title'   => '提示信息',
                                    'message' => '添加失败',
                                ]);
                                return back()->with(compact('error'));
                            }
                        }
                        //调用成功
                    }else{
                        $error = new MessageBag([
                            'title'   => '提示信息',
                            'message' => '添加失败',
                        ]);
                        return back()->with(compact('error'));
                    }
                }
            }

            return redirect('/admin/goods/index');
        });
        $form->tools(function (Form\Tools $tools) {
            // 去掉`列表`按钮
            $tools->disableList();
            // 去掉`删除`按钮
            $tools->disableDelete();
            // 去掉`查看`按钮
            $tools->disableView();
            // 添加一个按钮, 参数可以是字符串, 或者实现了Renderable或Htmlable接口的对象实例
            $tools->add('<div class="box-tools"><div class="btn-group pull-right" style="margin-right:5px"><a href="/admin/goods/index" class="btn btn-sm btn-default" title="列表"><i class="fa fa-list"></i><span class="hidden-xs">&nbsp;列表</span></a></div></div>');
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