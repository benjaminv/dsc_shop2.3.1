<?php exit;?>0014794826068ebf7367082f184e8e542f33de3c3198s:11574:"a:2:{s:8:"template";s:11509:"<?php $__Template->display($this->getTpl("page_header")); ?>
<div class="con" >
    <div class="shopping-prolist">
        <section class="search">
            <div class="text-all dis-box j-text-all">
                <div class="box-flex input-text n-input-text">
                    <a class="a-search-input j-search-input" href="javascript:void(0)"></a>
                    <input class="j-input-text input" name="showword" type="text" placeholder="商品搜索" />
                    <i class="iconfont icon-guanbi1 is-null j-is-null"></i>
                </div>
                <a href="#j-filter-div" class="s-filter j-s-filter">分类</a>
            </div>
        </section>
        <section class="product-sequence dis-box">
            <a class="sort box-flex a-change active"  sort="1" order="DESC">综合<i class="iconfont icon-xiajiantou"></i></a>
            <a class="sort box-flex" sort="2" order="DESC">新品</a>
            <a class="sort box-flex" sort="3" order="DESC">销量</a>
            <a class="sort box-flex" sort="4" order="DESC">价格<i class="iconfont icon-xiajiantou"></i></a>
            <a class="a-sequence j-a-sequence"><i class="iconfont icon-pailie" data="1"></i></a>
        </section>
        <section class="product-list j-product-list product-list-medium" data="1">
            <div class="mask-filter-div"></div>
            <div class="store_info">
                <script id="j-product" type="text/html">
                    <ul >
                        <%if show%>
                        <%each list as key%>
                        <li>
                            <div class="product-div">
                                <a class="product-div-link" href="<%key.goods_url%>"></a>
                                <img class="product-list-img" src="<%key.goods_thumb%>" />
                                <div class="product-text">
                                    <h4><%key.goods_name%></h4>
                                    <p class="dis-box p-t-remark"><span class="box-flex">库存:<%key.goods_number%></span><span class="box-flex">销量:<%key.sales_volume%></span></p>
                                    <p><span class="p-price t-first ">

                                    <%if key.is_promote && key.gmt_end_time %>
                                        <%#key.promote_price%>
                                    <%else%>
                                        <%#key.shop_price_formated%>
                                    <%/if%>
									<small><del><%#key.market_price%></del></small></span></p>
                                    <a onclick="addToCart(<%key.goods_id%>, 0)"  class="icon-flow-cart fr j-goods-attr"><i class="iconfont icon-gouwuche"></i></a>
                                </div>
                            </div>
                        </li>
                        <%/each%>
                    </ul>
                    <input type="hidden" name="maxpage" value="<%maxpage%>">
                    <%else%>
                    <div class="no-div-message">
                        <i class="iconfont icon-biaoqingleiben"></i>
                        <p>亲，此处没有东西～！</p>
                    </div>
                    <%/if%>
                </script>
            </div>
        </section>
    </div>
    <?php $__Template->display($this->getTpl("store_search")); ?>
    <!--筛选s-->
    <div id="j-filter-div" class="j-filter-div filter-div ts-5 c-filter-div">
        <section class="close-filter-div j-close-filter-div">
            <div class="close-f-btn">
                <i class="iconfont icon-fanhui"></i>
                <span>关闭</span>
            </div>
        </section>
        <section class="con-filter-div ">
            <div class="select-two">
                <!-- <?php $n=1; if(is_array($category)) foreach($category as $key => $val) { ?> -->
                <!--<?php if($key==0) { ?>-->
                <a class="select-title padding-all j-menu-select">
                    <label class="fl">品牌</label>
                    <span class="fr t-jiantou j-t-jiantou"><i class="iconfont icon-jiantou tf-180 ts-2"></i></span>
                </a>
                <ul class="j-sub-menu padding-all" data-istrue="true">
                    <!-- <?php $n=1;if(is_array($brand)) foreach($brand as $k) { ?> -->
                    <li class="ect-select">
                        <label class="brand ts-1" bid="<?php echo $k['bid']; ?>"><?php echo $k['brandName']; ?>(<?php echo $k['bank_name_letter']; ?>)</label>
                    </li>
                    <!-- <?php $n++;}unset($n); ?> -->
                </ul>
                <!--<?php } ?>-->
                <a class="select-title padding-all j-menu-select">
                    <label class="fl"><?php echo $val['cat_name']; ?></label>
                    <span class="fr t-jiantou j-t-jiantou"><i class="iconfont icon-jiantou tf-180 ts-2"></i></span>
                </a>
                <ul class="j-sub-menu padding-all" data-istrue="true">
                    <!-- <?php $n=1;if(is_array($val['child'])) foreach($val['child'] as $v) { ?> -->
                    <li class="ect-select">
                        <a href="#"><label class="category ts-1" cat-id="<?php echo $v['cat_id']; ?>"><?php echo $v['cat_name']; ?></label></a>
                    </li>
                    <!-- <?php $n++;}unset($n); ?> -->
                </ul>
                <!-- <?php $n++;}unset($n); ?> -->
            </div>
            <div >

            </div>
    </div>
</div>
</section>
</div>
<!--筛选e-->
</div>
<input type="hidden" name="type" value="<?php echo $type; ?>">
<input type="hidden" name="ru_id" value="<?php echo $ru_id; ?>">
<input type="hidden" name="bid" value="<?php echo $bid; ?>">
<input type="hidden" name="cat_id" value="<?php echo $cat_id; ?>">
<input type="hidden" name="bigcat" value="<?php echo $bigcat; ?>">
<input type="hidden" name="infokeyword" value="<?php echo $keyword; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="where" value="<?php echo $where; ?>">
<input type="hidden" name="warehouse_id" value="<?php echo $warehouse_id; ?>">
<input type="hidden" name="area_id" value="<?php echo $area_id; ?>">

