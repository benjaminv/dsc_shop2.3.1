<?php exit;?>001479806760019a6f875411e1e57ac520536302c3c5s:13368:"a:2:{s:8:"template";s:13303:"<?php $__Template->display($this->getTpl("page_header")); ?>
<div class="con"  >
    <div class="shopping-list j-shopping-list">
        <div class="goods-photo goods-photo-auto" >
            <span class="goods-num" style="display:none" id="goods-num"><span id="g-active-num"></span>/<span id="g-all-num"></span></span>
            <div class="swiper-wrapper">
                <li class="swiper-slide tb-lr-center"><img src="<?php echo __TPL__;?>img/shopplist_1.png" /></li>
            </div>
            <!-- Add Pagination -->
            <div class="swiper-pagination"></div>
        </div>

        <div class="shopping-menu">
            <div class="b-color-f">
                <nav class="j-g-s-p-con shopping-list-nav">
                    <ul class="dis-box text-center">
                        <li class="box-flex s-nav-select j-s-nav-select onelist-hidden"><span class="onelist-hidden">类别</span><i class="iconfont icon-xiajiantou"></i></li>
                        <li class="box-flex s-position-select j-s-position-select"><span class="onelist-hidden">地区</span><i class="iconfont icon-xiajiantou"></i></li>
                        <li class="box-flex s-distance-select j-s-distance-select translate"> <a id="paixu"  order="distance" sort="DESC"> 排序</a><i class="iconfont icon-xiajiantou"></i></li>
                        <li class="box-flex"><a href="<?php echo U('store/map/index');?>">附近</a></li>
                    </ul>
                </nav>
            </div>
            <div class="shopping-abs shopping-option-con">
                <div class="shopping-city-one swiper-scroll">
                    <div class="swiper-wrapper ">
                        <div class="swiper-slide j-province">
                            <ul>
                                <?php $n=1;if(is_array($province)) foreach($province as $key=>$list) { ?>
                                <li data-province="<?php echo $list['region_id']; ?>" <?php if($key===0) { ?>class="active"<?php } ?>><?php echo $list['region_name']; ?></li>
                                <?php $n++;}unset($n); ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="shopping-city-two">
                    <div class="swiper-wrapper ">
                        <div class="swiper-slide">
                            <ul class="j-city">
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="shopping-abs shopping-nav-con j-shopping-nav-list">
                <div class="swiper-scroll">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide j-catid">
                            <li><a href="<?php echo U('store/index/index');?>">返回默认</a></li>
                            <?php $n=1;if(is_array($category)) foreach($category as $list) { ?>
                            <li data-catid="<?php echo $list['cat_id']; ?>"><?php echo $list['cat_alias_name']; ?></li>
                            <?php $n++;}unset($n); ?>
                        </div>
                    </div>
                    <div class="swiper-scrollbar"></div>
                </div>
            </div>
        </div>
    </div>
        <!-- 店铺列表start -->
        <div class="store_info">

            <script id="j-product" type="text/html">
                <%if totalPage>0%>
                <%each list as key%>
                <section class="m-top08 padding-all goods-shop b-color-f">
                    <header class="goods-shop-info">
                        <a href="<%key.url%>" class="link-abs"></a>
                        <section class="dis-box">
                            <div class="g-s-i-img"><img src="<%key.shop_logo%>" /></div>
                            <div class="g-s-i-title box-flex">
                                <h3 class="ellipsis-one"><%key.shop_name%></h3>
                                <p class="t-remark m-top04">已经有 <span class="num<% key.user_id %>"><%key.gaze_number%></span> 人关注</p>
                                <%if key.distance >0%>
                                <p class="t-remark m-top04">距离<%key.distance%>km </p> 
                                <%/if%>
                            </div>
                            <div class="g-s-info-add">
                                <a href="javascript:void(0)" class="gaze<% key.user_id %> <% key.gaze_status %>"  onclick="addgaze(<% key.user_id %>)" >关注</a>
                            </div>
                        </section>
                        <section class="dis-box goods-shop-score m-top12">
                            <p class="box-flex">
                                <label class="fl">商品</label><span class="<%key.commentrank_bg%> margin-lr fl"><%key.commentrank%>分</span><em class="em-promotion fl <%key.commentrank_box%>"><%key.commentrank_font%></em></p>
                            <p class="box-flex">
                                <label class="fl">服务</label><span class="<%key.commentserver_bg%> margin-lr fl"><%key.commentserver%>分</span><em class="em-promotion fl <%key.commentserver_box%>"><%key.commentserver_font%></em></p>
                            <p class="box-flex">
                                <label class="fl">时效</label><span class="<%key.commentdelivery_bg%> margin-lr fl"><%key.commentdelivery%>分</span><em class="em-promotion fl <%key.commentdelivery_box%>"><%key.commentdelivery_font%></em></p>
                        </section>
                    </header>

                    <%if key.goods %>
                    <section class="goods-shop-pic of-hidden">
                        <h4 class="title-hrbg m-top06"><span><%key.title%></span><hr> </h4>
                        <div class="g-s-p-con product-one-list of-hidden scrollbar-none j-g-s-p-con">
                            <div class="swiper-wrapper ">
                                <!-- 商品列表 -->
                                <%each key.goods as list %>
                                <li class="swiper-slide">
                                    <div class="product-div">
                                        <a class="product-div-link" href="<%list.goods_url%>"></a>
                                        <img class="product-list-img" src="<%list.goods_img%>" />
                                        <div class="product-text m-top06">
                                            <h4><%list.goods_name%></h4>
                                            <p><span class="p-price t-first ">
													<%if list.is_promote && list.gmt_end_time %>
														<%#list.promote_price %>
													<%else%>
														<%#list.shop_price_formated %>
													<%/if%>
                                            </p>
                                        </div>
                                    </div>
                                </li>
                                <%/each%>
                                <!-- 商品列表 end -->
                            </div>
                        </div>
                    </section>
                    <%/if%>
                </section>
                <%/each%>
                <% else %>
                <div class="no-div-message">
                    <i class="iconfont icon-biaoqingleiben"></i>
                    <p>亲，此处没有内容～！</p>
                </div>
                <%/if%>
            </script>
        </div>
        <!-- 店铺列表end -->
    </div><div class="mask-filter-div"></div>
