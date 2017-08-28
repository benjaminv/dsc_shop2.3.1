<?php exit;?>00147867922177b36857d8646c09ad58ca64cc793dd7s:15798:"a:2:{s:8:"template";s:15733:"<?php $__Template->display($this->getTpl("page_header")); ?>
		<div class="con">
					    <header class="dis-box header-menu n-header-menu b-color color-whie">
        <a class="" href="javascript:history.go(-1);"><i class="iconfont icon-back"></i></a>
        <h3 class="box-flex">拍卖详情</h3>
        <a><i class="iconfont icon-13caidan j-nav-box"></i></a>
    </header>
    <?php $__Template->display($this->getTpl("header_nav")); ?>
            <?php if($auction['is_winner']) { ?>
            <form action="<?php echo U('buy');?>" name="buy" method="post">
            <?php } else { ?>
            <form action="<?php echo U('bid');?>" name="bid" method="post">
            <?php } ?>
			<div class="goods">
				<div class="goods-photo j-show-goods-img">
					<div class="group-time-box" id="fnTimeCountDown">
						<span class="day">00</span>天
						<span class="hour">00</span>时
						<span class="mini">00</span>分
						<span class="sec">00</span>秒
						<!--<span class="hm">000</span>-->
					</div>
					<span class="goods-num" id="goods-num">
						<span id="g-active-num"></span>/<span id="g-all-num"></span>
					</span>
					<div class="swiper-wrapper">
                        <?php $n=1;if(is_array($pictures)) foreach($pictures as $li) { ?>
                        <li class="swiper-slide tb-lr-center">
                            <img src="<?php echo $li['img_url']; ?>" />
                        </li>
                        <?php $n++;}unset($n); ?>
					</div>
					<!-- Add Pagination -->
					<div class="swiper-pagination"></div>
				</div>

				<section class="goods-title b-color-f padding-all ">
					<div class="dis-box">
                        <h3 class="box-flex"><?php echo $auction['goods_name']; ?></h3>
                        <span class="heart j-heart <?php if($goods_collect) { ?>active<?php } ?>" onclick="collect(<?php echo $auction_goods['goods_id']; ?>)" id="ECS_COLLECT"><i class="ts-2"></i><em class="ts-2">收藏</em></span>
					</div>
				</section>
				<section class="goods-price padding-all b-color-f">
                    <p class="p-price"><span class="t-first"><?php echo $auction['formated_current_price']; ?></span></p>
                    <p class="p-market">一口价 <del><?php echo $auction['formated_end_price']; ?></del></p>
					<p class=" dis-box g-p-tthree m-top06">
                    <span class="box-flex text-left">加价幅度 <?php echo $auction['formated_amplitude']; ?></span>
                    <span class="box-flex text-right">拍送机构  <?php echo $auction_goods['rz_shopName']; ?></span>
					</p>
				</section>
				<section class=" padding-all b-color-f new-auction-bor goods-promotion">
                    <?php if($auction['status_no'] == 0) { ?>
                    <div class="dis-box">
						<label class="t-remark g-t-temark">
                            <span class="aut-left-num">未开始</span>
						</label>
					</div>
                    <?php } elseif ($auction['status_no'] == 1) { ?>
                    <div class="dis-box">
						<label class="t-remark g-t-temark">
                            <span class="aut-left-num">最低加价:</span> <span class="my-bz">(<?php echo $auction['formated_amplitude']; ?>*N)</span>
						</label>
					</div>
					<div class="div-num dis-box m-top04 ">
						<a class="num-less" onclick="numLess()"></a>
                        <input class="box-flex price_times" type="number" value="<?php echo $auction['price_times']; ?>" name="price_times">
						<a class="num-plus" onclick="numPlus()"></a>
					</div>
                    <?php } else { ?>
                        <?php if($auction['is_winner']) { ?>
                        <div class="dis-box">
                            <label class="t-remark g-t-temark">
                                <span class="aut-left-num">恭喜您，您已<?php echo $auction['last_bid']['formated_bid_price']; ?>拍得本次活动商品。</span>
                            </label>
                        </div>
                        <?php } else { ?>
                        <div class="dis-box">
                            <label class="t-remark g-t-temark">
                                <span class="aut-left-num">已结束</span>
                            </label>
                        </div>
                        <?php } ?>
                    <?php } ?>
					
				</section>
				<section class="m-top04 goods-evaluation">
					<div class="padding-1 b-color-f g-evaluation-title ">
						<div class="box-flex">
							<h3 class="my-u-title-size">竞拍流程</h3> </div>
					</div>
					<div id="myarticle" class="padding-all-3 b-color-f g-evaluation-con">
						<div class="of-hidden evaluation-list new-auction-cont">
							<h4>1、获取资格</h4>
							<p class="aut-cont">用户账号当前抵用券大于出价所需抵用券 </p>
						</div>
						<div class="of-hidden evaluation-list new-auction-cont">
							<h4>2、拍的商品</h4>
							<p class="aut-cont">保证竞拍结束出价最低且唯一，获得竞拍商品 </p>
						</div>
						<div class="of-hidden evaluation-list new-auction-cont">
							<h4>3、确认订单</h4>
							<p class="aut-cont">填写订单信息，提交订单</p>
						</div>
						<div class="goods-more" style="display:none">
							<div class="of-hidden evaluation-list new-auction-cont">
								<h4>4、支付订单</h4>
								<p class="aut-cont">支付已提交的订单 </p>
							</div>
							<div class="of-hidden evaluation-list new-auction-cont">
								<h4>5、竞拍成功</h4>
								<p class="aut-cont">支付成功后等待收货，竞拍完成 </p>
							</div>
						</div>
					</div>

					<p id="btn" class="act-gengduo b-color-f but-mu"><em onclick="javascript:this.innerHTML=(this.innerHTML=='展开'?'收起':'展开');">展开</em><span class="t-jiantou"><i class="iconfont icon-jiantou tf-180"></i></span></p>
				</section>
				<section class="box b-color-f m-top04">
					<a href="<?php echo U('auction_log', array('id'=>$auction['act_id']));?>">
						<div class="box padding-all  wallet-bt">
							<h3 class="recom-title-1">
						出价记录					
                        <span class=" t-remark fr"><?php echo $auction['bid_user_count']; ?>人<span class="t-jiantou"><i class="iconfont icon-jiantou tf-180"></i></span></span>
					    </h3>
						</div>
					</a>
                    <?php $n=1;if(is_array($auction_log)) foreach($auction_log as $k=>$au) { ?>
					<div class="padding-1 m-top1px b-color-f g-evaluation-con">
						<div class="of-hidden evaluation-list">
							<div class="of-hidden ">
                                <?php if($k == 0) { ?>
                                <p class="fl"><span class="act-new-box a-col-red">领先</span><span class="aut-admin"><?php echo $au['user_name']; ?></span></p>
                                <?php } else { ?>
                                <p class="fl"><span class="act-new-box a-col-hs">出局</span><span class="aut-admin"><?php echo $au['user_name']; ?></span></p>
                                <?php } ?>
                                <p class="fr t-remark"><?php echo $au['bid_time']; ?></p>
							</div>
                            <p><span class="p-price t-first "><?php echo $au['formated_bid_price']; ?></span></p>
						</div>
					</div>
                    <?php $n++;}unset($n); ?>
                    <a href="<?php echo U('auction_log', array('id'=>$auction['act_id']));?>">
						<div class=" padding-all ect-button-more dis-box">
							<span class="box-flex btn-default">查看更多</span>
						</div>
					</a>
				</section>
				<section class="m-top04  goods-shop  b-color-f no-shopping-title">
					<div class="box padding-all wallet-bt">
						<h3 class="recom-title-1">
						推荐拍品											
					    </h3>
					</div>
					<div class="goods-shop-pic of-hidden padding-all ">
						<div class="g-s-p-con product-one-list of-hidden scrollbar-none j-g-s-p-con">
							<div class="swiper-wrapper">
                                <?php $n=1;if(is_array($hot_goods)) foreach($hot_goods as $goods) { ?>
								<li class="swiper-slide">
									<div class="product-div">
                                        <a class="product-div-link" href="<?php echo $goods['url']; ?>"></a>
                                        <img class="product-list-img" src="<?php echo $goods['thumb']; ?>" />
										<div class="product-text m-top06">
                                            <h4><?php echo $goods['short_style_name']; ?></h4>
                                            <p><span class="p-price t-first "><?php echo $goods['start_price']; ?></p>
										</div>
									</div>
								</li>
                                <?php $n++;}unset($n); ?>
							</div>
						</div>
					</div>
				</section>
				<section class="m-top04 product-sequence dis-box">
					<a class="box-flex active a-change">拍卖介绍</a>
					<a class="box-flex">服务保障</a>
					<a class="box-flex">拍卖攻略</a>
				</section>
			</div>

			<!--悬浮btn star-->
			<section class="filter-btn dis-box">
                 <?php if($basic_info['kf_type'] == 1) { ?>
                 <a class="filter-btn-kefu filter-btn-a" href="http://www.taobao.com/webww/ww.php?ver=3&touid=<?php echo $auction_goods['kf_ww']; ?>&siteid=cntaobao&status=1&charset=utf-8"><i class="iconfont icon-kefu"></i><em>客服</em></a>
                 <?php } else { ?>
                 <a class="filter-btn-kefu filter-btn-a" href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo $auction_goods['kf_qq']; ?>&site=qq&menu=yes"><i class="iconfont icon-kefu"></i><em>客服</em></a>
                 <?php } ?>
                 <a class="filter-btn-shop filter-btn-a" href="<?php echo $auction_goods['store_url']; ?>"><i class="iconfont icon-dianpu"></i><em>店铺</em></a>
                 <?php if($auction['status_no'] == 0) { ?>
				 <a class="btn-submit box-flex btn-disab">未开始</a>
                 <?php } elseif ($auction['status_no'] == 1) { ?>
                 <input type="hidden" name="id" value="<?php echo $auction['act_id']; ?>" />
                 <input type="submit" class="btn-submit box-flex" value="我要出价" />
                 <?php } else { ?>
                    <?php if($auction['is_winner']) { ?>
                    <input type="hidden" name="id" value="<?php echo $auction['act_id']; ?>" />
                    <input type="submit" class="btn-submit box-flex" value="立即购买" />
                    <?php } else { ?>
                    <a class="btn-submit box-flex btn-disab">已结束</a>
                    <?php } ?>
                <?php } ?>
			</section>
            </form>
			<!--悬浮btn end-->
            <script type="text/javascript" src="<?php echo __PUBLIC__;?>script/main/common.js"></script>
			<script>
				$(".product-sequence a").click(function (){
					$(this).addClass("active").siblings().removeClass("active");
				})
				/*展开全部内容*/
				$(document).ready(function() {
					$(".but-mu").click(function() {
						$(".goods-more").toggle();
					});
				});
                function numLess(){
                    var times = <?php echo $auction['price_times']; ?>;
                    var num = parseInt($("input.price_times").val()) - 1;
                    if(num < times){
                        d_messages("现在已经是最低拍价");
                        return false;
                    }
                    $("input.price_times").val(num);
                }
                function numPlus(){
                    $("input.price_times").val(parseInt($("input.price_times").val()) + 1);
                }
				/*商品详情相册切换*/
				var swiper = new Swiper('.goods-photo', {
					paginationClickable: true,
					onInit: function(swiper) {
						document.getElementById("g-active-num").innerHTML = swiper.activeIndex + 1;
						document.getElementById("g-all-num").innerHTML = swiper.slides.length;
					},
					onSlideChangeStart: function(swiper) {
						document.getElementById("g-active-num").innerHTML = swiper.activeIndex + 1;
					}
				});
				/*店铺信息商品滚动*/
				var swiper = new Swiper('.j-g-s-p-con', {
					scrollbarHide: true,
					slidesPerView: 'auto',
					centeredSlides: false,
					grabCursor: true
				});
				/*倒计时*/
				$.extend($.fn, {
					fnTimeCountDown: function(d) {
						this.each(function() {
							var $this = $(this);
							var o = {
								hm: $this['find'](".hm"),
								sec: $this['find'](".sec"),
								mini: $this['find'](".mini"),
								hour: $this['find'](".hour"),
								day: $this['find'](".day"),
								month: $this['find'](".month"),
								year: $this['find'](".year")
							};
							var f = {
								haomiao: function(n) {
									if (n < 10) return "00" + n.toString();
									if (n < 100) return "0" + n.toString();
									return n.toString();
								},
								zero: function(n) {
									var _n = parseInt(n, 10); //解析字符串,返回整数
									if (_n > 0) {
										if (_n <= 9) {
											_n = "0" + _n
										}
										return String(_n);
									} else {
										return "00";
									}
								},
								dv: function() {
									//d = d || Date.UTC(2050, 0, 1); //如果未定义时间，则我们设定倒计时日期是2050年1月1日
									var _d = $this['data']("end") || d;
									var now = new Date(),
										endDate = new Date(_d.replace(/-/g, "/"));
									//现在将来秒差值
									//alert(future.getTimezoneOffset());
									var dur = (endDate - now.getTime()) / 1000,
										mss = endDate - now.getTime(),
										pms = {
											sec: "00",
											mini: "00",
											hour: "00",
											day: "00",
										};
									if (mss > 0) {
										pms.hm = f.haomiao(mss % 1000);
										pms.sec = f.zero(dur % 60);
										pms.mini = Math.floor((dur / 60)) > 0 ? f.zero(Math.floor((dur / 60)) % 60) : "00";
										pms.hour = Math.floor((dur / 3600)) > 0 ? f.zero(Math.floor((dur / 3600)) % 24) : "00";
										pms.day = Math.floor((dur / 86400)) > 0 ? f.zero(Math.floor((dur / 86400))) : "00";
										//月份，以实际平均每月秒数计算
										//pms.month = Math.floor((dur / 2629744)) > 0 ? f.zero(Math.floor((dur / 2629744)) % 12) : "00";
										//年份，按按回归年365天5时48分46秒算
										//pms.year = Math.floor((dur / 31556926)) > 0 ? Math.floor((dur / 31556926)) : "0";
									} else {
										pms.year = pms.month = pms.day = pms.hour = pms.mini = pms.sec = "00";
										pms.hm = "000";
										//alert('结束了');
										return;
									}
									return pms;
								},
								ui: function() {
									if (o.hm) {
										o.hm.html(f.dv().hm);
									}
									if (o.sec) {
										o.sec.html(f.dv().sec);
									}
									if (o.mini) {
										o.mini.html(f.dv().mini);
									}
									if (o.hour) {
										o.hour.html(f.dv().hour);
									}
									if (o.day) {
										o.day.html(f.dv().day);
									}
									if (o.month) {
										o.month.html(f.dv().month);
									}
									if (o.year) {
										o.year.html(f.dv().year);
									}
									setTimeout(f.ui, 1);
								}
							};
							f.ui();
						});
					}
				});
                $("#fnTimeCountDown").fnTimeCountDown("<?php echo $auction['end_time']; ?>");
			</script>

	</body>
</html>";s:12:"compile_time";i:1478592821;}";