<script>
    //店铺街分类
    var keyword = $("input[name=infokeyword]").val();
    var bid = $("input[name=bid]").val();
    var cat_id = $("input[name=cat_id]").val();
    var bigcat = $("input[name=bigcat]").val();
    var maxpage ;
    //var page = $("input[name=page]").val();
    var type  = $("input[name=type]").val();
    var ru_id = $("input[name=ru_id]").val();
    var where = $("input[name=where]").val();
    var url = 'index.php?r=store/index/pro_list';
    var sort;
    template.config('openTag', '<%');
    template.config('closeTag', '%>');
    /* function show(html){
     $(".mask-filter-div").addClass("show");
     $(".j-show-goods-attr"+html).addClass("show");
     }
     function hiden(id){
     $(".mask-filter-div").removeClass("show");
     $(".j-show-goods-attr"+id).removeClass("show");
     }
     function attrprice(id){
     var attr = '';
     $("label.ts-1"+id).each(function() {
     if ($(this).hasClass("active")) {
     attr += $(this).attr("attr-id")+',';
     }
     })
     attr = attr.substr(0,attr.length-1);
     var number = $("input[name=number"+id+"]").val();
     var warehouse_id = $("input[name=warehouse_id]").val();
     var area_id = $("input[name=area_id]").val();
     $.get('index.php?r=goods/index/price',{id:id,warehouse_id:warehouse_id,area_id:area_id,number:number,attr:attr},function(data){
     $(".goods_attr_num"+id).text(data.attr_number);
     $("#ECS_GOODS_AMOUNT"+id).text(data.result);
     if(data.attr_number < 1){
     $(".add-to-cart"+id).hide();
     $(".quehuo"+id).show();
     }else{
     $(".add-to-cart"+id).show();
     $(".quehuo"+id).hide();
     }
     },'json')

     }
     $(window).scroll(function() {
     var scrollTop = $(window).scrollTop() + 100;
     var documentHeight = $(document).height() - $(window).height();
     if (scrollTop >= documentHeight) {
     maxpage = $("input[name=maxpage]").val();
     if(page<maxpage) {
     page++;
     keyword=$("input[name=showword]").val();
     $.post(url, {type:type,ru_id:ru_id,keyword:keyword,bid:bid,bigcat:bigcat,cat_id:cat_id,page:page,where:where}, function(data){
     var html = template('j-product', data);
     $('.store_info').append(html);
     swiper_scroll();
     }, 'json');
     }
     }
     });*/
    $(".sort").click(function(){
        sort = $(this).attr("sort");
        var order = $(this).attr('order');
        $(".sort").each(function (){
            if($(this).attr("sort")==sort){
                $(this).addClass('active');
                if(sort==1 || sort==4){
                    if($(this).hasClass("a-change")){
                        $(this).removeClass("a-change");
                    }else{
                        $(this).addClass("a-change");
                    }
                }
            }else{
                $(this).removeClass('active');
            }
        });
        var str ="type="+type+"&ru_id="+ru_id+"&sort="+sort+"&keyword="+keyword+"&bigcat="+bigcat+"&resetpage=1"+'&order='+order;
        infinite.onload(str);
        if(order == 'DESC'){
            $(this).attr("order","ASC");
            $(this).removeClass("a-change");
        }else{
            $(this).attr("order","DESC");
            $(this).addClass("a-change");
        }
    })
    if(keyword!=''){
        type='';
    }
    $("input[name=showword]").val(keyword);
    var str ="type="+type+"&ru_id="+ru_id+"&keyword="+keyword+"&bid="+bid+"&bigcat="+bigcat+"&cat_id="+cat_id;
    var infinite = $('.store_info').infinite({url: url,params:str, template: 'j-product'});
    swiper_scroll();
    //清空搜索
    $(".clear_history").click(function(){
        if(history){
            $.get("<?php echo U('category/index/clear_history');?>", '', function(data){
                if(data.status){
                    $("#search-con").remove();
                }
            }, 'json');
        }
    });
    //搜索
    $(".btn-submit").click(function (){
        $(".show-search-div").removeClass("show-search-div");
        keyword = $("input[name=inputkeyword]").val();
        var str ="type="+type+"&ru_id="+ru_id+"&keyword="+keyword;
        infinite.onload(str);
    })
    //分类
    $(".brand").click(function (){
        $(this).css({ color: "#BD5858"});
        bid = $(this).attr("bid");
        $(".show-filter-div").removeClass("show-filter-div");
        var str ="type="+type+"&ru_id="+ru_id+"&bid="+bid;
        infinite.onload(str);
    })
    $(".category").click(function (){
        $(this).css({ color: "#BD5858"});
        cat_id = $(this).attr("cat-id");
        //alert(cat_id);
        $(".show-filter-div").removeClass("show-filter-div");
        var str ="type="+type+"&ru_id="+ru_id+"&cat_id="+cat_id;
        infinite.onload(str);
    })
    /*店铺信息商品滚动*/
    var swiper = new Swiper('.j-f-n-c-prolist', {
        scrollbarHide: true,
        slidesPerView: 'auto',
        centeredSlides: false,
        grabCursor: true
    });
</script>
</body>

</html>";s:12:"compile_time";i:1479396206;}";