</div>
<div id="allmap"></div>

<script>
    
$(function(){
    var url = 'index.php?r=store/index/index';
    var cat_id = 0,
        city_id = 0,
        keywords = '<?php echo $keywords; ?>', 
        type = '<?php echo $type; ?>',
        lat = 0,
        lng = 0, 
        order = 'distance ', 
        sort = 'asc';
    // 首次加载
    var infinite = $('.store_info').infinite({url: url,params:'sort='+sort+'&order='+order+'&keywords='+keywords+'&type='+type,template: 'j-product'});
 
    getLocation();
    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition, errorPosition);
        } else {
            errorPosition();
        }
    }
    function showPosition(position) {
        lat= position.coords.latitude;
        lng= position.coords.longitude;
        infinite.onload('lat='+ lat +'&lng='+lng+'&sort='+sort+'&order='+order+'&keywords='+keywords+'&type='+type);
    }
    function errorPosition() {
      
    }
    // 点击分类
    $('.j-catid').on('click', 'li', function(){ 
        $(".shopping-menu").removeClass("nav-active");
        $(this).addClass("active").siblings().removeClass("active");
        $(".mask-filter-div").removeClass("show");
        cat_id = $(this).attr('data-catid');
        infinite.onload('lat='+ lat +'&lng='+lng+'&sort='+sort+'&order='+order+'&cat_id='+cat_id+'&city_id='+city_id+'&keywords='+keywords+'&type='+type);
    });
    
    var scorll_swiper1 = new Swiper('.shopping-city-two', {
		scrollbar: false,
		direction: 'vertical',
		slidesPerView: 'auto',
		mousewheelControl: true,
		freeMode: true
	});
    
    // 点击省份
    $('.j-province li').click(function(){
    	$(this).addClass("active").siblings().removeClass("active");	
    	      $.ajax({
                        type: "POST",
                        url: "<?php echo U('region');?>",
                        dataType: 'JSON',
                        data: {city: $(this).attr('data-province')}, 
                        success: function(data){
                               $('.j-city').empty();
                               scorll_swiper1.destroy();
                                $.each(data.list, function(i, item){//循环遍历                         
									$('.j-city').append('<li data-city="'+item.region_id+'">'+item.region_name+'</li>');
                                })
                                scorll_swiper1 = new Swiper('.shopping-city-two', {
									scrollbar: false,
									direction: 'vertical',
									slidesPerView: 'auto',
									mousewheelControl: true,
									freeMode: true
								});
								 scorll_swiper1.update(true);
                        }
               });
    });
    
    
    // 点击地区
    $(document).on("click",".j-city li",function(){
        $(this).addClass("active").siblings().removeClass("active");
        city_id = $(this).attr('data-city');
        infinite.onload('lat='+ lat +'&lng='+lng+'&sort='+sort+'&order='+order+'&cat_id='+cat_id+'&city_id='+city_id+'&keywords='+keywords+'&type='+type);
     	 $(".shopping-menu").removeClass("position-active");
        $(".mask-filter-div").removeClass("show");
    });
    //　点击排序
        var  off = true;
        $("#paixu").on('click', function(e) {  
         var sort = "";
         var order = $(this).attr("order");
           if(off){
               sort = $(this).attr("sort");
               $(this).attr("sort","ASC");
               off = false;
            }else{
               sort = $(this).attr("sort");
               $(this).attr("sort","DESC");
               off = true;
            }
           infinite.onload('lat='+ lat +'&lng='+lng+'&sort='+sort+'&order='+order+'&cat_id='+cat_id+'&city_id='+city_id+'&keywords='+keywords+'&type='+type);
        })    
})
</script>
<script>

    function addgaze(shop){
        if(shop!=''){
            $.post('index.php?r=store/index/add_collect', {shopid: shop}, function(data){
                if(data.error==1){
                    $(".gaze"+shop).addClass("active");
                }
                if(data.error==2){
                    $(".gaze"+shop).removeClass("active");
                }
                var num = $(".num"+shop).text();
                if(data.error==1){
                    num = num*1 + 1;
                    $(".num"+shop).text(num);
                }else if(data.error==2){
                    if(num>0)
                    {
                        num = num*1 - 1;
                        $(".num"+shop).text(num);
                    }
                }
                if(data.error>0){
                    d_messages(data.msg);
                }else {
                    layer.open({
                        content: '请登录后关注该店铺',
                        btn: ['立即登录', '取消'],
                        shadeClose: false,
                        yes: function () {
                            window.location.href = 'index.php?r=user/login';
                        },
                        no: function () {
                        }
                    });
                }
            }, 'json');
        }
    }
       
    /*顶部banner滚动*/
    var swiper = new Swiper('.goods-photo', {
        paginationClickable: true
    });


</script>

</body>

</html>
";s:12:"compile_time";i:1479720360;}";