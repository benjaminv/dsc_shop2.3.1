<?php exit;?>001478683287b77f23ead12127dc8cde5c68ad7e84b5s:2761:"a:2:{s:8:"template";s:2697:"<?php $__Template->display($this->getTpl("page_header")); ?>
<div class="con mb-7">
    <div class="my-admin-header-box">
        <div class="com-bg">
            <div class="com-header">
                <div class="margin-lr">
                    <div class="user-right-maxbox ">
                        <a href="<?php echo U('reply');?>">
                            <div <?php if($has_new) { ?>class="email-box"<?php } ?>></div>
                            <i class="iconfont icon-youxiang is-youxiang com-bs"></i>
                        </a>
                    </div>
                    </ul>
                </div>
                <div class="com-header-img ">
                    <a href="<?php echo U('user/profile/index');?>">
                        <div class="user-com-img-box"><img src="<?php echo $info['user_picture']; ?>"></div>
                    </a>
                    <div class="com-admin m-top08">
                        <h4><?php echo $info['username']; ?></h4>
                    </div>
                </div>
            </div>

        </div>

    </div>
    <section class="com-nav">
        <div class="user-nav-box  com-list dis-box text-center distypeclass">
            <div class="box-flex oncle-color">
                <a distype="3"><h4>圈子贴</h4>
                    <p class="t-remark4"><?php echo $type3_num; ?></p></a>
            </div>
            <div class="oncle-color box-flex">
                <a distype="2"><h4>问答贴</h4>
                    <p class="t-remark4"><?php echo $type2_num; ?></p></a>
            </div>
            <div class="oncle-color box-flex">
                <a distype="1"><h4>提问贴</h4>
                    <p class="t-remark4"><?php echo $type1_num; ?></p></a>
            </div>
            <div class="oncle-color box-flex">
                <a distype="4"><h4>晒单贴</h4>
                    <p class="t-remark4"><?php echo $type4_num; ?></p></a>
            </div>
        </div>
    </section>
<?php $__Template->display($this->getTpl("tzlist")); ?>
<script>
    /*店铺信息商品滚动*/
    var swiper = new Swiper('.j-g-s-p-con', {
        scrollbarHide: true,
        slidesPerView: 'auto',
        centeredSlides: false,
        grabCursor: true
    });
    //异步数据
    var url = "<?php echo U('my');?>";
    var infinite = $('.community-list').infinite({url: url, template: 'j-product'});
    $('.distypeclass a').on('click', function (e) {
        var distype = $(this).attr('distype');
        $(this).siblings().removeClass("active");
        infinite.onload('dis_type=' + distype);
    })
</script>
</body>

</html>";s:12:"compile_time";i:1478596887;}";